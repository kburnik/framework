<?
include_once(dirname(__FILE__).'/../base/Base.php');


interface IPaginatorViewGroup {

	function getRangeView( $start, $limit, $data);
	
	function getRangeHeaderView( $start, $limit );
	
	function getRangeFooterView( $start ,$limit );

	function getItemView( $item );
	
	function getPageNumberLinkView( $pageNumber , $pageURL );
	
	function getCurrentPageNumberView( $pageNumber , $pageURL );
	
	function getNextPageView( $nextPageNumber , $pageURL );
	
	function getPreviousPageView( $prevPageNumber , $pageURL );
	
	function getPageNumberLinkSeparatorView();
	
}
?>