#!/usr/bin/env php
<?
include_once(dirname(__FILE__) . "/../base/Base.php");

class IntegrityCheckCrawler extends EntityCrawler {
  protected function handleEntity($sourceEntry, $entityName) {
    $integrityCheckerClassName = "{$entityName}IntegrityChecker";
    if (class_exists($integrityCheckerClassName)) {
      $checker = new $integrityCheckerClassName();

      $checks = $checker->getIntegrityResults();

      $sc = ShellColors::getInstance();
      foreach ($checks as $check) {
        $descriptor = $entityName . ' ' . $check['method'];
        echo $sc->getColoredString("$descriptor", "yellow") . " ";
        if ($check['result']) {
          echo $sc->getColoredString("OK", "green");
        } else {
          echo $sc->getColoredString("FAIL", "red") . "\n" ;
          echo $check['message'];
        }
        echo "\n";
      }

    }
  }

  public function check($dir) {
    $this->resolveProject($dir);
    $this->traverse($dir);
  }
}

$c = new IntegrityCheckCrawler();

flush();
ob_flush();
ob_end_flush();

$fs = new FileSystem();

if (isset($argv[1]))
  $dir = $fs->realpath(strtolower($argv[1]));
else
  $dir = $fs->getcwd();

$c->check($dir);


?>