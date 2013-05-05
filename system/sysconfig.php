<?
///////////////////////////////////////////////////////////////////////////////////////////////////////////////
// SYSTEM CONFIG
///////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* ROOT DOMAIN DETERMINATION */
$__ROOT_DOMAIN__ = implode(".",array_slice(explode(".",$_SERVER['SERVER_NAME']),-2,2));
/* end of root domain determination */

///////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* ROOT DETERMINATION */
	$__ROOT__ = explode("/",str_replace("\\","/",dirname(__FILE__)));
	array_pop($__ROOT__);
	$__ROOT__ = implode("/",$__ROOT__);
/* end of root determination */

///////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* DOMAIN DETERMINATION */
	$__DOMAIN__ = str_replace (
		$_SERVER['DOCUMENT_ROOT'],
		"" ,
		$__ROOT__
	);
	//if (strlen($__DOMAIN__)>0) $__DOMAIN__="/".substr($__DOMAIN__,0,strlen($__DOMAIN__)-1);
	$__DOMAIN__ = $_SERVER['SERVER_NAME'].$__DOMAIN__;
/* end of domain determination */

define("__IN_SHELL__", isset($_SERVER['SHELL'])*1 );

define("__REQUEST_BASENAME__", basename($_SERVER['PHP_SELF']) );
define('__VERSION__','1.8');
define('__RELEASE_DATE__','16:27 20.12.2010.');

define('__AUTHOR__','Kristijan Burnik');
define('__AUTHOR_URL__','http://www.invision-web.net/');
define('__AUTHOR_MAIL__','kristijanburnik@gmail.com');
define('__AUTHOR_HEADER_SIGNATURE__','X-Web-Developer: '.__AUTHOR__.' | '.__AUTHOR_URL__);

define('__SUPERADMIN_USERNAME__','kburnik');
define('__SUPERADMIN_PASSWORD__','webhttp80');


define('__SERVER__',$_SERVER['SERVER_NAME']);
define('__ROOT_DOMAIN__',$__ROOT_DOMAIN__);
define('__DOMAIN__',$__DOMAIN__);


if (!defined("__IS_LOCAL_SERVER__")) define('__IS_LOCAL_SERVER__',($_SERVER['REMOTE_ADDR']=="127.0.0.1"));

define('__CACHE_DURATION__',60*60*24*30); // IN SECONDS == hold a file for a month and then delete it by modules/cache/cache.cron.php

// DIRS and URLS

define("__ROOT__",$__ROOT__);

if (!defined("__LANG__")) {
	define("__LANG__","hr");
}

$_LANG = __LANG__;
if (!in_array($_LANG,array("","hr"))) {
	$LANG_SUFFIX = "/".$_LANG;
	define("__LANG_PREFIX__","/".$_LANG);
} else {
	define("__LANG_PREFIX__","");
}



define('__URL__','http://'.__DOMAIN__);
define('__URLPATH__',str_replace($_SERVER['SERVER_NAME'],"",__DOMAIN__));
define('__LANG_URLPATH__',__URLPATH__.$LANG_SUFFIX);
define('%',__LANG_URLPATH__);
define('%%',__URLPATH__);

define('__TPL__', __ROOT__."/template");
define('__SUBTPL__', __ROOT__."/template/subtemplate/");

define('__CACHE_DIR__',__ROOT__."/cache");
define('__CACHE_URL__',__URL__."/cache");

define('__PHOTOS_DIR__',__ROOT__."/photos");
define('__PHOTOS_URL__',__URL__."/photos");

define('__SYSTEM_DIR__',__ROOT__."/system");
define('__PROJECT_DIR__',__ROOT__."/project");

define('__PROJECT_CONFIG_FILE__',__PROJECT_DIR__."/config.php");

define('__LOGFILE__',__ROOT__."/sitelog.txt");
define('__HTACCESS_FILE__',__ROOT__."/.htaccess");
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// project specific config
if (file_exists(__PROJECT_CONFIG_FILE__)) include(__PROJECT_CONFIG_FILE__);
//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>