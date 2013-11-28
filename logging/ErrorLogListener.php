<?

// listens to error_log files and stores new errors to the global error log for the project
class ErrorLogListener extends BaseModel {

	private 
		// the storage for keeping track of the error log files
		$storage,
		
		// the file in which all errors get copied		
		$projectErrorLogFile, 
		
		// once destroyed, can initiate a default scan
		$runDefaultScanAsyncOnDestroy = false
		
	;
	
	
	public function __construct( ) {
	
		// let parent do it's thing
		parent::__construct();
		
		// create a storage for keeping track of error logs
		$this->storage = new FileStorage( Project::GetProjectDir('/gen').'/error.log.state.php'  );
		
		// setup the destination file in which output will be copied to
		$this->projectErrorLogFile = Project::GetProjectDir('/gen')."/project_error_log";
		
		
	}
		
	
	// scan for new errors
	public function scan( $rootdir = null ) {
		
		// index the changes of the error_log files
		$this->indexErrorLogFiles( $rootdir );
		
	}
	
	// will the listener asynchronously start the default scan after being destroyed?		
	public function scanAsyncOnDestroy( $runDefaultScanAsyncOnDestroy ) 
	{
	
		// prep the signal for the __destruct
		$this->runDefaultScanAsyncOnDestroy = $runDefaultScanAsyncOnDestroy;
		
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
		if (  ! in_array( $rootdir , $stack )  ) 
		{
		
			// add this directory to the safety stack
			$stack[] = $rootdir;			
			
			// register all error logs in this directory
			foreach ( glob("$rootdir/error_log") as $entry )
			{
				$this->registerErrorLogFile( $entry );
			}
			
			// search all other directories
			foreach ( glob("$rootdir/*",GLOB_ONLYDIR) as $dir ) 
			{
				$this->indexErrorLogFiles( $dir , $stack );
			}
			
			// remove current dir from the stack
			array_pop($stack);
			
		} 
		else 
		{
			// going into inifinite recurstion since this is on stack
			// break off 
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
			
			// track these new errors
			$this->trackErrors( $filename , $old_size , $diff_size );		
			
		}
		
		// store the error_log state descriptor
		$this->storage[ $filename ] = $descriptor;				
		
	}
	
	// called when new errors have been found
	private function trackErrors( $filename , $startByte , $countBytes )
	{
		$fp = fopen($filename,'r');
		fseek($fp,$startByte);
		
		$chunksize = 4096;
		$remainBytes = $countBytes;
		
		while (!feof($fp)) {
			$chunk = fgets($fp, $chunksize);
			file_put_contents( $this->projectErrorLogFile , $chunk , FILE_APPEND | LOCK_EX );
		}
		
		fclose( $fp );
	}
	
	
	public function __destruct()
	{
		
		if ( $this->runDefaultScanAsyncOnDestroy ) 
		{
			$this->async()->scan();
		}
		
	}
	
	
	// produce an intentional error, for testing!
	public static function ProduceIntentionalError() 
	{
		echo 5 / 0;
	}

}




?>