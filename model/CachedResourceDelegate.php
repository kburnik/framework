<?phpclass CachedResourceDelegate {  private $modelObject;  private $modelClassName;  private $resourceName;  public function __construct( $modelObject , $resourceName = null , $duration = null ) {    $this->modelObject = $modelObject;    // the model on which the cached resource is called    $this->modelClassName = get_class($modelObject);    // the resource name, defaults to the name of the Model class    if ($this->resourceName === null) $resourceName = $this->modelClassName;    $this->resourceName = $resourceName;    // the duration of the cached store    if ($duration === null) $duration = CachedResource::$defaultDuration;    $this->duration = $duration;  }  public function __call( $function, $params ) {    $cachedResource      =  new CachedResource(          $this->resourceName        , md5( var_export( array( $this->modelClassName, $function , $params ) , true ) )        , array( $this->modelObject, $function )        , $params        , $this->duration      )    ;    return $cachedResource->read();  }}
