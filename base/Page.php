<?
include_once(dirname(__FILE__).'/Base.php');

class Page extends Base {
	public static function getURL() {
		return $_SERVER['REQUEST_URI'];
	}
}

?>