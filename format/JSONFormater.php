<?
include_once(dirname(__FILE__).'/../base/Base.php');

class JSONFormater implements IOutputFormater {
	private $pretty;
	
	function __construct($pretty = false) {
		$this->pretty = $pretty;
	}

	function Initialize() {	
		header('Content-type: application/json');
	}
	
	function Format($data) {
		if (!$this->pretty) {
			return json_encode($data);
		} else {
			return json_format(json_encode($data));
		}
	}
}

?>