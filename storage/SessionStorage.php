<?
include_once(dirname(__FILE__)."/../base/Base.php");

class SessionStorage extends Storage {

	private $identifier;

	function __construct($identifier = 'default') {
		session_start();
		$this->identifier = $identifier;
		parent::__construct();
	}

	function load() {
		$this->setData( $_SESSION["SessionStorage-{$this->identifier}"] );
	}
	
	function store() {
		$_SESSION["SessionStorage-{$this->identifier}"] = $this->getData();
	}
}

?>