<?php


class TeamCityTestReporter implements ITestReporter {

  private $output_to_stdout;

  public function __construct($output_to_stdout = true) {
    $this->output_to_stdout = $output_to_stdout;
  }

  public static function escape($string) {
    $replacements = array(
      "'" => "|'",
      "\n" => "|n",
      "\r" => "|r",
      "\uNNNN" => "|0xNNNN",
      "[" => "|[",
      "]" => "|]"
    );
    $string = str_replace("|", "||", $string);
    return strtr($string, $replacements);
  }

  public function reportEvent($eventName, $eventArgs) {
    $args_produced = produce("\$[ ]{[#]='[*:TeamCityTestReporter::escape]'}",
        $eventArgs);
    return $this->output("##teamcity[$eventName $args_produced]\n");
  }

  private function output($string) {
    if ($this->output_to_stdout) {
      file_put_contents("php://stdout", $string);
    }
    return $string;
  }
}

