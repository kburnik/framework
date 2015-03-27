<?php

class InMemoryTaskProvider implements IScheduledTaskProvider {
  private $tasks = array();
  private $count = 0;

  public function addTask(ITask $task, $arguments, $executeAfter = null) {
    $this->tasks[$executeAfter][] = array($this->count, $task, $arguments);
    ++$this->count;
    ksort($this->tasks);
    return true;
  }

  public function enumerate() {
    foreach ($this->tasks as $time => $definitions) {
      foreach ($definitions as $definition) {
        yield $time => $definition;
      }
    }
  }

  public function deleteTaskAt($taskKey) {
   foreach ($this->tasks as $time => $definitions) {
      foreach ($definitions as $i => $definition) {
        list($index, $task, $arguments) = $definition;
        if ($index == $taskKey) {
          unset($this->tasks[$time][$i]);
          --$this->count;
          return true;
        }
      }
    }
    return false;
  }

  public function count() {
    return $this->count;
  }

}
