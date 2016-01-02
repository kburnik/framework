<?php

include_once( dirname(__FILE__) . "/../../base/Base.php" );

class TplTestCase extends TestCase {

  public function test0() {
    $this->assertProduce("123",
                         '$([*]){[*]}',
                         array(1, 2, 3));
  }

  public function test00() {
    $this->assertProduce("112233",
                         '$([*]){[*][*]}',
                         array(1, 2, 3));

  }

  public function test000() {
    $this->assertProduce("1x12x23x3",
                         '$([*]){[*]x[*]}',
                         array(1, 2, 3));
  }

  public function test0000() {
    $this->assertProduce("123",
                         '${[*]}',
                         array(1, 2, 3));
  }

  public function test1() {
    $this->assertProduce("Hello1Hello2Hello3",
                         '$([*]){Hello[*]}',
                         array(1, 2, 3));
  }

  public function test2() {
    $this->assertProduce("1World2World3World",
                         '$([*]){[*]World}',
                         array(1, 2, 3));
  }

  public function test3() {
    $this->assertProduce("Hello1WorldHello2WorldHello3World",
                         '$([*]){Hello[*]World}',
                         array(1, 2, 3));
  }

  private function assertProduce($expected_value, $template, $data,
      $do_verbose=false) {
    $actual_value = $this->produce($template, $data, $do_verbose);
    $this->assertEqual($expected_value, $actual_value);
  }

  private function produce($template, $data, $do_verbose = false) {
    $tpl = new Tpl($do_verbose);
    $code = $tpl->compile($template);
    eval($code);
    return $x;
  }
}
