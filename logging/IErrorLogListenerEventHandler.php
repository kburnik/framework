<?interface IErrorLogListenerEventHandler {  // occurs when new errors have been detected across files  public function onTrackErrors( $filename, $startByte , $countBytes );  }?>