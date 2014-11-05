<?


interface ISchedulable {

  // the method to be implemented by user
  function execute($arguments,$execute_after = null) ;
  
  function schedule($arguments,$execute_after = null) ;
  
  function scheduleOnce($arguments,$execute_after = null) ;

}

?>