<?
Project::CreateTestCase();


class NonEntity
{
	public $someField;
}



class NakedDummy extends Entity 
{
	public
		$id,
		$name,
		$date,
		$size
		
	;
}


class Dummy extends Entity 
{
	public
			/** Integer(4) PrimaryKey() **/
			$id
			
		,	/** VarChar(32) **/
			$name
		
		,	/** DateTime() **/
			$date
		
		,	/** Integer(8) **/
			$size
		
		,	/** Enum('one','two','three') **/
			$type
		
	;
}

class DummyAutoEnumed extends Entity 
{
	
	const APPLE = 'APPLE_TASTE';
	const ORANGE = 'ORANGE_TASTE';
	const VANILLA = 'VANILLA_TASTE';
	
	public
			/** Integer(4) PrimaryKey() **/
			$id
			
		,	/** AutoEnum(DummyAutoEnumed) **/
			$fruit
		
	;

}


class EntityReflectionTestCase extends TestCase
{

	private $entityClassName , $reflection , $dataDriver;
	
	public function __construct()
	{
		$this->entityClassName = 'Dummy';
		
		$this->dataDriver = new InMemoryDataDriver();
		
		$this->reflection = new EntityReflection( $this->entityClassName , $this->dataDriver );
		
		// $this->qdp = new MySQLProvider( "localhost" );

	}
	
	
	public function construct_nonExistingEntityClassname_throwException()
	{
		$occured = false;
		try {
			$r = new EntityReflection( 'SurelyNonExistingEntityClassName' , $this->dataDriver );
		} catch ( Exception $ex ) 
		{
			$occured = true;
		}
		
		$this->assertEqual( true, $occured );
	}
	
	
	public function construct_nonEntity_throwException()
	{
		$occured = false;
		try {
			$r = new EntityReflection( 'NonEntity' , $this->dataDriver );
		} catch ( Exception $ex ) 
		{
			$occured = true;
		}
		
		$this->assertEqual( true, $occured );
	}
	
	
	public function getFields_DummyEntity_getsAllFields()
	{
		
		
		$fields = $this->reflection->getFields();
		
		$expected = array( 'id', 'name' , 'date' , 'size' , 'type' );
		
		$this->assertEqual( $expected  , $fields );
	
	}
	
	
	public function isDatabaseReady_DummyEntity_returnsTrue()
	{
	
		
	
		$this->assertEqual( true , $this->reflection->isDatabaseReady( ) );
		
	}
	
	
	public function isDatabaseReady_NakedDummyEntity_returnsFalse()
	{
	
		$reflection = new EntityReflection( 'NakedDummy' , $this->dataDriver );
		
		$this->assertEqual( false , $reflection->isDatabaseReady() );
	
	}
	
	
	public function getPrimaryKey_DummyEntity_returnsID()
	{
		
		$pkFieldName = $this->reflection->getPrimaryKey( );
		
		$this->assertEqual( 'id' , $pkFieldName );
		
	}
	
	
	public function getStructure_DummyEntity_getStructure()
	{
		$structure = $this->reflection->getStructure();
		
		$expected = array (
			'id' => 'IN_MEMORY_INTEGER(4) IN_MEMORY_PRIMARY_KEY() IN_MEMORY_NOT_NULL',
			'name' => 'IN_MEMORY_VARCHAR(32) IN_MEMORY_NOT_NULL',
			'date' => 'IN_MEMORY_DATETIME() IN_MEMORY_NOT_NULL',
			'size' => 'IN_MEMORY_INTEGER(8) IN_MEMORY_NOT_NULL',
			'type' => 'IN_MEMORY_ENUM(\'one\',\'two\',\'three\') IN_MEMORY_NOT_NULL',
		 );

		
		$this->assertEqual( $expected , $structure );
	}
	
	public function getStructure_DummyAutoenumedEntity_enumeratesConsts()
	{
	
		$er = new EntityReflection( "DummyAutoEnumed" , $this->dataDriver );
		
		$structure = $er->getStructure();
		
		$expected = "IN_MEMORY_ENUM('APPLE_TASTE','ORANGE_TASTE','VANILLA_TASTE') IN_MEMORY_NOT_NULL";
		
		$this->assertEqual( $expected , $structure['fruit'] );
	
	}
	
	
	

}

?>