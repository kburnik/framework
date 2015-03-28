<?php

include_once(dirname(__FILE__) . "/../../../base/Base.php");
include_once(dirname(__FILE__) . "/SchedulerTestConsumer.php");
include_once(dirname(__FILE__) . "/TestTask.php");


$directory = dirname(__FILE__) . "/temp";
$taskProvider = new ScheduledTaskProvider($directory);
$scheduler = new Scheduler($taskProvider);
$consumer = new SchedulerTestConsumer($scheduler);

$expectedValue = 0;
while ($consumer->consume($expectedValue)) {
  echo "Consumed $expectedValue\n";
  ++$expectedValue;
  sleep(1);
}
