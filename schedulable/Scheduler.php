<?php

/*
Scheduler for running tasks in background
*/
class Scheduler implements IScheduler {
  private $scheduledTaskProvider;
  private $errorLogger;

  public function __construct(
      IScheduledTaskProvider $scheduledTaskProvider = null,
      IErrorLogger $errorLogger = null) {

    if ($scheduledTaskProvider === null)
      $scheduledTaskProvider = new ScheduledTaskProvider();

    if ($errorLogger === null)
      $errorLogger = new ErrorLogger();

    $this->scheduledTaskProvider = $scheduledTaskProvider;
    $this->errorLogger = $errorLogger;
  }

  public function addTask(ITask $task, $arguments, $executeAfter = null) {
    if ($executeAfter == null)
      $executeAfter = now();

    $added =
      $this->scheduledTaskProvider->addTask($task, $arguments, $executeAfter);

    return $added;
  }

  // run all scheduled tasks for project
  public function run($maxTasks = -1, $now = null) {
    if ($now == null)
      $now = now();

    $micronow = strtotime($now);

    $numExecutedTasks = 0;
    $deleteList = array();

    foreach ($this->scheduledTaskProvider->enumerate() as
        $executeAfter => $taskDefinition) {

      if (($micronow - strtotime($executeAfter)) < 0 ||
          ($maxTasks > 0 && $numExecutedTasks >= $maxTasks))
        break;

      list($index, $task, $arguments) = $taskDefinition;

      try {
        $task->execute($arguments);
        ++$numExecutedTasks;
      } catch (Exception $ex) {
        $this->errorLogger->log("Scheduled Task failed: " .
            get_class($task) . " " . json_encode($arguments) . " Exception: " .
            $ex->getMessage());
      }

      $deleteList[] = $index;
    }

    foreach ($deleteList as $index)
      $this->scheduledTaskProvider->deleteTaskAt($index);

    return $numExecutedTasks;
  }

}
