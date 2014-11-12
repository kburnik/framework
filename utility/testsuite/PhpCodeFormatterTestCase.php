<?
include_once( dirname(__FILE__) . "/../../base/Base.php" );
class PhpCodeFormatterTestCase extends TestCase {

  private $formatter;

  public function __construct(){
    parent::__construct();

    $this->formatter = new PhpCodeFormatter();

  }

  private function assertPhpReformatted($source, $expected) {
    $wrapped_source = '<? ' . $source . ' ?>';
    $reformatted = $this->formatter->format( $wrapped_source );
    $reformatted = substr($reformatted, 3, strlen($reformatted) - 6);
    $this->assertEqual($expected, $reformatted);
  }


  public function spacesBetwenOperators() {
    $operators = $this->formatter->getOperators();
    foreach ($operators as $operator) {
      $this->assertPhpReformatted(
          '$a' . $operator . '$b',
          '$a ' . $operator . ' $b'
      );
      $this->assertPhpReformatted(
          '$a ' . $operator . '    $b',
          '$a ' . $operator . ' $b'
      );
      $this->assertPhpReformatted(
          '$a   ' . $operator . ' $b',
          '$a ' . $operator . ' $b'
      );
    }
  }

  public function oneSpaceBetweenFunctionArgsAndBody() {
    $this->assertPhpReformatted(
        'function foo($x, $y){}',
        'function foo($x, $y) {}'
    );

    $this->assertPhpReformatted(
        'function foo($x, $y)   {}',
        'function foo($x, $y) {}'
    );

    $this->assertPhpReformatted(
        "function foo(\$x, \$y)\n \t \n{}",
        'function foo($x, $y) {}'
    );
  }

  public function oneSpaceAfterComma() {
    $this->assertPhpReformatted(
        'foo(1,2,3)',
        'foo(1, 2, 3)'
    );

      $this->assertPhpReformatted(
        'foo(  1 ,  2 , 3  )',
        'foo(1, 2, 3)'
    );
  }

}

?>