<?
include_once( dirname( __FILE__ ) . '/.testsuite.include.php' );

class EntityTestCase extends TestCase
{

  public function createArticleEntity_EmptyArticle_CreatesArticleEntityObject()
  {

    $article = new Article();

    $this->assertEqual( true, $article instanceof Article );

  }


  public function createArticleFromArray_SampleArrayData_FillsPublicMembers()
  {

    $articleAsArray = array(
      'id' => 1 ,
      'title' => "First article" ,
      'created' => "2013-12-23 21:00:00"
    );

    $article = new Article( $articleAsArray );

    $expected = $articleAsArray;

    $measured = array (
        'id' => $article->id
      , 'title' => $article->title
      , 'created' => $article->created
    );

    $this->assertEqual( $expected , $measured );

  }

  public function createArticleFromArray_SampleArrayData_DoesNotFillNonPublicMembers() {
    $articleAsArray = array(
        'id' => 1
      , 'title' => "First article"
      , 'created' => "2013-12-23 21:00:00"
      , 'nonExistingValue' => "Some private value"
    );

    $article = new Article( $articleAsArray );

    $expected = array(
        'id' => 1
      , 'title' => "First article"
      , 'created' => "2013-12-23 21:00:00"
      , 'nonExistingValue' => null
    );

    $measured = array (
        'id' => $article->id
      , 'title' => $article->title
      , 'created' => $article->created
      , 'nonExistingValue' => $article->nonExistingValue
    );

    $this->assertEqual( $expected , $measured );

  }


  public function setAndGetArticlesCategoryViaMagic_sampleCategory_createsAndReturnsTheProvidedCategory()
  {
    $category = new Category(array(
        'id' => 111
      , 'title' => 'Sample Category'
    ));


    $article = new Article(array(
      'id' => 1 ,
      'title' => "First article" ,
      'created' => "2013-12-23 21:00:00"
    ));


    // set via magic
    $article->category = $category;

    $this->assertEqual( 111, $article->id_category );

    $fetchedCategory = $article->getCategory();

    $magicFetchedCategory = $article->category;

    $this->assertEqual( $category, $magicFetchedCategory );

    $this->assertEqual( $category, $fetchedCategory );

    $this->assertEqual( $magicFetchedCategory, $fetchedCategory );

    ///

    $category = new Category(array(
        'id' => 222
      , 'title' => 'Second Sample Category'
    ));

    // set explicitly
    $article->setCategory( $category );

    $this->assertEqual( 222, $article->id_category );

    $fetchedCategory = $article->getCategory();

    $magicFetchedCategory = $article->category;


    $this->assertEqual( $category, $magicFetchedCategory );

    $this->assertEqual( $category, $fetchedCategory );

    $this->assertEqual( $magicFetchedCategory, $fetchedCategory );


  }

  public function getFields_sampleArticle_returnsFieldsOfArticleInDefinitionOrder()
  {
    $article = new Article(array(
      'id' => 1 ,
      'title' => "First article" ,
      'created' => "2013-12-23 21:00:00"
    ));


    $fields = array(
      'id','title','created','id_category'
    );

    $this->assertEqual( $fields , $article->getFields() );

  }


  public function EntityGetFieldsStatically_sampleArticle_returnsFieldsOfArticleInDefinitionOrder()
  {
    $article = new Article(array(
      'id' => 1 ,
      'title' => "First article" ,
      'created' => "2013-12-23 21:00:00"
    ));


    $fields = array(
      'id','title','created','id_category'
    );

    $this->assertEqual( $fields , Entity::getFields('Article') );

  }

  public function ArticleGetFieldsStatically_sampleArticle_returnsFieldsOfArticleInDefinitionOrder()
  {
    $article = new Article(array(
      'id' => 1 ,
      'title' => "First article" ,
      'created' => "2013-12-23 21:00:00"
    ));


    $fields = array(
      'id','title','created','id_category'
    );

    $this->assertEqual( $fields , Article::getFields('Article') );

  }



}



?>