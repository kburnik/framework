<?

// Listens to error_log files and stores new errors to the project error log
// - may listen to the erros in bash via tail -f PROJECT_DIR/gen/project_error_log
// - also can hook up to onTrackErrors to handle them
// - errors expressed in text can be structured via ErrorLogListener::getStructuredErrors
// - runs asynchronously by calling ErrorLogListener::getInstance()->startAsyncScan();

class ErrorLogListener extends BaseModel {

  // the size of the file reading buffer (default = 4096)
  public static $fileReadBufferSize = 4096;

  // how long to wait for a long working process in seconds (default = 120)
  public static $maxParentProcessWaitTime = 120;

  // how to match the begining of the error (default = '/(^\[(.*) (.*) (.*)\]) (.*)/')
  public static $errorBeginRegexPattern = '/(^\[(.*) (.*) (.*)\]) (.*)/';

  // fields which describe the error and map to the regex ( default = array('date'=>2,'time'=>3,'timezone'=>4,'message'=>5) )
  public static $errorDescriptionFields = array('date'=>2,'time'=>3,'timezone'=>4,'message'=>5);


  // was the async scan already started?
  private $startedAsyncScan = false;

  // the storage for keeping track of the error log files
  private $storage;

  // the file in which all errors get copied
  private $projectErrorLogFile;

    // the lock file when working async
  private $lockFile;



  public function __construct( )
  {

    //
    // NOTE: parent constructor (BaseModel::__construct) omitted intentionaly not to require the QDP
    //

    // create a storage for keeping track of error logs
    $this->storage = new FileStorage( Project::GetProjectDir('/gen').'/error.log.state.php'  );

    // setup the destination file in which output will be copied to
    $this->projectErrorLogFile = Project::GetProjectDir('/gen')."/project_error_log";

    // the lock file when working async
    $this->lockFile = Project::GetProjectDir('/gen').'/error.log.scanner.active.txt';

  }


  // scan for new errors
  public function scan( $rootdir = null )
  {

    // index the changes of the error_log files
    $this->indexErrorLogFiles( $rootdir );

    // store the state of the errors
    $this->storage->store();
  }

  // Updates the state of error log files , stack is used to avoid infinite recursion
  private function indexErrorLogFiles( $rootdir = null , $stack = array() )
  {

    // obtain the real path for the directory
    $rootdir = realpath($rootdir);

    // assume working in project directory if non given
    if ( $rootdir == null )
    {
      $rootdir = Project::GetProjectDir();
    }

    // check if this is the error file, then we register it
    if ( !is_dir($rootdir) )
    {
      $this->registerFile( $rootdir );
      return;
    }

    // check if the directory is on stack to avoid infinite recursion
    if ( ! in_array( $rootdir , $stack )  )
    {

      // add this directory to the safety stack
      $stack[] = $rootdir;

      // register all error logs in this directory
      foreach ( glob( "{$rootdir}/error_log" ) as $entry )
      {
        $this->registerErrorLogFile( $entry );
      }

      // search all other directories
      foreach ( glob( "{$rootdir}/*" , GLOB_ONLYDIR ) as $dir )
      {
        $this->indexErrorLogFiles( $dir , $stack );
      }

      // remove current dir from the stack
      array_pop( $stack );

    }
    else
    {
      // surely would be going into inifinite recursion since $rootdir is already on stack
      // so break off from here
      return;
    }
  }


  // register a single error_log file
  private function registerErrorLogFile( $filename )
  {


    $stat = stat($filename);
    $size = filesize($filename);

    // descriptor of the current state of the error_log
    $descriptor = array(
        "filename" => $filename
      , "mtime" => $stat['mtime']
      , "size" => $size
    );

    // check if already registered and has changes to it
    if (
      is_array($this->storage[ $filename ])
        &&
      $this->storage[ $filename ]['mtime'] != $descriptor['mtime']
    )
    {
      // get the descriptors
      $old_descriptor = $this->storage[ $filename ];
      $new_descriptor = $descriptor;

      // get the sizes
      $old_size = $old_descriptor['size'];
      $new_size = $new_descriptor['size'];

      // calculate changes
      $diff_size = $new_size - $old_size;

      // adjust the start and length
      $startByte = $old_size;
      $lengthBytes = $diff_size;

      // maybe the file has been truncated manually?
      if ( $lengthBytes <= 0 ) {
        $startByte = 0;
        $lengthBytes = $new_size;
      }

      // track these new errors
      $this->trackErrors( $filename , $startByte , $lengthBytes );

    }

    // store the error_log state descriptor
    $this->storage[ $filename ] = $descriptor;


  }

  // called when new errors have been found
  private function trackErrors( $filename , $startByte , $lengthBytes )
  {
    // open the source error log
    $fp = fopen($filename,'r');

    // skip to the "new" part
    fseek( $fp, $startByte );

    // read file to the end and write it to the project error log
    while (!feof($fp))
    {
      // read a chunk from given error log file
      $chunk = fgets($fp, self::$fileReadBufferSize);

      // copy the chunk to the project error log file
      file_put_contents( $this->projectErrorLogFile , $chunk , FILE_APPEND | LOCK_EX );

    }

    // close the file
    fclose( $fp );

    // raise the event
    $this->onTrackErrors( $filename, $startByte , $lengthBytes );
  }

  public function startAsyncScan()
  {
    // cannot start in asynchronous mode or if already started
    if ( defined( '__ASYNCHRONOUS_MODE__' ) || $this->startedAsyncScan )
    {
      return;
    }

    $this->startedAsyncScan = true;

    // use the lock file not to recursively run
    if ( ! file_exists( $this->lockFile ) )
    {
      $this->async()->__internalAsynchronousScanningProcedure( getmypid() );
    }

  }

  private function isProcessRunning( $pid )
  {
    return file_exists( "/proc/$pid" );
  }

  // should never be called outside this class, use startAsyncScan instead
  public function __internalAsynchronousScanningProcedure( $pid )
  {
    // cannot run if not in asynchronous mode
    if ( ! defined( '__ASYNCHRONOUS_MODE__' ) )
    {
      throw new Exception('Cannot run in non async mode');
    }

    // lock it
    file_put_contents( $this->lockFile , microtime( true ) );

    $timeLeft = self::$maxParentProcessWaitTime;

    // first iteration doesn't sleep
    $iterationSleepTime = 0;
    do
    {
      // sleep for some time if not first iteration
      sleep( $iterationSleepTime );

      // run the actual work
      $this->scan( Project::GetProjectDir() );

      // decrement the timer
      $timeLeft--;

      // set the sleep time for next iteration
      $iterationSleepTime = 1;

    }
    while ( $this->isProcessRunning( $pid ) && $timeLeft > 0  );

    // unlock it
    if (file_exists( $this->lockFile ))
      unlink( $this->lockFile );

  }

  // compare two errors for a descending sort
  public static function errorCompare( $a , $b )
  {
    return strtotime( $a['date'] . ' ' . $a['time'] ) < strtotime( $b['date'] . ' ' . $b['time'] );
  }

  // create a structured array of errors from text lines or array of lines
  public static function getStructuredErrors( $mixed , $sortByDateDesc = true )
  {

    // determine the format of the argument
    if ( !is_array( $mixed ) ) {
      $errorLines = explode("\n",$mixed);
    } else {
      $errorLines = $mixed;
    }

    // the dumpage goes here once an error is collected from the lines
    $structuredErrors = array();

    // error descriptor is empty to help indicate the start
    $e = null;

    foreach ( $errorLines as $error )
    {

      // match for date, time and timezone at begining of the error line
      $matched = preg_match( self::$errorBeginRegexPattern , $error , $matches );

      if ( $matched )
      {
        if ( $e != null )
        {
          // already have a fully collected error description, so dump it
          $structuredErrors[] = $e;
        }

        // start a new error description with help of matched parts
        foreach ( self::$errorDescriptionFields as $fieldName => $regexMatchPosition )
        {
          $e[ $fieldName ] = $matches[ $regexMatchPosition ];
        }

      }
      else if ( is_array( $e ) )
      {
        // must have matched the date stamp before,
        // so concat the message to the last descriptor
        $e['message'] .= "\n" . $error;
      }
    }

    if ( $e != null )
    {
      // the last collected error is still waiting to be dumped
      $structuredErrors[] = $e;
    }

    // sort by recency -- more recent errors go first
    if ( $sortByDateDesc )
      usort( $structuredErrors , array( self , 'errorCompare' ) );

    return $structuredErrors;

  }


  public function getErrors( $maxLines = 100 , $sortByDateDesc = true )
  {

    if ( !file_exists ($this->projectErrorLogFile) ) {
      // error file doesn't exist so return empty array
      return array();
    }

    // be safe
    $workingFile = escapeshellarg( $this->projectErrorLogFile );

    // prep the command
    $command = "tail -n {$maxLines} {$workingFile}";

    // execute the tail command and gather output
    exec( $command , $output );

    $structuredErrors = self::getStructuredErrors( $output , $sortByDateDesc );

    // return the errors
    return $structuredErrors;

  }


}


?>