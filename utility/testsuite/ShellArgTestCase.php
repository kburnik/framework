<?php

include_once(dirname(__FILE__) . "/../../base/Base.php");

class ShellArgTestCase extends TestCase {
  private $options;

  public function __construct() {
  }

  public function parse_validOptionsGiven_parsed() {
    $options = array(
        new ShellArg(ShellArg::OPTION_ARGUMENTS,
                     ShellArg::OPTION_REQUIRED,
                     ShellArg::OPTION_ONE_OR_MORE,
                     "Items to handle"),
        new ShellArg(array("f", "file"),
                     ShellArg::OPTION_OPTIONAL,
                     ShellArg::OPTION_ONE,
                     "Files to import"));

    $argv = array("script.php", "-f", "sample.txt", "1.dat", "2.dat");
    $parsed = ShellArg::parse($argv, $options);

    $expected = array(
      "f" => "sample.txt",
      "file" => "sample.txt",
      "__command__" => "script.php",
      "__arguments__" => array("1.dat", "2.dat")
    );

    $this->assertEqual($expected, $parsed);
  }

  public function parse_unknownOptionsGiven_throws() {
    $options = array(
        new ShellArg(ShellArg::OPTION_ARGUMENTS,
                     ShellArg::OPTION_REQUIRED,
                     ShellArg::OPTION_ONE_OR_MORE,
                     "Items to handle"));

    $argv = array("script.php", "-f", "sample.txt", "1.dat", "2.dat");

    $this->assertThrows(ShellArg::EXCEPTION_UNKNOWN_OPTION, $argv, $options);
  }

  public function parse_flagOptionSet_setsToTrue() {
    $options = array(
        new ShellArg("x",
                     ShellArg::OPTION_OPTIONAL,
                     ShellArg::OPTION_FLAG,
                     "Sets x flag to true"));

    $argv = array("script.php", "-x",  "1.dat", "2.dat");

    $parsed = ShellArg::parse($argv, $options);

    $expected = array(
      "x" => true,
      "__command__" => "script.php",
      "__arguments__" => array("1.dat", "2.dat")
    );

    $this->assertEqual($expected, $parsed);
  }

  public function shellArg_invalidCardinalityForRequiredOption_throws() {
    try {
        new ShellArg("x",
                     ShellArg::OPTION_REQUIRED,
                     ShellArg::OPTION_FLAG,
                     "Sets x flag to true");
        $this->assertFalse(true, "Should have thrown.");
    } catch (Exception $ex) {
      $this->assertEqual(ShellArg::EXCEPTION_INVALID_CARDINALITY,
                         $ex->getCode());
    }
  }

  public function shellArg_invalidOptionSpecifier_throws() {
    $invalids = array(
      array("x"),
      array("x", "x"),
      array("x", "y", "z"),
      array("o", array()),
      array(array(), "o"),
      array(array(), array()),
      new stdclass,
      false,
    );

    foreach ($invalids as $specifier) {
      try {
          new ShellArg($specifier,
                       ShellArg::OPTION_OPTIONAL,
                       ShellArg::OPTION_FLAG,
                       "Some option");
          $this->assertFalse(true, "Should have thrown.");
      } catch (Exception $ex) {
        $this->assertEqual(ShellArg::EXCEPTION_INVALID_OPTION_SPECIFIER,
                           $ex->getCode());
      }
    }
  }

  public function shellArg_invalidCardinality_throws() {
    try {
        new ShellArg("x",
                    ShellArg::OPTION_OPTIONAL,
                    "invalid",
                    "Some option");
          $this->assertFalse(true, "Should have thrown.");
      } catch (Exception $ex) {
        $this->assertEqual(ShellArg::EXCEPTION_INVALID_CARDINALITY,
                           $ex->getCode());
      }
  }

  public function parse_flagOptionUnset_setsToFalse() {
    $options = array(
        new ShellArg("x",
                     ShellArg::OPTION_OPTIONAL,
                     ShellArg::OPTION_FLAG,
                     "Sets x flag to true"));

    $argv = array("script.php", "1.dat", "2.dat");

    $parsed = ShellArg::parse($argv, $options);

    $expected = array(
      "x" => false,
      "__command__" => "script.php",
      "__arguments__" => array("1.dat", "2.dat")
    );

    $this->assertEqual($expected, $parsed);
  }

  public function parse_flagOptionsOptional_setsDefaults() {
    $options = array(
        new ShellArg(array("f", "file"),
                     ShellArg::OPTION_OPTIONAL,
                     ShellArg::OPTION_ZERO_OR_MORE,
                     "Some files to handle"));

    $argv = array("script.php", "1.dat", "2.dat");

    $parsed = ShellArg::parse($argv, $options);

    $expected = array(
      "f" => array(),
      "file" => array(),
      "__command__" => "script.php",
      "__arguments__" => array("1.dat", "2.dat")
    );

    $this->assertEqual($expected, $parsed);
  }

  public function parse_optionZeroOrMoreNoValues_setsDefaults() {
    $options = array(
        new ShellArg(array("f", "file"),
                     ShellArg::OPTION_OPTIONAL,
                     ShellArg::OPTION_ZERO_OR_MORE,
                     "Some files to handle"));

    $argv = array("script.php", "1.dat", "2.dat", "-f");

    $parsed = ShellArg::parse($argv, $options);

    $expected = array(
      "f" => array(),
      "file" => array(),
      "__command__" => "script.php",
      "__arguments__" => array("1.dat", "2.dat")
    );

    $this->assertEqual($expected, $parsed);
  }

  public function parse_flagOptionsRequiredButNotSpecified_throws() {
    $options = array(
        new ShellArg(array("f", "file"),
                     ShellArg::OPTION_REQUIRED,
                     ShellArg::OPTION_ZERO_OR_MORE,
                     "Some files to handle"));

    $argv = array("script.php", "1.dat", "2.dat");

    $this->assertThrows(ShellArg::EXCEPTION_MISSING_REQUIRED_OPTION,
                        $argv,
                        $options);
  }

  public function parse_flagOptions_AcceptsAllForms() {
    $options = array(
        new ShellArg(array("f", "file"),
                     ShellArg::OPTION_OPTIONAL,
                     ShellArg::OPTION_ONE_OR_MORE,
                     "Some files to handle"));

    $argv = array("script.php", "-f", "1.dat", "2.dat",
                                "-file", "3.dat",
                                "--file", "4.dat");

    $parsed = ShellArg::parse($argv, $options);

    $expected = array(
      "f" => array("1.dat", "2.dat", "3.dat", "4.dat"),
      "file" => array("1.dat", "2.dat", "3.dat", "4.dat"),
      "__command__" => "script.php",
      "__arguments__" => array()
    );

    $this->assertEqual($expected, $parsed);
  }

  public function parse_optionWithOneOrMoreButNoneSpecified_throws() {
    $options = array(
        new ShellArg(array("f", "file"),
                     ShellArg::OPTION_OPTIONAL,
                     ShellArg::OPTION_ONE_OR_MORE,
                     "Some files to handle"));

    $argv = array("script.php", "-f");

    $this->assertThrows(ShellArg::EXCEPTION_MISSING_REQUIRED_VALUE,
                        $argv,
                        $options);
  }

  private function assertThrows($expectedCode, $argv, $options) {
    try {
      ShellArg::parse($argv, $options);
      $this->assertFalse(true, "Should have thrown");
    } catch (Exception $ex) {
      $this->assertEqual($expectedCode, $ex->getCode());
    }
  }

}
