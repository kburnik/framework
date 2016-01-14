<?php
/*
This class represents a utility for writing output-generating templates.

The concept for using this class is for a user to input a template and
some data (e.g. an array). The output is a serialized representation of the
data described by the provided template.

Example code:
  $tpl = '$[; ]([*]){Item #[#]: [*]}';
  $data = array('foo', 'bar', 'baz');
  var_export(produce($tpl, $data));

Output:
  "Item #0: foo; Item #1: bar; Item #2: baz"

The template compiler is implemented as an extended variation of a
pushdown automaton (PDA) which includes a buffer, a loop scope stack and an
auxiliary state vector (e.g. variables which help produce the PHP code).

The machine reads each input character, the current machine state and the
top of the state stack and uses that triplet to transit to the next state
while updating the internal state and producing output (PHP code).

               +---------------------------+
 input_char -> |  * state                  | -> state_change
      state -> |  * stack                  | -> stack_change
stack_state -> |  * buffer                 | -> buffer_change
               |  * auxiliary state vector |    (+ aux_state_vector_change
               |  * code                   |     + code_chunk)
               |                           | -> scope_change
               |                           | -> code_chunk
               +---------------------------+

The transition table is somewhat implicit in regards to locating a match
for the transition input. That means we can match any input char if we don't
find an explicit one (notice the null key in the transition array below).

Same goes for the current machine state and top of the stack. A null key
in the transition table implies that it matches any char/state/stack state if
a previous explicit one was not found. This allows us to have a more sparse
transition table.

The entry in the transition table is a description of the
overall state change in regards to 4 side effects, achieved in order:
 1) Change of machine state and the stack
 2) Change of the buffer
 3) Change of the loop scope
 4) Change of the output code

While transitioning, the machine must end up in an explicit next state, this
can either be a new state or the current one, but it's always explicitly
defined.

Optionally, a transition can make changes to the machine stack:
 1) Pop number of states or pop a single state if it matches a specified one
 2) Push a new state

Most of the functionality is achieved by flushing the buffer on a particular
transition. In some cases this results in concatenating produced code while in
other cases we can just disregard the buffer contents (e.g. comments).

The buffer is used in the following regards (each optional for a transition):
 1) Trim a number of characters from the end of the buffer.
 2) Append input char before flush (mutually exclusive with 4)
 3) Process buffer value (optional) and flush the buffer
 4) Append input char after flush (mutually exclusive with 2)

A transition can also optionally make changes to the loop scope. This means
it can either enter a new scope or exit the current one. The change of the
scope implies that the context of an expression (e.g. [*]) also changes.

Other than producing code via flushing the buffer with a specified method,
the transition can explicitly append PHP code as the last step of the
transition.

For any valid template, the machine should end in a reset state and produce
a valid PHP code which serializes that template. This, however, is currently
not true for the branching scope (e.g. $?(anything_goes){}).
*/
class Tpl {
  //
  // MACHINE STATES.
  //

  // This is the starting state for the machine
  const STATE_IN_FREE_TEXT = 'STATE_IN_FREE_TEXT';

  // Expecting a loop or if clause.
  const STATE_EXPECT_CLAUSE = 'STATE_EXPECT_CLAUSE';

  // Expecting a left paren for condition.
  const STATE_EXPECT_CONDITION = 'STATE_EXPECT_CONDITION';

  // Expecting definition of the loop scope.
  const STATE_IN_LOOP_SCOPE = 'STATE_IN_LOOP_SCOPE';

  // Expecting the body char '{'.
  const STATE_EXPECT_BODY = 'STATE_EXPECT_BODY';

  // In loop, if or else body.
  const STATE_IN_BODY = 'STATE_IN_BODY';

  // A key/value or other expression is getting collected.
  const STATE_in_brackets = 'STATE_in_brackets';

  // A branching condition is being collected.
  const STATE_IN_BRANCH_SCOPE = 'STATE_IN_BRANCH_SCOPE';

  // Waiting for '>' to confirm '$<>' for start of literal block.
  const STATE_EXPECT_LITERAL_BLOCK_START = 'STATE_EXPECT_LITERAL_BLOCK_START';

  // Waiting for '>' to confirm '$<>' for end of literal block.
  const STATE_EXPECT_LITERAL_BLOCK_END = 'STATE_EXPECT_LITERAL_BLOCK_END';

  // Collecting literals between '$<>' and '$<>'.
  const STATE_IN_LITERAL_BLOCK = 'STATE_IN_LITERAL_BLOCK';

  // Collecting literals for a delimiter.
  const STATE_IN_DELIMITER = 'STATE_IN_DELIMITER';

  // Expecting a comment to start. Encountered '$/'.
  const STATE_EXPECT_COMMENT_BLOCK_START = 'STATE_EXPECT_COMMENT_BLOCK_START';

  // Expecting a comment to end. Encountered '*'.
  const STATE_EXPECT_COMMENT_BLOCK_END = 'STATE_EXPECT_COMMENT_BLOCK_END';

  // In a comment.
  const STATE_IN_COMMENT_BLOCK = 'STATE_IN_COMMENT_BLOCK';

  // Expect an escapable char after '\'. E.g.: '\$' or '\['.
  const STATE_EXPECT_ESCAPABLE_CHAR = 'STATE_EXPECT_ESCAPABLE_CHAR';

  //
  // STACK STATES.
  //

  // A loop was started.
  const STACK_STATE_LOOP = 'STACK_STATE_LOOP';

  // An if branch was started.
  const STACK_STATE_BRANCH = 'STACK_STATE_BRANCH';

  // An else branch may occur on next char.
  const STACK_STATE_EXPECT_ELSE_BRANCH = 'STACK_STATE_EXPECT_ELSE_BRANCH';

  // An else branch was started.
  const STACK_STATE_IN_ELSE_BRANCH = 'STACK_STATE_IN_ELSE_BRANCH';

  // Expect </> after '$' to close the literal block sequence.
  const STACK_STATE_EXPECT_LITERAL_BLOCK_END =
    'STACK_STATE_EXPECT_LITERAL_BLOCK_END';

  // A brace is on the stack while reading free text. E.g.: '${{[*]}}'
  const STACK_STATE_BRACE = 'STACK_STATE_BRACE';

  //
  // EXCEPTION CODES.
  //

  // Errors which occur during compilation.
  // This usually occurs before final validation.
  const EXCEPTION_CODE_COMPILE_ERROR = 1;

  // Errors identified after code was produced.
  const EXCEPTION_CODE_VALIDATION_ERROR = 2;


  //
  // CURRENT VALUES.
  //

  // The input template to be compiled.
  private $template;

  // Current char index of the template.
  private $char_index;

  // The current state of the machine.
  private $state;

  // The stack of the machine.
  private $stack;

  // Current value of the buffer.
  private $buffer;

  // Current scope value reference (e.g. $data).
  private $scope_value;

  // Scope stack for entering/exiting scopes.
  private $scope_stack;

  // Delimiter for current scope.
  private $scope_delimiter;

  // A branching condition.
  private $condition;

  // Code generated by the template.
  private $code;

  // Output details when compiling.
  private $do_verbose;

  // Reference to the IFileSystem object.
  private $file_system;

  // Maps the transitions: transitions[input_char][state][stack_state].
  // The map returns the transition description which is evaluated in the
  // following order:
  //
  // (I) Update machine internal state and stack state.
  //   1. state: The new state of the machine.
  //   2. stack_pop: Whether to pop of the stack.
  //                   a) Set to an integer to count how many to pop.
  //                   b) Set to a STACK_STATE value if only that match should
  //                      be removed when present on top of stack.
  //   3. stack_push: A STACK_STATE value to push to the stack.
  //
  // (II) Handle the buffer.
  //   4. trim_buffer: Number of chars to remove from end of the buffer.
  //                   This occurs prior to flushing the buffer.
  //   5. precollect: Whether to immediately append to the buffer after entering
  //                  the state.
  //   6. flush: Whether to flush the buffer when entered this state:
  //              a) Set to true if only needs to be flushed (disregarded).
  //              b) Set to a private flush_ method to use the buffer value and
  //                 then empty the buffer.
  //   7. collect: Whether to buffer the input char before reading the next one.
  //
  // (III) Handle the looping scope.
  //   8. enter_scope: Whether this state causes entering to a new scope.
  //   9. exit_scope: Whether this state causes exiting the current scope.
  //
  // (IV) Output generated code.
  //   10. code: The output code to generate by entering the state.
  private $transitions = array(
    '$' => array(
      Tpl::STATE_EXPECT_LITERAL_BLOCK_END => array(
        Tpl::STACK_STATE_EXPECT_LITERAL_BLOCK_END =>
          array('state' => Tpl::STATE_IN_LITERAL_BLOCK,
                'collect' => true)
      ),
      Tpl::STATE_IN_LITERAL_BLOCK => array(
        null => array('state' => Tpl::STATE_IN_LITERAL_BLOCK,
                      'stack_pop' => Tpl::STACK_STATE_EXPECT_LITERAL_BLOCK_END,
                      'stack_push' => Tpl::STACK_STATE_EXPECT_LITERAL_BLOCK_END,
                      'collect' => true)
      ),
      Tpl::STATE_IN_FREE_TEXT => array(
        Tpl::STACK_STATE_EXPECT_ELSE_BRANCH =>
          array('state' => Tpl::STATE_EXPECT_CLAUSE,
                'stack_pop' => 1,
                'flush' => 'flush_append_literal'),
        Tpl::STACK_STATE_BRACE =>
          array('state' => Tpl::STATE_EXPECT_CLAUSE,
                'stack_pop' => array(Tpl::STACK_STATE_BRACE),
                'flush' => 'flush_append_literal'),
        null => array('state' => Tpl::STATE_EXPECT_CLAUSE,
                      'flush' => 'flush_append_literal'),
      ),
      Tpl::STATE_EXPECT_ESCAPABLE_CHAR => array(
        null => array('state' => Tpl::STATE_IN_FREE_TEXT,
                      'trim_buffer' => 1,
                      'precollect' => true,
                      'flush' => 'flush_append_literal')
      ),
      Tpl::STATE_EXPECT_CLAUSE => array(
        null => array('state' => Tpl::STATE_EXPECT_CLAUSE,
                      'flush' => 'flush_append_dollar')
      ),
    ),
    '?' => array(
      Tpl::STATE_EXPECT_CLAUSE => array(
        null => array('state' => Tpl::STATE_EXPECT_CONDITION)
      )
    ),
    '[' => array(
      Tpl::STATE_IN_FREE_TEXT => array(
        null =>
          array('state' => Tpl::STATE_in_brackets,
                'collect' => true,
                'flush' => 'flush_append_literal')
      ),
      Tpl::STATE_EXPECT_CLAUSE => array(
        null => array('state' => Tpl::STATE_IN_DELIMITER)
      ),
      Tpl::STATE_EXPECT_ESCAPABLE_CHAR => array(
        null => array('state' => Tpl::STATE_IN_FREE_TEXT,
                      'trim_buffer' => 1,
                      'precollect' => true,
                      'flush' => 'flush_append_literal')
      )
    ),
    ']' => array(
      Tpl::STATE_in_brackets => array(
        null => array('state' => Tpl::STATE_IN_FREE_TEXT,
                      'precollect' => true,
                      'flush' => 'flush_append_expression')
      ),
      Tpl::STATE_IN_DELIMITER => array(
        null => array('state' => Tpl::STATE_EXPECT_CLAUSE,
                      'flush' => 'flush_set_delimiter')
      ),
      Tpl::STATE_EXPECT_ESCAPABLE_CHAR => array(
        null => array('state' => Tpl::STATE_IN_FREE_TEXT,
                      'trim_buffer' => 1,
                      'precollect' => true,
                      'flush' => 'flush_append_literal')
      )
    ),
    '(' => array(
      Tpl::STATE_EXPECT_CLAUSE => array(
        null => array('state' => Tpl::STATE_IN_LOOP_SCOPE)
      ),
      Tpl::STATE_EXPECT_CONDITION => array(
        null => array('state' => Tpl::STATE_IN_BRANCH_SCOPE)
      )
    ),
    ')' => array(
      Tpl::STATE_IN_LOOP_SCOPE => array(
        null => array('state' => Tpl::STATE_EXPECT_BODY,
                      'stack_push' => Tpl::STACK_STATE_LOOP,
                      'flush' => 'flush_set_scope',
                      'enter_scope' => true)
      ),
      Tpl::STATE_IN_BRANCH_SCOPE => array(
        null => array('state' => Tpl::STATE_EXPECT_BODY,
                      'stack_push' => Tpl::STACK_STATE_BRANCH,
                      'flush' => 'flush_set_condition',
                      'code' => 'if (__condition__) {')
      ),
    ),
    '{' => array(
      Tpl::STATE_EXPECT_BODY => array(
        null => array('state' => Tpl::STATE_IN_FREE_TEXT)
      ),
      Tpl::STATE_EXPECT_CLAUSE => array(
        null => array('state' => Tpl::STATE_IN_FREE_TEXT,
                      'stack_push' => Tpl::STACK_STATE_LOOP,
                      'flush' => 'flush_set_scope',
                      'enter_scope' => true)
      ),
      Tpl::STATE_EXPECT_ESCAPABLE_CHAR => array(
        null => array('state' => Tpl::STATE_IN_FREE_TEXT,
                      'trim_buffer' => 1,
                      'precollect' => true,
                      'flush' => 'flush_append_literal')
      ),
      Tpl::STATE_IN_FREE_TEXT => array(
        Tpl::STACK_STATE_EXPECT_ELSE_BRANCH =>
          array('state' => Tpl::STATE_IN_FREE_TEXT,
                'stack_pop' => 1,
                'stack_push' => Tpl::STACK_STATE_IN_ELSE_BRANCH,
                'code' => ' else {'),
         // Allow for '{}'.
         null => array('state' => Tpl::STATE_IN_FREE_TEXT,
                       'stack_push' => Tpl::STACK_STATE_BRACE,
                       'collect' => true)
      ),
    ),
    '}' => array(
      // This is a copy from below (null, STATE_IN_LITERAL_BLOCK, null).
      // Because '}' can be matched for any state.
      Tpl::STATE_IN_LITERAL_BLOCK => array(
        null => array('state' => Tpl::STATE_IN_LITERAL_BLOCK,
                      'stack_pop' => Tpl::STACK_STATE_EXPECT_LITERAL_BLOCK_END,
                      'collect' => true)
      ),
      // This is a copy from below (null, STATE_IN_COMMENT_BLOCK, null).
      // Because '}' can be matched for any state.
      Tpl::STATE_IN_COMMENT_BLOCK => array(
        null => array('state' => Tpl::STATE_IN_COMMENT_BLOCK)
      ),
      // This is a copy from below (null, STATE_EXPECT_ESCAPABLE_CHAR, null).
      // Because '}' can be matched for any state.
      Tpl::STATE_EXPECT_ESCAPABLE_CHAR => array(
        null => array('state' => Tpl::STATE_IN_FREE_TEXT,
                      'trim_buffer' => 1,
                      'precollect' => true,
                      'flush' => 'flush_append_literal')
      ),
      // All other cases for '}' are covered by any machine state, but depend
      // on the stack state.
      null => array(
        Tpl::STACK_STATE_LOOP =>
          array('state' => Tpl::STATE_IN_FREE_TEXT,
                'stack_pop' => 1,
                'flush' => 'flush_append_literal',
                'exit_scope' => true,
                'code' => '}'),
        Tpl::STACK_STATE_BRANCH =>
          array('state' => Tpl::STATE_IN_FREE_TEXT,
                'stack_pop' => 1,
                'stack_push' => Tpl::STACK_STATE_EXPECT_ELSE_BRANCH,
                'flush' => 'flush_append_literal',
                'code' => '}'),
        Tpl::STACK_STATE_IN_ELSE_BRANCH =>
          array('state' => Tpl::STATE_IN_FREE_TEXT,
                'stack_pop' => 1,
                'flush' => 'flush_append_literal',
                'code' => '}'),
        // This covers no-else condition when ending the loop: ${$?(...){...}}.
        Tpl::STACK_STATE_EXPECT_ELSE_BRANCH =>
          array('state' => Tpl::STATE_IN_FREE_TEXT,
                'stack_pop' => 2,
                'flush' => 'flush_append_literal',
                'exit_scope' => true,
                'code' => '}'),
        Tpl::STACK_STATE_BRACE =>
          array('state' => Tpl::STATE_IN_FREE_TEXT,
                'stack_pop' => 1,
                'flush' => 'flush_append_literal',
                'collect' => true),
        // Allow for '{}'.
        null => array('state' => Tpl::STATE_IN_FREE_TEXT,
                      'collect' => true)
      )
    ),
    '<' => array(
      Tpl::STATE_EXPECT_CLAUSE => array(
        null => array('state' => Tpl::STATE_EXPECT_LITERAL_BLOCK_START)
      ),
      Tpl::STATE_IN_LITERAL_BLOCK => array(
        Tpl::STACK_STATE_EXPECT_LITERAL_BLOCK_END =>
          array('state' => Tpl::STATE_EXPECT_LITERAL_BLOCK_END,
                'collect' => true),
        null => array('state' => TPL::STATE_IN_LITERAL_BLOCK,
                      'stack_pop' => Tpl::STACK_STATE_EXPECT_LITERAL_BLOCK_END,
                      'collect' => true)
      )
    ),
    '>' => array(
      Tpl::STATE_EXPECT_LITERAL_BLOCK_START => array(
        null => array('state' => Tpl::STATE_IN_LITERAL_BLOCK,
                      'collect' => false)
      ),
      Tpl::STATE_EXPECT_LITERAL_BLOCK_END => array(
        null => array('state' => Tpl::STATE_IN_FREE_TEXT,
                      'stack_pop' => Tpl::STACK_STATE_EXPECT_LITERAL_BLOCK_END,
                      'trim_buffer' => 2, // Remove '$<' from buffer.
                      'flush' => 'flush_append_literal')
      ),
    ),
    '/' => array(
      // $/
      Tpl::STATE_EXPECT_CLAUSE => array(
        null => array('state' => Tpl::STATE_EXPECT_COMMENT_BLOCK_START)
      ),
      // $/* ... */
      Tpl::STATE_EXPECT_COMMENT_BLOCK_END => array(
        null => array('state' => Tpl::STATE_IN_FREE_TEXT)
      )
    ),
    '*' => array(
      // $/*
      Tpl::STATE_EXPECT_COMMENT_BLOCK_START => array(
        null => array('state' => Tpl::STATE_IN_COMMENT_BLOCK)
      ),
      // $/* ... *
      Tpl::STATE_IN_COMMENT_BLOCK => array(
        null => array('state' => Tpl::STATE_EXPECT_COMMENT_BLOCK_END)
      ),
      // $/***/
      Tpl::STATE_EXPECT_COMMENT_BLOCK_END => array(
        null => array('state' => Tpl::STATE_EXPECT_COMMENT_BLOCK_END)
      ),
    ),
    '\\' => array(
      Tpl::STATE_IN_FREE_TEXT => array(
        null => array('state' => Tpl::STATE_EXPECT_ESCAPABLE_CHAR,
                      'flush' => 'flush_append_literal')
      ),
      Tpl::STATE_EXPECT_ESCAPABLE_CHAR => array(
        null => array('state' => Tpl::STATE_IN_FREE_TEXT,
                      'flush' => 'flush_append_backslash')
      )
    ),
    null => array(
      Tpl::STATE_EXPECT_BODY => array(
          null => array('state' => Tpl::STATE_EXPECT_BODY)
      ),
      // $/* ... *...
      Tpl::STATE_EXPECT_COMMENT_BLOCK_END => array(
        null => array('state' => Tpl::STATE_IN_COMMENT_BLOCK)
      ),
      // $/* ...
      Tpl::STATE_IN_COMMENT_BLOCK => array(
        null => array('state' => Tpl::STATE_IN_COMMENT_BLOCK)
      ),
      Tpl::STATE_in_brackets => array(
        null => array('state' => Tpl::STATE_in_brackets,
                      'collect' => true)
      ),
      Tpl::STATE_IN_LOOP_SCOPE => array(
        null => array('state' => Tpl::STATE_IN_LOOP_SCOPE,
                      'collect' => true)
      ),
      Tpl::STATE_IN_BRANCH_SCOPE => array(
        null => array('state' => Tpl::STATE_IN_BRANCH_SCOPE,
                      'collect' => true)
      ),
      // Removes the anticipated else.
      Tpl::STATE_IN_FREE_TEXT => array(
        null => array('state' => Tpl::STATE_IN_FREE_TEXT,
                      'stack_pop' => Tpl::STACK_STATE_EXPECT_ELSE_BRANCH,
                      'collect' => true)
      ),
      Tpl::STATE_IN_LITERAL_BLOCK => array(
        null => array('state' => Tpl::STATE_IN_LITERAL_BLOCK,
                      'stack_pop' => Tpl::STACK_STATE_EXPECT_LITERAL_BLOCK_END,
                      'collect' => true)
      ),
      Tpl::STATE_IN_DELIMITER => array(
        null => array('state' => Tpl::STATE_IN_DELIMITER,
                      'collect' => true)
      ),
      Tpl::STATE_EXPECT_CLAUSE => array(
        null => array('state' => Tpl::STATE_IN_FREE_TEXT,
                      'flush' => 'flush_append_dollar',
                      'collect' => true)
      ),
      Tpl::STATE_EXPECT_ESCAPABLE_CHAR => array(
        null => array('state' => Tpl::STATE_IN_FREE_TEXT,
                      'flush' => 'flush_append_backslash',
                      'collect' => true)
      ),
    )
  );

  public function __construct($do_verbose = false, $file_system = null) {
    $this->do_verbose = $do_verbose;
    $this->file_system =
        ($file_system != null) ? $file_system : new FileSystem();
  }

  public function produce($template,
                          $data,
                          $use_cache = true,
                          $do_warn = true,
                          $do_validate = true) {
    if (!is_string($template))
      return $template;

    $tpl_function = "tpl_" . md5($template);

    if ($use_cache) {
      $tpl_file = Project::GetProjectDir("/gen/template/" .
                                         $tpl_function . ".php");
    }

    if ($use_cache && function_exists($tpl_function)) {
      return $tpl_function($data);
    } else if ($use_cache && $this->file_system->file_exists($tpl_file) ) {
      include_once($tpl_file);

      return $tpl_function($data);
    } else {
      $code = $this->compile($template, $do_warn, $do_validate);
      eval($code);
      if ($use_cache) {
        $function =
            "function {$tpl_function}(\$data=array()) {{$code} return \$x;}";
        $this->file_system->file_put_contents($tpl_file, "<?php\n{$function}");
      }

      return $x;
    }
  }

  public function compile($template, $do_warn = true, $do_validate = true) {
    $this->reset($template);

    $transition_vars =
        array('state', 'stack_pop', 'stack_push',
              'trim_buffer', 'precollect', 'flush', 'collect',
              'enter_scope', 'exit_scope',
              'code');

    $transition_var_map =
        array_combine($transition_vars,
                      array_fill(0, count($transition_vars), null));

    while (($input_char = $this->read()) !== null) {
      $transition = $this->transit($input_char,
                                   $this->state,
                                   end($this->stack));

      $transition_values =
          array_pick(array_merge($transition_var_map, $transition),
                     $transition_vars);

      extract($transition_values);

      // Check for rules.
      $this->assert($state != null, "Unexpected: state is null.");
      $this->assert(!($enter_scope && $exit_scope),
                    "Internal error: " .
                    "Enter and exit scope must be mutually exclusive.");
      $this->assert(!($precollect && $collect),
                    "Internal error: " .
                    "Precollect and collect must be mutually exclusive.");

      $this->verbose("TR: {$this->state} -> {$state}\n");

      //
      // (I) Update machine internals.
      //

      // 1. Change state.
      $this->state = $state;

      // 2. Pop the stack.
      if ($stack_pop !== null && end($this->stack) == $stack_pop) {
        array_pop($this->stack);
      } else if (intval($stack_pop) > 0) {
        for ($i=0; $i < intval($stack_pop); $i++)
          array_pop($this->stack);
      }

      // 3. Push to the stack.
      if ($stack_push)
        $this->stack[] = $stack_push;

      //
      // (II) Handle the buffer.
      //

      // 4. Trim end of buffer.
      if ($trim_buffer > 0)
        $this->buffer = substr($this->buffer, 0, -$trim_buffer);

      // 5. Append to buffer before flushing.
      if ($precollect)
        $this->buffer .= $input_char;

      // 6. Flush the buffer and optionally use the value.
      if ($flush) {
        $this->verbose("Flushing: {$this->buffer}\n");

        if (method_exists($this, $flush))
          $this->$flush($this->buffer);

        $this->buffer = "";
      }

      // 7. Append to buffer after flushing.
      if ($collect)
        $this->buffer .= $input_char;

      //
      // (III) Update loop scope.
      //

      // 8. Enter scope.
      if ($enter_scope) {
        $this->verbose("Entering scope\n");
        $this->scope_stack[] = array($this->currentValueName(),
                                     $this->currentKeyName());
      }

      // 9. Exit scope.
      if ($exit_scope) {
        $this->verbose("Exiting scope\n");
        array_pop($this->scope_stack);
        $this->scope_value = reset(end($this->scope_stack));
        $this->scope_delimiter = '';
      }

      //
      // (IV) Output resulting code.
      //

      // 10. Output code.
      if ($code)
        $this->code .= $this->expandCode($code);

      // 11. Reset stuff.
      $this->condition = null;
    }

    // Assert machine state: Most should be reset.
    if ($do_warn) {
      $this->assert($this->state == Tpl::STATE_EXPECT_CLAUSE,
                    "Machine ended in invalid state: " .
                    $this->state);
      $this->assert($this->stack == array(null),
                    "Machine statck did not empty." .
                    var_export($this->stack, true));
      $this->assert($this->char_index == strlen($this->template),
                    "Machine did not reach end of input. Index = " .
                    $this->char_index);
      $this->assert($this->buffer == "",
                    "Machine buffer did not empty.");
      $this->assert($this->scope_stack == array(array('$data', '$key')),
                    "Machine scope stack did not empty.");
      $this->assert($this->scope_value == '$data',
                    "Machine scope value was not reset: " . $this->scope_value);
      $this->assert($this->condition == null,
                    "Machine condition was not reset.");
      $this->assert($this->scope_delimiter == '',
                    "Machine scope delimiter was not reset.");
    }

    // Validate produced code via PHP.
    if ($do_validate)
      $this->validateCode($this->code);

    return $this->code;
  }

  private function assert($condition, $message = "") {
    if (!$condition) {
      throw new Exception("Compile error: " . $message,
                          Tpl::EXCEPTION_CODE_COMPILE_ERROR);
    }
  }

  private function validateCode($code) {
    assert(defined('PHP_BINARY'), 'PHP_BINARY is not defined');
    assert(file_exists(PHP_BINARY));

    $temp_code_file = tempnam(sys_get_temp_dir(), 'tpl');
    $temp_out_file = tempnam(sys_get_temp_dir(), 'out');

    $code = '<?php' . "\n$code\n";
    file_put_contents($temp_code_file, $code);

    $cmd = PHP_BINARY .
           " -l " . escapeshellarg($temp_code_file) .
           " 2>&1 >" . escapeshellarg($temp_out_file);
    $retval = null;

    system($cmd, $retval);

    $err = file_get_contents($temp_out_file);

    unlink($temp_code_file);
    unlink($temp_out_file);

    if ($retval != 0) {
      $err .= "\n" . $code;

      throw new Exception($err, Tpl::EXCEPTION_CODE_VALIDATION_ERROR);
    }
  }

  private function reset($template) {
    $this->state = Tpl::STATE_IN_FREE_TEXT;
    $this->stack = array(null);
    $this->char_index = 0;
    $this->buffer = "";
    $this->template = $template . '$';
    $this->code = "";
    $this->scope_stack = array(array('$data', '$key'));
    $this->scope_value = '$data';
    $this->scope_delimiter = '';
    $this->condition = null;
  }

  // Read single template char, increment internal index by 1.
  // Returns null when passed the template length.
  private function read() {
    if ($this->char_index >= strlen($this->template))
      return null;

    return $this->template[$this->char_index++];
  }

  private function currentKeyName() {
    return '$k' . (count($this->scope_stack) - 1);
  }

  private function currentValueName() {
    return '$v' . (count($this->scope_stack) - 1);
  }

  private function expandCode($code_template) {
    return strtr(strtr($code_template, array(
        '__delimiter_pre_code__' =>
            ($this->scope_delimiter) ?
            '$s=__scope__; reset($s); $f=key($s);'
            : '',
        '__delimiter_code__' =>
            ($this->scope_delimiter) ?
              'if (__key__!=$f)' .
              ' $x.=__delimiter__; '
              : ''
      )), array(
        '__scope__' => $this->scope_value,
        '__condition__' => $this->condition,
        '__delimiter__' => var_export($this->scope_delimiter, true),
        '__key__' => $this->currentKeyName(),
        '__value__' => $this->currentValueName(),
      ));
  }

  private function findTransition($input_char, $state, $stack_state) {
    $this->verbose("transit" . json_encode(func_get_args()) . "\n");

    if (!array_key_exists($input_char, $this->transitions))
      $input_char = null;

    $candidate_states = $this->transitions[$input_char];

    if (!array_key_exists($state, $candidate_states)) {
      if (!array_key_exists(null, $candidate_states)){
        $input_char = null;
        $candidate_states = $this->transitions[null];
      } else {
        $state = null;
      }
    }

    if (!array_key_exists($state, $candidate_states))
      return null;

    $candidate_stack_states = $candidate_states[$state];

    if (!array_key_exists($stack_state, $candidate_stack_states))
      $stack_state = null;

    return $candidate_stack_states[$stack_state];
  }

  private function transit($input_char, $state, $stack_state) {
    $transition = $this->findTransition($input_char,
                                        $state,
                                        $stack_state);

    if ($transition == null)
      throw new Exception(
        "Template error at char index {$this->char_index}! Near " .
        var_export(substr($this->template, $this->char_index - 50, 100), true) .
        "\nNo transition found. ($input_char, $state, $stack_state);",
        Tpl::EXCEPTION_CODE_COMPILE_ERROR);

    return $transition;
  }

  private function flush_set_scope($buffer) {
    // If scope is not explicitly set, assume current scope value is used.
    if ($buffer == null) {
      $this->scope_value = reset(end($this->scope_stack));
    } else {
      $this->scope_value = $this->resolveExpression($buffer);
    }

    $code_template = '__delimiter_pre_code__' .
                     'if (__scope__) ' .
                        'foreach (__scope__ as __key__ => __value__) {' .
                          '__delimiter_code__';

    $this->code .= $this->expandCode($code_template);
  }

  private function flush_set_delimiter($buffer) {
    $this->scope_delimiter = $buffer;
  }

  private function flush_set_condition($buffer) {
    $this->condition = $this->resolveExpression($buffer, true);
  }

  private function flush_append_expression($buffer) {
    $expression_code = $this->resolveExpression($buffer);
    $this->code .= '$x.=' . $expression_code . ';';
  }

  private function flush_append_literal($buffer) {
    if (strlen($buffer) > 0)
      $this->code .= '$x.=' . var_export($buffer, true) . ';';
  }

  // TODO(kburnik): These methods should be compacted to a single one.
  // Alternative would be to collect and flush when reading '$'.
  private function flush_append_dollar($buffer) {
    $this->flush_append_literal('$');
  }

  private function flush_append_backslash($buffer) {
    $this->flush_append_literal('\\');
  }
  //

  private function verbose($mixed) {
    if ($this->do_verbose)
      print_r($mixed);
  }

  // TODO(kburnik): This entire method needs rewriting.
  private function resolveExpression($expression) {
    $this->verbose("Resolving expression: $expression\n");

    $buffer = "";
    $bracket_buffer = "";
    $length = strlen($expression);
    $in_brackets = false;

    for ($i = 0; $i < $length; $i++) {
      $input_char = substr($expression, $i, 1);
      if ($input_char == '[') {
        $in_brackets = true;
      } else if ($input_char == ']') {
        $bracket_buffer .= $input_char;
        $buffer .= $this->resolveBracketExpression($bracket_buffer);
        $bracket_buffer = "";
        $in_brackets = false;
        $input_char = '';
      } else {
      }

      if ($in_brackets)
        $bracket_buffer .= $input_char;
      else
        $buffer .= $input_char;
    }

    $this->verbose("Resolved: $buffer\n");

    return $buffer;
  }

  // TODO(kburnik): This entire method needs rewriting.
  private function resolveBracketExpression($buffer) {
    $scope = $this->scope_stack;

    $var = substr(trim($buffer), 1, -1);

    $varname = "";
    if ($var[0] == "'") {
      $varname = substr($var, 0, strpos($var, "'", 1) + 1);
      $var = substr($var, strlen($varname));
      $index = 1;
    }

    $or_vector = explode("|",$var);
    $var = reset($or_vector);
    array_shift($or_vector);
    $or = array_pop($or_vector);

    $trans_vector =
      explode(":", str_replace('::', '<?/*DOUBLE_SEMICOLON*/?>', $var));
    $var = array_shift($trans_vector);

    if ($var[0] == '@')
      return "constant('" . substr($var, 1) . "')";

    $c = count($scope) - 1;
    $var = explode(".", $var);
    $key_or_val = 0;

    foreach ($var as $key => $part) {
      switch($part) {
        case '*': // current context value operator
          break;
        case '**': // parent context value operator
          $c--;
          break;
        case "#": // current context key
          $key_or_val = 1;
          break;
        case "#+": // current context key + 1
          $key_or_val = 1;
          $prefix = "";
          $sufix = "+1";
          break;
        case "!#": // current context reverse order key
          $prefix = "count(";
          $sufix = ")-".$scope[$c][1]."-1";
          $c--;
          break;
        case "!#+": // current context reverse order key + 1
          $prefix = "count(";
          $sufix = ")-".$scope[$c][1];
          $c--;
          break;
        case "~": // number of elements (count)
          $prefix = "count(";
          $sufix = ")";
        break;
        case "#%2": // index mod 2 operator
          $prefix = "";
          $sufix ="%2";
          $key_or_val = 1;
          break;
        case "#+%2": // index+1 mod 2 operator
          $prefix = "(";
          $sufix ="+1)%2";
          $key_or_val = 1;
          break;
        case "#last": // output last if last element, otherwise output middle
          $prefix = "((end(array_keys(" . $scope[$c-1][0] . ")) == ";
          $sufix = ") ? 'last' : 'middle' )";
          $key_or_val = 1;
          break;
        default:
          if ($part != '')
            $rest.="['{$part}']";
      }

      $index++;
    }

    if ($var == '') {
      $varname = "";
    } else if ($varname == '') {
      $varname = $scope[$c][$key_or_val] . $rest;
    }

    if ($or != '') {
      // escaping
      $or = str_replace("\\" , "\\\\", $or);
      $or = str_replace("'" , "\\'", $or);
      $prefix = "($varname == null) ? '{$or}' : " . $prefix;
    }

    $ctv = count($trans_vector);
    if ($ctv > 0) {
      $prefix .= str_replace('<?/*DOUBLE_SEMICOLON*/?>',
                             '::',
                             implode("(", array_reverse($trans_vector)) . "(");
      $sufix .= str_repeat(")", $ctv);
    }

    return $prefix . $varname . $sufix;
  }
}
