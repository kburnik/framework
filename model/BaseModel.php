<?

/**
 * Base model :
    - provides shortcut to project's default
      or other QueriedDataProvider (if specified in the constructor)
    - allows async calls (no callbacks however)
    - allows cached calls
    - provides the getInstance method for the derived class

 * @author Kristijan Burnik
 *
 */
abstract class BaseModel extends BaseSingleton {


  /**
   * All model instances are stored statically here
   * @var array
   */
  private static $modelInstances = array();

  /**
   * The model's queried data provider instance
   * @var IQueriedDataProvider
   */
  protected $qdp = null;


  /**
   * The log instance for this model
   * @var ModelLog
   */
  protected $log = null;

  function __construct($queryDataProvider = null) {

    $className = get_class($this);

    // check if test model exists
    if (!class_exists("{$className}TestModule")) {
      # error_log("Warning: TestModule missing for $className");
    }

    $useLog = !defined('SKIP_MODEL_LOGGING');

    if ($useLog)
      Console::WriteLine('BaseModel :: Constructing abstract class BaseModel for ' . $className );


    // Queried Data provider
    $this->qdp = ($queryDataProvider == null) ? Project::GetQDP() : $queryDataProvider ;
    if ($useLog)
      Console::WriteLine('Model :: Setting model\'s data provider ' . get_class($this->qdp) );

  }

  // runs a query on assigned QDP and returns the QDP object containing the result
  protected function query($q) {
    return $this->qdp->execute($q);
  }

  // allows asynchronous calls to a Model instance function
  public function async( $description = null ) {
    // $this->writeLog("Async call","ASYNC.CALL");
    // return the async delegate for this model
    return new AsyncDelegate( get_class($this) , $description );
  }

  // allows cached calls to a Model instance function
  public function cached( $resourceName = null , $resourceDuration = null ) {
    // $this->writeLog("Cached call for resource \"$resourceName\"","CACHE.READ",null,$resourceName);
    return new CachedResourceDelegate( $this , $resourceName , $resourceDuration );
  }

  // disrupt the cache of a named resource
  public function touchCache( $resourceName = null ) {
    if ($resourceName === null) {
      $resourceName = get_class($this);
    }
    CachedResource::Touch( $resourceName );

    $this->writeLog("Cache touched for resource $resourceName","CACHE.TOUCH",null,$resourceName);
  }


  // default implementation
  // overrideable for custom log classes
  // returns the Log class
  public function getLog() {
    if ($this->log === null) {
      $this->log = new ModelLog() ;
    }
    return $this->log;
  }


  // write a log entry
  public function writeLog( $text , $token = "ENTRY" , $level = null , $data = null  ) {

    if ($level === null)
      $level = "VERBOSE";

    $log = $this->getLog();

    if (! ($log instanceof ILog) ) {
      throw new Exception("Cannot write log! Expected instance of ILog, got " . var_export($log,true));
    }

    $modelClassName = get_called_class();

    return $log->write( $modelClassName."|".$token , $text ,  $level , $data  );
  }


  // default singleton access to a Model class!


  /**
   * Create and return a singleton instance of a BaseModel derived class
   * @return BaseModel
   */
  public static function getInstance()  {
    $modelClassName = get_called_class();
    if (!isset(self::$modelInstances[ $modelClassName ])) {
      self::$modelInstances[ $modelClassName ] = new $modelClassName();
    }
    return self::$modelInstances[ $modelClassName ];
  }


}


?>