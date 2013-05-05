<?
include_once(dirname(__FILE__).'/../base/Base.php');

interface IPaginator {
	
	// initialize paginator // before constructed!
	function initialize();

	// return an array with a range of records
	function getData($start = 0,$limit = 30);

	// get total number of records
	function getCount();

	// get the link for the navigation
	function getPageURL($page);

	// number of pages to be displayed via page navigation
	function getNavigationCount();
	
	function getViewGroup();
}

?>