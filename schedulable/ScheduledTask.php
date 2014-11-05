<?


/*
abstract Task to be run by scheduler
*/

abstract class ScheduledTask extends BaseSingleton implements ISchedulable {
  
  public function schedule($arguments,$execute_after = null) {  
    Scheduler::AddTask(get_class($this),$arguments,$execute_after);
  }
  
  public function scheduleOnce($arguments,$execute_after = null) {  
    Scheduler::AddTaskOnce(get_class($this),$arguments,$execute_after);
  }
  
}


?>