<?php

class ExampleTaskReporter implements ITestReporter {
  private static $instance;
  public $events = array();

  private function __construct() {}

  public function reportEvent($eventName, $eventArgs) {
    $this->events[]  = array($eventName, $eventArgs);
  }

  public static function CreateInstance() {
    self::$instance =  new ExampleTaskReporter();
    return self::$instance;
  }

  public static function GetInstance() {
    return self::$instance;
  }
}

class ExampleErrorLogger implements IErrorLogger {
  public $messages = array();
  public function log($message) {
    $this->messages[] = $message;
  }
}

class ExampleTask implements ITask {

  public function execute($arguments) {
    ExampleTaskReporter::GetInstance()->reportEvent(
        "sample_task_executed", $arguments);
  }

}

class ExceptionThrowingTask implements ITask {
  public function execute($arguments) {
    throw new Exception("Intentional exception for testing.");
  }
}

abstract class SchedulerTestCaseBase extends TestCase {

  private $reporter;
  private $scheduler;
  private $taskProvider;
  private $errorLogger;

  public function __construct(IScheduledTaskProvider $taskProvider) {
    $this->taskProvider = $taskProvider;
    $this->errorLogger = new ExampleErrorLogger();
    $this->scheduler = new Scheduler($this->taskProvider, $this->errorLogger);
    $this->reporter = ExampleTaskReporter::CreateInstance();
  }

  public function scheduleTask_singleTask_schedulesAndExecutes() {
    $task = new ExampleTask($this->reporter);
    $arguments = array("a" => 1, "b" => 2);
    $now = now();

    $added = $this->scheduler->addTask($task, $arguments, $now);
    $this->assertTrue($added);

    $this->assertEqual(1, $this->taskProvider->count());

    $numExecutedTasks = $this->scheduler->run(-1, $now);
    $this->assertEqual(1, $numExecutedTasks);
    $this->assertEqual(array("sample_task_executed", $arguments),
                       $this->reporter->events[0]);
    $this->assertEqual(0, $this->taskProvider->count());
  }

  public function scheduleTask_multipleTasksSameTime_schedulesAndExecutes() {
    $tasks = array(
        new ExampleTask($this->reporter),
        new ExampleTask($this->reporter),
        new ExampleTask($this->reporter)
    );

    $now = now();
    foreach ($tasks as $index => $task)
      $this->scheduler->addTask($task, $index, $now);

    $numExecutedTasks = $this->scheduler->run(-1, $now);
    $this->assertEqual(3, $numExecutedTasks);

    foreach ($this->reporter->events as $index => $event)
      $this->assertEqual(array("sample_task_executed", $index), $event);
  }

  public function scheduleTask_futureTasks_timeTicksSequentialExecution() {
    $time = strtotime(now());
    $timespan = 10;

    $tasks = array();
    for ($i = 1; $i <= $timespan; $i++) {
        $task = new ExampleTask($this->reporter);
        $arguments = $i;
        $executionTime = date("Y-m-d H:i:s", $time + $i);
        $this->scheduler->addTask($task, $arguments, $executionTime);
    }

    $this->assertEqual($timespan, $this->taskProvider->count());

    for ($i = 1; $i <= $timespan; $i++) {
      $executionTime = date("Y-m-d H:i:s", $time + $i);
      $numExecutedTasks = $this->scheduler->run(1, $executionTime);
      $this->assertEqual(1, $numExecutedTasks);
      $this->assertEqual($timespan - $i, $this->taskProvider->count());
      $event = end($this->reporter->events);
      $this->assertEqual(array("sample_task_executed", $i), $event);
    }
  }

  public function scheduleTask_multipleTasks_executesExactAmount() {
    $time = strtotime(now());
    $timespan = 10;

    $tasks = array();
    for ($i = 0; $i < $timespan; $i++) {
        $task = new ExampleTask($this->reporter);
        $arguments = $i;
        $executionTime = date("Y-m-d H:i:s", $time + $i);
        $this->scheduler->addTask($task, $arguments, $executionTime);
    }

    $this->assertEqual($timespan, $this->taskProvider->count());
    $limit = 5;

    $executionTime = date("Y-m-d H:i:s", $time + $timespan);

    $numExecutedTasks = $this->scheduler->run($limit, $executionTime);
    $this->assertEqual($limit, $numExecutedTasks);
    $this->assertEqual($limit, count($this->reporter->events));

    // Execute the rest.
    $numExecutedTasks = $this->scheduler->run(-1, $executionTime);
    $this->assertEqual($timespan - $limit, $numExecutedTasks);
    $this->assertEqual($timespan, count($this->reporter->events));

    // Nothing left to execute.
    $numExecutedTasks = $this->scheduler->run(-1, $executionTime);
    $this->assertEqual(0, $numExecutedTasks);
    $this->assertEqual($timespan, count($this->reporter->events));
  }

  public function scheduleTask_taskThrowsException_discardedAndErrorLogged() {
    $task = new ExceptionThrowingTask($this->reporter);
    $arguments = array("a" => 1, "b" => 2);
    $now = now();

    $this->scheduler->addTask($task, $arguments, $now);

    $this->assertEqual(1, $this->taskProvider->count());

    // Will trigger exception.
    $numExecutedTasks = $this->scheduler->run(-1, $now);
    $this->assertEqual(0, $numExecutedTasks);
    $this->assertEqual(0, $this->taskProvider->count());

    $errorMessage = end($this->errorLogger->messages);
    $this->assertEqual(
        'Scheduled Task failed: ExceptionThrowingTask {"a":1,"b":2} ' .
        'Exception: Intentional exception for testing.',
        $errorMessage);
  }

  public function scheduleTask_largeLagBehind_allGetsExecuted() {
    $dates = array(
        "2014-01-01 10:00:00",
        "2014-01-01 12:00:00",
        "2014-10-01 13:30:00",
        "2014-11-28 13:30:00",
        "2015-02-10 11:30:00",
        "2015-02-10 12:30:00",
        "2015-02-28 13:30:00",
        "2015-02-28 13:30:00",
        "2015-02-28 13:30:01",
        "2015-02-28 13:30:01",
        "2015-02-28 13:30:02",
        "2015-02-28 13:32:33",
    );

    $arguments = null;
    foreach ($dates as $date) {
      $task = new ExampleTask();
      $this->scheduler->addTask($task, $arguments, $date);
    }

    // A month has passed.
    $now = "2015-03-29 20:11:00";
    $numExecutedTasks = $this->scheduler->run(-1, $now);
    $this->assertEqual(count($dates), $numExecutedTasks);
  }

  public function scheduleTask_onlyFutureTasks_noneGetsExecuted() {
    $dates = array(
        "2014-01-01 10:00:00",
        "2014-01-01 12:00:00",
        "2014-10-01 13:30:00",
        "2014-11-28 13:30:00",
        "2015-02-10 11:30:00",
        "2015-02-10 12:30:00",
        "2015-02-28 13:30:00",
        "2015-02-28 13:30:00",
        "2015-02-28 13:30:01",
        "2015-02-28 13:30:01",
        "2015-02-28 13:30:02",
        "2015-02-28 13:32:33",
    );

    $index = 0;
    foreach ($dates as $date) {
      $task = new ExampleTask();

      $this->assertFalse($this->taskProvider->exists(
          get_class($task), $index, $date));

      $this->scheduler->addTask($task, $index, $date);

      $this->assertTrue($this->taskProvider->exists(
          get_class($task), $index, $date));

      $index++;
    }

    $now = "2013-03-29 20:11:00";
    $numExecutedTasks = $this->scheduler->run(-1, $now);
    $this->assertEqual(0, $numExecutedTasks);
  }

}
