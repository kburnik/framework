<?php

interface IScheduler {

  public function addTask(ITask $task,
                          $arguments,
                          $executeAfter = null);

  // Run scheduled tasks.
  public function run($maxTasks = -1, $now = null);

  public function setScheduledTaskProvider(
      IScheduledTaskProvider $taskProvider);
  public function getScheduledTaskProvider();

  public function setErrorLogger(IErrorLogger $errorLogger);
  public function getErrorLogger();

}