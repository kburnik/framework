<?

class Application extends BaseSingleton {
  private static $Instance;
  private static $Output;
  private static $StartTime;

  private $logOutput = "";
  private $started = false;
  private $shutdown = false;

  public static function getInstance() {
    if (!isset(self::$Instance)) {
      self::$Instance = new Application();
    }
    return self::$Instance;
  }

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

  public function __construct() {}

  public function getEventHandlerInterface() {
    return 'IApplicationEventHandler';
  }

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

  public function log($text) {
    $this->logOutput .= $text;
  }

  private function shutdownApp() {
    if ($this->shutdown)
      return;

    $this->shutdown = true;
    self::$Output = str_replace('%log%',$this->logOutput,self::$Output);

    // get the size of the output
    $size = strlen(self::$Output);

    Console::WriteLine("Application :: Outputing text of length = ". $size . " B");
    Console::WriteLine("Application :: ShutDown");
    Console::WriteLine("-------------------------------------------------------------------------------------");

    if (!headers_sent() )
      Console::Flush();

    ob_end_flush();
    ob_flush();
    flush();

    echo self::$Output;
    $this->onShutdown();
  }

}

?>