<?
include_once(dirname(__FILE__).'/../base/Base.php');

abstract class XHRResponder extends Base implements IResponder {

	protected $errorMessage,$errorCode,$message, $fields = array();	
	
	protected $formater;
	
	protected function isSuccessfull() 
	{
		return ! ( isset($this->errorMessage) || isset($this->errorCode) ) ;
	}
	
	protected function setField( $field , $value ) {
		$this->fields[ $field ] = $value;
	}
	
	// additional success fileds to be overriden by subclasses
	protected function getAdditionalSuccessFields() {
		return array();
	}
	
	protected function isAuthorized( $method , $params ) 
	{
		return true;
	}
	
	public function inputJSON() 
	{
	
		$input = file_get_contents("php://input");
		
		$json = json_decode( $input , true );
		
		if (is_array( $json )) 
		{
			$_REQUEST = array_merge( (array) $_REQUEST ,  $json );
			return true;
		} 
		else 
		{
			return false;
		}
	}
	
	protected function outputHeaders()
	{
		header("Cache-Control: no-cache");
		header("Pragma: nocache");
	}
	
	protected function getFormater(  $formater = null , $params = null , $action = null )
	{
	
		$formaterClassName = 'JSONFormater';
		
		if ($formater === null && isset($params['format']) )
		{
			$prefix = $params['format'];
			$formaterClassName = "{$prefix}Formater";
		} 
		
		
		if ( !class_exists( $formaterClassName ) )
		{
		
			throw new Exception("Nonexisting formatter: '$formaterClassName'");
		
		}
		
		if ( $formaterClassName == 'JSONFormater' && ( in_array($params['pretty'],array('true','1')) ) )
		{
			$formater = new $formaterClassName( true );
		}
		else
		{
			$formater = new $formaterClassName( );
		}
		
		
		return $formater;
	}
	
	protected function methodExists( $methodName )
	{
		return method_exists( $this, $methodName );
	} 
	
	protected function callMethod( $action , $params )
	{
		return call_user_func_array(array($this,$action),$params);
	}
	
	
	function respond($formater = null , $params = null , $action = null) 
	{
		
		$this->outputHeaders();
		
		if ($params === null) 
		{
			$params = $_REQUEST;
		}
		
		if ($action === null) 
		{
			if (array_key_exists('action',$params)) 
			{
				$action = $params['action'];
			} 
			else 
			{
				$action = null;
			}
		}
		
		$formater = $this->getFormater( $formater, $params, $action );
		
		$this->formater = $formater;
		
		$formater->Initialize();
		
		
		$topClass = get_called_class(); // the class which extends XHRResponder
		
		$class = get_class($this);
		
		if ( $this->methodExists( $action ))
		{
		
			// allowed only if authorized ( true by default )
			if ( ! $this->isAuthorized( $action , $params ) ) 
			{
				
				return $formater->Format( $this->handleUnathorizedException($action,$params) );
			}
			
			$rm = new ReflectionMethod($class,$action);
			
			// calls allowed only in extended classes
			if ($rm->class == 'XHRResponder') {
				
				return $formater->Format( $this->handleProtectedMethodException($action) );
			}
			
			// allowed only if public
			if ( !$rm->isPublic() ) {
				
				return $formater->Format( $this->handleProtectedMethodException($action) );
			}
			
			
			$call_param_array = array();
			$ok = true;
			foreach ($rm->getParameters() as $param) {
				if (!array_key_exists( $param->name , $params )) {
					
					
					
					return $formater->Format(  $this->handleArgumentException($param->name) );
					
				} else {
					$call_param_array[] = $params[$param->name];
				}
			}
			
			
			
			
			if ($ok) {
				$result = $this->callMethod($action,$call_param_array);
				
				if ( isset($this->errorMessage) || isset($this->errorCode) ) {
					return $formater->Format(
						array_merge(							
							array(
								  "status" => "error"
								, "message" => $this->errorMessage
								, "errorcode" => $this->errorCode
								, "result" => $result
								, "method" => $action
							),
							$this->fields							
						)
					);
				} else {
					if (array_key_exists('barebones',$params) && $params['barebones']) {
						return $formater->Format( array($result) );
					} else {
						// note the success
						$this->successfull = true;
						
						return $formater->Format(
							array_merge(								
								array(
									  "status" => "success"
									, "message" => $this->message
									, "result" => $result
									, "method" => $action
								) 
								, $this->fields
								, $this->getAdditionalSuccessFields()
							)
						);
					}
				}
			} else {
				return $formater->Format( $this->handleMethodException($action) );
			}
		} else {
			return $formater->Format( $this->handleNoActionException($action) );
		
		}
	}
	
	function handleUnathorizedException( $method , $params )
	{
		return array(
			"status" => "error", 
			"message" => "Unauthorized for this method '{$method}'", 
			"result" => null 
		);
	}
	
	function handleNoActionException( $method )
	{
		return array(
			"status" => "error", 
			"message" => "Unknown method '{$method}'", 
			"result" => null 
		);
	}
	
	function handleArgumentException($argument) {
		return array("status" => "error", "message" => "Missing argument '{$argument}'", "result" => null );
	}
	
	function handleMethodException($method) {
		return array("status" => "error", "message" => "Missing method '{$method}'", "result" => null );
	}
	
	function handleProtectedMethodException($method) {
		return array("status" => "error", "message" => "Calling non-public method '{$method}'", "result" => null );
	}
	
	public function handleError($message,$code) {
		$this->errorMessage = $message;
		$this->errorCode = $code;
	}
	
	public function setMessage($message) {
		$this->message = $message;
	}
	
	// enumerate all public functions in this class
	protected function describe() {
		$topClass = get_called_class(); // the class which extends XHRResponder
		$refClass = new ReflectionClass( $topClass  );
		
		$methods = $refClass->getMethods( ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_FINAL );
		
		$out = array();
		foreach ($methods as $method) {
			$paramlist = array();
			if (
				$method->class != 'XHRResponder' 
				&& substr($method->name,0,1) != '_' 
				&& !method_exists( XHRResponder , $method->name ) ) {
				
				foreach ($method->getParameters() as $param) {
					$paramlist[] = $param->name;
				}
				
				$out[] = array( "method" => $method->name , "params" => $paramlist );
				
			}
		}
		
		return $out;
	}
	
}

?>