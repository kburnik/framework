<?
include_once(dirname(__FILE__).'/../base/Base.php');

abstract class XHRResponder implements IResponder {

	protected $errorMessage,$errorCode,$message, $fields = array();	
	
	protected function isSuccessfull() {
		return ! ( isset($this->errorMessage) || isset($this->errorCode) ) ;
	}
	
	protected function setField( $field , $value ) {
		$this->fields[ $field ] = $value;
	}
	
	// additional success fileds to be overriden by subclasses
	protected function getAdditionalSuccessFields() {
		return array();
	}
	
	function respond($formater = null , $params = null , $action = null) {
		
		if ($params === null) {
			$params = $_REQUEST;
		}
		
		if ($action === null) {
			if (array_key_exists('action',$params)) {
				$action = $params['action'];
			} else {
				$action = null;
			}
		}
		
		if ($formater === null) {
			if (isset($params['format'])) {
				$prefix = $params['format'];
				$formaterClassName = $prefix."Formater";				
				$formater = new $formaterClassName();
			} else {
				$formater = new JSONFormater();
			}
			
		}
		
		
		$topClass = get_called_class(); // the class which extends XHRResponder
		$class = get_class($this);		
		if ( method_exists( $this, $action )) {
			
			$rm = new ReflectionMethod($class,$action);
			
			// allowed only in top class
			if ($rm->class != $topClass) {
				return $formater->Format( $this->handleProtectedMethodException($action) );
			}
			
			// allowed only if public
			if ( !$rm->isPublic() ) {
				return $formater->Format( $this->handleProtectedMethodException($action) );
			}
			
			$call_param_array = array();
			$ok = true;
			foreach ($rm->getParameters() as $param) {
				if (!isset( $params[$param->name])) {
					$result = $this->handleArgumentException($param->name);
					$ok = false; break;
				} else {
					$call_param_array[] = $params[$param->name];
				}
			}
			
			header("Cache-Control: no-cache");
			header("Pragma: nocache");
			$formater->Initialize();
			
			if ($ok) {
				$result = call_user_func_array(array($this,$action),$call_param_array);
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
		}
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
	protected function enumerateCalls() {
		$topClass = get_called_class(); // the class which extends XHRResponder
		$refClass = new ReflectionClass( $topClass  );
		
		$methods = $refClass->getMethods( ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_FINAL );
		
		$out = array();
		foreach ($methods as $method) {
			$paramlist = array();
			if ($method->class == $topClass) {
				
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