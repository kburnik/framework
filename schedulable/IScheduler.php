<?php

interface IScheduler {

  public function addTask(ITask $task,
                          $arguments,
                          $executeAfter = null);

  // Run scheduled tasks.
  public function run($maxTasks = -1, $now = null);

}