<?

class EntityBulk
{

	private $items;
	
	public function __construct( $items )
	{
		
		$this->items = $items;	
	}
	
	public function __call( $method , $params )
	{
		
		foreach ( $this->items as $item )
		{
			
			if ( !method_exists( $item,$method ) )
				throw new Exception("Missing method for bulk operation ".get_class($item)."::{{$method}}");
			
			call_user_func_array( array( $item , $method ) , $params  );
		}
	
			
		return $this;
	
	}
	
	
	public function ret()
	{
		return $this->items;
	}

}


?>