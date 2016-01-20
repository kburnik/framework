<?
include_once(dirname(__FILE__) . '/.testsuite.include.php');

class EntityModelTestCase extends TestCase {

  protected $articleModel, $categoryModel;

  public function __construct() {
    $this->articleModel = new ArticleModel(new ArticleModelDataDriver());
    $this->categoryModel = new CategoryModel(new CategoryModelDataDriver());
  }

  public function createArticleObject_articleAsArray_fillsFieldsFromArrayToObject() {
    $articleAsArray = array(
	      'id' => 1,
	      'title' => "First article",
	      'created' => "2013-12-23 21:00:00");
    $article =  $this->articleModel->create($articleAsArray);
    $measured = array (
      	'id' => $article->id,
				'title' => $article->title,
				'created' => $article->created);
    $expected = $articleAsArray;
    $this->assertEqual($expected, $measured);
  }

  public function insert_SampleArticle_getsLastInsertId101() {
    // 101 is used instead of 1 not to confuse the inserted id = 1 with count of inserted = 1
    $lastInsertId = $this->articleModel->insert(array('id'=>101, 'title'=>'ArticleOne'));
    $this->assertEqual(101, $lastInsertId);
  }

  public function insert_ArticleAsObject_getsLastInsertId1() {
    $article = new Article(array('id'=>1, 'title'=>'ArticleOne'));
    $lastInsertId = $this->articleModel->insert($article);
    $this->assertEqual(1, $lastInsertId);
  }

  public function insert_ThreeArticlesAsArrays_getsLastInsertId3() {
    $articles = array(
      array('id'=>'1', 'title'=>'One'),
      array('id'=>'2', 'title'=>'Two'),
      array('id'=>'3', 'title'=>'Three'));

    $lastInsertId = $this->articleModel->insert($articles);
    $this->assertEqual(3, $lastInsertId);
  }

  public function insert_ThreeArticlesAsObjects_getsLastInsertId3() {
    $articles = array(
      new Article(array('id'=>'1', 'title'=>'One')),
      new Article(array('id'=>'2', 'title'=>'Two')),
      new Article(array('id'=>'3', 'title'=>'Three')));

    $lastInsertId = $this->articleModel->insert($articles);
    $this->assertEqual(3, $lastInsertId);
  }

  public function insert_null_throwsException() {
    try {
     $this->articleModel->insert(null);
    } catch (Exception $ex) {}
    $this->assertEqual(true, $ex instanceof Exception);
  }

  public function insert_otherObjectType_throwsException() {
    try {
      $this->articleModel->insert(new Category());
    } catch (Exception $ex) {}

    $this->assertEqual(true, $ex instanceof Exception);
  }

  public function insertArray_nullAndotherObjectType_throwsException() {
    $mixed = array(
	      new Article(array('id'=>'1', 'title'=>'One')),
	      new Category(array('id'=>'2', 'title'=>'Category')),
	      new Article(array('id'=>'3', 'title'=>'Three')),
	      null);

    try {
      $this->articleModel->insert($mixed);
    } catch (Exception $ex) {}

    $this->assertEqual(true, $ex instanceof Exception);
  }

  public function insertArray_MixedSomeArraySomeArticleObject_insertAcceptsReturns4asLastInsertId() {
    $articles = array(
      array('id'=>'1', 'title'=>'One'),
      new Article(array('id'=>'2', 'title'=>'Two')),
      array('id'=>'3', 'title'=>'Three'),
      new Article(array('id'=>'4', 'title'=>'Four')));

    $lastInsertId = $this->articleModel->insert($articles);
    $this->assertEqual(4, $lastInsertId);
  }

  public function insertupdate_newArticle_InsertsAndReturnsAffected1(){
    $article = new Article(array('id'=>'10', 'title' => 'Ten',
                                 'created'=>now(), 'modified' => now()));
    $ret = $this->articleModel->insertupdate($article);
    $this->assertEqual($article, $this->articleModel->findById(10));
    $this->assertEqual(1, $ret);
  }

  // TODO(kburnik): This test fails for some flaky reason. Sometimes affected=2.
  private function insertupdate_existingArticleUpdated_UpdatesAndReturnsAffected1() {
    $ret = $this->articleModel->insert(
        new Article(array('id'=>'13', 'title'=>'Thrteen')));
    $this->assertEqual(13, $ret);

    $ret = $this->articleModel->insertupdate(
        new Article(array('id'=>'13', 'title'=>'Thirteen')));
    $this->assertEqual(1, $ret);
  }

  public function insertupdate_existingArticleNotUpdated_ReturnsAffected0() {
    $ret = $this->articleModel->insert(
        new Article(array('id'=>'10', 'title'=>'Ten')));
    $this->assertEqual(10, $ret);

    $ret = $this->articleModel->insertupdate(
        new Article(array('id'=>'10', 'title'=>'Ten')));
    $this->assertEqual(0, $ret);
  }

  public function findById_singleArticleAfterMixedInsertion_returnsSingleInsertedArticleWithMatchingID() {
    $articles = array(
      array('id'=>'1', 'title'=>'One'),
      new Article(array('id'=>'2', 'title'=>'Two')),
      array('id'=>'3', 'title'=>'Three'),
      new Article(array('id'=>'4', 'title'=>'Four')));

    $lastInsertId = $this->articleModel->insert($articles);
    $article = $this->articleModel->findById(3);
    $this->assertEqual(new Article($articles[2]), $article);
  }

  public function findById_nonExistingID_returnsNull() {
    $articles = array(
      array('id'=>'1', 'title'=>'One'),
      new Article(array('id'=>'2', 'title'=>'Two')),
      array('id'=>'3', 'title'=>'Three'),
      new Article(array('id'=>'4', 'title'=>'Four')));

    $lastInsertId = $this->articleModel->insert($articles);
    $article = $this->articleModel->findById(5);
    $this->assertEqual(null, $article);
  }

  public function findFirst_byExistingTitle_returnsOneArticleObjectMatchingTheTitle() {
    $articles = array(
      array('id'=>'1', 'title'=>'One', 'created'=>now(), 'modified'=>now(), 'id_category'=>1),
      new Article(array('id'=>'2', 'title'=>'Two', 'created'=>now(), 'modified'=>now(), 'id_category'=>1)),
      array('id'=>'3', 'title'=>'Two', 'created'=>now(), 'modified'=>now()),
      new Article(array('id'=>'4', 'title'=>'Three', 'created'=>now(), 'modified'=>now(), 'id_category'=>1)));

    $lastInsertId = $this->articleModel->insert($articles);
    $article = $this->articleModel->findFirst(array('title' => 'Two'));
    $this->assertEqual($articles[1], $article);
  }

  public function findFirst_byNonExistingTitle_returnsNull() {
    $articles = array(
      array('id'=>'1', 'title'=>'One', 'created'=>now(), 'modified'=>now(), 'id_category'=>1),
      new Article(array('id'=>'2', 'title'=>'Two', 'created'=>now(),'modified'=>now(), 'id_category'=>1)),
      array('id'=>'3', 'title'=>'Two', 'created'=>now(),'modified'=>now()),
      new Article(array('id'=>'4', 'title'=>'Three', 'created'=>now(),'modified'=>now(), 'id_category'=>1)));

    $lastInsertId = $this->articleModel->insert($articles);
    $article = $this->articleModel->findFirst(array('title' => 'NonExistingTitle'));
    $this->assertEqual(null, $article);
  }

  public function find_invalidFilterNull_throwsException() {
    $articles = array(
      array('id'=>'1', 'title'=>'One', 'created'=>now(),'modified'=>now(), 'id_category'=>1),
      new Article(array('id'=>'2', 'title'=>'Doubled', 'created'=>now(),'modified'=>now(), 'id_category'=>1)),
      array('id'=>'3', 'title'=>'Doubled', 'created'=>now(),'modified'=>now()),
      new Article(array('id'=>'4', 'title'=>'Three', 'created'=>now(),'modified'=>now(), 'id_category'=>1)));

    $lastInsertId = $this->articleModel->insert($articles);

    try {
      $results = $this->articleModel->find(null)->ret();
    } catch (Exception $ex) {}

    $this->assertEqual(true, $ex instanceof Exception);
  }

  public function find_invalidFilterObject_throwsException() {
    $articles = array(
      array('id'=>'1', 'title'=>'One', 'created'=>now(), 'id_category'=>1),
      new Article(array('id'=>'2', 'title'=>'Doubled', 'created'=>now(), 'id_category'=>1)),
      array('id'=>'3', 'title'=>'Doubled', 'created'=>now()),
      new Article(array('id'=>'4', 'title'=>'Three', 'created'=>now(), 'id_category'=>1)));

    $lastInsertId = $this->articleModel->insert($articles);

    try {
      $results = $this->articleModel->find(new Article())->ret();
    } catch (Exception $ex) {}

    $this->assertEqual(true, $ex instanceof Exception);
  }

  public function find_invalidFilterWithNonExistingFields_throwsException() {
    $articles = array(
      array('id'=>'1', 'title'=>'One', 'created'=>now(), 'id_category'=>1),
      new Article(array('id'=>'2', 'title'=>'Doubled', 'created'=>now(), 'id_category'=>1)),
      array('id'=>'3', 'title'=>'Doubled', 'created'=>now()),
      new Article(array('id'=>'4', 'title'=>'Three', 'created'=>now(), 'id_category'=>1)));

    $lastInsertId = $this->articleModel->insert($articles);

    try {
      $results = $this->articleModel->find(array("nonExistingField" => 'Doubled'))->ret();
    } catch (Exception $ex) {}

    $this->assertEqual(true, $ex instanceof Exception);
  }

  public function find_byTitle_returnsTwoArticlesWithSameTitle() {
    $articles = array(
      array('id'=>'1', 'title'=>'One', 'created'=>now(), 'id_category'=>1),
      new Article(array('id'=>'2', 'title'=>'Doubled', 'created'=>now(),'modified'=>now(), 'id_category'=>1)),
      array('id'=>'3', 'title'=>'Doubled', 'created'=>now(),'modified'=>now()),
      new Article(array('id'=>'4', 'title'=>'Three', 'created'=>now(),'modified'=>now(), 'id_category'=>1)));

    $lastInsertId = $this->articleModel->insert($articles);
    $results = $this->articleModel->find(array("title" => 'Doubled'))->ret();

    $this->assertEqual(array(
	      new Article((array) $articles[1]),
	      new Article((array) $articles[2])), $results);
  }

  public function find_byNonExistingTitle_returnsEmptyArray() {
    $articles = array(
      array('id'=>'1', 'title'=>'One'),
      new Article(array('id'=>'2', 'title'=>'Doubled')),
      array('id'=>'3', 'title'=>'Doubled'),
      new Article(array('id'=>'4', 'title'=>'Four')));

    $lastInsertId = $this->articleModel->insert($articles);
    $results = $this->articleModel->find(array("title" => 'NonExistingTitle'))->ret();
    $this->assertEqual(array(), $results);
  }

  public function find_emptyFilter_returnsAllData() {
    $articles = array(
      new Article(array('id'=>'1', 'title'=>'One', 'created'=>now(),'modified'=>now(), 'id_category'=>1)),
      new Article(array('id'=>'2', 'title'=>'Doubled', 'created'=>now(),'modified'=>now(), 'id_category'=>1)),
      new Article(array('id'=>'3', 'title'=>'Doubled', 'created'=>now(),'modified'=>now(), 'id_category'=>1)),
      new Article(array('id'=>'4', 'title'=>'Three', 'created'=>now(),'modified'=>now(), 'id_category'=>1)));

    $lastInsertId = $this->articleModel->insert($articles);
    $measured = $this->articleModel->find()->ret();
    $this->assertEqual($articles, $measured);
  }

  public function findAndExtract_idAndtitle_returnsArrayWithIdAndTitle() {
    $articles = array(
      new Article(array('id'=>'1', 'title'=>'One', 'created'=>now(), 'id_category'=>1)),
      new Article(array('id'=>'2', 'title'=>'Two', 'created'=>now(), 'id_category'=>1)),
      new Article(array('id'=>'3', 'title'=>'Three', 'created'=>now(), 'id_category'=>1)),
      new Article(array('id'=>'4', 'title'=>'Four', 'created'=>now(), 'id_category'=>1)));

    $lastInsertId = $this->articleModel->insert($articles);
    $measured =  $this->articleModel->find()->extract('id', 'title');
    $this->assertEqual(array(
	      array('id'=>'1', 'title'=>'One'),
	      array('id'=>'2', 'title'=>'Two'),
	      array('id'=>'3', 'title'=>'Three'),
	      array('id'=>'4', 'title'=>'Four')), $measured);
  }

  public function orderBy_sampleArticlesByIdDesc_returnAllArticlesInDescOrder() {
    $now = now();
    $articles = array(
	      new Article(array('id'=>'1', 'title'=>'A', 'created'=>$now, 'modified'=>$now,'id_category'=>1)),
	      new Article(array('id'=>'2', 'title'=>'A', 'created'=>$now, 'modified'=>$now,'id_category'=>1)),
	      new Article(array('id'=>'3', 'title'=>'B', 'created'=>$now, 'modified'=>$now,'id_category'=>1)),
	      new Article(array('id'=>'4', 'title'=>'B', 'created'=>$now, 'modified'=>$now,'id_category'=>1)),
	      new Article(array('id'=>'5', 'title'=>'C', 'created'=>$now, 'modified'=>$now,'id_category'=>1)));

    $lastInsertId = $this->articleModel->insert($articles);
    $results = $this->articleModel->find()->orderBy(array('id' => -1))->ret();
    $this->assertEqual(array_reverse($articles), $results);
  }

  public function orderBy_sampleArticlesByTitleAscIdDesc_returnAllArticlesInExpectedOrder() {
    $now = now();
    $articles = array(
	      new Article(array('id'=>'1', 'title'=>'A', 'created'=>$now, 'modified'=>$now,'id_category'=>1)),
	      new Article(array('id'=>'2', 'title'=>'A', 'created'=>$now, 'modified'=>$now,'id_category'=>1)),
	      new Article(array('id'=>'3', 'title'=>'B', 'created'=>$now, 'modified'=>$now,'id_category'=>1)),
	      new Article(array('id'=>'4', 'title'=>'B', 'created'=>$now, 'modified'=>$now,'id_category'=>1)),
	      new Article(array('id'=>'5', 'title'=>'C', 'created'=>$now, 'modified'=>$now,'id_category'=>1)));

    $lastInsertId = $this->articleModel->insert($articles);

    $expected = array(
	      new Article(array('id'=>'2', 'title'=>'A', 'created'=>$now, 'modified'=>$now,'id_category'=>1)),
	      new Article(array('id'=>'1', 'title'=>'A', 'created'=>$now, 'modified'=>$now,'id_category'=>1)),
	      new Article(array('id'=>'4', 'title'=>'B', 'created'=>$now, 'modified'=>$now,'id_category'=>1)),
	      new Article(array('id'=>'3', 'title'=>'B', 'created'=>$now, 'modified'=>$now,'id_category'=>1)),
	      new Article(array('id'=>'5', 'title'=>'C', 'created'=>$now, 'modified'=>$now,'id_category'=>1)));

    $results = $this->articleModel->find()->orderBy(array('title' => 1, 'id' => -1))->ret();
    $this->assertEqual($expected, $results);
  }

  public function orderBy_sampleArticlesByNonExistingFieldAsc_doesNotThrowExceptionEarly() {
    $articles = $this->create8Articles();

    try {
      $results = $this->articleModel->find()->orderBy(array('nonExistingField' => 1));
    }
    catch (Exception $ex) {}

    $this->assertEqual(false, $ex instanceof Exception);
  }

  public function orderBy_sampleArticlesByNonExistingFieldAsc_throwsExceptionLate() {
    $articles = $this->create8Articles();

    try {
      $results = $this->articleModel->find()->orderBy(array('nonExistingField' => 1))->ret();
    }
    catch (Exception $ex) {}

    $this->assertEqual(true, $ex instanceof Exception);
  }

  public function limit_startWith2LimitTo3Items_returnsOnly3ItemsStartingWith2nd() {
    $now = now();
    $articles = array(
      new Article(array('id'=>'1', 'title'=>'A', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
      new Article(array('id'=>'2', 'title'=>'B', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
      new Article(array('id'=>'3', 'title'=>'C', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
      new Article(array('id'=>'4', 'title'=>'D', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
      new Article(array('id'=>'5', 'title'=>'E', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
      new Article(array('id'=>'6', 'title'=>'F', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
      new Article(array('id'=>'7', 'title'=>'G', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
      new Article(array('id'=>'8', 'title'=>'H', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)));

    $lastInsertId = $this->articleModel->insert($articles);
    $results = $this->articleModel->find()->limit(2, 3)->ret();
    $expected = array_slice($articles, 2, 3, false);
    $this->assertEqual($expected, $results);
  }

  public function limit_startWith2LimitTo3Items_returnsOnly2ItemsStartingWith2nd() {
    $now = now();
    $articles = array(
      new Article(array('id'=>'1', 'title'=>'A', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
      new Article(array('id'=>'2', 'title'=>'B', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
      new Article(array('id'=>'3', 'title'=>'C', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
      new Article(array('id'=>'4', 'title'=>'D', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
      new Article(array('id'=>'5', 'title'=>'E', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
      new Article(array('id'=>'6', 'title'=>'F', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
      new Article(array('id'=>'7', 'title'=>'G', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
      new Article(array('id'=>'8', 'title'=>'H', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)));
    $lastInsertId = $this->articleModel->insert($articles);
    $results = $this->articleModel->find()->limit(2, 3)->ret();
    $expected = array_slice($articles, 2, 3, false);
    $this->assertEqual($expected, $results);
  }

  public function count_insertedArticles_sameAsNumberOfInserts() {
    $now = now();
    $articles = array(
	      new Article(array('id'=>'1', 'title'=>'A', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
	      new Article(array('id'=>'2', 'title'=>'B', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
	      new Article(array('id'=>'3', 'title'=>'C', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
	      new Article(array('id'=>'4', 'title'=>'D', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
	      new Article(array('id'=>'5', 'title'=>'E', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
	      new Article(array('id'=>'6', 'title'=>'F', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
	      new Article(array('id'=>'7', 'title'=>'G', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
	      new Article(array('id'=>'8', 'title'=>'H', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)));

    $lastInsertId = $this->articleModel->insert($articles);

    $this->assertEqual(8, $this->articleModel->count());
  }

  private function create8Articles() {
    $now = now();
    $articles = array(
	      new Article(array('id'=>'1', 'title'=>'A', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
	      new Article(array('id'=>'2', 'title'=>'B', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
	      new Article(array('id'=>'3', 'title'=>'C', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
	      new Article(array('id'=>'4', 'title'=>'D', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
	      new Article(array('id'=>'5', 'title'=>'E', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
	      new Article(array('id'=>'6', 'title'=>'F', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
	      new Article(array('id'=>'7', 'title'=>'G', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
	      new Article(array('id'=>'8', 'title'=>'H', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)));

    $lastInsertId = $this->articleModel->insert($articles);

    return $articles;
  }

  private function create8ArticlesAsArrays(){
    $now = now();
    $articles = array(
	      array('id'=>'1', 'title'=>'A', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'2', 'title'=>'B', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'3', 'title'=>'C', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'4', 'title'=>'D', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'5', 'title'=>'E', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'6', 'title'=>'F', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'7', 'title'=>'G', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'8', 'title'=>'H', 'created'=>$now, 'modified'=>$now,'id_category'=> 1));
    $lastInsertId = $this->articleModel->insert($articles);

    return $articles;
  }

  public function affected_findArticlesFrom2to5_return4() {
    $this->create8Articles();
    $affected = $this->articleModel->find(array(':between' => array('id', 2, 5)))->affected();
    $this->assertEqual(4, $affected);
  }

  public function filterGt_5_affectedReturns3() {
    $this->create8Articles();
    $affected = $this->articleModel->find(array(':gt' => array('id', 5)))->affected();
    $this->assertEqual(3, $affected);
  }

  public function filtersByFieldMembers_ComparesFields() {
    $articles = array(
      array('id'=>'1', 'created'=>"2015-03-22 19:23:00", 'modified'=> "2015-03-22 19:23:00"),
      array('id'=>'2', 'created'=>"2015-03-22 19:23:00", 'modified'=> "2015-03-22 19:24:00"),
      array('id'=>'3', 'created'=>"2015-03-22 19:23:00", 'modified'=> "2015-03-22 19:25:00"),
        );
    $lastInsertId = $this->articleModel->insert($articles);

    $affected = $this->articleModel->find(array(
      ':eq' => array(array('created', 'modified'))))->affected();
    $this->assertEqual(1, $affected);

    $affected = $this->articleModel->find(array(
      ':gt' => array(array('created', 'modified'))))->affected();
    $this->assertEqual(0, $affected);

    $affected = $this->articleModel->find(array(
      ':lt' => array(array('created', 'modified'))))->affected();
    $this->assertEqual(2, $affected);

    $affected = $this->articleModel->find(array(
      ':lteq' => array(array('created', 'modified'))))->affected();
    $this->assertEqual(3, $affected);

    $affected = $this->articleModel->find(array(
      ':gteq' => array(array('created', 'modified'))))->affected();
    $this->assertEqual(1, $affected);

    $affected = $this->articleModel->find(array(
      ':gteq' => array(array('modified', 'created'))))->affected();
    $this->assertEqual(3, $affected);
  }

  public function filterGtEq_5_affectedReturns4() {
    $this->create8Articles();
    $affected = $this->articleModel->find(array(':gteq' => array('id', 5)))->affected();
    $this->assertEqual(4, $affected);
  }

  public function filterLt_5_affectedReturns4() {
    $this->create8Articles();
    $affected = $this->articleModel->find(array(':lt' => array('id', 5)))->affected();
    $this->assertEqual(4, $affected);
  }

  public function filterLtEq_5_affectedReturns5() {
    $this->create8Articles();
    $affected = $this->articleModel->find(array(':lteq' => array('id', 5)))->affected();
    $this->assertEqual(5, $affected);
  }

  public function filterIn_EmptyList_affectsNoData() {
    $this->create8Articles();
    $occured = false;
    $affected = $this->articleModel->find(array(':in' => array('id', array())))->affected();
    $this->assertEqual(0, $affected);
  }

  public function filterIn_2_5_8_affectedReturns3() {
    $this->create8Articles();
    $affected = $this->articleModel->find(array(':in' => array('id', array(2, 5, 8))))->affected();
    $this->assertEqual(3, $affected);
  }

  public function filterNIn_EmptyList_affectsNoData() {
    $this->create8Articles();
    $occured = false;
    $affected = $this->articleModel->find(array(':nin' => array('id', array())))->affected();
    $this->assertEqual(8, $affected);
  }

  public function filterNin_2_5_8_affectedReturns5() {
    $this->create8Articles();
    $affected = $this->articleModel->find(array(':nin' => array('id', array(2, 5, 8))))->affected();
    $this->assertEqual(5, $affected);
  }

  public function filterEq_5_ReturnsAffected1() {
    $this->create8Articles();
    $affected = $this->articleModel->find(array(':eq' => array('id', 5)))->affected();
    $this->assertEqual(1, $affected);
  }

  public function filterNe_5_ReturnsAffected7() {
    $this->create8Articles();
    $affected = $this->articleModel->find(array(':ne' => array('id', 5)))->affected();
    $this->assertEqual(7, $affected);
  }

  private function createArticlesForLikeFilter() {
    $now = now();
    $articles = array(
	      array('id'=>'1', 'title'=>'foobar', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'2', 'title'=>'foo', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'3', 'title'=>'dafoo', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'4', 'title'=>'barfoo', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'5', 'title'=>'1bar2foo', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'6', 'title'=>'1foo2bar', 'created'=>$now, 'modified'=>$now,'id_category'=> 1));

    $lastInsertId = $this->articleModel->insert($articles);
  }

  public function filterLikeExact_ArticlesForLikeFilter_ReturnsAffected1() {
    $this->createArticlesForLikeFilter();
    $affected = $this->articleModel->find(array('title' => array('foobar')))->affected();
    $this->assertEqual(1, $affected);
  }

  public function filterLikePrefix_ArticlesForLikeFilter_ReturnsAffected2() {
    $this->createArticlesForLikeFilter();
    $affected = $this->articleModel->find(array('title' => array('%bar')))->affected();
    $this->assertEqual(2, $affected);
  }

  public function filterLikeSufix_ArticlesForLikeFilter_ReturnsAffected2() {
    $this->createArticlesForLikeFilter();
    $affected = $this->articleModel->find(array('title' => array('foo%')))->affected();
    $this->assertEqual(2, $affected);
  }

  public function filterLikeSandwich_ArticlesForLikeFilter_ReturnsAffected6() {
    $this->createArticlesForLikeFilter();
    $affected = $this->articleModel->find(array('title' => array('%foo%')))->affected();
    $this->assertEqual(6, $affected);
  }

  public function filterLikeInterfix_ArticlesForLikeFilter_ReturnsAffected1() {
    $this->createArticlesForLikeFilter();
    $affected = $this->articleModel->find(array('title' => array('1%2bar')))->affected();
    $this->assertEqual(1, $affected);
  }

  public function filterLikeInterfixSufix_ArticlesForLikeFilter_ReturnsAffected2() {
    $this->createArticlesForLikeFilter();
    $affected = $this->articleModel->find(array('title' => array('1%2%')))->affected();
    $this->assertEqual(2, $affected);
  }

  public function filterLikePrefixInterfix_ArticlesForLikeFilter_ReturnsAffected2() {
    $this->createArticlesForLikeFilter();
    $affected = $this->articleModel->find(array('title' => array('%foo%bar')))->affected();
    $this->assertEqual(2, $affected);
  }

  public function filterLikeAnything_ArticlesForLikeFilter_ReturnsAffected6() {
    $this->createArticlesForLikeFilter();
    $affected = $this->articleModel->find(array('title' => array('%')))->affected();
    $this->assertEqual(6, $affected);
  }

  public function update_SampleExistingArticle_updatesSingleArticle() {
    $now = now();
    $articles = array(
	      array('id'=>'1', 'title'=>'A', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'2', 'title'=>'B', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'3', 'title'=>'C', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'4', 'title'=>'D', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'5', 'title'=>'E', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'6', 'title'=>'F', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'7', 'title'=>'G', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'8', 'title'=>'H', 'created'=>$now, 'modified'=>$now,'id_category'=> 1));

    $lastInsertId = $this->articleModel->insert($articles);
    $article = new Article($articles[3]);
    $article->title = 'Updated';
    $updateCount = $this->articleModel->update($article);
    $result = $this->articleModel->findById(4);
    $this->assertEqual(array(1, $article), array($updateCount, $result));
  }

  public function update_SampleNonExistingArticle_doesNotUpdate() {
    $now = now();
    $articles = array(
	      array('id'=>'1', 'title'=>'A', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'2', 'title'=>'B', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'3', 'title'=>'C', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'4', 'title'=>'D', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'5', 'title'=>'E', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'6', 'title'=>'F', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'7', 'title'=>'G', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'8', 'title'=>'H', 'created'=>$now, 'modified'=>$now,'id_category'=> 1));

    $lastInsertId = $this->articleModel->insert($articles);
    $article = new Article(
      	array('id'=>'9', 'title'=>'I', 'created'=>$now, 'modified'=>$now,'id_category'=> 1));
    $article->title = 'Updated';
    $updateCount = $this->articleModel->update($article);
    $result = $this->articleModel->findById(9);
    $this->assertEqual(array(0, null), array($updateCount, $result));
  }

  public function update_SampleExistingArticleWithNochanges_returnsAffectedCount0() {
    $articles = $this->create8ArticlesAsArrays();

    $article = new Article($articles[0]);

    $affectedCount = $this->articleModel->update($article);

    $this->assertEqual(0, $affectedCount);

  }

  public function delete_byArticleObject_deletesArticle() {
    $articles = $this->create8ArticlesAsArrays();

    $deleteCount = $this->articleModel->delete(new Article($articles[3]));

    $result = $this->articleModel->findById(4);

    $this->assertEqual(array(1, null), array($deleteCount, $result));

  }

  public function delete_nonExistingArticle_doesNotDelete() {
    $now = now();
    $articles = array(
	      array('id'=>'1', 'title'=>'A', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'2', 'title'=>'B', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'3', 'title'=>'C', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'4', 'title'=>'D', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'5', 'title'=>'E', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'6', 'title'=>'F', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'7', 'title'=>'G', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'8', 'title'=>'H', 'created'=>$now, 'modified'=>$now,'id_category'=> 1));

    $lastInsertId = $this->articleModel->insert($articles);

    $deleteCount = $this->articleModel->delete(
      new Article(
        array('id'=>'9', 'title'=>'I', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)));

    $articlesInStorage = $this->articleModel->count();

    $this->assertEqual(
	      array(0, 8),
	      array($delete, $articlesInStorage));
  }

  public function deleteBy_FilterBetween2and6_deletes5Articles() {
    $articles = $this->create8Articles();
    $affected  = $this->articleModel->deleteBy(array(':between' => array('id', 2, 6)));
    $remains = $this->articleModel->count();
    $this->assertEqual(array(5, 3), array($affected, $remains));
  }

  public function deleteById_ArticleId6_deletesArticleWithId6RestRemain() {
    $articles = $this->create8Articles();
    $affected  = $this->articleModel->deleteById(6);
    $remains = $this->articleModel->count();
    $this->assertEqual(array(1, 7), array($affected, $remains));
  }

  public function userModelMethodCallingDriverMethod_getArticlesWithIDInRange_CallsMethodReturnsResult() {
    $now = now();
    $articles = array(
	      array('id'=>'1', 'title'=>'A', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'2', 'title'=>'B', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'3', 'title'=>'C', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'4', 'title'=>'D', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'5', 'title'=>'E', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'6', 'title'=>'F', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'7', 'title'=>'G', 'created'=>$now, 'modified'=>$now,'id_category'=> 1),
	      array('id'=>'8', 'title'=>'H', 'created'=>$now, 'modified'=>$now,'id_category'=> 1));

    $lastInsertId = $this->articleModel->insert($articles);

    $res = $this->articleModel->getArticlesWithIDInRange(3, 5);

    $this->assertEqual(array(
	      new Article(array('id'=>'3', 'title'=>'C', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
	      new Article(array('id'=>'4', 'title'=>'D', 'created'=>$now, 'modified'=>$now,'id_category'=> 1)),
	      new Article(array('id'=>'5', 'title'=>'E', 'created'=>$now, 'modified'=>$now,'id_category'=> 1))),
	    	$res);

  }

  public function magicCallToImplicitDriverMethod___getArticlesWithOddIds_activatesMethodReturnsObjectsArray() {
    $articles = array(
	      array('id'=>'1', 'title'=>'A'),
	      array('id'=>'2', 'title'=>'B'),
	      array('id'=>'3', 'title'=>'C'),
	      array('id'=>'4', 'title'=>'D'));

    $lastInsertId = $this->articleModel->insert($articles);

    $res = $this->articleModel->__getArticlesWithOddIds();

    $this->assertEqual(array(
        new Article (array('id'=>'1', 'title'=>'A')),
        new Article (array('id'=>'3', 'title'=>'C'))),
				$res);
  }

  public function magicCallToImplicitDriverMethod___nonExistingMethod_throwsException() {
    try {
    	$this->articleModel->__nonExistingMethod();
    } catch (Exception $ex) {}

    $this->assertEqual(true, $ex instanceof Exception);
  }

  public function magicCallToEntityModel_nonExistingMethodNoDoubleUnderScorePrefix_throwsException() {
    try {
      $this->articleModel->nonExistingMethod();
    }
    catch (Exception $ex) {}

    $this->assertEqual(true, $ex instanceof Exception);
  }

  public $__eventRaised = false;

  public function raiseEvent_dummyMethodsetDiscount50Percent_raisesEvent() {
    $this->articleModel->addEventHandler(new ArticleModelEventHandler());
    $this->articleModel->setDiscount50Percent($this);
    $this->assertEqual(true, $this->__eventRaised);
  }

  public function getEntityFields_ArticleModel_returnsFieldsOfArticle() {
    $expected = array('id', 'title', 'created', 'modified', 'id_category');
    $measured = $this->articleModel->getEntityfields();

    $this->assertEqual($expected, $measured);
  }

  public function naturalJoin_ArticleWithCategoryAndCertainFieldExtraction_returnsJoined() {

    $articles = array(
	      array('id'=>'1', 'title'=>'A', 'id_category'=>1),
	      array('id'=>'2', 'title'=>'B', 'id_category'=>2),
	      array('id'=>'3', 'title'=>'C', 'id_category'=>3));
    $this->articleModel->insert($articles);

    $categories = array(
	      array('id'=>1, 'title'=>"One"),
	      array('id'=>2, 'title'=>"Two"),
	      array('id'=>3, 'title'=>"Three"));
    $this->categoryModel->insert($categories);

    $res = $this->articleModel
      ->find()
      ->join($this->categoryModel, "refcategory", array("id_category" => "id"))
      ->extract('id', 'refcategory');

    $expected = array(
	      array('id'=>1, 'refcategory'=> new Category($categories[0])),
	      array('id'=>2, 'refcategory'=> new Category($categories[1])),
	      array('id'=>3, 'refcategory'=> new Category($categories[2])));

    $this->assertEqual($expected, $res);

  }

  public function naturalJoin_ArticleWithCategoryAndNoJoinedFieldExtraction_returnsOnlySelectedFields() {

    $articles = array(
	      array('id'=>'1', 'title'=>'A', 'id_category'=>1),
	      array('id'=>'2', 'title'=>'B', 'id_category'=>2),
	      array('id'=>'3', 'title'=>'C', 'id_category'=>3));
    $this->articleModel->insert($articles);

    $categories = array(
	      array('id'=>1, 'title'=>"One"),
	      array('id'=>2, 'title'=>"Two"),
	      array('id'=>3, 'title'=>"Three"));
    $this->categoryModel->insert($categories);

    $res = $this->articleModel
	      ->find()
	      ->join($this->categoryModel, "refcategory", array("id_category" => "id"))
	      ->extract('id');

    $expected = array(
	      array('id'=>1),
	      array('id'=>2),
	      array('id'=>3));

    $this->assertEqual($expected, $res);

  }

  public function naturalJoin_ArticleWithCategoryAndYield_returnsJoined() {

    $articles = array(
	      new Article(array('id'=>'1', 'title'=>'A', 'id_category'=>1)),
	      new Article(array('id'=>'2', 'title'=>'B', 'id_category'=>2)),
	      new Article(array('id'=>'3', 'title'=>'C', 'id_category'=>3)));

    $this->articleModel->insert($articles);

    $categories = array(
	      array('id'=>1, 'title'=>"One"),
	      array('id'=>2, 'title'=>"Two"),
	      array('id'=>3, 'title'=>"Three"));

    $articles[0]->refcategory = new Category($categories[0]);
    $articles[1]->refcategory = new Category($categories[1]);
    $articles[2]->refcategory = new Category($categories[2]);

    $this->categoryModel->insert($categories);



    $res = $this->articleModel
      ->find()
      ->join($this->categoryModel, "refcategory", array("id_category" => "id"))
      ->ret();

    $expected = array(
      $articles[0], $articles[1], $articles[2]);

    $this->assertEqual($expected, $res);
  }

  public function naturalJoinWithFields_ArticleWithCategoryAndCertainFieldExtraction_returnsJoinedAndExtracted() {
    $articles = array(
	      array('id'=>'1', 'title'=>'A', 'id_category'=>1),
	      array('id'=>'2', 'title'=>'B', 'id_category'=>2),
	      array('id'=>'3', 'title'=>'C', 'id_category'=>3));
    $this->articleModel->insert($articles);

    $categories = array(
	      array('id'=>1, 'title'=>"One"),
	      array('id'=>2, 'title'=>"Two"),
	      array('id'=>3, 'title'=>"Three"));
    $this->categoryModel->insert($categories);

    $res = $this->articleModel
      ->find()
      ->join($this->categoryModel,
             "refcategory",
             array("id_category" => "id"),
             array('title'))
      ->extract('id', 'refcategory');

    $expected = array(
      array('id'=>1, 'refcategory'=> array('title' => 'One')),
      array('id'=>2, 'refcategory'=> array('title' => 'Two')),
      array('id'=>3, 'refcategory'=> array('title' => 'Three')));

    $this->assertEqual($expected, $res);
  }

}

?>
