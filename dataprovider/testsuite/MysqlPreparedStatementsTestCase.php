<?

class MysqlPreparedStatementsTestCase extends TestCase
{

  private $qdp;

  public function __construct()
  {
    $this->qdp = Project::GetQDP();

    $this->createTables();

  }

  public function __destruct()
  {

    $this->dropAll();
  }


  private function dropAll()
  {

    $this->qdp->drop(array(
      'article' ,
    ));

    $this->assertEqual( false ,  in_array( 'article' , $this->qdp->getTables() ) );
  }

  private function createTables()
  {


    $this->qdp->prepareTable("article",array(
      "id" => "int(4) unsigned not null primary key auto_increment",
      "title" => "varchar(256) not null",
      "created" => "datetime not null"
    ));

    $this->assertEqual( true ,  in_array( 'article' , $this->qdp->getTables() ) );


  }

  private function insertData()
  {

    $this->qdp->insert("article",array(
      array('title'=>'First','created'=> now() ),
      array('title'=>'Second','created'=> now() ),
      array('title'=>'Third','created'=> now() ),
      array('title'=>'Duplicate','created'=> '2013-12-24 19:00:00' ),
      array('title'=>'Duplicate','created'=> '2013-12-24 19:00:00' ),
    ));

  }


  public function insert()
  {
    $this->insertData();
    $this->assertEqual( 5 , $this->qdp->getAffectedRowCount() );
  }

  public function preparedStatement_stringArgs_array()
  {

    $this->insertData();


    $result =
      $this->qdp
        ->prepare('select title,created from article where title like ?','s')
        ->executeWith('Duplicate')
        ->toArray();


    $this->assertEqual(
      array(
        array('title'=>'Duplicate','created'=> '2013-12-24 19:00:00' ),
        array('title'=>'Duplicate','created'=> '2013-12-24 19:00:00' ),
      ),
      $result
    );

  }

  public function preparedStatement_integerArgs_array()
  {

    $this->insertData();


    $result =
      $this->qdp
        ->prepare('select title,created from article where id between ? and ?','ii')
        ->executeWith(4,5)
        ->toArray();


    $this->assertEqual(
      array(
        array('title'=>'Duplicate','created'=> '2013-12-24 19:00:00' ),
        array('title'=>'Duplicate','created'=> '2013-12-24 19:00:00' ),
      ),
      $result
    );

  }

  public function preparedStatement_integerArgs_row()
  {

    $this->insertData();


    $result =
      $this->qdp
        ->prepare('select title,created from article where id between ? and ?','ii')
        ->executeWith(4,5)
        ->toRow();


    $this->assertEqual(
      array('title'=>'Duplicate','created'=> '2013-12-24 19:00:00' )
      , $result
    );

  }

  public function preparedStatement_integerArgs_cell()
  {

    $this->insertData();

    $result =
      $this->qdp
        ->prepare('select title,created from article where id between ? and ?','ii')
        ->executeWith(4,5)
        ->toCell();

    $this->assertEqual(
      'Duplicate',
      $result
    );

  }


  public function preparedStatement_integerArgs_vector()
  {

    $this->insertData();

    $result =
      $this->qdp
        ->prepare('select title from article where id between ? and ?','ii')
        ->executeWith(4,5)
        ->toVector();

    $this->assertEqual(
      array('Duplicate','Duplicate'),
      $result
    );

  }


  public function preparedStatement_integerArgs_arrayMap()
  {

    $this->insertData();

    $result =
      $this->qdp
        ->prepare('select id,title,created from article where id between ? and ? order by id asc','ii')
        ->executeWith(4,5)
        ->toArrayMap();

    $this->assertEqual(
      array(
          '4' => array( 'id' => '4' , 'title'=>'Duplicate' , 'created' => '2013-12-24 19:00:00' )
        , '5' => array( 'id' => '5' , 'title'=>'Duplicate' , 'created' => '2013-12-24 19:00:00' )
      ),
      $result
    );

  }

  public function preparedStatement_integerArgs_pairMap()
  {

    $this->insertData();

    $result =
      $this->qdp
        ->prepare('select id,title  from article where id between ? and ? order by id asc','ii')
        ->executeWith(4,5)
        ->toPairMap();

    $this->assertEqual(
      array(
          '4' => 'Duplicate'
        , '5' => 'Duplicate'
      ),
      $result
    );

  }




}

?>