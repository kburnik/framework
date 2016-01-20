<?php
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
define('PROJECT_MYSQL_USERNAME',"framework_test");
define('PROJECT_MYSQL_PASSWORD',"dBZT6DbtWrBYmwqZ");
define('PROJECT_MYSQL_DATABASE',"framework_test");

# VIEW
define('PROJECT_VIEW_DIR', constant('PROJECT_DIR') . '/public_html/view');
