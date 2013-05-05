<?
class _ajax {	
	var $output = "";
	var $action_identifier = "action";
	var $header_set = false;
	public function start($action_identifier = "action") {
		$this->action_identifier = $action_identifier;
		return $this;
	}
	
	public function run( $class ) {
		$action = $_REQUEST[ $this->action_identifier ];
		if ($action!='' && method_exists($class,$action)) {
			$this->output = $class->$action();	
		}
		return $this;
	}
		
	public function output( $output = null, $json = true ) {
		if ($output===null) $output = $this->output;
		if (!$this->header_set) {
			header(
			  "Cache-Control: no-cache"
			. "Pragma: nocache"
			. ($json) ? "Content-type: application/json" : ""
			);	
			$this->header_set = true;
		}
		echo( ($json) ? json_encode($output) : $output);
		return $this;
	}
}



function ajax($action_identifier = "action") {
	global $_ajax;
	if (!isset($_ajax)) {
		$_ajax = new _ajax();
	}
	return $_ajax -> start( $action_identifier );
}


?>