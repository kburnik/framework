<?php
include_once( dirname(__FILE__) . "/../base/Base.php" );

function eol(){
  echo "\n";
}


function select( $question )
{

  echo "[ ";
  clr_question( $question );
  echo " ] ";


  $fp = fopen("php://stdin","r");
  $ans = trim(fgets($fp));
  fclose($fp);

  return $ans;
}


function ans( $question , $multiple = true ){

  if ( defined('YES_TO_ALL')  )
  {
    if ( $multiple )
      sleep(5);

    return true;
  }

  echo "[ ";
  clr_question( $question );
  echo " ] ";

  if ( !$multiple )
  {
    $fp = fopen("php://stdin","r");
    $ans = trim(fgets($fp));
    fclose($fp);
    return $ans;
  }




  clr_success("y (YES)");
  echo " / ";
  clr_section("enter (NO)");
  echo ": ";


  $fp = fopen("php://stdin","r");
  $ans = trim(fgets($fp));
  fclose($fp);

  switch( $ans ){
    case "y": return true;
    case "": return false;
    default:
      echo "Answer y for YES or empty for NO!\n";
      return ans( $question );
  }

}


function colored( $str , $clr )
{

  return ShellColors::getInstance()->getColoredString( $str, $clr );
}

class StdinReader
{
  public function __construct()
  {
    $this->fp = fopen( 'php://stdin', 'r' );
  }

  public function read()
  {
    return fgets( $this->fp ) ;
  }

  public function __destruct()
  {
    fclose( $this->fp );
  }
}

if (!function_exists('readline')) {
function readline(){
  static $reader;
  if ( $reader == null )
    $reader = new StdinReader();

  return $reader->read();
}
}




if ( $argv[1] == '-y' )
  define('YES_TO_ALL',1);

// locate the project file and include it
try {

  Project::Resolve();

  flush();
  ob_flush();
  ob_end_flush();

  $project = Project::GetCurrent();
  echo colored ( $project->GetProjectRoot()."\n" . $project->getProjectTitle() ,  "cyan" ) . "\n\n";
  unset( $project );



} catch (Exception $ex){

  echo colored( $ex->getMessage() , "red" ) . "\n";

  exit (-1);
}

