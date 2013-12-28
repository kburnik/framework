<?class EntityModelXHRResponder extends XHRResponder{	protected $defaultLimit = 100;			protected $entityModel;		protected $params;		public function __construct( $params )	{			$this->params = $params;				}			public function getEventHandlerInterface()	{		return 'IEntityModelXHRResponderEventHandler';	}		protected function handleEntityModelException( $message  )	{		return array(			"status" => "error", 			"message" => $message, 			"result" => null 		);	}		public function respond($formater = null , $params = null , $action = null) 	{				$params = $this->params;						$entityModelFactory = $params['entityModelFactory'];				if ( ! $entityModelFactory instanceof IEntityModelFactory )		{			throw new Exception( 'Expected instance of EntityModelFactory in $params["entityModelFactory"] ' );		}				unset ( $params['entityModelFactory'] );												if ( is_array( $params ) )		{			$params = array_merge( $this->params , $params );		}		else		{			$params = $this->params;		}				if ( $action == null ) 		{						if  ( !isset( $params[ 'action' ] ) )			{				throw new Exception( 'Missing field $params["action"]' );								}						$action = $params[ 'action' ];						unset ( $params['action'] );		}						$formater = $this->getFormatter( $params['format'] , $params , $action );				try 		{			$this->entityModel = $entityModelFactory->createModelForEntity( $params[ 'entity' ] );						unset( $params['entity'] );					return parent::respond(  $formater , $params, $action );			} 		catch ( Exception $ex )		{			return  $formater->Format(  $this->handleEntityModelException( $ex->getMessage() ) );		}				}		public function find()	{			$params = $this->params;			$start = intval( $params['start'] );		$limit = intval( $params['limit'] );				if ( $limit === 0 ) 			$limit = $this->defaultLimit;											$orderBy = null;		if ( isset($params['orderBy']) )		{			$ordering = explode(',' , $params['orderBy'] );			unset( $params['orderBy'] );			$orderBy  = array();						foreach ( $ordering as $orderInstruction )			{							$orderField = $orderInstruction;				$orderDirection = 1;				if ( $orderField[0] == '-' )				{					$orderField = substr( $orderField , 1 );					$orderDirection = -1;									}								$orderBy[ $orderField ] = $orderDirection;						}								}				$filter = array_pick( $params , $this->entityModel->getEntityFields() );				$res = $this->entityModel->find( $filter );						if ( $orderBy != null )		{			$res = $res->orderBy( $orderBy );		}						$res->limit( $start , $limit );						return $res->yield();				}			public function insert()	{		$fields = $this->entityModel->getEntityFields();				$data = array_pick( $this->params , $fields );				$id = $this->entityModel->insert( $data );				$result = $this->entityModel->findById( $id );				$this->onInsert( $this , $this->entityModel , $data , $result );				return $result;			}			public function update()	{				$fields = $this->entityModel->getEntityFields();				$data = array_pick( $this->params , $fields );				$result = $this->entityModel->update( $data );				$this->onUpdate( $this->entityModel , $data , $result );				return $result;			}			public function delete()	{		$fields = $this->entityModel->getEntityFields();				$data = array_pick( $this->params , $fields );				$result = $this->entityModel->deleteBy( $data );				$this->onDelete( $this , $this->entityModel , $data , $result );				return $result;	}			public function findById( $id )	{		return $this->entityModel->findById( $id );					}				public function help()	{		return $this->describe();	}}?>