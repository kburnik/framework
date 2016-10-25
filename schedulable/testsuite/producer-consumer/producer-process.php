<?php

include_once(dirname(__FILE__) . "/../../../base/Base.php");
include_once(dirname(__FILE__) . "/SchedulerTestProducer.php");
include_once(dirname(__FILE__) . "/TestTask.php");

$directory = dirname(__FILE__) . "/temp";
$taskProvider = new ScheduledTaskProvider($directory);
$scheduler = new Scheduler($taskProvider);
$producer = new SchedulerTestProducer($scheduler);

$max = max(intval($argv[1]), 1);

$value = 0;
while ($value < $max) {
  echo "Producing $value\n";
  $producer->produce($value++);
  sleep(1);
}

