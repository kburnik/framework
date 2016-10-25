<?php

class MySQLTablePaginator extends QueryPaginator {
  function __construct($table, $filter = null,$limit = 30,$viewGroup = null,$queryDataProvider = null,$autosetPageNumber = true) {

    if (! ($filter instanceof SQLFilter ) ) $filter  = new SQLFilter();
        parent::__construct(
      "select ". $filter->getFields() ." from `{$table}` " . $filter->toString(true,true,true,false),
      "select count(*) c from `{$table}` " . $filter->toString(true,true,false,false),
      $limit,$viewGroup,$queryDataProvider,$autosetPageNumber
    );
  }
}

