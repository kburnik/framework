<?

class _cache {
	var $map;
	var $calls;
	var $values;
	var $call_result = null;
	var $identifier = null;
	var $timemap = null;
	var $default_duration = 60;
	var $duration = 60;
	
	function uselocal() {
		// TODO: implement
		return $this;
	}
	
	function useglobal() {
		// TODO: implement
		return $this;
	}
	
	function load() {
		// load the cache
		$this->values = $_SESSION["_cache"];
		$this->timemap = $_SESSION["_timemap"];
	}
	
	function store() {
		// store the cache
		$_SESSION["_cache"] = $this->values;
		$_SESSION["_timemap"] = $this->timemap;
	}
	
	function reset() {
		// reset identifier
		$this->identifier = null;
		// reset the calls
		$this->calls = array();
		// reset the duration to default_duration
		$this->duration = $this->default_duration;
	}
	
	function __construct(){
		$this->load();
		$this->reset();
	}
	
	function __destruct() {
		$this->store();
	}
	
	function __call($function,$arguments) {
		// store each call, and calculate part of the identifier
		$this->identifier .= $function."|".md5(json_encode($arguments)).";";
		$this->calls[] = array($function,$arguments);
		return $this;
	}
	
	
	function runcalls() {
		foreach ($this->calls as $call) {
			list ($function,$arguments) = $call;
			if (is_object($this->call_result)) {
				$obj = $this->call_result;
				$this->call_result = call_user_func_array(array($obj,$function),$arguments);
			} else {
				$this->call_result = call_user_func_array($function,$arguments);
			}
		}
	}
	
	
	function returncached() {
		// prolongue the expiration time
		$this->timemap[$this->identifier] = micronow() + $this->duration * 1000;
		
		// return cached result
		return $this->values[$this->identifier];
	}
	
	function returncalls() {
		
		// run the stored calls
		$this->runcalls();
		// cache the result
		$this->values[$this->identifier] = $this->call_result;
		// set the result expiration
		$this->timemap[$this->identifier] = micronow() + $this->duration * 1000;
		
		
	
		// return result
		return $this->call_result;
	}
	
	function start($duration=null) {
		// set this instance's duration
		if ($duration!=null) $this->duration = $duration;
		return $this;
	}
	
	
	
	function end() {
		// calculate identifier
		$this->identifier = md5($this->identifier);
		
		// the value is cached;
		if (isset($this->values[$this->identifier]) && $this->timemap[$this->identifier] >= micronow()) {
			// echo "Returning cached";
			$result = $this->returncached();
		} else {
			unset($this->values[$this->identifier]);
			unset($this->timemap[$this->identifier]);
			$result = $this->returncalls();
		}
		
		$this->reset();
	
		return $result;
	}
	
}


function cached($duration=null) {
	global $_cache;
	if (!isset($_cache)) {
		$_cache = new _cache();
	}
	return $_cache->start($duration);
}

function lcached($duration=null) {
	return cached($duration)->uselocal();
}

function gcached($duration=null) {
	return cached($duration)->useglobal();
}

?>