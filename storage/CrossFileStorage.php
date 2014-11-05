<?


class CrossFileStorage extends Storage {
  protected $pathWithPrefix;

  function __construct($pathWithPrefix) {
    $this->pathWithPrefix = $pathWithPrefix;
    parent::__construct();
  }

  function read($variable)
  {
    $this->onRead($variable);
    if (!isset($this->data[$variable]) && $this->exists($variable)) {
      $this->data[$variable] = include($this->pathWithPrefix.".".$variable.".php");
    }
    return  $this->data[$variable];
  }

  function write($variable,$value) {
    if ( $variable === null )
    {
      $variable = count( $this->data );
    }
    if ($this->data[$variable] !== $value) {
      $this->data[$variable] = $value;
      file_put_contents($this->pathWithPrefix.".".$variable.".php",'<? return '.var_export($value,true). '; ?>',LOCK_EX);
      $this->onWrite($variable,$value);
    }
  }

  function clear($variable) {
    if ($this->exists($variable)) {
      $this->onClear($variable);
      unset($this->data[$variable]);
      unlink($this->pathWithPrefix.".".$variable.".php");
    }

  }

  function exists($variable) {
    $exists = file_exists($this->pathWithPrefix.".".$variable.".php");
    if (!$exists) Console::WriteLine("$variable not in Storage!");
    return $exists;
  }


  function load() {

  }

  function store() {

  }

}

?>