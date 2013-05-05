<?
include_once(dirname(__FILE__)."/../system/system.php");


spl_autoload_register(array(Base,'__framework_class_loader'));

///

/**
 * The Abstract Base class
 * Derive this class for the event binding and triggering mechanism
 * @author Kristijan Burnik
 *
 */
abstract class Base {

	private static $eventsEnabled = true;
	private static $onEventTriggeredCallback;

	
	
	/**
	 * used for dynamic class loading
	 * @param unknown_type $class
	 */
	public static function __framework_class_loader($class) {

		// loookup in framework directories
		// lookup in current project directory recursively for ".include" file
		if (self::IncludeFrom($class, realpath( dirname(__FILE__).'/../' ) , '<FRAMEWORK>' )) return;

		// lookup in current directory of first called script
		$file = $class.".php";
		if (file_exists($file)) {
			include_once($file);
			//return;
		}

		// lookup in current project directory recursively for ".include" file
		self::IncludeFrom($class, Project::GetProjectRoot() , Project::getCurrent()->getName() );

	}


	private static $classLocation = null;

	/**
	 * Include a class from a directory and a given project
	 * @param unknown_type $class
	 * @param unknown_type $directory
	 * @param unknown_type $project
	 * @return boolean
	 */
	private static function IncludeFrom($class, $directory , $project = null) {
		// cached map of files getting included
		$classLocationFile = dirname(__FILE__).'/../class.location.cache.php';
		if (self::$classLocation === null) {
			if (file_exists($classLocationFile)) {
				self::$classLocation = include( $classLocationFile );
			} else {
				self::$classLocation = array();
			}
		}

		// cached include
		if ($project !== null && isset(self::$classLocation[$project][$class]) && file_exists(self::$classLocation[$project][$class]) ) {
			include_once(self::$classLocation[$project][$class]);
			return true;
		}

		//Console::WriteLine("Base :: Searching for class {$class} in directory {$directory}");
		$d = dir( $directory );
		$found = false;

		while (false !== ($entry = $d->read())) {
			$path = $d->path."/".$entry;
			if (
			$entry[0] != '.'
			&& is_dir($path)
			&& file_exists($path."/.include")
			) {

				$file = $path . "/" . $class . '.php';
				if (file_exists($file)) {
					//Console::WriteLine("Base :: Found class {$class} in {$file} ... including");
					if ($project !== null) {
						self::$classLocation[$project][$class] = realpath( $file );
						file_put_contents($classLocationFile,'<? return ' . var_export(self::$classLocation,true) . '?>');
					}
					include_once($file);
					$found = true;
					//Console::WriteLine("Base :: Class {$class} included");
					break;
				} else {
					if (self::IncludeFrom($class,$path,$project)) {
						$found = true;
						break;
					}
						
				}
			}
		}

		$d->close();

		return $found;

	}


	// list of dervied classes extending the Base class
	private static $baseClasses = array();
	private static $events = null;

	private $eventCallbacks = array();



	protected function getEventHandlerInterface() {
		$class = get_class($this);
		$interface_name = "I{$class}EventHandler";
		return $interface_name;
	}

	/**
	 * Geta new Base derived class' TestModule instance
	 * @return TestModule
	 */
	public function getTestModule() {
		$class = get_class($this);
		$testmodule_name = "{$class}TestModule";
		return new $testmodule_name($this);
	}

	function __construct() {
		// register this base class
		self::$baseClasses[] = $this;
	}

	function __destruct() {

	}


	/**
	 * return all defined base classes
	 * @return array of Base:
	 */
	public static function GetBaseClasses() {
		return self::$baseClasses;
	}


	/**
	 * return list of all class methods which are events defined on this Base derived class
	 * @return array of string:
	 */
	protected function getEvents() {
		static $events;
		if ($events === null) {
			$events = array_flip( get_class_methods(  $this->getEventHandlerInterface() ) );
		}
		return $events;
	}




	// some magic for the event methods
	public function __call($methodName,$methodArgs) {
		if (array_key_exists($methodName,$this->getEvents())) {
			$this->triggerEvent($methodName,$methodArgs);
		} else {
			throw new Exception ("Call to undefined method ".get_class($this)."->$methodName!");
		}

	}

	/**
	 * Add an single event listener to this Base derived class
	 * @param string $name
	 * @param mixed $callback
	 * @throws Exception
	 */
	public function addEventListener($name,$callback) {
		$events = $this->getEvents();
		if ($events == null || !array_key_exists($name,$events)) {
			throw new Exception("Event named '{$name}' doesn't exist in the EventHandler!" );
		} else  {
			$this->eventCallbacks[$name][] = $callback;
		}
	}




	/**
	 * Enable event mechanism for all Base derived classes (enabled by default)
	 */
	public static function EnableEvents() {
		self::$eventsEnabled = true;
	}

	/**
	 * Disable event mechanism for all Base derived classes (enabled by default)
	 */
	public static function DisableEvents() {
		self::$eventsEnabled = false;
	}



	/**
	 * Set up a general event handler when each event gets fired
	 * @param callback $method
	 */
	public static function addEventTriggerCallback( $method ) {
		self::$onEventTriggeredCallback[] = $method;
	}


	/**
	 * Occurs when each event gets triggered
	 * @param string $className
	 * @param string $eventName
	 * @param array of mixed $arguments
	 */
	private static function onEventTriggered($className,$eventName,$arguments) {
		if (isset(self::$onEventTriggeredCallback)) {
			foreach (self::$onEventTriggeredCallback as $method) {
				call_user_func_array($method,array($className,$eventName,$arguments));
			}
		}
	}

	// prevent recursive event triggering with map!
	// private $triggering = array();

	/**
	 * Trigger an event internally
	 * @param string $name
	 * @param array of mixed $arguments
	 */
	private function triggerEvent($name,$arguments = null) {
		// prevent triggering if events are disabled
		if (!self::$eventsEnabled) return;

		// if ($this->triggering[ $name ] == true) return;



		// $this->triggering[ $name ] =  true;
		if (array_key_exists($name,$this->eventCallbacks)) {
			foreach ($this->eventCallbacks[$name] as $callback) {
				$callback_name = (is_array($callback)) ? $callback[1] : "Annonymous" ;
				if (!defined('PRODUCTION_MODE')) {
					if (!defined('SKIP_EVENT_LOGGING')) {
						Console::WriteLine("Triggering event ".get_class($this)."->".$callback_name." with arguments ". var_export($arguments,true));
					}
				}
				call_user_func_array($callback,$arguments);
			}
			self::onEventTriggered(get_class($this),$name,$arguments);
		}


		// $this->triggering[ $name ] = false;
	}

	/**
	 * Assign a new complete event handler class as a listener,
	 * each future fired event will be forwarded to the added event handler
	 * @param unknown_type $eventHandler
	 * @throws Exception
	 */
	public function addEventHandler($eventHandler) {
		$interface = $this->getEventHandlerInterface();
		if (!$eventHandler instanceof $interface ) {
			throw new Exception("EventHandler must implement interface $interface!");
		} else {
			foreach ( $this->getEvents() as $methodName => $index ) {
				$this->addEventListener($methodName, array($eventHandler,$methodName));

			}
		}
	}



	/**
	 * Add a partial event handler object which listens to some or none of the events.
	 * Does not have to implement the whole interface of a full event handler
	 * @param object $eventHandler
	 */
	public function addPartialEventHandler( $eventHandler ) {
		// bind only to existing methods
		foreach ( $this->getEvents() as $methodName => $index ) {
			if (method_exists($eventHandler,$methodName)) {
				$this->addEventListener($methodName, array($eventHandler,$methodName));
			}
		}

	}

}



?>
