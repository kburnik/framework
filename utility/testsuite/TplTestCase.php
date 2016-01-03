<?php

include_once( dirname(__FILE__) . "/../../base/Base.php" );

class TplTestCase extends TestCase {

  public function test1() {
    $this->assertProduced("123",
                         '$([*]){[*]}',
                         array(1, 2, 3));
  }

  public function test2() {
    $this->assertProduced("112233",
                         '$([*]){[*][*]}',
                         array(1, 2, 3));

  }

  public function test3() {
    $this->assertProduced("1x12x23x3",
                         '$([*]){[*]x[*]}',
                         array(1, 2, 3));
  }

  public function test4() {
    $this->assertProduced("123",
                         '${[*]}',
                         array(1, 2, 3));
  }

   public function test5() {
    $this->assertProduced("Literal",
                         'Literal',
                         array(1, 2, 3));
  }

  public function test6() {
    $this->assertProduced("Hello1Hello2Hello3",
                         '$([*]){Hello[*]}',
                         array(1, 2, 3));
  }

  public function test7() {
    $this->assertProduced("1World2World3World",
                         '$([*]){[*]World}',
                         array(1, 2, 3));
  }

  public function test8() {
    $this->assertProduced("Hello1WorldHello2WorldHello3World",
                         '$([*]){Hello[*]World}',
                         array(1, 2, 3));
  }

  public function test9() {
     $this->assertProduced("123;456;789;",
                         '${${[*]};}',
                         array(array(1, 2, 3),
                               array(4, 5, 6),
                               array(7, 8, 9)));
  }

  public function test10() {
    $this->assertProduced("123",
                         '$([a]){[*]}',
                         array("a" => array(1, 2, 3)));
  }

  public function test11() {
    $this->assertProduced("111",
                         '$([a]){[**.a.0]}',
                         array("a" => array(1, 2, 3)));
  }

  public function test12() {
    $this->assertProduced("abc",
                         '${[#]}',
                         array("a" => 1, "b" => 2, "c" => 3));
  }

  public function test13() {
    $this->assertProduced("a->1; b->2; c->3; ",
                         '${[#]->[*]; }',
                         array("a" => 1, "b" => 2, "c" => 3));
  }

  public function test14() {
    $this->assertProduced("1 2 3",
                         '[a] [b] [c]',
                         array("a" => 1, "b" => 2, "c" => 3));
  }

  public function test15() {
    $this->assertProduced("1 2 3",
                         '${[a] [b] [c]}',
                         array(array("a" => 1, "b" => 2, "c" => 3)));
  }

  public function test16() {
    $this->assertProduced("1 2 3",
                         '$([*]){[a] [b] [c]}',
                         array(array("a" => 1, "b" => 2, "c" => 3)));
  }

  public function test17() {
    $data = array(
      "numbers" => array(1, 2, 3),
      "letters" => array("a", "b", "c")
    );
    $template = '$([numbers]){[*]}$([letters]){[*]}';

    $this->assertProduced("123abc", $template, $data);
  }

  public function test18() {
    $data = array(
      array(
        "numbers" => array(1, 2, 3),
        "letters" => array("a", "b", "c")
      )
    );
    $template = '${$([numbers]){[*]}$([letters]){[*]}}';

    $this->assertProduced("123abc", $template, $data);
  }

  public function test19() {
    $this->assertProduced("",
                         '$([*]){[a] [b] [c]}',
                         array());
  }

  public function test20() {
    $this->assertProduced("",
                         '$([*]){[a] [b] [c]}',
                         null);
  }

  public function test21() {
    $this->assertProduced("",
                         '$([*]){[a] [b] [c]}',
                         "");
  }

  public function test22() {
    $this->assertProduced("",
                         '$([*]){[a] [b] [c]}',
                         false);
  }

  public function test23() {
    $this->assertProduced("",
                         '$([*]){[a] [b] [c]}',
                         new stdClass);
  }

  public function test24() {
    $this->assertProduced("yes;",
                          '$?([*]){yes};',
                          true);
  }

  public function test25() {
    $this->assertProduced(";",
                          '$?([*]){yes};',
                          false);
  }

  public function test26() {
    $this->assertProduced("yes;",
                          '$?([*]){yes}{no};',
                          true);
  }

  public function test27() {
    $this->assertProduced("no",
                          '$?([*]){yes}{no}',
                          false);
  }

  public function test28() {
    $this->assertProduced("ab",
                          '$?([*.0]){${[*]}}{${[#]}}',
                          array('a', 'b', ''));

  }

  public function test29() {
    $this->assertProduced("012",
                          '$?([*.2]){${[*]}}{${[#]}}',
                          array('a', 'b', ''));

  }

  public function test30() {
    $this->assertProduced('$(\'#hello\').foo(function() {});',
                          '$<>$(\'#hello\').foo(function() {});$<>',
                          array());
  }

  public function test31() {
    $this->assertProduced('$$$$',
                          '$<>$$$$$<>',
                          array());
  }

  public function test32() {
    $this->assertProduced('$<$<$<',
                          '$<>$<$<$<$<>',
                          array());
  }

  public function test33() {
    $this->assertProduced('$<$<$',
                          '$<>$<$<$$<>',
                          array());
  }

  public function test34() {
    $this->assertProduced('<><><>',
                          '$<><><><>$<>',
                          array());
  }

  public function test35() {
    $this->assertProduced('><><><',
                          '$<>><><><$<>',
                          array());
  }

  public function test36() {
    $this->assertProduced('<<<',
                          '$<><<<$<>',
                          array());
  }

  public function test37() {
    $this->assertProduced('>>>',
                          '$<>>>>$<>',
                          array());
  }

  public function test38() {
    $this->assertProduced('<<<>>>',
                          '$<><<<$<>$<>>>>$<>',
                          array());
  }

  public function test39() {
    $this->assertProduced('value',
                          '$?([*]){[*]}{empty}',
                          'value');
  }

  public function test40() {
    $this->assertProduced('hello',
                          '[*]',
                          'hello');

  }

  public function test41() {
    $this->assertProduced('HELLO',
                          '[*:strtoupper]',
                          'hello');

  }

  public function test42() {
    $this->assertProduced('HELLO',
                          '[*:strtoupper:trim]',
                          ' hello ');

  }

  public function test43() {
    $this->assertProduced('',
                          '[#]',
                          'hello');

  }

  public function test44() {
    $this->assertProduced('1',
                          '[~]',
                          'hello');

  }

  public function test45() {
    $this->assertProduced('5',
                          '[~]',
                          array(1, 2, 3, 4, 5));
  }

  public function test46() {
    $this->assertProduced('5',
                          '[*:count]',
                          array(1, 2, 3, 4, 5));
  }

  public function test47() {
    $this->assertProduced('empty',
                          '[*|empty]',
                          null);
  }

  public function test48() {
    $this->assertProduced(date("Ymd"),
                          '[\'Ymd\':date]',
                          null);
  }

  public function test49() {
    $this->assertProduced('24',
                          '${$?([#%2]){[*]}}',
                          array(1, 2, 3, 4, 5));
  }

  public function test50() {
    $this->assertProduced('135',
                          '${$?([#+%2]){[*]}}',
                          array(1, 2, 3, 4, 5));
  }

  public function test51() {
    $this->assertProduced('ab',
                          '$?([0]){a}$?([1]){b}',
                          array(true, 1));
  }

  public function test52() {
    $template =
      '<ul>${<li$?([active]){ class="active"}>' .
      '<a href="[url]">[title]</a></li>}</ul>';

    $data = array(
      array("title" => "one", "url" => "http://1"),
      array("title" => "two", "url" => "http://2", "active" => 1),
      array("title" => "three", "url" => "http://3"));

    $expected = '<ul>' .
                  '<li><a href="http://1">one</a></li>' .
                  '<li class="active"><a href="http://2">two</a></li>' .
                  '<li><a href="http://3">three</a></li>' .
                '</ul>';

    $this->assertProduced($expected, $template, $data);
  }

  public function test53() {
    $template =
      '<ul>${<li $?([active]){class="active"}{class="normal"}>' .
      '<a href="[url]">[title]</a></li>}</ul>';

    $data = array(
      array("title" => "one", "url" => "http://1"),
      array("title" => "two", "url" => "http://2", "active" => 1),
      array("title" => "three", "url" => "http://3"));

    $expected = '<ul>' .
                  '<li class="normal"><a href="http://1">one</a></li>' .
                  '<li class="active"><a href="http://2">two</a></li>' .
                  '<li class="normal"><a href="http://3">three</a></li>' .
                '</ul>';

    $this->assertProduced($expected, $template, $data);
  }

  public function test54() {
    $this->assertProduced('543210',
                          '${[!#]}',
                          str_split('abcdef'));
  }

  public function test55() {
    $this->assertProduced('654321',
                          '${[!#+]}',
                          str_split('abcdef'));
  }

  public function test56() {
    define('CONST', 'CONST_VALUE');
    $this->assertProduced('CONST_VALUE',
                          '[@CONST]',
                          array());
  }

  // TODO(kburnik):
  // * Support for delimiter $[,]...
  // * Support for lambda expressions [*:trim:strtotlower]
  // * Support for complex if expressions $(!([x]==5) || [y]==2)
  // * Support for nested expressions ${ [*.[pointer]] }
  // * Support for escaping chars, e.g.: \$\<\> or \*\/
  // * Support for comments: $/*   */

  public function test_x() {
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
