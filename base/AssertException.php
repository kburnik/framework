<?

class AssertException extends Exception {

  private $expected, $measured, $test_message;

  public function __construct($message = null,
                     $code = 0,
                     Exception $previous = null,
                     $expected = null,
                     $measured = null,
                     $test_message = null) {
    parent::__construct($message, $code, $previous);
    $this->expected = $expected;
    $this->measured = $measured;
    $this->test_message = $test_message;
  }

  public function getExpected() {
    return $this->expected;
  }

  public function getMeasured() {
    return $this->measured;
  }

  public function getTestMessage() {
   return $this->test_message;
  }

}

?>