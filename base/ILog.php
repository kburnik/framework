<?

interface ILog {

	// write to log
	function write($tag,$text,$level = 'VERBOSE',$data = array());

	// clear the log
	function clear();
	
	// tail the log and leave a maximum of numLines
	function tail( $numLines );
	
	// encode data to be written (i.e. json_encode, var_export, etc.)
	function encode( $data );
	
	// read newest lines
	function readTop( $numBottomLines = 1000 );
	
	
}

?>