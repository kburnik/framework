<?

abstract class Session extends BaseSingleton {
	
	var $justLoggedIn = false;
	var $loggedIn = false;
	var $loginTime;
	var $logoutTime;
	
	var $credential;
	
	var $hash;
	var $registered = false;
	var $registerTime;	
	var $resumeCount;
	
	var $sessionID = null;
	var $errorMessage,$errorCode;
	var $uid;
	
	protected $userData = null;
	
	public abstract function attemptLogin($credential,$password);
	
	public function setUserData( $userData )  {
		$this->userData = $userData;
		Console::WriteLine("Session :: Setting user data: " . var_export($this->userData,true));
	}
	
	public function resetUserData() {
		$this->userData = null;
	}
	
	public function getUserData(){
		return $this->userData;
	}
		
	function __construct() {
		
		session_start();		
		
		$this->sessionID = str_replace('.','',microtime(true));
		
		$this->uid = get_class($this);
		
		if (isset($_SESSION[$this->uid])) {
			foreach ($_SESSION[$this->uid] as $var => $val) {
				$this->$var = $val;
			}
		}
		$this->errorMessage = null;
		$this->errorCode = null;
		$this->justLoggedIn = false;
	}
	
	function __destruct() {	
		foreach ($this as $var=>$val) {
			$_SESSION[$this->uid][$var] = $val;
		}
	}
	
	function start() {
		if ($this->loggedIn) {
			// echo "logged in";
			$this->resumeCount++;
			$this->onResume( $this->credential , $this->resumeCount , $this );
		}
		
		if (!$this->registered) {
			$this->hash = md5($this->loginTime);
			$this->registerTime = microtime(true);
			$this->registered = true;
			$this->onRegister($this->hash, $this->registerTime , $this);
		}
	}
	
	
	public function logout() {
		if (!$this->loggedIn || $this->justLoggedIn) return;
		
		$this->loggedIn = false;
		$this->logoutTime = microtime(true);
		$this->onLogout($this->credential, $this);	
		$this->resumeCount = 0;
	}
	
	
	
	public function getEventHandlerInterface() {
		return ISessionEventHandler;
	}
	
	public function isloggedIn() {
		return $this->loggedIn;
	}
	
	public function setError($message,$code = 0) {
		$this->errorMessage = $message;
		$this->errorCode = $code;
	}
	
	public function getErrorMessage() {
		return $this->errorMessage;
	}
	
	public function getErrorCode() {
		return $this->errorCode;
	}
	
	public function errorOccured() {
		return ( $this->errorCode !== null );
	}
	
	public function login($credential,$password) {
		if ($this->loggedIn) {	
			$this->userData = $this->getUserData();
			return;
		}
		
		$this->userData = null;
		if ($this->attemptLogin($credential,$password)) {
			if ($this->userData === null ) {
				throw new Exception('Session :: User data not set in attemptLogin method!');
			}
			$this->loginTime = microtime(true);
			$this->loggedIn = true;
			$this->justLoggedIn	= true;
			
			$this->credential = $credential;
			
			$this->onLogin( $credential, $password, $this);
			
		} else {
			$this->loggedIn = false;
			$this->onLoginError($credential,$password,$this);
		}
	}
	
	public function getSessionID() {
		return $this->sessionID;
	}
	
	
	
}

?>
