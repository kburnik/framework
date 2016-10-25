<?php


class SQLFilter implements IQueryFilter {

  // object part

  private $fields = "*";
  private $where = null;
  private $order = null;
  private $limit = null;

  public static function Create() {
    return new SQLFilter();
  }

  public static function Merge($a,$b) {
    $a->mergeWith($b);
    return $a;
  }

  //
  function __construct($filterArray = array()) {
    if (is_array($filterArray)) {
      foreach ($filterArray as $construct => $value) {
        $this->$construct = $value;
      }
    }
  }


  function mergeWith($mixed = array()) {

    if ($mixed instanceof SQLFilter) {
      $filterArray = $mixed->toArray();
    } else {
      $filterArray = $mixed;
    }

    if (is_array($filterArray)) {
      foreach ($filterArray as $var => $value) {
        $this->$var = $value;
      }
      return true;
    } else {
      return false;
    }


  }

  function setWhere($where) {
    if (is_array($where)) {
      $this->where = produce('$[ AND ]{`[#]` = "[*:mysql_real_escape_string]"}',$where);
    } else {
      $this->where = $where;
    }
    return $this;
  }

  function prependWhere($where) {
    if (is_array($where)) $where = "(".implode(") and (",$where).")";

    if (strlen($this->where) > 0 ) {
      $this->where = "({$where}) and ({$this->where})";
    } else {
      $this->where = "{$where}";
    }
  }


  function appendWhere($where) {
    if (is_array($where)) $where = "(".implode(") and (",$where).")";

    if (strlen($this->where) > 0 ) {
      $this->where = "({$this->where}) and ({$where})";
    } else {
      $this->where = "{$where}";
    }
  }

  function getWhere() {
    return $this->where;
  }

  function setFields($fields) {
    if (is_array($fields)) {
      $this->fields = "`".implode("`, `", $fields)."`";
    } else {
      $this->fields = $fields;
    }
    return $this;
  }

  function getFields() {
    return $this->fields;
  }

  function setOrder($order) {
    if (is_array($order)) {
      $this->order = produce('$[ , ]{`[#]` [*]}',$order);
    } else {
      $this->order = $order;
    }
    return $this;
  }

  function getOrder() {
    return $this->order;
  }

  function setGroup($group) {
    if (is_array($group)) {
      $this->group = produce('$[ , ]{`[*]`}',$group);
    } else {
      $this->group = $group;
    }
    return $this;
  }

  function getGroup() {
    return $this->group;
  }


  function setLimit($limit) {
    $this->limit = $limit;
    return $this;
  }

  function getLimit() {
    return $this->limit;
  }


  function toString() {
    $out = "";
    if (!empty($this->where)) $out .= "\n where ".$this->where;
    if (!empty($this->group)) $out .= "\n group by ".$this->group;
    if (!empty($this->order)) $out .= "\n order by ".$this->order;
    if (!empty($this->limit)) $out .= "\n limit ".$this->limit;

    return $out;
  }


  function __toString() {
    return $this->toString();
  }


  function toArray() {
    $out = array();
    foreach ($this as $var => $val) {
      if (!empty($val)) $out[$var] = $val;
    }
    return $out;
  }
}

