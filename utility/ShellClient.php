<?php

class ShellClient implements IShellClient {
  const COLOR_BLUE = "blue";
  const COLOR_CYAN = "cyan";
  const COLOR_GREEN = "green";
  const COLOR_MAGENTA = "magenta";
  const COLOR_PURPLE = "purple";
  const COLOR_RED = "red";
  const COLOR_WHITE = "white";
  const COLOR_YELLOW = "yellow";

  private $command;
  private $args;
  private $injected_arguments;

  private $shellColors;

  public function __construct() {
    $func_args = func_get_args();

    $this->args = array_shift($func_args);
    $this->injected_arguments = $func_args;
    $this->command = array_shift($this->args);
    $this->shellColors = ShellColors::GetInstance();
  }

  public static function create() {
    $reflector = new ReflectionClass('ShellClient');
    return $reflector->newInstanceArgs(func_get_args());
  }

  public function start($callable) {
    flush();
    ob_flush();
    ob_end_flush();

    set_exception_handler(array($this, "__handle_exception"));

    $arguments = array($this->command, $this->args, $this);
    $arguments = array_merge($arguments, $this->injected_arguments);

    $exitCode =
        call_user_func_array($callable, $arguments);
    $this->quit($exitCode);
  }

  public function quit($exitCode) {
    exit($exitCode);
  }

  public function args() {
    return $this->args;
  }

  public function command() {
    return $this->command;
  }

  public function write($str, $color = null) {
    $this->output($str, $color, false, 'php://stdout');
  }

  public function writeLine($str, $color = null) {
    $this->output($str, $color, true, 'php://stdout');
  }

  public function writeJson($data, $color = null) {
    $json_str = json_encode($data, JSON_PRETTY_PRINT);

    if (json_last_error() != JSON_ERROR_NONE &&
        $error_message = json_last_error_msg())
      throw new Exception("Cannot encode to JSON: " . $error_message);

    $this->writeLine($json_str, $color);
  }

  public function error($str, $color = null) {
    $this->output($str, $color, false, 'php://stderr');
  }

  public function errorLine($str, $color = null) {
    $this->output($str, $color, true, 'php://stderr');
  }

  public function input($message = "Input:") {
    $this->write($message);
    $handle = fopen ("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);

    return trim($line);
  }

  private function output($str,
                          $color = null,
                          $newLine = false,
                          $stream = 'php://stdout') {
    if ($color != null)
      $str = $this->shellColors->getColoredString($str, $color);

    if ($newLine)
      $str .= PHP_EOL;

    file_put_contents($stream, $str);
  }

  public function __handle_exception($ex) {
    $this->errorLine($ex->getMessage(), ShellClient::COLOR_RED);
    $this->quit(2);
  }

}
