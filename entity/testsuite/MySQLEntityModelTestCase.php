<?

include_once( dirname( __FILE__ ) . '/.testsuite.include.php' );


class MySQLEntityModelTestCase extends EntityModelTestCase
{

  public static $inherits = array('EntityModelTestCase');


  // @Override
  public function __construct()
  {

    $this->articleModel = new ArticleModel( new ArticleModelMySQLDataDriver() );
    $this->categoryModel = new CategoryModel( new MySQLDataDriver() );

    $qdp = Project::GetQDP();

    $qdp->prepareTable(
      "article" ,
      array(
          "title" => "varchar(256) not null"
        , "created" => "datetime null"
        , "modified" => "datetime null"
        , "id_category" => "int(4) unsigned null"
      )
    );

    $qdp->truncate( "article" );

    $qdp->prepareTable(
      "category",
      array(
          "title" => "varchar(256) not null"
      ));

    $qdp->truncate( "category" );
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
