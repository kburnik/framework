<?php

include_once( dirname(__FILE__) . "/../../base/Base.php" );

class TplTestCase extends TestCase {

  public function test0() {
    $this->assertProduced("123",
                         '$([*]){[*]}',
                         array(1, 2, 3));
  }

  public function test00() {
    $this->assertProduced("112233",
                         '$([*]){[*][*]}',
                         array(1, 2, 3));

  }

  public function test000() {
    $this->assertProduced("1x12x23x3",
                         '$([*]){[*]x[*]}',
                         array(1, 2, 3));
  }

  public function test0000() {
    $this->assertProduced("123",
                         '${[*]}',
                         array(1, 2, 3));
  }

  public function test1() {
    $this->assertProduced("Hello1Hello2Hello3",
                         '$([*]){Hello[*]}',
                         array(1, 2, 3));
  }

  public function test2() {
    $this->assertProduced("1World2World3World",
                         '$([*]){[*]World}',
                         array(1, 2, 3));
  }

  public function test3() {
    $this->assertProduced("Hello1WorldHello2WorldHello3World",
                         '$([*]){Hello[*]World}',
                         array(1, 2, 3));
  }

  public function test4() {
     $this->assertProduced("123;456;789;",
                         '${${[*]};}',
                         array(array(1, 2, 3),
                               array(4, 5, 6),
                               array(7, 8, 9)));
  }

  public function test5() {
    $this->assertProduced("123",
                         '$([a]){[*]}',
                         array("a" => array(1, 2, 3)));
  }

  public function test6() {
    $this->assertProduced("111",
                         '$([a]){[**.0]}',
                         array("a" => array(1, 2, 3)));
  }

  public function test7() {
    $this->assertProduced("abc",
                         '${[#]}',
                         array("a" => 1, "b" => 2, "c" => 3));
  }

  public function test8() {
    $this->assertProduced("a->1; b->2; c->3; ",
                         '${[#]->[*]; }',
                         array("a" => 1, "b" => 2, "c" => 3));
  }

  public function test9() {
    $this->assertProduced("1 2 3",
                         '[a] [b] [c]',
                         array("a" => 1, "b" => 2, "c" => 3));
  }

  public function test10() {
    $this->assertProduced("1 2 3",
                         '${[a] [b] [c]}',
                         array(array("a" => 1, "b" => 2, "c" => 3)));
  }

  public function test11() {
    $this->assertProduced("1 2 3",
                         '$([*]){[a] [b] [c]}',
                         array(array("a" => 1, "b" => 2, "c" => 3)));
  }

  public function test12() {
    $data = array(
      "numbers" => array(1, 2, 3),
      "letters" => array("a", "b", "c")
    );
    $template = '$([numbers]){[*]}$([letters]){[*]}';

    $this->assertProduced("123abc", $template, $data);
  }

  public function test13() {
    $data = array(
      array(
        "numbers" => array(1, 2, 3),
        "letters" => array("a", "b", "c")
      )
    );
    $template = '${$([numbers]){[*]}$([letters]){[*]}}';

    $this->assertProduced("123abc", $template, $data);
  }


  public function test14() {
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

  private function assertProduced($expected_value, $template, $data,
      $do_verbose=false) {
    $actual_value = $this->produce($template, $data, $do_verbose);
    $this->assertEqual($expected_value, $actual_value);
  }

  private function produce($template, $data, $do_verbose = false) {
    $tpl = new Tpl($do_verbose);
    $code = $tpl->compile($template);
    if ($do_verbose) {
      echo $code;
    }
    eval($code);
    return $x;
  }
}
