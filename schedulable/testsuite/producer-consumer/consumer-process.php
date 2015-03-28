<?php

include_once(dirname(__FILE__) . "/../../../base/Base.php");
include_once(dirname(__FILE__) . "/SchedulerTestConsumer.php");
include_once(dirname(__FILE__) . "/TestTask.php");

$directory = dirname(__FILE__) . "/temp";
$taskProvider = new ScheduledTaskProvider($directory);
$scheduler = new Scheduler($taskProvider);
$consumer = new SchedulerTestConsumer($scheduler);

sleep(1);
$expectedValue = 0;
while ($taskProvider->count() > 0) {
  if ($consumer->consume($expectedValue)) {
    echo "Consumed $expectedValue\n";
  }

  for ($i=0; $i < 100; $i++)
    if ($taskProvider->count() == 0)
      usleep(10000);
    else
      break;

  ++$expectedValue;
}

if (strlen($directory) > strlen(dirname(__FILE__)))
  @rmdir($directory);
