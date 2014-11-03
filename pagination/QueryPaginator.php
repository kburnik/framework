<?
include_once(dirname(__FILE__).'/../base/Base.php');

class QueryPaginator extends Paginator {

  protected $query,$countQuery,$viewGroup;  
  
  function __construct($query,$countQuery,$limit = 30,$viewGroup = null,$queryDataProvider = null,$autosetPageNumber = true) {
    $this->template = $template;
    $this->query = $query;
    $this->countQuery = $countQuery;
    $this->viewGroup = ($viewGroup instanceof IPaginatorViewGroup) ? $viewGroup : new TemplatedPaginatorViewGroup( TPL_STD_TABLE );
    $this->queryDataProvider = ($queryDataProvider instanceof IQueryDataProvider) ? $queryDataProvider : Project::GetQDP();
    
    parent::__construct($limit,$autosetPageNumber);
  }
  
  function getViewGroup() {
    return $this->viewGroup;
  }
  
  function getCount() {
    return $this->queryDataProvider->execute($this->countQuery)->toCell();
  }
  
  function getData($start = 0,$limit = 10) {
    return $this->queryDataProvider->execute("{$this->query} limit {$start},{$limit};")->toArray();      
  }  
}



?>