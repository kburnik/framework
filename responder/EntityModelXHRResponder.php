<?

class EntityModelXHRResponder extends XHRResponder
{

  protected $defaultLimit = 100;

  protected $entityModel;

  protected $params;

  protected $viewProvider;


  protected $em;


  public final function __construct( $params , $viewProvider , $dependencyResolver = null )
  {

    $this->params = $params;

    if ( ! $viewProvider instanceof IViewProvider )
    {
      throw new Exception("Expected IViewProvider, got: " . var_export( $viewProvider , true ));
    }

    if ( $dependencyResolver === null )
      $dependencyResolver = new EntityModelDependencyResolver();

    $this->em = $dependencyResolver;

    $this->viewProvider = $viewProvider;

  }


  public function getEventHandlerInterface()
  {
    return 'IEntityModelXHRResponderEventHandler';
  }

  protected function handleEntityModelException( $message  )
  {
    return array(
      "status" => "error",
      "message" => $message,
      "result" => null
    );
  }

  private function filter_valid_fields( $dataFilterMixed , $allow_additional = true )
  {

    $fields = $this->entityModel->getEntityFields();

    $additional = array(":or");

    $allowed_fields = $fields;

    if ( $allow_additional )
      $allowed_fields = array_merge( $fields , $additional );

    return array_pick( $dataFilterMixed , $allowed_fields );
  }

  public function respond($formater = null , $params = null , $action = null)
  {



    $params = $this->params;

    $entityModelFactory = $params['entityModelFactory'];

    if ( ! $entityModelFactory instanceof IEntityModelFactory )
    {
      throw new Exception( 'Expected instance of EntityModelFactory in $params["entityModelFactory"] ' );
    }

    unset ( $params['entityModelFactory'] );




    if ( is_array( $params ) )
    {
      $params = array_merge( $this->params , $params );
    }
    else
    {
      $params = $this->params;
    }

    if ( $action == null )
    {

      if  ( !isset( $params[ 'action' ] ) )
      {
        throw new Exception( 'Missing field $params["action"]' );

      }

      $action = $params[ 'action' ];

      unset ( $params['action'] );
    }


    $formater = $this->getFormater( $params['format'] , $params , $action );

    try
    {
      $this->entityModel = $entityModelFactory->createModelForEntity( $params[ 'entity' ] );

      unset( $params['entity'] );

      return parent::respond(  $formater , $params, $action );
    }
    catch ( Exception $ex )
    {
      return  $formater->Format(  $this->handleEntityModelException( $ex->getMessage() ) );
    }


  }


  public function __setMessageView( $viewKey , $data )
  {

    if ( $this->viewProvider->containsTemplate( $viewKey ) )
    {
      return $this->setMessage(
        $this->viewProvider->getView( $viewKey , $data )
      );

    }
    else
    {
      $className = get_class($this);
      $viewProviderClassName = get_class( $this->viewProvider );
      error_log("Missing view '$viewKey' in {$viewProviderClassName} of Responder {$className}  ");
      return $this->setMessage( $this->formater->format( func_get_args() ) );

    }
  }


  public function fields()
  {

    return $this->entityModel->getEntityFields();

  }


  public function find()
  {

    $params = $this->params;

    $start = intval( $params['start'] );
    $limit = intval( $params['limit'] );

    if ( $limit === 0 )
      $limit = $this->defaultLimit;



    $orderBy = null;
    if ( isset($params['orderBy']) )
    {
      $ordering = explode(',' , $params['orderBy'] );
      unset( $params['orderBy'] );
      $orderBy  = array();

      foreach ( $ordering as $orderInstruction )
      {

        $orderField = $orderInstruction;
        $orderDirection = 1;
        if ( $orderField[0] == '-' )
        {
          $orderField = substr( $orderField , 1 );
          $orderDirection = -1;
        }

        $orderBy[ $orderField ] = $orderDirection;

      }


    }

    // pick entity fields and allow "search" too
    $filter = $this->filter_valid_fields( $params , true );

    if ( isset( $params["search"] ) )
    {
      $searchFilter = $this->createSearchFilter( $params["search"] );
      $filter = array_merge( $filter, $searchFilter );
    }

    // in clause
    if   (isset($params['in']))
    {
      $inClause = explode( ',' , $params['in'] );
      $field = array_shift( $inClause );
      $filter[':in'] = array($field,$inClause);
    }

    // operators
    $operators = array('gt','lt','gteq','lteq','between');
    $ops = array_pick( $params , $operators );

    foreach ($ops as $operator => $value)
      $filter[":{$operator}"] = explode( ',' , $value );


    $res = $this->entityModel->find( $filter );


    if ( $orderBy != null )
    {
      $res = $res->orderBy( $orderBy );
    }


    $res->limit( $start , $limit );


    $result = $res->ret();

    $min_id = 1000000000;
    $max_id = 0;

    foreach ($result as $i => $e)
    {
      $result[$i] = $this->wrapEntity( $e , false );

      if ($e->id > $max_id)
        $max_id = $e->id;

      if ($e->id < $min_id)
        $min_id = $e->id;
    }


    if ( count($result) )
    {
      $this->setField( "range" , array($min_id,$max_id) );
    }

    return $result;

  }


  public function findById( $id )
  {

    $filters = array(
        "previous" => array(array( ":lt" => array( "id" , $id ) ),array("id" => -1))
       ,  "next" => array(array( ":gt" => array( "id" , $id ) ),array("id" => 1))
    );



    foreach ( $filters as $context => $descriptor )
    {
      list( $filter, $order ) = $descriptor;
      $res = $this->entityModel->find( $filter )->orderBy( $order )->limit(0,1)->ret();

      if (count($res))
        $res = reset($res);
      else
        $res = null;

      $sibling_id = null;

      if ( $res )
      {
        $sibling_id = $res->id;
      }


      $this->setField( $context , $sibling_id );
    }


    return $this->wrapEntity( $this->entityModel->findById( $id ) , true );

  }

  public function findFirst()
  {

    $fields = $this->entityModel->getEntityFields();


    $filter = $this->filter_valid_fields( $this->params ); // , $fields );

    return $this->wrapEntity( $this->entityModel->findFirst( $filter ) , true );

  }





  public function insert()
  {

    $data = $this->filter_valid_fields( $this->params );

    $id = $this->entityModel->insert( $data );

    $result = $this->entityModel->findById( $id );

    $this->onInsert( $this , $this->entityModel , $data , $result );

    return $result;

  }



  public function update()
  {

    $data = $this->filter_valid_fields( $this->params );

    $result = $this->entityModel->update( $data );

    $this->onUpdate( $this , $this->entityModel , $data , $result );

    return $result;

  }


  public function delete()
  {

    $filter = $this->filter_valid_fields( $this->params , true);

    $result = $this->entityModel->deleteBy( $filter );

    $this->onDelete( $this , $this->entityModel , $filter , $result );

    return $result;
  }

  public function count()
  {

    $params = $this->params;

    $filter = $this->filter_valid_fields( $params );


    if ( isset( $params["search"] ) )
    {
      $searchFilter = $this->createSearchFilter( $params["search"] );
      $filter = array_merge( $filter, $searchFilter );
    }

    return $this->entityModel->find( $filter )->affected();
  }


  public function commit( $update , $insert , $delete )
  {

    $fields = $this->entityModel->getEntityFields();

    $results = array(
      "update"=>array(),
      "insert"=>array(),
      "delete"=>array(),
    );

    foreach ( $update as $up )
    {
      list( $id , $changes ) = $up;

      $data = array_pick( $changes , $fields );

      $data['id'] = $id;

      $result = $this->entityModel->update( $data );

      $results['update'][] = $result;


      // handle extra data
      $extra = array_diff_key( $changes , $data );

      foreach( $extra as $extraName => $extraData )
      {
        $this->entityModel->handleExtra( "update" , $id , $extraName , $extraData );
      }


    }

    foreach ( $insert as $in )
    {

      $entityArray = array_pick( $in, $fields );

      $id = $this->entityModel->insert( $entityArray );

      $results['insert'][] = $id;

      // handle extra data
      $extra = array_diff_key( $in , $entityArray );

      foreach( $extra as $extraName => $extraData )
      {
        $this->entityModel->handleExtra( "insert" , $id , $extraName , $extraData );
      }

    }

    foreach ( $delete as $id )
    {
      $result = $this->entityModel->deleteById( $id );

      $results['delete'][] = $result;

    }


    $this->onCommit( $this, $this->entityModel , $changeset , $result );

    return $results;


  }

  protected function wrapEntity( $entity , $singleEntity )
  {
    return $entity->toArray();
  }


  public function help()
  {
    return $this->describe();
  }



  protected $search_fields = array();

  protected function createSearchFilter( $value )
  {


    $filter = array();

    $searchTerms = trim( $value );

    // ID search
    if ( substr($searchTerms,0,1) == '#' )
    {

      $searchTerms = substr( $searchTerms , 1 );

      if ( strpos( $searchTerms , '-' ) )
      {
        // ranged
        $range = explode( "-" , $searchTerms );
        $range = array_map("intval",$range);
        sort($range);
        list( $start_id, $end_id ) = $range;

        $filter[':gteq'] = array( "id" , $start_id);
        $filter[':lteq'] = array( "id" , $end_id);

      } else {

        // listed

        $list = explode(",",$searchTerms);
        $list = array_map("intval",$list);

        $filter[":in"] = array( "id" , $list );
      }

    } else {

      foreach ($this->search_fields as $field)
        $filter[':or'][] = array($field,array("%{$searchTerms}%"));

    }

    return $filter;

  }



}


?>