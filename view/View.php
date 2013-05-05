<?
include_once(dirname(__FILE__)."/../base/Base.php");

abstract class View extends Base implements ArrayAccess, IteratorAggregate, Countable, Serializable {

	public function __construct($template = null) {
		$this->initialize();
		$models = $this->getUsedModels();
		
		// produce all parts if template is set
		if ($template !== null && file_exists($template)) {
			echo produceview($template,$this);
		}
		
	}
	
	
	// return array of used models
	public abstract function getUsedModels();
	
	// initialize object
	public abstract function initialize();
	
	public static function getView($filename,$values = null) {
		return produce(get_once('./views/'.$filename),$values);
	}
	
	// ArrayOffset
	public function offsetGet($offset) {	
        return 
		(method_exists($this,$offset)) 
			?  
				$this->$offset()
			:
				(
					( isset($this->$offset)  )
							? 
						$this->$offset 
							: 
						"Missing view method/variable $offset"
				)
			;
    }
	
    public function offsetExists($offset) {
        return method_exists($this,$offset);
    }
	
	public function offsetSet($offset, $value) {
        //
    }
	
    public function offsetUnset($offset) {
        //
    }

	
	// IteratorAggregate
	public function getIterator() {
		// if (!is_array($this->data)) $this->data = array();
		// return new ArrayIterator($this->data);
	}
	
	function valid() {
        // var_dump(__METHOD__);
        // return isset($this->data[$this->position]);
    }
	
	// Countable
	function count() {
		// return count($this->data);
	}
	
	// Serializable
	public function serialize() {
        // return serialize($this->data);
    }
	
    public function unserialize($data) {
        // $this->data = unserialize($data);
    }
	
}
?>