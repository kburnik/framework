<?
# FRAMEWORK PROJECT
# Modify project settings

# DIR
define('PROJECT_DIR',dirname(__FILE__));
define('PATH_TO_FRAMEWORK', constant('PROJECT_DIR') . '/../' );

# REGIONAL
define('PROJECT_LANGUAGE','hr');
define('PROJECT_TIMEZONE',"Europe/Zagreb");

# NAME
define('PROJECT_NAME',basename(constant('PROJECT_DIR')));
define('PROJECT_TITLE',"Empty Project");


# AUTHOR
define('PROJECT_AUTHOR',"Kristijan Burnik");
define('PROJECT_AUTHOR_MAIL',"admin@localhost");

# MYSQL
define('PROJECT_MYSQL_USERNAME',"eval_framework");
define('PROJECT_MYSQL_PASSWORD',"webhttp80");
define('PROJECT_MYSQL_DATABASE',"eval_framework");

# VIEW
define('PROJECT_VIEW_DIR', constant('PROJECT_DIR') . '/public_html/view');

# error
define('PROJECT_ERRORS_SCAN_ASYNC',true); // asynchronously detect errors on each project.php inclusion
define('PROJECT_ERRORS_SEND_MAIL',true); // send all mails to the author of the project when true

?>
