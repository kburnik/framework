<?
interface IPaginator {
  // Initialize paginator // before constructed!
  function initialize();
  // Return an array with a range of records.
  function getData($start = 0, $limit = 30);
  // Get total number of records.
  function getCount();
  // Get the link for the navigation.
  function getPageURL($page);
  // Get starting index of displayed items.
  function getStart();
  // Get number of items on page.
  function getLimit();
  // Get ending index of displayed items.
  function getEnd();
  // Number of pages to be displayed via page navigation.
  function getNavigationCount();
  function getViewGroup();
}
?>