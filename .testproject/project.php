<?
define('PRODUCTION_MODE',1);

include_once(dirname(__FILE__) . '/project-settings.php');

include_once( constant('PATH_TO_FRAMEWORK') . "/base/Base.php" );

if (PHP_OS != 'Linux') {

  $mysql = new MySQLProvider('localhost',PROJECT_MYSQL_USERNAME,PROJECT_MYSQL_PASSWORD,PROJECT_MYSQL_DATABASE);
  define('PROJECTROOT',"/".constant('PROJECT_NAME'));

} else {

  $mysql = new MySQLProvider('localhost',PROJECT_MYSQL_USERNAME,PROJECT_MYSQL_PASSWORD,PROJECT_MYSQL_DATABASE);
  define('PROJECTROOT',"");
}



// create the project
$project = Project::Create(
  /* name */     constant("PROJECT_NAME"),
  /* title */   constant("PROJECT_TITLE"),
  /* author */   constant("PROJECT_AUTHOR"),
  /* root */     constant("PROJECT_DIR"),
  /* timezone */   constant("PROJECT_TIMEZONE")
);


// ERROR HANDLING
// register the callback for new errors to get mailed to admin
if (defined('PROJECT_ERRORS_SEND_MAIL') && constant('PROJECT_ERRORS_SEND_MAIL') == true )
{
  function mailNewErrorsToAdmin( $filename , $startByte , $lengthBytes ) {
    $fp = fopen( $filename , 'r' );
    fseek($fp,$startByte);
    while (!feof($fp)) { $out.=fgets($fp,4096);  }
    fclose($fp);

    $out = ErrorLogListener::getStructuredErrors( $out );

    mail(constant('PROJECT_AUTHOR_MAIL'),'Errors: ' . $filename, json_format( json_encode($out) ) );
  }
  ErrorLogListener::getInstance()->addEventListener('onTrackErrors','mailNewErrorsToAdmin');
}


// scan for errors asynchronously and store to /gen/project_error_log
if ( defined('PROJECT_ERRORS_SCAN_ASYNC') && constant('PROJECT_ERRORS_SCAN_ASYNC') == true )
{
  ErrorLogListener::getInstance()->startAsyncScan();
}
//


$application = Application::getInstance();

$project->setQueriedDataProvider($mysql);

$application->Start();

if (!defined('SKIP_DB'))
  $mysql->connect();


define('PUBLICROOT',PROJECTROOT."");
define('REQUEST_URI',$_SERVER['REQUEST_URI']);

#####################################################

include_once( dirname(__FILE__) . "/functions.php");


?>
