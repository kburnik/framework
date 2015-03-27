<?php

interface IScheduledTaskProvider {
  public function addTask(ITask $task, $arguments, $executeAfter = null);
  public function deleteTaskAt($taskKey);

  // yield $time => array($key, $task, $arguments)
  public function enumerate();
}
