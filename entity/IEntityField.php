<?

interface IEntityField
{

	public function PrimaryKey();
	
	public function ForeignKey( $refTable , $refField );
	
	public function Integer($size , $notNull = true);
	
	public function VarChar( $size , $notNull = true );
	
	public function Text( $notNull = true );
	
	public function DateTime( $notNull = true );
	
	public function Date( $notNull = true );
	
	public function Decimal( $total , $decimal ,  $notNull = true );
	
	public function Enum();
	
		
	public function yield();
	
	
}


?>