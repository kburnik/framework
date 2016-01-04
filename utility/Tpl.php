<?php

class Tpl {
  //
  // MACHINE STATES.
  //

  // This is the starting state for the machine
  const STATE_IN_FREE_TEXT = 'STATE_IN_FREE_TEXT';

  // Error in the template syntax has occurred.
  const STATE_INVALID = 'STATE_INVALID';

  // Expecting a loop or if clause.
  const STATE_CLAUSE = 'STATE_CLAUSE';

  // Expecting a left paren for condition.
  const STATE_EXPECT_CONDITION = 'STATE_EXPECT_CONDITION';

  // Expecting definition of the loop scope.
  const STATE_IN_LOOP_SCOPE = 'STATE_IN_LOOP_SCOPE';

  // Expecting the body char '{'.
  const STATE_EXPECT_BODY = 'STATE_EXPECT_BODY';

  // In loop, if or else body.
  const STATE_IN_BODY = 'STATE_IN_BODY';

  // A key/value or other expression is getting collected.
  const STATE_EXPRESSION = 'STATE_EXPRESSION';

  // A branching condition is being collected.
  const STATE_IN_BRANCH_SCOPE = 'STATE_IN_BRANCH_SCOPE';

  // Waiting for '>' to confirm '$<>' for start of literal block.
  const STATE_EXPECT_LITERAL_BLOCK_TERMINAL =
      'STATE_EXPECT_LITERAL_BLOCK_TERMINAL';

  // Waiting for '>' to confirm '$<>' for end of literal block.
  const STATE_EXPECT_LITERAL_BLOCK_END = 'STATE_EXPECT_LITERAL_BLOCK_END';

  // Collecting literals between '$<>' and '$<>'.
  const STATE_IN_LITERAL_BLOCK = 'STATE_IN_LITERAL_BLOCK';

  // Collecting literals for a delimiter.
  const STATE_IN_DELIMITER = 'STATE_IN_DELIMITER';

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

  // Whether to output details when compiling.
  private $do_verbose;

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
  //   4. precollect: Whether to immediately append to the buffer after entering
  //                  the state.
  //   5. trim_buffer: Number of chars to remove from end of the buffer.
  //                   This occurs prior to flushing the buffer.
  //   6. flush: Whether to flush the buffer when entered this state:
  //              a) Set to true if only needs to be flushed (disregarded).
  //              b) Set to a private flush_ method to use the buffer value and
  //                 then empty the buffer.
  //   7. collect: Whether to buffer the input char before reading the next one.
  //
  // (III) Handle the looping scope.
  //   8. enter_scope: Whether this state enters a new scope.
  //   9. exit_scope: Whether this state exits the current scope.
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
        null => array('state' => Tpl::STATE_CLAUSE,
                      'stack_pop' => Tpl::STACK_STATE_EXPECT_ELSE_BRANCH,
                      'flush' => 'flush_append_literal')
      )
    ),
    '?' => array(
      Tpl::STATE_CLAUSE => array(
        null => array('state' => Tpl::STATE_EXPECT_CONDITION)
      )
    ),
    '[' => array(
      Tpl::STATE_IN_FREE_TEXT => array(
        null =>
          array('state' => Tpl::STATE_EXPRESSION,
                'collect' => true,
                'flush' => 'flush_append_literal')
      ),
      Tpl::STATE_CLAUSE => array(
        null => array('state' => Tpl::STATE_IN_DELIMITER)
      )
    ),
    ']' => array(
      Tpl::STATE_EXPRESSION => array(
        null => array('state' => Tpl::STATE_IN_FREE_TEXT,
                      'precollect' => true,
                      'flush' => 'flush_append_expression')
      ),
      Tpl::STATE_IN_DELIMITER => array(
        null => array('state' => Tpl::STATE_CLAUSE,
                      'flush' => 'flush_set_delimiter')
      )
    ),
    '(' => array(
      Tpl::STATE_CLAUSE => array(
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
      )
    ),
    '{' => array(
      Tpl::STATE_EXPECT_BODY => array(
        null => array('state' => Tpl::STATE_IN_FREE_TEXT)
      ),
      Tpl::STATE_CLAUSE => array(
        null => array('state' => Tpl::STATE_IN_FREE_TEXT,
                      'stack_push' => Tpl::STACK_STATE_LOOP,
                      'flush' => 'flush_set_scope',
                      'enter_scope' => true)
      ),
      Tpl::STATE_IN_FREE_TEXT => array(
        Tpl::STACK_STATE_EXPECT_ELSE_BRANCH =>
          array('state' => Tpl::STATE_IN_FREE_TEXT,
                'stack_pop' => 1,
                'stack_push' => Tpl::STACK_STATE_IN_ELSE_BRANCH,
                'code' => ' else {')
      ),
    ),
    '}' => array(
      // This is a copy from down bellow (null, STATE_IN_LITERAL_BLOCK, null).
      // Because '}' can be matched for any state.
      Tpl::STATE_IN_LITERAL_BLOCK => array(
        null => array('state' => Tpl::STATE_IN_LITERAL_BLOCK,
                      'stack_pop' => Tpl::STACK_STATE_EXPECT_LITERAL_BLOCK_END,
                      'collect' => true)
      ),
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
                'code' => '}')
      )
    ),
    '<' => array(
      Tpl::STATE_CLAUSE => array(
        null => array('state' => Tpl::STATE_EXPECT_LITERAL_BLOCK_TERMINAL)
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
      Tpl::STATE_EXPECT_LITERAL_BLOCK_TERMINAL => array(
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
    null => array(
      Tpl::STATE_EXPRESSION => array(
        null => array('state' => Tpl::STATE_EXPRESSION,
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
      )
    )
  );

  public function __construct($do_verbose = false) {
    $this->do_verbose = $do_verbose;
  }

  public function compile($template, $pretty = false) {
    $this->reset($template);

    $transition_vars =
        array('state', 'stack_pop', 'stack_push',
              'precollect', 'trim_buffer', 'flush', 'collect',
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

      // (0) Check for rules.
      assert($state != null);
      assert(!($enter_scope && $exit_scope));
      assert(!($precollect && $collect));

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
        for ($i=0; $i < $stack_pop; $i++)
          array_pop($this->stack);
      }

      // 3. Push to the stack.
      if ($stack_push)
        $this->stack[] = $stack_push;

      //
      // (II) Handle the buffer.
      //

      // 4. Append to buffer before flushing.
      if ($precollect)
        $this->buffer .= $input_char;

      // 5. Trim end of buffer.
      if ($trim_buffer > 0)
        $this->buffer = substr($this->buffer, 0, -$trim_buffer);

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

    // Assert machine state: All should be reset.
    assert($this->state == Tpl::STATE_CLAUSE);
    assert($this->stack == array(null), var_export($this->stack, true));
    assert($this->char_index == strlen($this->template));
    assert($this->buffer == "");
    assert($this->scope_stack == array(array('$data', '$key')));
    assert($this->scope_value == '$data', $this->scope_value);
    assert($this->scope_level == 0);
    assert($this->condition == null);
    assert($this->scope_delimiter == '');

    return $this->code;
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
        "No transition found. $input_char, $state, $stack_state;");

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
    $this->condition = $this->resolveExpression($buffer);
  }

  private function flush_append_expression($buffer) {
    $expression_code = $this->resolveExpression($buffer);
    $this->code .= '$x.=' . $expression_code . ';';
  }

  private function flush_append_literal($buffer) {
    if (strlen($buffer) > 0)
      $this->code .= '$x.=' . var_export($buffer, true) . ';';
  }

  private function verbose($mixed) {
    if ($this->do_verbose)
      print_r($mixed);
  }

  // TODO(kburnik): This entire method needs rewriting.
  private function resolveExpression($buffer) {
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
