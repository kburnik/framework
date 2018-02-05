<?php

class JsonSearchFilter {
  private $filter;

  public static function maybeCreate($filter) {
    $filter = json_decode($filter);
    if (json_last_error() != JSON_ERROR_NONE) {
      return null;
    } else {
      return new JsonSearchFilter($filter);
    }
  }

  public function __construct($filter) {
    $this->filter = $filter;
  }

  public function serializeTo($queryFilter, $fieldPrefix) {
    $serialized = $this->serialize($this->filter, $fieldPrefix);
    header('X-JSON-FILTER: ' . $serialized);
    $queryFilter->appendWhere($serialized);
  }

  private function serialize(
      $filter, $fieldPrefix='', $glue=" or ", $prefix='') {
    if ($filter === null) {
      return 'NULL';
    } else if (is_numeric($filter)) {
      return $prefix . mysql_real_escape_string($filter);
    } else if (is_string($filter)) {
      return $prefix . "\"" . mysql_real_escape_string($filter) . "\"";
    }

    $out = array();

    if (!self::isAssoc($filter)) {
      foreach ($filter as $term) {
        $out[] = $this->serialize($term, $fieldPrefix);
      }
      return implode($glue, $out);
    } else if (self::isOperator($filter)) {
      foreach ($filter as $op => $args) {
        return $this->buildOp($op, $args);
      }
    } else {
      // TODO: check for valid/existing fields.
      foreach ($filter as $k => $v) {
        $var = mysql_real_escape_string($k);
        $fieldName = "{$fieldPrefix}`{$var}`";
        $value = $this->serialize($v, $fieldPrefix, '', ' = ');
        $out[] =  "{$fieldName} {$value}";
      }
      return implode(" and ", $out);
    }
  }

  private function isAssoc($arr) {
    if (array() === $arr) return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
  }

  private function isOperator($arr) {
    if (count($arr) != 1) {
      return false;
    }
    foreach ($arr as $k => $v) {
      if ($k[0] == '$') {
        return true;
      }
    }
    return false;
  }

  private function buildOp($op, $args) {
    if ($op == '$in') {
      if (count($args) == 0) {
        return "in (SELECT * FROM (SELECT 1) AS _EMPTY_SET_ WHERE 2=3)";
      } else {
        return "in (" . $this->serialize($args, '', ', ') .  ")";
      }
    } else if ($op == '$lt') {
      return $this->serialize($args, '', '', ' < ');
    } else if  ($op == '$gt') {
      return $this->serialize($args, '', '', ' > ');
    } else if ($op == '$le') {
      return $this->serialize($args, '', '', ' <= ');
    } else if  ($op == '$ge') {
      return $this->serialize($args, '', '', ' >= ');
    } else if ($op == '$between') {
      $first = $this->serialize($args[0]);
      $second = $this->serialize($args[1]);
      return "between {$first} and {$second}";
    } else if ($op == '$like') {
      return "like " . $this->serialize($args);
    } else if ($op == '$unlike' || $op == '$notlike' || $op == '$not-like') {
      return "not like " . $this->serialize($args);
    }

    throw new Exception("Invalid operator: $op");
  }
}