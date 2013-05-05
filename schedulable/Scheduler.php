<?
include_once(dirname(__FILE__)."/../base/Base.php");

/*
Scheduler for running tasks in background
*/
class Scheduler extends BaseSingleton {
		
	private static $Instance;
	public static function getInstance() {
		if (!isset(self::$Instance)) {
			self::$Instance = new Scheduler();
		}
		return self::$Instance;
	}
	
	public function getEventHandlerInterface() {
		return 'ISchedulerEventHandler';
	}
	
	public static function AddTask($task,$arguments,$execute_after = null) {
		if ($execute_after == null) $execute_after = now();
		$storage = self::GetTaskListStorage();		
		$storage[] = array($task,$arguments,$execute_after);
		self::getInstance()->onAddTask($task,$arguments,$execute_after);
	}
	
	// add task to list if not already!
	public static function AddTaskOnce($task,$arguments,$execute_after = null) {
		if ($execute_after == null) $execute_after = now();
		$storage = self::GetTaskListStorage();
		$found = false;
		foreach ($storage as $i=>$task_desc) {
			list($taskname,$arguments,$execute_after) = $task_desc;
			if ($taskname == $task) { $found = true; break; }
		}
		if (!$found) {
			$storage[] = array($task,$arguments,$execute_after);
			self::getInstance()->onAddTask($task,$arguments,$execute_after);
		}
	}
	
	private static $task_list_storage;
	public static function GetTaskListStorage() {
		if (!isset(self::$task_list_storage)) {
			self::$task_list_storage = new FileStorage(Project::GetProjectDir('/gen/scheduled.tasks.storage.php'));
		}
		return self::$task_list_storage;
	}
	
	// run all scheduled tasks for project
	public static function Run() {
		$storage = self::GetTaskListStorage();
		foreach ($storage as $i=>$task_desc) {
			list($task,$arguments,$execute_after) = $task_desc;
			if ((microtime(true) - strtotime($execute_after)) > 0) {
				$storage->clear($i);
				echo "Running $task<br />";
				$task = new $task();
				$task->execute($arguments,$execute_after);
				
				self::getInstance()->onExecuteTask($task,$arguments,$execute_after);
			} else {
				echo "Skipping task $task for later<br />";
			}
		}
	}
	
}


?>