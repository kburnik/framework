<?
include_once(dirname(__FILE__)."/Base.php");

class Console {

	private static $enabled = true;

	public static function IsEnabled() {
		return self::$enabled;
	}
	
	public static function Enable() {
		self::$enabled = true;
	}
	
	public static function Disable() {
		self::$enabled = false;
	}
	
	private static $Singleton;
	private static function getInstance() {
		if (!isset(self::$Singleton)) {
			self::$Singleton = new Console();
		}
		return self::$Singleton;
	}
	
	/// static	
	
	public static function Write($text) {		
		self::getInstance()->addText($text);
	}
	
	public static function WriteLine($text) {
		if (defined('TERMINAL_MODE_DETAILS')) {
			echo "$text\n";
		}
		self::getInstance()->addText($text."\n");
	}
	
	public static function Flush() {
		self::getInstance()->flushText();		
	}
	
	
	/// object
	
	public function __construct () {
	
	}
	
	private $text = "";
	private function addText($text) {
		
		if (!self::IsEnabled()) { return; }
		
		if (defined('SHELL_MODE')) echo $text;
		if (defined('PRODUCTION_MODE')) return;
		
		$lines = explode("\n",$text);
		$et = Application::ExecutionTime();
		foreach ($lines as $line) {
			if ($line != null) {
				$this->text[] = array($et,$line);
				
				
				if (!defined('PRODUCTION_MODE')) {
					$parts = array();
					while (strlen($line) > 0) {
						$parts[] = substr($line,0,40);
						$line = substr($line,40);
					}
					$tm = round($et*1000,2);
					$tm = str_repeat(' ',max(5-strlen($tm),0)).$tm;
					$index = count($this->text);
					foreach ($parts as $line) {
						$line = "Z-$index: [ {$tm} ms ] ".$line;						
					}					
					$last_tm = $tm;
				}
				
			}
		}
		
		
	}
	
	private $flushed = false;
	public function flushText(){
		if (defined('PRODUCTION_MODE')) {
			header('X-PRODUCTION_MODE: true');
			return;
		}
		
		if (defined('TERMINAL_MODE')) {
			return;
		}
		// if ($this->flushed) return;
		// $this->flushed = true;
	
		$c = count($this->text);
				
		foreach ($this->text as $i=>$compound) {
			list($time, $line) = $compound;
			$tm = number_format($time*1000,2);			
			
			$tm = str_repeat(' ',max(9-strlen($tm),0)).$tm;
			$index = str_repeat("0",strlen($c)-strlen($i)).$i;
			header("Z-$index: [ {$tm} ms ] ".$line);			
		}
		
		
	}
	
	public function __destruct() {
		// $this->flush();
		
	}
	
}
?>
