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
	
	public function respond($formater = null , $params = null , $action = null) 
	{
	
		
		
		$params = $this->params;
		
		// header('x-received-params:'.json_encode($params));
		
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
		
		$filter = array_pick( $params , $this->entityModel->getEntityFields() );
		
		// in clause
		if 	(isset($params['in']))
		{
			$inClause = explode( ',' , $params['in'] );
			$field = array_shift( $inClause );
			$filter[':in'] = array($field,$inClause);
		}
		
		$res = $this->entityModel->find( $filter );
		
		
		if ( $orderBy != null )
		{
			$res = $res->orderBy( $orderBy );
		}
		
		
		$res->limit( $start , $limit );
		
		
		$result = $res->yield();
		
		
		foreach ($result as $i => $e)
		{
			$result[$i] = $this->wrapEntity( $e );
		}

		return $result;		
		
	}
	
	
	public function findById( $id )
	{
		return $this->wrapEntity( $this->entityModel->findById( $id ) );
		
	}
	
	public function findFirst()
	{
	
		$fields = $this->entityModel->getEntityFields();
	
		$filter = array_pick( $this->params , $fields );
		
		return $this->wrapEntity( $this->entityModel->findFirst( $filter ) );
		
	}
	
	
	
	
	
	public function insert()
	{
		$fields = $this->entityModel->getEntityFields();
		
		$data = array_pick( $this->params , $fields );
		
		$id = $this->entityModel->insert( $data );
		
		$result = $this->entityModel->findById( $id );
		
		$this->onInsert( $this , $this->entityModel , $data , $result );
		
		return $result;
		
	}
	

	
	public function update()
	{
		
		$fields = $this->entityModel->getEntityFields();
		
		$data = array_pick( $this->params , $fields );
		
		$result = $this->entityModel->update( $data );
		
		$this->onUpdate( $this , $this->entityModel , $data , $result );
		
		return $result;
		
	}
	
	
	public function delete()
	{
		$fields = $this->entityModel->getEntityFields();
		
		$data = array_pick( $this->params , $fields );
		
		$result = $this->entityModel->deleteBy( $data );
		
		$this->onDelete( $this , $this->entityModel , $data , $result );
		
		return $result;
	}
	
	public function count()
	{
	
		$fields = $this->entityModel->getEntityFields();
		
		$data = array_pick( $this->params , $fields );
	
		return $this->entityModel->find( $data )->affected();
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
		}
		
		foreach ( $insert as $in )
		{
		
			$entityArray = array_pick( $in, $fields );
			
			$result = $this->entityModel->insert( $entityArray );
			
			$results['insert'][] = $result;
		
		}
		
		foreach ( $delete as $id )
		{
			$result = $this->entityModel->deleteById( $id );
			
			$results['delete'][] = $result;
		}
		
	
		$this->onCommit( $this, $this->entityModel , $changeset , $result );
		
		return $results;	
		
		
	}
	
	protected function wrapEntity( $entity )
	{
		return $entity->toArray();
	}
	
		
	public function help()
	{
		return $this->describe();
	}


}


?>