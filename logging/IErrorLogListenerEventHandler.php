<?phpinterface IErrorLogListenerEventHandler {  // Should be called when new errors have been detected across files.  public function onTrackErrors($filename, $startByte, $countBytes);}
