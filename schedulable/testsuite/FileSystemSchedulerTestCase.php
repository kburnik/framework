git config --global --edit<?php

class FileSystemSchedulerTestCase extends SchedulerTestCaseBase {
  public static $inherits = array('SchedulerTestCaseBase');

  public function __construct() {
    $directory = dirname(__FILE__) . "/temp";
    $taskProvider = new ScheduledTaskProvider($directory);
    parent::__construct($taskProvider);
  }
}