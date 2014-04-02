<?


abstract class EntityField implements IEntityField
{

	private $descriptor = array();
	
	protected $isPrimaryKey = false;
	
	protected $isNullField = false;
	
	protected $nullStatusSet = false;
	
	
	public function isPrimaryKey()
	{
		return $this->isPrimaryKey;
	}
	
	protected function attach( $string )
	{
		$this->descriptor[] = $string;
		
		return $this;
	} 

	public function reset()
	{
	
		// reset all vars;

		$this->descriptor = array();
		
		$this->isPrimaryKey = false;		
						
		$this->isNullField = false;
		
		$this->nullStatusSet = false;
	}
	
	public function yield() 
	{
		// add null status
		if ( ! $this->nullStatusSet )
		{
			if ( $this->isNullField ) 
				$this->IsNull();
			else
				$this->NotNull();
		}
			
			
		$res = implode(" ",  $this->descriptor);
		
		$this->reset();
		
		return $res;
	}

}


?>