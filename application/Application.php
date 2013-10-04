<?
include_once(dirname(__FILE__)."/../base/Base.php");

class Application extends BaseSingleton {
	
	// inhertied from BaseSingleton
	
	private static $Instance;
	public static function getInstance() {
		if (!isset(self::$Instance)) {
			self::$Instance = new Application();
		}
		return self::$Instance;
	}
	
	/// inherited from  Base
	
	public function getEventHandlerInterface() {
		return 'IApplicationEventHandler';
	}
	
	function __construct() {
		//echo "Constructed Application!";
	}
	
	public function getTestModule() {
		static $tm;
		if (!isset($tm)) 
			$tm = new ApplicationTestModule( $this );
		return $tm;
	}
	
	// static
	
	private static $Output;
	private static $StartTime;
	
	public static function Output($text) {
		self::$Output = $text;
	}
	
	
	public static function Start() {
		self::getInstance()->startApp();
	}
	
	public static function Shutdown() {	
		self::getInstance()->shutdownApp();
	}
	
	public static function ExecutionTime() {
		 return microtime(true) - self::$StartTime;
	}
	
	
	/// object
	private $started = false;
	private $shutdown = false;
	
	private function startApp() {
		if ($this->started) return;		
		$this->started = true;
		
		self::$StartTime = microtime(true);
		ob_start(array($this,"Output"));
		register_shutdown_function(array('Application',"Shutdown"));
		Console::WriteLine("-------------------------------------------------------------------------------------");
		Console::WriteLine("Application :: Start");
		$this->onStart();
	}
	
	
	private $logOutput = "";
	public function log( $text ) {
		$this->logOutput .= $text;
	}
	
	private function shutdownApp() {
		if ($this->shutdown) return;
		$this->shutdown = true;
		
		
		
		#ob_end_flush();		
		#ob_start();
		
		self::$Output = str_replace('%log%',$this->logOutput,self::$Output);
		
		
		// get the size of the output
		$size = strlen(self::$Output);

		# header("Content-Length: $size");
		# header('Connection: close');
		
		
		
		Console::WriteLine("Application :: Outputing text of length = ". $size . " B");
		Console::WriteLine("Application :: ShutDown");
		Console::WriteLine("-------------------------------------------------------------------------------------");
		Console::Flush();
		
		
		
		ob_end_flush();		
		ob_flush();
		flush();
		
		
		echo self::$Output;
		
	
		
		// close current session
		
		
		// flush all output
		
		// get the size of the output
		
		
		// send headers to tell the browser to close the connection
		
		
		//if (session_id()) session_write_close();
		
		

	
				
		$this->onShutdown();
		
	}

}

// Application::getInstance()->getTestModule()->start();


?>