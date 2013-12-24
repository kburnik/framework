<?
include_once( dirname( __FILE__ ) . '/.testsuite.include.php' );

class EntityModelTestCase extends TestCase
{

	protected $articleModel;
	
	public function __construct()
	{
	
		$this->articleModel = new ArticleModel( new ArticleModelDataDriver() );
		
	}
	

	public function createArticleObject_articleAsArray_fillsFieldsFromArrayToObject() 
	{
			
		
		$articleAsArray = array(
			'id' => 1 ,
			'title' => "First article" ,
			'created' => "2013-12-23 21:00:00"
		);
		
		
		$article =  $this->articleModel->create( $articleAsArray );
		
		$measured = array (
			  'id' => $article->id
			, 'title' => $article->title
			, 'created' => $article->created
		);
		
		$expected = $articleAsArray;
		
		$this->assertEqual( $expected ,  $measured );
	
	
	}
	
	
	public function insert_SampleArticle_getsNumberOfInserts1()	
	{
		$numInserted = $this->articleModel->insert( array('id'=>1,'title'=>'ArticleOne') );
		
		$this->assertEqual( 1 , $numInserted );
	
	}
	
	
	public function insert_ArticleAsObject_getsNumberOfInserts1()	
	{
	
		$article = new Article( array('id'=>1,'title'=>'ArticleOne') );
	
		$numInserted = $this->articleModel->insert( $article );
		
		$this->assertEqual( 1 , $numInserted );
	
	}
	
	public function insert_ThreeArticlesAsArrays_getsNumberOfInserts3()
	{
	
	
		$articles = array(
			array('id'=>'1','title'=>'One'),
			array('id'=>'2','title'=>'Two'),
			array('id'=>'3','title'=>'Three'),					
		);
		
		$numInserted = $this->articleModel->insert( $articles );
		
		$this->assertEqual( 3 , $numInserted );
	
	}
		
	
	
	public function insert_ThreeArticlesAsObjects_getsNumberOfInserts3()
	{
	
	
		$articles = array(
			new Article(array('id'=>'1','title'=>'One')),
			new Article(array('id'=>'2','title'=>'Two')),
			new Article(array('id'=>'3','title'=>'Three')),					
		);
		
		$numInserted = $this->articleModel->insert( $articles );
		
		$this->assertEqual( 3 , $numInserted );
	
	}
	
	
	public function insert_null_throwsException() {
		
		
		try {
		 $this->articleModel->insert( null );
		} catch (Exception $ex) {
		 
		}
		
		$this->assertEqual( true , $ex instanceof Exception );
	
	}
	
	
	public function insert_otherObjectType_throwsException() 
	{
		
		
		try {
			$this->articleModel->insert( new Category() );
		} catch (Exception $ex) 
		{
		 
		}
		
		$this->assertEqual( true , $ex instanceof Exception );
	
	}
	
	
	public function insertArray_nullAndotherObjectType_throwsException() 
	{
		$mixed = array(
			new Article(array('id'=>'1','title'=>'One')),
			new Category(array('id'=>'2','title'=>'Category')),
			new Article(array('id'=>'3','title'=>'Three')),	
			null
		);
		
		try {
			$this->articleModel->insert( $mixed );
		} catch (Exception $ex) 
		{
		 
		}
		
		$this->assertEqual( true , $ex instanceof Exception );
				
	}
	
	
	public function insertArray_MixedSomeArraySomeArticleObject_insertAcceptsReturns4() 
	{
		$articles = array(
			array('id'=>'1','title'=>'One'),
			new Article(array('id'=>'2','title'=>'Two')),
			array('id'=>'3','title'=>'Three'),
			new Article(array('id'=>'4','title'=>'Four')),					
		);
		
		$numInserted = $this->articleModel->insert( $articles );
		
		$this->assertEqual( 4 , $numInserted );
	
	}
	
	

	public function findById_singleArticleAfterMixedInsertion_returnsSingleInsertedArticleWithMatchingID() 
	{
		$articles = array(
			array('id'=>'1','title'=>'One'),
			new Article(array('id'=>'2','title'=>'Two')),
			array('id'=>'3','title'=>'Three'),
			new Article(array('id'=>'4','title'=>'Four')),					
		);
		
		$numInserted = $this->articleModel->insert( $articles );
		
		$article = $this->articleModel->findById( 3 );
		
		$this->assertEqual( new Article($articles[2]) , $article );
		
	
	}
	
	
	public function findById_nonExistingID_returnsNull() 
	{
		$articles = array(
			array('id'=>'1','title'=>'One'),
			new Article(array('id'=>'2','title'=>'Two')),
			array('id'=>'3','title'=>'Three'),
			new Article(array('id'=>'4','title'=>'Four')),					
		);
		
		$numInserted = $this->articleModel->insert( $articles );
		
		$article = $this->articleModel->findById( 5 );
		
		$this->assertEqual( null , $article );
		
	
	}
	
	
	
	public function findFirst_byExistingTitle_returnsOneArticleObjectMatchingTheTitle() 
	{
		$articles = array(
			array('id'=>'1','title'=>'One','created'=>now(),'id_category'=>1),
			new Article(array('id'=>'2','title'=>'Two','created'=>now(),'id_category'=>1)),
			array('id'=>'3','title'=>'Two','created'=>now()),
			new Article(array('id'=>'4','title'=>'Three','created'=>now(),'id_category'=>1 )),
		);
		
		$numInserted = $this->articleModel->insert( $articles );
		
		$article = $this->articleModel->findFirst( array( 'title' => 'Two' ) );
		
		$this->assertEqual( $articles[1] , $article );
		
	
	}
	
	public function findFirst_byNonExistingTitle_returnsNull() 
	{
		$articles = array(
			array('id'=>'1','title'=>'One','created'=>now(),'id_category'=>1),
			new Article(array('id'=>'2','title'=>'Two','created'=>now(),'id_category'=>1)),
			array('id'=>'3','title'=>'Two','created'=>now()),
			new Article(array('id'=>'4','title'=>'Three','created'=>now(),'id_category'=>1 )),
		);
		
		$numInserted = $this->articleModel->insert( $articles );
		
		$article = $this->articleModel->findFirst( array( 'title' => 'NonExistingTitle' ) );
		
		$this->assertEqual( null , $article );
		
	
	}

	public function find_invalidFilterNull_throwsException()
	{
		$articles = array(
			array('id'=>'1','title'=>'One','created'=>now(),'id_category'=>1),
			new Article(array('id'=>'2','title'=>'Doubled','created'=>now(),'id_category'=>1)),
			array('id'=>'3','title'=>'Doubled','created'=>now()),
			new Article(array('id'=>'4','title'=>'Three','created'=>now(),'id_category'=>1 )),
		);
		
		$numInserted = $this->articleModel->insert( $articles );
		
		try {
			$results = $this->articleModel->find( null )->yield();		
		} catch ( Exception $ex ) 
		{
		
		}
		
		$this->assertEqual(  true, $ex instanceof Exception );		
	
	}
	
	
	public function find_invalidFilterObject_throwsException()
	{
		$articles = array(
			array('id'=>'1','title'=>'One','created'=>now(),'id_category'=>1),
			new Article(array('id'=>'2','title'=>'Doubled','created'=>now(),'id_category'=>1)),
			array('id'=>'3','title'=>'Doubled','created'=>now()),
			new Article(array('id'=>'4','title'=>'Three','created'=>now(),'id_category'=>1 )),
		);
		
		$numInserted = $this->articleModel->insert( $articles );
		
		try {
			$results = $this->articleModel->find( new Article() )->yield();		
		} catch ( Exception $ex ) 
		{
		
		}
		
		$this->assertEqual(  true, $ex instanceof Exception );		
	
	}
	
	
	
	public function find_invalidFilterWithNonExistingFields_throwsException()
	{
		$articles = array(
			array('id'=>'1','title'=>'One','created'=>now(),'id_category'=>1),
			new Article(array('id'=>'2','title'=>'Doubled','created'=>now(),'id_category'=>1)),
			array('id'=>'3','title'=>'Doubled','created'=>now()),
			new Article(array('id'=>'4','title'=>'Three','created'=>now(),'id_category'=>1 )),
		);
		
		$numInserted = $this->articleModel->insert( $articles );
		
		try {
			$results = $this->articleModel->find( array( "nonExistingField" => 'Doubled' ) )->yield();		
		} catch ( Exception $ex ) 
		{
		
		}
		
		$this->assertEqual(  true, $ex instanceof Exception );
		
	
	}
		
	
	public function find_byTitle_returnsTwoArticlesWithSameTitle() 
	{
		$articles = array(
			array('id'=>'1','title'=>'One','created'=>now(),'id_category'=>1),
			new Article(array('id'=>'2','title'=>'Doubled','created'=>now(),'id_category'=>1)),
			array('id'=>'3','title'=>'Doubled','created'=>now()),
			new Article(array('id'=>'4','title'=>'Three','created'=>now(),'id_category'=>1 )),
		);
		
		$numInserted = $this->articleModel->insert( $articles );
		
		$results = $this->articleModel->find( array( "title" => 'Doubled' ) )->yield();
		
		$this->assertEqual( array
		( 
			new Article( (array) $articles[1]),
			new Article( (array) $articles[2])
		), $results );
		
	
	}
	
	
	public function find_byNonExistingTitle_returnsEmptyArray() 
	{
		$articles = array(
			array('id'=>'1','title'=>'One'),
			new Article(array('id'=>'2','title'=>'Doubled')),
			array('id'=>'3','title'=>'Doubled'),
			new Article(array('id'=>'4','title'=>'Four')),					
		);
		
		$numInserted = $this->articleModel->insert( $articles );
		
		$results = $this->articleModel->find( array( "title" => 'NonExistingTitle' ) )->yield();
		
		$this->assertEqual( array( ), $results );
		
	
	}
	
	
	public function find_emptyFilter_returnsAllData()
	{
	
		
		$articles = array(
			new Article(array('id'=>'1','title'=>'One','created'=>now(),'id_category'=>1)),
			new Article(array('id'=>'2','title'=>'Doubled','created'=>now(),'id_category'=>1)),
			new Article(array('id'=>'3','title'=>'Doubled','created'=>now(),'id_category'=>1)),
			new Article(array('id'=>'4','title'=>'Three','created'=>now(),'id_category'=>1 )),		
		);
		
		$numInserted = $this->articleModel->insert( $articles );
		
		$measured = $this->articleModel->find()->yield();
		
		$this->assertEqual( $articles, $measured );
		
		
	
	}
	
	
	public function orderBy_sampleArticlesByIdDesc_returnAllArticlesInDescOrder()
	{
		
		$now = now();
		$articles = array(
			new Article(array('id'=>'1','title'=>'A','created'=>$now,'id_category'=>1)),
			new Article(array('id'=>'2','title'=>'A','created'=>$now,'id_category'=>1)),
			new Article(array('id'=>'3','title'=>'B','created'=>$now,'id_category'=>1)),
			new Article(array('id'=>'4','title'=>'B','created'=>$now,'id_category'=>1 )),		
			new Article(array('id'=>'5','title'=>'C','created'=>$now,'id_category'=>1 )),		
		);
		
		$numInserted = $this->articleModel->insert( $articles );
		
		$results = $this->articleModel->find()->orderBy( array( 'id' => -1 ) )->yield();
		
		$this->assertEqual (
			array_reverse( $articles )
			, $results
		);
	
	}
	
	public function orderBy_sampleArticlesByTitleAscIdDesc_returnAllArticlesInExpectedOrder()
	{
		$now = now();
		$articles = array(
			new Article(array('id'=>'1','title'=>'A','created'=>$now,'id_category'=>1)),
			new Article(array('id'=>'2','title'=>'A','created'=>$now,'id_category'=>1)),
			new Article(array('id'=>'3','title'=>'B','created'=>$now,'id_category'=>1)),
			new Article(array('id'=>'4','title'=>'B','created'=>$now,'id_category'=>1 )),		
			new Article(array('id'=>'5','title'=>'C','created'=>$now,'id_category'=>1 )),		
		);
		
		$numInserted = $this->articleModel->insert( $articles );
		
		
		$expected = array(
			new Article(array('id'=>'2','title'=>'A','created'=>$now,'id_category'=>1)),
			new Article(array('id'=>'1','title'=>'A','created'=>$now,'id_category'=>1)),
			new Article(array('id'=>'4','title'=>'B','created'=>$now,'id_category'=>1 )),		
			new Article(array('id'=>'3','title'=>'B','created'=>$now,'id_category'=>1)),
			new Article(array('id'=>'5','title'=>'C','created'=>$now,'id_category'=>1 )),	
		);
		
		
		$results = $this->articleModel->find()->orderBy( array( 'title' => 1 , 'id' => -1 ) )->yield();
		
		
		$this->assertEqual (
			$expected
			, $results
		);
	
	}
	
	public function limit_startWith2LimitTo3Items_returnsOnly3ItemsStartingWith2nd( ) 
	{
	
		$now = now();
		$articles = array(
			new Article(array( 'id'=>'1' , 'title'=>'A' , 'created'=>$now , 'id_category'=> 1 )),
			new Article(array( 'id'=>'2' , 'title'=>'B' , 'created'=>$now , 'id_category'=> 1 )),
			new Article(array( 'id'=>'3' , 'title'=>'C' , 'created'=>$now , 'id_category'=> 1 )),
			new Article(array( 'id'=>'4' , 'title'=>'D' , 'created'=>$now , 'id_category'=> 1 )),		
			new Article(array( 'id'=>'5' , 'title'=>'E' , 'created'=>$now , 'id_category'=> 1 )),		
			new Article(array( 'id'=>'6' , 'title'=>'F' , 'created'=>$now , 'id_category'=> 1 )),		
			new Article(array( 'id'=>'7' , 'title'=>'G' , 'created'=>$now , 'id_category'=> 1 )),		
			new Article(array( 'id'=>'8' , 'title'=>'H' , 'created'=>$now , 'id_category'=> 1 )),		
		); 
		
		$numInserted = $this->articleModel->insert( $articles );
		
		$results = $this->articleModel->find()->limit(2,3)->yield();

		$expected = array_slice( $articles , 2 , 3 , false );
		
		$this->assertEqual( $expected , $results );
	
	}
	
	
	public function limit_startWith2LimitTo3Items_returnsOnly2ItemsStartingWith2nd( ) 
	{
	
		$now = now();
		$articles = array(
			new Article(array( 'id'=>'1' , 'title'=>'A' , 'created'=>$now , 'id_category'=> 1 )),
			new Article(array( 'id'=>'2' , 'title'=>'B' , 'created'=>$now , 'id_category'=> 1 )),
			new Article(array( 'id'=>'3' , 'title'=>'C' , 'created'=>$now , 'id_category'=> 1 )),
			new Article(array( 'id'=>'4' , 'title'=>'D' , 'created'=>$now , 'id_category'=> 1 )),		
			new Article(array( 'id'=>'5' , 'title'=>'E' , 'created'=>$now , 'id_category'=> 1 )),		
			new Article(array( 'id'=>'6' , 'title'=>'F' , 'created'=>$now , 'id_category'=> 1 )),		
			new Article(array( 'id'=>'7' , 'title'=>'G' , 'created'=>$now , 'id_category'=> 1 )),		
			new Article(array( 'id'=>'8' , 'title'=>'H' , 'created'=>$now , 'id_category'=> 1 )),		
		); 
		
		$numInserted = $this->articleModel->insert( $articles );
		
		$results = $this->articleModel->find()->limit(2,3)->yield();

		$expected = array_slice( $articles , 2 , 3 , false );
		
		$this->assertEqual( $expected , $results );
	
	}
	
	
	public function count_insertedArticles_sameAsNumberOfInserts() 
	{
	
		$now = now();
		$articles = array(
			new Article(array( 'id'=>'1' , 'title'=>'A' , 'created'=>$now , 'id_category'=> 1 )),
			new Article(array( 'id'=>'2' , 'title'=>'B' , 'created'=>$now , 'id_category'=> 1 )),
			new Article(array( 'id'=>'3' , 'title'=>'C' , 'created'=>$now , 'id_category'=> 1 )),
			new Article(array( 'id'=>'4' , 'title'=>'D' , 'created'=>$now , 'id_category'=> 1 )),		
			new Article(array( 'id'=>'5' , 'title'=>'E' , 'created'=>$now , 'id_category'=> 1 )),		
			new Article(array( 'id'=>'6' , 'title'=>'F' , 'created'=>$now , 'id_category'=> 1 )),		
			new Article(array( 'id'=>'7' , 'title'=>'G' , 'created'=>$now , 'id_category'=> 1 )),		
			new Article(array( 'id'=>'8' , 'title'=>'H' , 'created'=>$now , 'id_category'=> 1 )),		
		); 
		
		$numInserted = $this->articleModel->insert( $articles );
	
	
		$this->assertEqual( 8 , $this->articleModel->count() ) ;
	
	}
	
	
	public function update_SampleExistingArticle_updatesSingleArticle() 
	{
		$now = now();
		$articles = array(
			array( 'id'=>'1' , 'title'=>'A' , 'created'=>$now , 'id_category'=> 1 ),
			array( 'id'=>'2' , 'title'=>'B' , 'created'=>$now , 'id_category'=> 1 ),
			array( 'id'=>'3' , 'title'=>'C' , 'created'=>$now , 'id_category'=> 1 ),
			array( 'id'=>'4' , 'title'=>'D' , 'created'=>$now , 'id_category'=> 1 ),		
			array( 'id'=>'5' , 'title'=>'E' , 'created'=>$now , 'id_category'=> 1 ),		
			array( 'id'=>'6' , 'title'=>'F' , 'created'=>$now , 'id_category'=> 1 ),		
			array( 'id'=>'7' , 'title'=>'G' , 'created'=>$now , 'id_category'=> 1 ),		
			array( 'id'=>'8' , 'title'=>'H' , 'created'=>$now , 'id_category'=> 1 ),		
		); 
		
		$numInserted = $this->articleModel->insert( $articles );
		
		$article = new Article( $articles[3] );
		
		$article->title = 'Updated';
		
		$updateCount = $this->articleModel->update( $article );
		
		
		$result = $this->articleModel->findById( 4 );
		
		$this->assertEqual( array(1,$article) , array($updateCount , $result )  );		
		
	}
	
	
	
	public function update_SampleNonExistingArticle_doesNotUpdate() 
	{
		$now = now();
		$articles = array(
			array( 'id'=>'1' , 'title'=>'A' , 'created'=>$now , 'id_category'=> 1 ),
			array( 'id'=>'2' , 'title'=>'B' , 'created'=>$now , 'id_category'=> 1 ),
			array( 'id'=>'3' , 'title'=>'C' , 'created'=>$now , 'id_category'=> 1 ),
			array( 'id'=>'4' , 'title'=>'D' , 'created'=>$now , 'id_category'=> 1 ),		
			array( 'id'=>'5' , 'title'=>'E' , 'created'=>$now , 'id_category'=> 1 ),		
			array( 'id'=>'6' , 'title'=>'F' , 'created'=>$now , 'id_category'=> 1 ),		
			array( 'id'=>'7' , 'title'=>'G' , 'created'=>$now , 'id_category'=> 1 ),		
			array( 'id'=>'8' , 'title'=>'H' , 'created'=>$now , 'id_category'=> 1 ),		
		); 
		
		
		$numInserted = $this->articleModel->insert( $articles );
		
		$article = new Article( 
			array( 'id'=>'9' , 'title'=>'I' , 'created'=>$now , 'id_category'=> 1 )
		);
		
		$article->title = 'Updated';
		
		$updateCount = $this->articleModel->update( $article );
		
		
		$result = $this->articleModel->findById( 9 );
		
		$this->assertEqual( array( 0, null ) , array( $updateCount , $result ) );		
		
	}
	
	
	
	public function delete_byArticleObject_deletesArticle() 
	{
		$now = now();
		$articles = array(
			array( 'id'=>'1' , 'title'=>'A' , 'created'=>$now , 'id_category'=> 1 ),
			array( 'id'=>'2' , 'title'=>'B' , 'created'=>$now , 'id_category'=> 1 ),
			array( 'id'=>'3' , 'title'=>'C' , 'created'=>$now , 'id_category'=> 1 ),
			array( 'id'=>'4' , 'title'=>'D' , 'created'=>$now , 'id_category'=> 1 ),		
			array( 'id'=>'5' , 'title'=>'E' , 'created'=>$now , 'id_category'=> 1 ),		
			array( 'id'=>'6' , 'title'=>'F' , 'created'=>$now , 'id_category'=> 1 ),		
			array( 'id'=>'7' , 'title'=>'G' , 'created'=>$now , 'id_category'=> 1 ),		
			array( 'id'=>'8' , 'title'=>'H' , 'created'=>$now , 'id_category'=> 1 ),		
		); 
		
		$numInserted = $this->articleModel->insert( $articles );
		
		$deleteCount = $this->articleModel->delete( new Article( $articles[3] ) );
		
		$result = $this->articleModel->findById( 4 );
		
		
		$this->assertEqual( array( 1 , null ) , array( $deleteCount , $result ) );
		
	}
	
	public function delete_nonExistingArticle_doesNotDelete() 
	{
		$now = now();
		$articles = array(
			array( 'id'=>'1' , 'title'=>'A' , 'created'=>$now , 'id_category'=> 1 ),
			array( 'id'=>'2' , 'title'=>'B' , 'created'=>$now , 'id_category'=> 1 ),
			array( 'id'=>'3' , 'title'=>'C' , 'created'=>$now , 'id_category'=> 1 ),
			array( 'id'=>'4' , 'title'=>'D' , 'created'=>$now , 'id_category'=> 1 ),		
			array( 'id'=>'5' , 'title'=>'E' , 'created'=>$now , 'id_category'=> 1 ),		
			array( 'id'=>'6' , 'title'=>'F' , 'created'=>$now , 'id_category'=> 1 ),		
			array( 'id'=>'7' , 'title'=>'G' , 'created'=>$now , 'id_category'=> 1 ),		
			array( 'id'=>'8' , 'title'=>'H' , 'created'=>$now , 'id_category'=> 1 ),		
		); 
		
		$numInserted = $this->articleModel->insert( $articles );
		
		$deleteCount = $this->articleModel->delete( 
			new Article( 
				array( 'id'=>'9' , 'title'=>'I' , 'created'=>$now , 'id_category'=> 1 )
			)
		);
		
		$articlesInStorage = $this->articleModel->count();
		
		$this->assertEqual( 
			array( 0 , 8 ),
			array( $delete , $articlesInStorage )		
		);		
		
	}
	
	public function userModelMethodCallingDriverMethod_getArticlesWithIDInRange_CallsMethodReturnsResult()  
	{
		$now = now();
		$articles = array(
			array( 'id'=>'1' , 'title'=>'A' , 'created'=>$now , 'id_category'=> 1 ),
			array( 'id'=>'2' , 'title'=>'B' , 'created'=>$now , 'id_category'=> 1 ),
			array( 'id'=>'3' , 'title'=>'C' , 'created'=>$now , 'id_category'=> 1 ),
			array( 'id'=>'4' , 'title'=>'D' , 'created'=>$now , 'id_category'=> 1 ),		
			array( 'id'=>'5' , 'title'=>'E' , 'created'=>$now , 'id_category'=> 1 ),		
			array( 'id'=>'6' , 'title'=>'F' , 'created'=>$now , 'id_category'=> 1 ),		
			array( 'id'=>'7' , 'title'=>'G' , 'created'=>$now , 'id_category'=> 1 ),		
			array( 'id'=>'8' , 'title'=>'H' , 'created'=>$now , 'id_category'=> 1 ),		
		); 
		
		$numInserted = $this->articleModel->insert( $articles );
		
		$res = $this->articleModel->getArticlesWithIDInRange(3,5);
		
		$this->assertEqual( array( 
			new Article( array( 'id'=>'3' , 'title'=>'C' , 'created'=>$now , 'id_category'=> 1 ) ),
			new Article( array( 'id'=>'4' , 'title'=>'D' , 'created'=>$now , 'id_category'=> 1 ) ),		
			new Article( array( 'id'=>'5' , 'title'=>'E' , 'created'=>$now , 'id_category'=> 1 ) ),		
		) , $res  );
		
	
	}
	
	
	public function magicCallToImplicitDriverMethod___getArticlesWithOddIds_activatesMethodReturnsObjectsArray() 
	{
		$articles = array(
			array('id'=>'1','title'=>'A'),
			array('id'=>'2','title'=>'B'),
			array('id'=>'3','title'=>'C'),
			array('id'=>'4','title'=>'D'),			
		);
		
		$numInserted = $this->articleModel->insert( $articles );
		
		$res = $this->articleModel->__getArticlesWithOddIds();
		
		$this->assertEqual( 
			array( 
				new Article ( array('id'=>'1','title'=>'A') ),
				new Article ( array('id'=>'3','title'=>'C') ),			
			) 
			, $res  
		);
		
	}
	
	
	public function magicCallToImplicitDriverMethod___nonExistingMethod_throwsException() 
	{
		try 
		{
			$this->articleModel->__nonExistingMethod();
		} 
		catch ( Exception $ex ) 
		{
		
		}
		
		$this->assertEqual( true , $ex instanceof Exception );
		
	}
	
	
	public function magicCallToEntityModel_nonExistingMethodNoDoubleUnderScorePrefix_throwsException() 
	{
		try 
		{
			$this->articleModel->nonExistingMethod();
		} 
		catch ( Exception $ex ) 
		{
		
		}
		
		$this->assertEqual( true , $ex instanceof Exception );
		
	}	
		
	
	public $__eventRaised = false;
	
	public function raiseEvent_dummyMethodsetDiscount50Percent_raisesEvent()
	{
		
		$this->articleModel->addEventHandler( new ArticleModelEventHandler() );
		
		$this->articleModel->setDiscount50Percent( $this );
				
		
		$this->assertEqual( true , $this->__eventRaised  );
		
	
	}

}


?>