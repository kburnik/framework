<?php

class FileSystemSchedulerTestCase extends SchedulerTestCaseBase {
  public static $inherits = array('SchedulerTestCaseBase');

  public function __construct() {
    $date = intval(microtime(true) * 1000);
    $directory = dirname(__FILE__) . "/temp/$date";
    $taskProvider = new ScheduledTaskProvider($directory);
    parent::__construct($taskProvider);
  }
}