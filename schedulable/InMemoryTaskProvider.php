<?php

class InMemoryTaskProvider implements IScheduledTaskProvider {
  private $tasks = array();
  private $count = 0;
  private $locked = array();

  public function addTask(ITask $task, $arguments, $executeAfter = null) {
    $this->tasks[$executeAfter][] = array($this->count, $task, $arguments);
    ++$this->count;
    ksort($this->tasks);
    return true;
  }

  public function enumerate() {
    foreach ($this->tasks as $time => $definitions) {
      foreach ($definitions as $definition) {
        if (!$this->locked[$definition[0]])
          yield $time => $definition;
      }
    }
  }

  public function deleteTaskAt($taskKey) {
   foreach ($this->tasks as $time => $definitions) {
      foreach ($definitions as $i => $definition) {
        list($index, $task, $arguments) = $definition;
        if ($index == $taskKey && !$this->locked[$taskKey]) {
          unset($this->tasks[$time][$i]);
          --$this->count;
          return true;
        }
      }
    }
    return false;
  }

  public function lockTaskAt($taskKey) {
    $this->locked[$taskKey] = true;
    --$this->count;
    return true;
  }

  public function unlockTaskAt($taskKey) {
    $this->locked[$taskKey] = false;
    ++$this->count;
    return true;
  }

  public function count() {
    return $this->count;
  }

  public function exists($taskClass, $taskArguments, $executeAfter) {
    foreach ($this->tasks as $time => $definitions) {
      foreach ($definitions as $i => $definition) {
        list($index, $task, $arguments) = $definition;
        if ((get_class($task) == $taskClass) &&
            ($arguments == $taskArguments) &&
            ($time == $executeAfter))
          return true;
      }
    }

    return false;
  }

}
