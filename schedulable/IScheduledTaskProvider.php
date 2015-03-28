<?php

interface IScheduledTaskProvider {
  public function addTask(ITask $task, $arguments, $executeAfter = null);
  public function deleteTaskAt($taskKey);
  public function lockTaskAt($taskKey);
  public function unlockTaskAt($taskKey);

  public function exists($taskClass, $arguments, $executeAfter);

  // yield $time => array($key, $task, $arguments)
  public function enumerate();
}
