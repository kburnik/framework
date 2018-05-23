<?php

class Pagination {
  protected
    $itemStart, $itemLimit, $itemCount, $itemNumber, $pageCount, $pageNumber,
    $pagePointerRange = 7 // default
    ;

  public static
    $ZOOM_VIEW = 0, // view only a few pages in range
    $LOG_VIEW = 1; // view logarithmic

  public static $LIMIT_SESSION_VAR = '__FW_PAGININATION_LIMIT__';
  public static $LIMIT_GET_VAR = 'limit';
  public static $CURRENT_LIMIT = 10;
  public static $DEFAULT_LIMITS = array(10, 25, 50, 100);

  // set number of items per page
  public function setItemLimit( $itemLimit ) {
    $this->itemLimit = $itemLimit;

    return $this;
  }

  // Initializes default limits of items per page and sets current limit.
  public static function DefaultInit($limits) {
    self::$DEFAULT_LIMITS = $limits;
    $min_limit = min($limits);
    $max_limit = max($limits);

    $_SESSION = (array)$_SESSION;

    if (array_key_exists(self::$LIMIT_GET_VAR, $_GET)) {
      $limit = $_GET[self::$LIMIT_GET_VAR];
    } else if (array_key_exists(self::$LIMIT_SESSION_VAR, $_SESSION)) {
      $limit = $_SESSION[self::$LIMIT_SESSION_VAR];
    } else {
      $limit = $min_limit;
    }
    $limit = max($min_limit, $limit);
    $limit = min($max_limit, $limit);

    $_SESSION[self::$LIMIT_SESSION_VAR] = $limit;
    self::$CURRENT_LIMIT = $limit;
  }

  public static function GetQueryString() {
    $params = $_GET;
    $params[self::$LIMIT_GET_VAR] = self::$CURRENT_LIMIT;
    return "?" . http_build_query($params);
  }

  public function getItemLimit() {
    return $this->itemLimit;
  }

  public function getItemNumber() {
    return $this->itemNumber;
  }

  // set total number of items
  public function setItemCount( $itemCount )
  {
    $this->itemCount = $itemCount;



    return $this;
  }

  public function getItemCount()
  {
    return $this->itemCount;
  }


  // set the start --> also changes the pageNumber
  public function setItemStart( $itemStart )
  {
    $itemStart = min( $itemStart, $this->itemCount );
    $itemStart = max( 0 , $itemStart  );
    $this->itemStart = $itemStart;
    $this->itemNumber = min( $this->itemCount - $this->itemStart + 1 , $this->itemLimit );
  }


  public function getItemRange( $offset = 0 )
  {
    return array(  $offset + $this->itemStart ,  min ( $offset + $this->itemStart + $this->itemNumber - 1 , $this->itemCount) );
  }

  // set the start --> also changes the pageNumber
  public function getItemStart( )
  {
    return $this->itemStart;
  }


  // set the current page number
  public function setPageNumber( $pageNumber )
  {
    $pageNumber = max( 1 , $pageNumber );
    $pageNumber = min( $pageNumber , $this->getPageCount() );

    $this->setItemStart( ( $pageNumber - 1 ) * $this->itemLimit );

    return $this;
  }

  public function getItemSlice()
  {

    return array( $this->itemStart , $this->itemLimit , $this->itemNumber );

  }

  public function getPageNumber()
  {
    return 1 + floor( $this->itemStart / $this->itemLimit );
  }

  public function getPageCount()
  {
    return ceil( $this->itemCount / $this->itemLimit );
  }


  public function setPagePointerRange( $pagePointerRange )
  {

    $this->pagePointerRange = $pagePointerRange;

    return $this;
  }


  public function getPagePointers()
  {

    $pageCount = $this->getPageCount();
    $currentPageNumber = $this->getPageNumber();
    $navCount = max( 5, $this->pagePointerRange );
    $navCount += $navCount % 2;
    $out = "";


    // parts
    // [previous page] [first page] [...] [left part] (current page) [right part] [...] [end page] [next page]


    $navCount--;

    // $out_prev
    $previous = array();
    if ($currentPageNumber > 1)
    {
      $previous = array( $currentPageNumber - 1 );
    }

    // $out_next
    $next = array();
    if ($currentPageNumber < $pageCount)
    {
      $next = array( $currentPageNumber + 1 );
    }


    // out_current
    $current = array();
    if ($this->itemCount > $this->itemLimit)
    {
      $current =  array($currentPageNumber);
    }

    // $out_first
    $first = array();
    if ($currentPageNumber != 1)
    {
      $first = array(1);
      $navCount--;
    }

    // $out_last
    $last = array();
    if ($currentPageNumber != $pageCount)
    {
      $last = array( $pageCount );
      $navCount--;
    }

    // left and right part
    $left = array();
    $right = array();
    $c = 0;


    for ($j = 1; $j <= $navCount && $c < $navCount;   ) {
      $c++;
      $i = $currentPageNumber - $c;
      if ($i >= 2 && $i < $pageCount )
      {

        if ($i > $currentPageNumber) {
          $left[] = $i;
        } else {
          array_unshift( $left , $i );
          $firstLeftPageNumber = $i;
        }
        $j++;
      }

      $i = $currentPageNumber + $c;
      if ($i >= 2 && $i < $pageCount )
      {
        $right[] = $i;
        $lastRightPageNumber = max($i,$lastRightPageNumber);
        $j++;
      }

    }

    // $out_lsep;
    $lsep = false;
    if ($firstLeftPageNumber > 2) {
      $lsep = true;
    }

    // $out_rsep
    $rsep = false;
    if ($lastRightPageNumber > $currentPageNumber && $lastRightPageNumber + 1 < $pageCount) {
      $rsep = true;
    }

    return array(
      'previous' => $previous,
      'first' => $first,
      'lsep' => $lsep,
      'left' => $left,
      'current' => $current,
      'right' => $right,
      'rsep' => $rsep,
      'last' => $last,
      'next' => $next
    );
  }
}

