<?
class _storage {

	var $storage_loaded = false;
	var $storage = array();
	var $storage_filename = "";
	
	function __loadstorage () {
		if ($this->storage_loaded) return true;
		$storage_filename = $this->storage_filename;
		if (file_exists($storage_filename)) {
			$this->storage = include($storage_filename);
			
			if (!is_array($this->storage)) {
				$this->storage = array();
				// throw new Exception(realpath($storage_filename). " is invalid! " );
			}
			$this->storage_loaded = true;
			return true;
		
		}
	}
	
	function __savestorage() {
		return file_put_contents($this->storage_filename,"<? return ". var_export($this->storage,true) ."; ?>",LOCK_EX);
	}
	
	function __construct($storage_filename) {
		$this->storage_filename = $storage_filename;
		if (!file_exists($storage_filename)) {
			if (!$this->__savestorage()) {
				trigger_error("Cannot create storage file $storage_filename!",E_USER_ERROR);
			}
		}
	}
	
	function __destruct() {
		$this->__savestorage();
	}
	
	function __set($name,$value) {
		if (isset($this->$name)) {
			$this->$name = $value;
		} else {
			$this->__loadstorage();
			if ($this->storage[$name] !== $value){
				$this->storage[$name] = $value;
			}
			return $this->storage[$name];
		}
	}
	
	function __unset($name) {	
		$this->__loadstorage();
		unset($this->storage[$name]);
	}
	
	function __get($name) {
		if (isset($this->$name)) {
			return $this->$name;
		} else {
			$this->__loadstorage();
			return $this->storage[ $name ];
		}
	}
	
}


?>