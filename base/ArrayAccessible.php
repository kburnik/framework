<?class ArrayAccessible implements ArrayAccess, IteratorAggregate{	public function offsetGet($offset) 	{	        return $this->$offset;		    }	    public function offsetExists($offset) 	{        return property_exists($this,$offset);    }		public function offsetSet($offset, $value) 	{       $this->$offset = $value;    }	    public function offsetUnset($offset)	{        unset( $this->$offset );    }		// IteratorAggregate	public function getIterator() 	{		return new ArrayIterator($this);	}		function valid() 	{        // var_dump(__METHOD__);        // return isset($this->data[$this->position]);    }	}	?>