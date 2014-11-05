<?


interface ISessionEventHandler {

  function onLogin( $credential, $password , $session );
  
  function onLogout( $credential , $session );
  
  function onLoginError( $credential , $password , $session );
  
  function onRegister( $hash , $time , $session );
  
  function onResume( $credential , $resumeCount , $session );
  
}

?>