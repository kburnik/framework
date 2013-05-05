<?
include_once(dirname(__FILE__).'/../base/Base.php');

class TemplatedPaginatorViewGroup extends BasePaginatorViewGroup {
	protected $tplRange,$tplPageNumberLink,$tplCurrentPageNumber,$tplNextPage,$tplPrevPage,$tplSeparator;

	function __construct(
		$tplRange = TPL_STD_TABLE
		, $tplPageNumberLink = ' <a href="[pageURL]" /> [pageNumber] </a> &nbsp; '
		, $tplCurrentPageNumber = ' <strong> [pageNumber] </strong> &nbsp;  '
		, $tplPrevPage = '<a href="[pageURL]" /> &lt; </a> &nbsp;  '
		, $tplNextPage = '<a href="[pageURL]" /> &gt; </a> &nbsp;  '
		, $tplSeparator = ' ... '
	) {
		$this->tplRange = $tplRange;
		$this->tplPageNumberLink = $tplPageNumberLink;
		$this->tplCurrentPageNumber = $tplCurrentPageNumber;
		$this->tplPrevPage = $tplPrevPage;
		$this->tplNextPage = $tplNextPage;
		$this->tplSeparator = $tplSeparator;
	}
	
	function getRangeView($start,$limit,$data) {
		return produce($this->tplRange,$data);
	}

	function getPageNumberLinkView( $pageNumber , $pageURL ) {
		return produce($this->tplPageNumberLink,array("pageURL"=>$pageURL, "pageNumber" => $pageNumber));
	}
	
	function getCurrentPageNumberView( $pageNumber , $pageURL ) {
		return produce($this->tplCurrentPageNumber,array("pageURL"=>$pageURL, "pageNumber" => $pageNumber));
	}
	
	function getPreviousPageView( $pageNumber , $pageURL ) {
		return produce($this->tplPrevPage,array("pageURL"=>$pageURL, "pageNumber" => $pageNumber));
	}
	
	function getNextPageView( $pageNumber , $pageURL ) {
		return produce($this->tplNextPage,array("pageURL"=>$pageURL, "pageNumber" => $pageNumber));
	}
	
	function getPageNumberLinkSeparatorView() {
		return $this->tplSeparator;
	}
	
}
?>