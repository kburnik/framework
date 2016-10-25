<?php
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

$application = Application::getInstance();

$project->setQueriedDataProvider($mysql);

$application->Start();

if (!defined('SKIP_DB'))
  $mysql->connect();


define('PUBLICROOT',PROJECTROOT."");
define('REQUEST_URI',$_SERVER['REQUEST_URI']);

#####################################################

include_once( dirname(__FILE__) . "/functions.php");

