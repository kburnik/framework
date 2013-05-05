<?

/* Web Projects Common System v. 1.8. */

$WORKTIME = microtime(true)*1000;
define("__SYSDIR__",dirname(__FILE__));

include_once(__SYSDIR__."/sysconfig.php");

// THE SYSTEM CLASS
class _system {
	var $destructor = null;
	
	function __construct() {
	
	}
	
	function setdestructor($function) {
		$this->destructor = $function;
	}
	
	function __destruct() {
		if ($this->destructor!=null){
			$function = $this->destructor;
			$function();
		}
	}
	
}

function sys() {
	global $_system;
	if (!isset($_system)) {
		$_system = new _system();
	}
	return $_system;
}



// SYSTEM INCLUDES
$_classes=array(
	"auxiliary.php",
	"template.php",	
	"common_templates.php",	
	// "mysql.php",	
	"session.php",
	
	"regex.php",	
	
	"http_build_url.php",
	"storage.php",
	
);

foreach ($_classes as $c) include(__SYSDIR__."/".$c);
unset($dir,$c,$_classes);

?>