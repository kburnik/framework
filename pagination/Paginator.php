<?
include_once(dirname(__FILE__).'/../base/Base.php');

abstract class Paginator implements IPaginator {

  // private $count;
  protected $start,$limit;
  protected $viewGroup;
  protected $data;


  // hook method
  function initialize() {

  }

  private $__count = null;
  private function count() {
    if ($this->__count === null) {
      $this->data = $this->getData($this->start,$this->limit);
      $this->__count = max(1,$this->getCount());
    }
    return $this->__count;
  }

  function __construct($limit = 30, $autoSetPageNumber = false) {
    $this->setRange(0,$limit);

    $this->initialize();


    if ($autoSetPageNumber) {
      $this->setPageNumber(intval($_GET['page']));
    }

    $this->viewGroup = $this->getViewGroup();

  }


  function setPageNumber($page) {

    // grab data (so counting can be handled later)

    // see if current page is over pagecount



    $this->start = ($page - 1) * $this->limit;

    if ($this->start < 0 || $this->start >= $this->count() ) $this->start = 0;

    $pageCount = $this->getPageCount();
    if ($pageCount > 0 && $page > $pageCount) {
      // reset counter
      $page = $pageCount;
      $this->start = ($page - 1) * $this->limit;
      $this->data = $this->getData($this->start,$this->limit);
    }


  }

  private function setRange($start,$limit) {
    $this->start = $start;
    $this->limit = $limit;
  }

  public function getPageCount() {
    return ceil( $this->count() / $this->limit );
  }

  public function getCurrentPageNumber() {
    return floor(   ($this->start / $this->count()) * $this->getPageCount() ) + 1;
  }

  public function getNavigationCount() {
    return 7;
  }

  public  function getNavigationView() {
    $pageCount = $this->getPageCount();
    $currentPageNumber = $this->getCurrentPageNumber();
    $navCount = max( 5, $this->getNavigationCount() );
    $navCount += $navCount % 2;
    $out = "";


    // parts
    // [previous page] [first page] [...] [left part] (current page) [right part] [...] [end page] [next page]


    $navCount--;

    // $out_prev
    if ($currentPageNumber > 1) {
      $out_prev = $this->viewGroup->getPreviousPageView( $currentPageNumber - 1 , $this->getPageURL( $currentPageNumber - 1 ) );
    }

    // $out_next
    if ($currentPageNumber < $pageCount) {
      $out_next = $this->viewGroup->getNextPageView( $currentPageNumber + 1 , $this->getPageURL( $currentPageNumber + 1 ) );
    }


    // out_current
    if ($this->count() > $this->limit) {
      $out_current =  $this->viewGroup->getCurrentPageNumberView( $currentPageNumber , $this->getPageURL($currentPageNumber) );
    }

    // $out_first
    if ($currentPageNumber != 1) {
      $out_first = $this->viewGroup->getPageNumberLinkView(1,$this->getPageURL(1));
      $navCount--;
    }

    // $out_last
    if ($currentPageNumber != $pageCount) {
      $out_last = $this->viewGroup->getPageNumberLinkView($pageCount,$this->getPageURL($pageCount));
      $navCount--;
    }

    // left and right part
    $out_left = "";
    $out_right = "";
    $c = 0;


    for ($j = 1; $j <= $navCount && $c < $navCount;   ) {
      $c++;
      $i = $currentPageNumber - $c;
      if ($i >= 2 && $i < $pageCount ) {
        $pageURL = $this->getPageURL($i);
        if ($i > $currentPageNumber) {
          $out_left .= $this->viewGroup->getPageNumberLinkView($i,$pageURL);
        } else {
          $out_left = $this->viewGroup->getPageNumberLinkView($i,$pageURL) . $out_left;
          $firstLeftPageNumber = $i;
        }
        $j++;
      }

      $i = $currentPageNumber + $c;
      if ($i >= 2 && $i < $pageCount ) {
        $pageURL = $this->getPageURL($i);
        $out_right .= $this->viewGroup->getPageNumberLinkView($i,$pageURL);
        $lastRightPageNumber = max($i,$lastRightPageNumber);
        $j++;
      }

    }

    // $out_lsep;
    if ($firstLeftPageNumber > 2) {
      $out_lsep = $this->viewGroup->getPageNumberLinkSeparatorView();
    }

    // $out_rsep
    if ($lastRightPageNumber > $currentPageNumber && $lastRightPageNumber + 1 < $pageCount) {
      $out_rsep = $this->viewGroup->getPageNumberLinkSeparatorView();
    }

    return $this->viewGroup->getNavigationTemplateView(
          array(
            "previous"       =>  $out_prev,
            "first"       =>  $out_first,
            "left_separator"   =>  $out_lsep,
            "left"         =>  $out_left,
            "current"       =>  $out_current,
            "right"       =>  $out_right,
            "right_separator"   =>  $out_rsep,
            "last"         =>  $out_last,
            "next"         =>  $out_next
          )
      );
  }


  public function getPageURL($page) {
    $p = parse_url($_SERVER['REQUEST_URI']);
    parse_str($p['query'],$query);
    $query['page'] =  $page;
    return $p['path']."?".http_build_query($query);
  }

  public function getStart() {
    return $this->start + 1;
  }

  public function getLimit() {
    return min($this->limit,$this->getCount());
  }

  public function getEnd() {
    return min($this->getStart() + $this->getLimit() - 1,$this->getCount());
  }

  public function getCurrentPageView() {
    return $this->viewGroup->getRangeView($this->start,$this->limit,$this->data);
  }
}
?>