<?

class PhpToken {
  public $type;
  public $value;
  public $retain;

  public function __construct($type, $value, $retain = false) {
    $this->type = $type;
    $this->value = $value;
    $this->retain = $retain;
  }

}

class PhpCodeFormatter {

  private $tokens;
  private $output;
  private $state_stack;
  private $stripping;

  private function reset() {
    $this->stripping = null;
    $this->tokens = array();
    $this->output = array();
    $this->state_stack = array();
  }

  private function push_state($state) {
    $this->state_stack[] = $state;
  }

  private function pop_state() {
    return array_pop($this->state_stack);
  }

  private function in_state($state) {
    return end($this->state_stack) == $state;
  }

  private function replace_state($assumed_state, $next_state){
    if ($this->in_state($assumed_state)) {
      $this->pop_state();

      if ($next_state !== null)
        $this->push_state($next_state);

      return true;
    }
    return false;
  }

  private function strip_output($token_type) {
    $token = end($this->output);
    do {
      if ($token->type == $token_type) {
        if (!$token->retain)
          unset($this->output[key($this->output)]);
      } else {
        break;
      }

    } while($token = prev($this->output));

  }

  private function strip_while($token_type) {
    $this->stripping = $token_type;
  }

  private function push($token) {
    if ($this->stripping != $token->type) {
      $this->output[] = $token;
      $this->stripping = null;
    }
  }

  private function text_output() {
    $text = "";
    foreach ($this->output as $token){
      $text .= $token->value;
    }

    return $text;
  }

  private function is_operator($op) {
    return in_array($op, $this->getOperators());
  }

  public function getOperators() {
    return array(
        "=",
        ".=", "+=", "-=", "*=", "/=",
        ".", "+", "-", "*", "/", "**", "%",
        "&", "|", "^", "~", "<<", ">>",
        "&&", "||", "!",
        "<", ">", "<=", ">=", "==", "===", "!=", "<>", "!==");
  }

  private function format_internal() {
    while (count($this->tokens)) {
      $token = array_shift($this->tokens);
      $next_token = reset($this->tokens);

      echo "$token->type : ". json_encode($token->value)
          . " -- [" . end($this->state_stack) . "]\n";

      switch($token->type) {
        case "T_RAW":   // Fall through.
          if ($token->value == '(') {
            if ($this->in_state("function")) {
              $this->push_state("function_args");
            }
          } else if ($token->value == ')') {
            $this->replace_state(
                "function_args", "expect_function_body_or_semicolon");
          } else if ($token->value == '{') {
            if ($this->replace_state("expect_function_body_or_semicolon",
                                     "function_body")) {
              $this->strip_output("T_WHITESPACE");
              $this->push(new PhpToken("T_WHITESPACE", " ", true));
            } else {
              $this->push_state("block");
            }

          } else if ($token->value == '}') {
            if ($this->replace_state("function_body", null)) {
              $this->replace_state("function", null);
            } else {
              $this->replace_state("block", null);
            }
          } else if ($token->value == ',') {
            $this->strip_output("T_WHITESPACE");
            $this->push($token);
            $this->push(new PhpToken("T_WHITESPACE", " ", true));
            $this->strip_while("T_WHITESPACE");
            $token = null;

            continue;
          }

        case "T_BOOLEAN_AND":
        case "T_BOOLEAN_OR":
        case "T_CONCAT_EQUAL":
        case "T_PLUS_EQUAL":
        case "T_MINUS_EQUAL":
        case "T_MUL_EQUAL":
        case "T_DIV_EQUAL":
        case "T_SL":
        case "T_SR":
        case "T_IS_SMALLER_OR_EQUAL":
        case "T_IS_GREATER_OR_EQUAL":
        case "T_IS_EQUAL":
        case "T_IS_IDENTICAL":
        case "T_IS_NOT_IDENTICAL":
        case "T_IS_NOT_EQUAL":
          if ($this->is_operator($token->value)) {
            $this->strip_output("T_WHITESPACE");
            $this->push(new PhpToken("T_WHITESPACE", " ", true));
            $this->push($token);
            if ($next_token && !$this->is_operator($next_token->value))
              $this->push(new PhpToken("T_WHITESPACE", " ", true));
            $this->strip_while("T_WHITESPACE");
            $token = null;

            continue;
          }

        break;
        case "T_FUNCTION":
          $this->push_state("function");
        break;
      }

      if ($token)
        $this->push($token);
    }

    return $this->text_output();
  }

  function format($code) {
    echo "\nFormatting:\n";
    $tokens = token_get_all($code);
    $this->reset();
    foreach ($tokens as $token) {
      if (is_array($token)){
        $token_name = token_name($token[0]);
        $token_value = $token[1];
      } else {
        $token_value = $token;
        $token_name = "T_RAW";
      }
      $this->tokens[] = new PhpToken($token_name, $token_value, false);
    }

    return $this->format_internal();
  }

}


?>