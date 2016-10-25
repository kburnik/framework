<?php

$df = dirname( __FILE__ );

$mocks  = "$df/mocks";

include_once( "$mocks/Article.php" );
include_once( "$mocks/ArticleModel.php" );
include_once( "$mocks/ArticleModelDataDriver.php" );
include_once( "$mocks/ArticleModelMySQLDataDriver.php" );
include_once( "$mocks/IArticleModelEventHandler.php" );
include_once( "$mocks/ArticleModelEventHandler.php" );
include_once( "$mocks/Category.php" );
include_once( "$mocks/CategoryModel.php" );
include_once( "$mocks/CategoryModelDataDriver.php" );

