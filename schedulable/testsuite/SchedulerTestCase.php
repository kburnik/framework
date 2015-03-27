<?php

class SchedulerTestCase extends SchedulerTestCaseBase {
  public static $inherits = array('SchedulerTestCaseBase');

  public function __construct() {
    $taskProvider = new InMemoryTaskProvider();
    parent::__construct($taskProvider);
  }
}