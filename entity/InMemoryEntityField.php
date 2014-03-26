<?

class InMemoryEntityField extends EntityField
{


	function getNullClause( $notNull = true )
	{
		return ($notNull) ? "IN_MEMORY_NOT_NULL" : "IN_MEMORY_NULL";
	}

	public function PrimaryKey()
	{
		$this->isPrimaryKey = true;
		return $this->attach("IN_MEMORY_PRIMARY_KEY()");
	}
	
	public function ForeignKey( $refTable , $refField )
	{
		return $this->attach("IN_MEMORY_FOREIGN_KEY($refTable,$refField)");;
	}
	
	public function Integer($size , $notNull = true)
	{
		
		$nullClause = $this->getNullClause( $notNull );
		return $this->attach("IN_MEMORY_INTEGER($size) $nullClause");
		
	}
	
	public function VarChar( $size , $notNull = true )
	{
		$nullClause = $this->getNullClause( $notNull );
		return $this->attach("IN_MEMORY_VARCHAR($size) $nullClause");
	}
	
	public function Text( $notNull = true )
	{
		$nullClause = $this->getNullClause( $notNull );
		return $this->attach("IN_MEMORY_TEXT() $nullClause");
	}
	
	public function DateTime( $notNull = true )
	{
		$nullClause = $this->getNullClause( $notNull );
		return $this->attach("IN_MEMORY_DATETIME() $nullClause");
	}
	
	public function Date( $notNull = true )
	{
		$nullClause = $this->getNullClause( $notNull );
		return $this->attach("IN_MEMORY_DATE() $nullClause");
	}
	
	public function Decimal( $total , $decimal ,  $notNull = true )
	{
		$nullClause = $this->getNullClause( $notNull );
		return $this->attach("IN_MEMORY_DECIMAL($total,$decimal) $nullClause");
	}
	
	public function Enum()
	{
		$values = implode(',',func_get_args());
		return $this->attach("IN_MEMORY_ENUM($values)");
	}
	
}

?>