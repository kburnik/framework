<?php

class SchedulerTestProducer {

  private $scheduler;

  public function __construct($scheduler) {
    $this->scheduler = $scheduler;
  }

  public function produce($value, $afterSeconds = 0) {
    $task = new TestTask();
    $time = date("Y-m-d H:i:s", strtotime(now()) + $afterSeconds);
    return $this->scheduler->addTask($task, $value, $time);
  }

}
