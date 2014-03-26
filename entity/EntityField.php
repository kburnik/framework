<?


abstract class EntityField implements IEntityField
{

	private $descriptor = array();
	
	protected $isPrimaryKey = false;
	
	
	protected abstract function getNullClause( $notNull = true );
	
	
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
	}
	
	public function yield() 
	{
		$res = implode(" ",  $this->descriptor);
		
		$this->reset();
		
		return $res;
	}

}


?>