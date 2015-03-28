<?php

class SchedulerTestConsumer {

  private $scheduler;

  public function __construct($scheduler) {
    $this->scheduler = $scheduler;
  }

  public function consume($value) {
    $this->scheduler->run(1);
    $ok = file_exists(dirname(__FILE__) . "/temp/$value");
    if (!$ok)
      return false;

    unlink(dirname(__FILE__) . "/temp/$value");
    return true;
  }

}
