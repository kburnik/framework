<?php

include_once( dirname(__FILE__) . "/../../base/Base.php" );

class TemplateTestCase extends TestCase {

  public function __construct() {}

  public function produce_simpleIterationImplicitScope_produces() {
    $data = array("one", "two", "three");
    $template = '${[*] }';
    $this->assertProduced("one two three ", $template, $data);
  }

  public function produce_simpleIterationExplicitScope_produces() {
    $data = array("one", "two", "three");
    $template = '$([*]){[*] }';
    $this->assertProduced("one two three ", $template, $data);
  }

  public function produce_simpleIterationImplicitScopeDelimited_produces() {
    $data = array("one", "two", "three");
    $template = '$[,]{[*]}';
    $this->assertProduced("one,two,three", $template, $data);
  }

  public function produce_simpleIterationExplicitScopeDelimited_produces() {
    $data = array("one", "two", "three");
    $template = '$[,]([*]){[*]}';
    $this->assertProduced("one,two,three", $template, $data);
  }

  public function produce_simpleIterationImplicitScopeKeys_produces() {
    $data = array("one"=>1, "two"=>2, "three"=>3);
    $template = '${[#] }';
    $this->assertProduced("one two three ", $template, $data);
  }

  public function produce_simpleIterationExplicitScopeKeys_produces() {
    $data = array("one"=>1, "two"=>2, "three"=>3);
    $template = '$([*]){[#] }';
    $this->assertProduced("one two three ", $template, $data);
  }

  public function produce_rows_produces() {
    $data = array(
     array( "ID" => "1" , "name" => "Jimmy" , "surname" => "Hendrix" ),
     array( "ID" => "2" , "name" => "James" , "surname" => "Hetfield" ),
     array( "ID" => "3" , "name" => "Dexter" , "surname" => "Holland" ),
    );

    $template = "<table border='1'>\n" .
                "  <thead>\n" .
                "    <tr>\n" .
                "      \$([*.0]){<th>[#]</th>}\n" .
                "    </tr>\n" .
                "  </thead>\n" .
                "  <tbody>\n" .
                "\${    <tr>\n" .
                "      \${<td>[*]</td>}\n" .
                "    </tr>\n" .
                "}" .
                "    </tbody>\n" .
                "</table>\n";

    $expected =
        "<table border='1'>\n" .
        "  <thead>\n" .
        "    <tr>\n" .
        "      <th>ID</th><th>name</th><th>surname</th>\n" .
        "    </tr>\n" .
        "  </thead>\n" .
        "  <tbody>\n" .
        "    <tr>\n" .
        "      <td>1</td><td>Jimmy</td><td>Hendrix</td>\n" .
        "    </tr>\n" .
        "    <tr>\n" .
        "      <td>2</td><td>James</td><td>Hetfield</td>\n" .
        "    </tr>\n" .
        "    <tr>\n" .
        "      <td>3</td><td>Dexter</td><td>Holland</td>\n" .
        "    </tr>\n" .
        "    </tbody>\n" .
        "</table>\n";
    $this->assertProduced($expected, $template, $data);
  }

  public function produce_orederedList_produces() {
    $template = '<ol>${<li>[*]</li>}</ol>';
    $data = array("One","Two","Three");
    $this->assertProduced("<ol><li>One</li><li>Two</li><li>Three</li></ol>",
                          $template,
                          $data);
  }

  public function produce_iterationWithKeynames_produces() {
    $data = array(
      array(
        "id" => 1,
        "name" => "Foo"
      ),
      array(
        "id" => 2,
        "name" => "Bar"
      )
    );
    $template = '${id=[id]:name=[name];}';

    $this->assertProduced('id=1:name=Foo;id=2:name=Bar;',
                          $template,
                          $data);
  }

  public function produce_iterationReferencedByKeys_produces() {
    $data = array(
      "numbers" => array(1, 2, 3),
      "letters" => array("a", "b", "c")
    );
    $template = '$([numbers]){[*]}$([letters]){[*]}';

    $this->assertProduced("123abc", $template, $data);
  }

  public function produce_referenceUpperLevel_produces() {
    $data = array(
      "numbers" => array(1, 2, 3),
      "letters" => array("a", "b", "c")
    );
    $template = '$([numbers]){[**.numbers.0][*]}$([letters]){[**.letters.0][*]}';

    $this->assertProduced("111213aaabac",
                          $template, $data);
  }

  public function produce_multiLevel_produces() {
   $data = array(
      "numbers" => array(1, 2, 3),
      "letters" => array("a", "b", "c")
    );
    $template = '${[#]=>${[*]};}';

    $this->assertProduced("numbers=>123;letters=>abc;", $template, $data);
  }

  public function produce_ifStatementWhenTrue_produces() {
    $data = array("value" => true);
    $template='$?([value]){set}';

    $this->assertProduced('set', $template, $data);
  }

  public function produce_ifStatementWhenFalse_doesNotProduce() {
    $data = array("value" => false);
    $template = '$?([value]){set}';

    $this->assertProduced('', $template, $data);
  }

  public function produce_ifStatementWhenArrayHasElements_produces() {
    $data = array(1, 2, 3);
    $template = '$?([*]){[*.0]}';

    $this->assertProduced('1', $template, $data);
  }

  public function produce_ifFilterForItems_producesMatching() {
    $data = array(1, 2, 3);
    $template = '${$?([*]!=2){[*]}}';
    $this->assertProduced('13', $template, $data);
  }

  public function produce_ifElseFilterForItems_producesMatching() {
    $data = array(1, 2, 3);
    $template = '${$?([*]!=2){[*]}{skip}}';
    $this->assertProduced('1skip3', $template, $data);
  }

  public function produce_ifSwitchLoops_producesOnes() {
    $data = array("ones" => array(1, 1),
                  "twos" => array(2, 2, 2, 2));
    $template = '$?([ones]){$([ones]){[*]}}{$([twos]){[*]}}';

    $this->assertProduced('11', $template, $data);
  }

  public function produce_ifSwitchLoops_producesTwos() {
    $data = array("ones" => array(1, 1),
                  "twos" => array(2, 2, 2, 2));
    $template = '$?([threes]){$([ones]){[*]}}{$([twos]){[*]}}';

    $this->assertProduced('2222', $template, $data);
  }

  public function produce_ifStatementWhenArrayEmpty_doesNotProduce() {
    $data = array();
    $template='$?([*]){[*.0]}';

    $this->assertProduced('', $template, $data);
  }

  private function assertProduced($expectedOutput, $template, $data) {
    $actualOutput = $this->produce($template, $data);
    $this->assertEqual($expectedOutput, $actualOutput);
  }

  private function produce($template, $data) {
    return tpl()->Produce($template, $data, false);
  }
}
