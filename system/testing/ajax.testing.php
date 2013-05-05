<?
include_once("testing.module.php");

class _module() {
	public function gettime() {
		return now();
	}
}

ajax() -> run( new _module() ) -> output();

?>