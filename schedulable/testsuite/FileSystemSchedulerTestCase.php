<?php

class FileSystemSchedulerTestCase extends SchedulerTestCaseBase {
  public static $inherits = array('SchedulerTestCaseBase');
  private $date;

  public function __construct() {
    $directory = dirname(__FILE__) . "/temp";
    $taskProvider = new ScheduledTaskProvider($directory);
    parent::__construct($taskProvider);
  }

  private static function delTree($dir) {
    if (strlen($dir) < strlen(dirname(__FILE__)))
      throw new Exception("Cannot remove $dir");

    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file)
      (is_dir("$dir/$file")) ?
          self::delTree("$dir/$file") : unlink("$dir/$file");

    return !file_exists($dir) || rmdir($dir);
  }

  public function __destruct() {
    if (!file_exists(dirname(__FILE__) . "/temp"))
      return;

    $directory = realpath(dirname(__FILE__) . "/temp");
    assert(self::delTree($directory));
  }
}

