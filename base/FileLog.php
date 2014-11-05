<?



class FileLog implements ILog {

  private $logFile;

  public function __construct( $logFile ) {

    // check if log file is writeable

    if (!file_exists($logFile)) {
      $ok = @touch($logFile);
      if (!$ok) {
        throw new Exception("Cannot create logfile '$logFile'");
      }
    }

    if (!is_writable($logFile)) {
      throw new Exception("Provided log file '$logFile' is not writeable!");
    }

    $this->logFile = $logFile;

  }

  // overridebale (default implementation for encoding the LOG data)
  public function encode($data) {
    return json_encode($data);
  }

  public function write($tag,$text,$level = 'VERBOSE',$data = array()) {
    $text = $this->encode($text);
    $data = $this->encode($data);
    $date = date("Y-m-d H:i:s");
    $timestamp = intval(microtime(true)*1000);
    $line = "{$date}\t{$timestamp}\t{$level}\t{$tag}\t{$text}\t{$data}\n";
    return file_put_contents($this->logFile,$line,FILE_APPEND);
  }

  // clear the entire log
  public function clear() {
    return @unlink($this->logFile);
  }

  // tail the log and leave a maximum of numLines
  public function tail( $numLines ) {
    $numLines = intval($numLines);
    $logFile = $this->logFile;
    $tempFile = "{$logFile}.tmp";
    $command =
        "cat \"{$logFile}\" | tail -n {$numLines} > \"{$tempFile}\""
      . " && mv {$tempFile} {$logFile}"
    ;
    $ok = exec($command);
    echo $ok;
  }

  public function readTop( $numBottomLines = 1000 ) {

    $logFile = $this->logFile;
    $tempFile = "{$logFile}.tmp";

    $command =
      "cat \"{$logFile}\" | tail -n {$numBottomLines} | tac > {$tempFile} ";
    ;

    exec( $command );

    $output = file_get_contents( $tempFile );
    unlink($tempFile);
    return $output;
  }

  public function getLogFile() {
    return $this->logFile;
  }

}

?>