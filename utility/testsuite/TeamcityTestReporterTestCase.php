<?php

if (!defined('SKIP_DB'))
  define('SKIP_DB', true);
include_once(dirname(__FILE__) . "/../../.testproject/project.php");

class TeamCityTestReporterTestCase extends TestCase {

  public function __construct() {
    $this->reporter = new TeamCityTestReporter(false);
  }

  private function assertEscaping($input, $escaped_expected) {
    $res = $this->reporter->reportEvent("escapeTestEvent", array(
        "input" => $input));

    $expected = "##teamcity[escapeTestEvent input='{$escaped_expected}']\n";
    $this->assertEqual($expected, $res);
  }

  public function simpleReport_produces() {

    $res = $this->reporter->reportEvent("testSuiteStarted",
        array("name" => "MyClass"));

    $this->assertEqual("##teamcity[testSuiteStarted name='MyClass']\n", $res);
  }

  public function multipleEventArgs_AllProduced() {

    $res = $this->reporter->reportEvent("testFailed",
        array("name" => "MyClass.MyMethod",
              "message" => "Bad boy",
              "expected" => "good",
              "actual" => "bad"));

    $expected = "##teamcity[testFailed name='MyClass.MyMethod' "
                ."message='Bad boy' expected='good' actual='bad']\n";

    $this->assertEqual($expected, $res);
  }

  public function escaping_pipe_doubled() {
    $this->assertEscaping("|", "||");
  }

  public function escaping_multiplePipes_allDoubled() {
    $this->assertEscaping("|||", "||||||");
  }

  public function escaping_quotes_allPrefixedWithPipe() {
    $this->assertEscaping("'", "|'");
    $this->assertEscaping("''", "|'|'");
  }

  public function escaping_newlines_allPrefixedWithPipe() {
    $this->assertEscaping("\n", "|n");
    $this->assertEscaping("\r", "|r");
  }

  public function escaping_brackets_allPrefixedWithPipe() {
    $this->assertEscaping("[", "|[");
    $this->assertEscaping("]", "|]");
    $this->assertEscaping("[Hello little one]", "|[Hello little one|]");
  }

  public function escaping_mixed_allPrefixedWithPipe() {
    $this->assertEscaping("[bold]text[/bold]\nNext line\r\n Wendy's place.",
      "|[bold|]text|[/bold|]|nNext line|r|n Wendy|'s place.");
  }


}
