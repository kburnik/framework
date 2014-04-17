<?

include_once( dirname( __FILE__ ) . '/.testsuite.include.php' );


class MySQLEntityModelTestCase extends EntityModelTestCase
{

	public static $inherits = array('EntityModelTestCase');

	
	public function __construct()
	{
	
		$this->articleModel = new ArticleModel( new ArticleModelMySQLDataDriver() );
		
		$qdp = Project::GetQDP();
		
		$qdp->prepareTable( 
			"article" ,
			array(
				  "title" => "varchar(256) not null"
				, "created" => "datetime null"
				, "id_category" => "int(4) unsigned null"
			)
		);
		
		$qdp->truncate( "article" );
	
	}
	
	/*
	
		Rest of methods are inherited from EntityModelTestCase
		
	*/
	
	public function __destruct() 
	{
	
		$qdp = Project::GetQDP();
		
		$qdp->drop( 'article' );
	
	}
	


}



?>