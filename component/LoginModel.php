<?
include_once(dirname(__FILE__)."/../base/Base.php");

abstract class LoginModel extends Base {
  
  protected $session;
  
  public abstract function getLoginView( $message );
  
  public abstract function getLogoutView( $userdata );
  
  public abstract function getErrorView($errorMessage,$errorCode);
  
  public abstract function getMessageView();
  
    
  // public abstract function attemptLogin($session);
  
  // public abstract function attemptLogout($session);
  
  public function __construct($session) {
    if (!$session instanceof Session) {
      throw new Exception('Given session object not instance of Session!');
    }
    $this->session = $session;
    
    
    $args = func_get_args();
    array_shift($args);
    $eventHandlers = $args;
    
    foreach ($eventHandlers as $handler) {
      $this->session->addEventHandler( $handler );
    }
    
    $this->session->start();
    // $this->attemptLogin($session);
    // $this->attemptLogout($session);
  }
    
  
  
  public function outputLoginView() {
    if (!$this->session->isLoggedIn()){
      return $this->getLoginView( $this->outputMessageView() );
    }
  }
  
  public function outputLogoutView() {
    if ($this->session->isLoggedIn()){
      return $this->getLogoutView( $this->session->getUserData() );
    }
  }
  
  public function outputMessageView() {
    if ($this->session->errorOccured()){
      return $this->getErrorView($this->session->getErrorMessage(), $this->session->getErrorCode());
    } else {
      return $this->getMessageView();
    }
  }
  
}

?>