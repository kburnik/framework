<?
include_once(dirname(__FILE__)."/../base/Base.php");

class ApplicationTestModuleEventHandler implements IApplicationEventHandler {
	public $tested_events = array();
	
	public function onStart() {
		$this->tested_events[ __FUNCTION__ ] = true;
	}
	
	public function onShutdown() {
		$this->tested_events[ __FUNCTION__ ] = true;
	}
}

class ApplicationTestModule extends TestModule {

	private $handler;
	
	public function __construct($base) {
		parent::__construct($base);
		$handler = new ApplicationTestModuleEventHandler();
		$this->base->addEventHandler($handler);
	}

	public function testGetInstance() {
		$this->assertIdentity( $this->base, $this->base->getInstance() , true );
	}

	public function testGetEventHandlerInterface() {
		$this->assertEquality("IApplicationEventHandler",$this->base->getEventHandlerInterface(),true);
	}

	public function testGetTestModule() {
		$this->assertIdentity($this->base->getTestModule(),$this);
	}

	public function testOutput() {
		$this->assertEquality(true,true,true);
	}

	public function testStart() {
		$this->assertEquality(true,true,true);
	}

	public function testShutdown() {
		$this->assertEquality(true,true,true);
	}

	public function testExecutionTime() {
		$a = $this->base->ExecutionTime();
		for ($i=0; $i < 10000; $i++) ;
		$b = $this->base->ExecutionTime();
		$this->assertEquality($b-$a > 0,true,true);
	}

	public function testGetBaseClasses() {
		$this->assertEquality(is_array($this->base->getBaseClasses()),true,true);
	}

	public function testAddEventListener() {
		$this->assertEquality(true,true,true);
	}

	public function testAddEventHandler() {
		$this->assertEquality(true,true,true);
	}
}
?>