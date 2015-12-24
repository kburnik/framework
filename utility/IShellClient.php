<?php

interface IShellClient {
  public function start($callable);

  public function write($str, $color);
  public function writeLine($str, $color);
  public function writeJson($str, $color);

  public function error($str, $color);
  public function errorLine($str, $color);

  public function quit($exitCode);
}
