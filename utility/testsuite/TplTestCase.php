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

  public function test57() {
    $this->assertProduced('123',
                          '$([\'123\':str_split]){[*]}',
                          null);
  }

  public function test58() {
    $this->assertProduced('1,2,3',
                          '$[,]([\'123\':str_split]){[*]}',
                          null);
  }

  public function test59() {
    $this->assertProduced('C--S--V',
                          '$[--]{[*]}',
                          str_split('CSV'));
  }

  public function test60() {
    $this->assertProduced('C--S--V;CSV',
                          '$[--]{[*]};${[*]}',
                          str_split('CSV'));
  }

  public function test61() {
      $data = array(
      "numbers" => array(1, 2, 3),
      "letters" => array("a", "b", "c")
    );
    $template = "$[\n]([numbers]){[*]:$[;]([**.letters]){[*]}}";

    $this->assertProduced("1:a;b;c\n2:a;b;c\n3:a;b;c",
                          $template,
                          $data);
  }

  public function test62() {
    $data = array(
      "numbers" => array(1, 2, 3),
      "letters" => array("a", "b", "c")
    );
    $template = "$[\n]([numbers]){([*]):$[;]([**.letters]){[**][*]}}";

    $this->assertProduced("(1):1a;1b;1c\n(2):2a;2b;2c\n(3):3a;3b;3c",
                          $template,
                          $data);
  }

  public function test63() {
    $this->assertProduced('',
                          '',
                          array());
  }

  public function test64() {
    $this->assertProduced('',
                          '$/**/',
                          array());
  }

  public function test65() {
    $this->assertProduced('',
                          '$/* This is a block comment. */',
                          array());
  }

  public function test66() {
    $this->assertProduced('Literal',
                          '$/* Comment1 */Literal$/* Comment2 */',
                          array());
  }

  public function test67() {
    $this->assertProduced('12',
                          '1$/* Comment1 */2$/* Comment2 */',
                          array());
  }

  public function test68() {
    $this->assertProduced('23',
                          '$/* Comment1 */2$/* Comment2 */3',
                          array());
  }

  public function test69() {
    $this->assertProduced('123',
                          '1$/* Comment1 */2$/* Comment2 */3',
                          array());
  }

  public function test70() {
    $this->assertProduced('',
                          '$/*$*/',
                          array());
  }

  public function test71() {
    $this->assertProduced('',
                          '$/***/',
                          array());
  }

  public function test72() {
    $this->assertProduced('',
                          '$/****/',
                          array());
  }

  public function test73() {
    $this->assertProduced('',
                          '$/*/*/',
                          array());
  }

  public function test74() {
    $this->assertProduced('',
                          '$/* ${[*]} $?([*]){[*]}{[#]} */',
                          array());
  }

  public function test75() {
    $this->assertProduced('',
                          '$/* $/* */',
                          array());
  }

  public function test76() {
    $this->assertProduced('',
                          '$/* $<> */',
                          array());
  }

  public function test77() {
    $this->assertProduced('',
                          '$/* $<> $<> */',
                          array());
  }

  public function test78() {
    $this->assertProduced('$/*  */',
                          '$<>$/*  */$<>',
                          array());
  }

  public function test79() {
    $this->assertProduced('{}',
                          '{}',
                          array());
  }

  public function test80() {
    $this->assertProduced('{{}}',
                          '{{}}',
                          array());
  }

  public function test81() {
    $this->assertProduced('',
                          '$/* }} */',
                          array());
  }

  public function test82() {
    $this->assertProduced('',
                          '$/* {{ */',
                          array());
  }

  public function test83() {
    $this->assertProduced('$$$',
                          '$([*]){\$}',
                          str_split('123'));
  }

  public function test84() {
    $this->assertProduced('[[[',
                          '$([*]){\[}',
                          str_split('123'));
  }

  public function test85() {
    $this->assertProduced(']]]',
                          '$([*]){\]}',
                          str_split('123'));
  }

  public function test86() {
    $this->assertProduced('()()()',
                          '$([*]){()}',
                          str_split('123'));
  }

  public function test87() {
    $this->assertProduced('$$',
                          '\$\$',
                          array());
  }

  public function test88() {
    $this->assertProduced('[]',
                          '\[\]',
                          array());
  }

  public function test89() {
    $this->assertProduced('()',
                          '()',
                          array());
  }

  public function test90() {
    $this->assertProduced('*',
                          '*',
                          array());
  }

  public function test91() {
    $this->assertProduced('$([*]){[*]}',
                          '\$(\[*\]){\[*\]}',
                          array());
  }

  public function test92() {
    $this->assertProduced('value$([*]){[*]}',
                          '[*]\$(\[*\]){\[*\]}',
                          "value");
  }

  public function test93() {
    $this->assertProduced('[value]',
                          '\[[*]\]',
                          "value");
  }

  public function test94() {
    $this->assertProduced('${}',
                          '\${}',
                          array());
  }

  public function test95() {
    $this->assertProduced('$variable',
                          '$variable',
                          array());
  }

  public function test96() {
    $this->assertProduced('$!xy',
                          '$!xy',
                          array());
  }

  public function test97() {
    $this->assertProduced('$',
                          '$',
                          array());
  }

  public function test98() {
    $this->assertProduced('$$',
                          '$$',
                          array());
  }

  public function test99() {
    $this->assertProduced('$1,2,3',
                          '$$[,]{[*]}',
                          array(1, 2, 3));
  }

  public function test100() {
    $this->assertProduced('$123$',
                          '$${[*]}$',
                          array(1, 2, 3));
  }

  public function test101() {
    $this->assertProduced('$1,2,3$',
                          '$$[,]{[*]}$',
                          array(1, 2, 3));
  }

  public function test102() {
    $this->assertProduced('x[1] = "abc";',
                          '$<>x[1] = "abc";$<>',
                          array());
  }

  public function test103() {
    $this->assertProduced('{1}{2}{3}',
                          '${{[*]}}',
                          array(1, 2, 3));
  }

  public function test104() {
    $this->assertProduced('{{1}{2}{3}}',
                          '{${{[*]}}}',
                          array(1, 2, 3));
  }

  public function test105() {
    $this->assertProduced('{{1{0}}{2{1}}{3{2}}}',
                          '{${{[*]{[#]}}}}',
                          array(1, 2, 3));
  }

  public function test106() {
    $this->assertProduced('}{',
                          '}{',
                          array());
  }

  public function test107() {
    $this->assertProduced('{{{',
                          '{{{',
                          array(),
                          false,
                          false);
  }

  public function test108() {
    $this->assertProduced('{a{b{c',
                          '{a{b{c',
                          array(),
                          false,
                          false);
  }

  public function test109() {
    $this->assertProduced('}}}',
                          '}}}',
                          array());
  }

  // TODO(kburnik):
  // * Support for complex if expressions $(!([x]==5) || [y]==2)
  // * Support for nested expressions ${ [*.[pointer]] }

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

  private function assertProduced($expected_value,
                                  $template,
                                  $data,
                                  $do_verbose=false,
                                  $do_warn=true) {
    $actual_value = $this->produce($template, $data, $do_verbose, $do_warn);
    $this->assertEqual($expected_value, $actual_value);
  }

  private function produce($template,
                           $data,
                           $do_verbose = false,
                           $do_warn = true) {
    $tpl = new Tpl($do_verbose, $do_warn);
    $code = $tpl->compile($template);

    if ($do_verbose)
      echo $code;

    eval($code);
    return $x;
  }
}
