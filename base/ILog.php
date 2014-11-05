<?

interface ILog {
  function write($tag, $text, $level = 'VERBOSE', $data = array());
  function clear();
  // Tail the log and leave a maximum of numLines.
  function tail($numLines);
  // Encode data to be written (i.e. json_encode, var_export, etc.).
  function encode($data);
  // Read newest lines.
  function readTop($numBottomLines = 1000);
}

?>