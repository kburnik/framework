<?

class MySQLEntityField extends EntityField
{


	function getNullClause( $notNull = true )
	{
		return ($notNull) ? "NOT NULL" : "NULL";
	}

	public function PrimaryKey()
	{
		$this->isPrimaryKey = true;
		return $this->attach("primary key");
	}
	
	public function ForeignKey( $refTable , $refField )
	{
	
	}
	
	public function Integer($size , $notNull = true)
	{
		
		$nullClause = $this->getNullClause( $notNull );
		return $this->attach("int($size) {$nullClause}");
	}
	
	public function VarChar( $size , $notNull = true )
	{
		$nullClause = $this->getNullClause( $notNull );
		return $this->attach("varchar($size) {$nullClause}");
	}
	
	public function Text( $notNull = true )
	{
		$nullClause = $this->getNullClause( $notNull );
		return $this->attach("text {$nullClause}");
	}
	
	public function DateTime( $notNull = true )
	{
		$nullClause = $this->getNullClause( $notNull );
		return $this->attach("datetime {$nullClause}");
	}
	
	public function Date( $notNull = true )
	{
		$nullClause = $this->getNullClause( $notNull );
		return $this->attach("datetime {$nullClause}");
	}
	
	public function Decimal( $total , $decimal ,  $notNull = true )
	{
		$nullClause = $this->getNullClause( $notNull );
		return $this->attach("decimal($total,$decimal) {$nullClause}");
	}
	
	public function Enum()
	{
		$values = implode(',',func_get_args());
		return $this->attach("enum($values) {$nullClause}");
	}
	
}


?>