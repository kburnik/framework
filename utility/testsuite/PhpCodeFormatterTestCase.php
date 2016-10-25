<?php
include_once( dirname(__FILE__) . "/../../base/Base.php" );
class PhpCodeFormatterTestCase extends TestCase {

  private $formatter;

  public function __construct(){
    parent::__construct();

    $this->formatter = new PhpCodeFormatter();

  }

  private function assertPhpReformatted($source, $expected) {
    $wrapped_source = '<?php ' . $source . ' ?>';
    $reformatted = $this->formatter->format( $wrapped_source );
    $reformatted = substr($reformatted, 3, strlen($reformatted) - 6);
    $this->assertEqual($expected, $reformatted);
  }

  public function spacesBetwenOperators() {
    $operators = $this->formatter->getOperators();
    foreach ($operators as $operator) {
       $this->assertPhpReformatted(
          '$a ' . $operator . ' $b',
          '$a ' . $operator . ' $b'
      );
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

  public function operatorMinusInSpecialCases() {
    $this->assertPhpReformatted("echo - 5;", "echo -5;");
    $this->assertPhpReformatted("echo -5;", "echo -5;");
    $this->assertPhpReformatted("foo(-5);", "foo(-5);");
    $this->assertPhpReformatted('$x = -5;', '$x = -5;');
    $this->assertPhpReformatted('$x + -5;', '$x + -5;');
    $this->assertPhpReformatted('bar() -foo();', 'bar() - foo();');
    $this->assertPhpReformatted('bar()-foo();', 'bar() - foo();');
    $this->assertPhpReformatted('bar()  - foo();', 'bar() - foo();');
    $this->assertPhpReformatted('bar() -   foo();', 'bar() - foo();');
    $this->assertPhpReformatted('bar()   -  foo();', 'bar() - foo();');
  }

  public function incrementingOperatorsHaveNoSpacesAfter() {
    $this->assertPhpReformatted('--$x;', '--$x;');
    $this->assertPhpReformatted('-- $x;', '--$x;');
    $this->assertPhpReformatted('++$x;', '++$x;');
    $this->assertPhpReformatted('++ $x;', '++$x;');
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

  public function spacesAndCommas() {
    $this->assertPhpReformatted(
        'foo(1,2,3)',
        'foo(1, 2, 3)'
    );

    $this->assertPhpReformatted(
        'foo(  1 ,  2 , 3  )',
        'foo(1, 2, 3)'
    );

    $this->assertPhpReformatted(
        'foo (  1 ,  2 , 3  )',
        'foo(1, 2, 3)'
    );

    $this->assertPhpReformatted(
        'foo($x+7, array(3,2,1, array(5=>4)))',
        'foo($x + 7, array(3, 2, 1, array(5 => 4)))'
    );
  }

  public function noSpaceAroundObjectOperator() {
    $this->assertPhpReformatted(
        '$rect -> width = 100;',
        '$rect->width = 100;'
    );
  }

  public function noSpaceBeforeSemicolon() {
    $this->assertPhpReformatted(
        '$a = 100   ;',
        '$a = 100;'
    );
  }

  public function noSpacesAfterPhpClosedTagInNonMixedFile() {
    $source = "<?php echo \"Hello\"; ?>\n";
    $expected = "<?php echo \"Hello\"; ?>";
    $reformatted = $this->formatter->format($source);
    $this->assertEqual($expected, $reformatted);

    $source = "<?php echo \"Hello\"; ?>\n<html><?php echo \"Bye\";?>\n</html>";
    $expected = "<?php echo \"Hello\"; ?>\n<html><?php echo \"Bye\";?>\n</html>";
    $reformatted = $this->formatter->format($source);
    $this->assertEqual($expected, $reformatted);
  }

  public function oneSpaceAfterKeywords() {
    $keywords = array("if", "for", "foreach", "while", "do");

    foreach ($keywords as $keyword) {
      $this->assertPhpReformatted(
          "$keyword(expression)",
          "$keyword (expression)"
      );

      $this->assertPhpReformatted(
          "$keyword   (expression)",
          "$keyword (expression)"
      );
    }
  }

}

