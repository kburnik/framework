<?
include_once(dirname(__FILE__).'/../base/Base.php');

class BasePaginatorViewGroup extends PaginatorViewGroup {
  
  function getRangeHeaderView($start,$limit) {
    return "";
  }
  
  function getRangeFooterView($start,$limit) {
    return "";
  }
  
  function getItemView($item) {
    return implode($item);
  }
  
  function getPageNumberLinkView( $pageNumber , $pageURL ) {
    return '[ <a href="' . $pageURL . '" /> page ' . $pageNumber . "</a> ] ";
  }
  
  function getCurrentPageNumberView( $pageNumber , $pageURL ) {
    return '[ <strong> page ' . $pageNumber . "</strong> ] ";
  }
  
  function getNextPageView( $nextPageNumber , $pageURL ) {
    return '[ <a href="' . $pageURL . '" /> next </a> ] ';
  }
  
  function getPreviousPageView( $prevPageNumber , $pageURL ) {
    return '[ <a href="' . $pageURL . '" /> previous </a> ] ';
  }
  
  function getPageNumberLinkSeparatorView() {
    return " ... ";
  }
}

?>