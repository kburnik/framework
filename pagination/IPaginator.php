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

  // get starting index of displayed items
  function getStart();

  // get number of items on page
  function getLimit();

  // get ending index of displayed items
  function getEnd();

  // number of pages to be displayed via page navigation
  function getNavigationCount();

  function getViewGroup();
}

?>