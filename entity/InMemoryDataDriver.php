<?

class InMemoryDataDriver implements IDataDriver {
  protected $data = array();
  protected $resultSet = array();
  protected $exception;
  private $comparison;

  public function find($sourceObjectName, $filterArray) {

    // using an in memory data filter
    $filter = InMemoryDataFilter::Resolve($filterArray);

    $this->resultSet = array();

    foreach ($this->data as $row) {
      if ($filter->matches($row)) {
        $this->resultSet[] = $row;
      }
    }

    return $this;
  }

  public function findFullText($sourceObjectName, $query, $fields) {
    throw new Exception("Not implemented!");
  }

  public function internalCompare($a, $b) {
    foreach ($this->comparison as $field => $direction) {
      $t = $a;
      if (!array_key_exists($field, $t)) {
        throw new Exception("Nonexisting field '{$field}'");
      }

      $isEqual = false;
      $needsSwap = false;

      if ($a[$field] == $b[$field]){
        $isEqual = true;
      }
      else if ($direction < 0 || $direction == 'desc') {
        $needsSwap = $a[$field] < $b[$field];
      }
      else {
        $needsSwap = $a[$field] > $b[$field];
      }

      if ($isEqual)
        continue;

      return $needsSwap;
    }

    return false;
  }

  // chains
  public function orderBy($comparisonMixed) {
    $this->comparison = $comparisonMixed;

    try {
      @usort($this->resultSet, array($this, 'internalCompare'));
    }
    catch (Exception $ex) {
      $this->exception = $ex;
    }

    return $this;
  }

  public function select($sourceObjectName, $fields) {

    foreach ($this->resultSet as $i => $row) {
      $this->resultSet[$i] = array_pick($row, $fields);
    }

    return $this;
  }

  // chains
  public function limit($start, $limit) {
    $this->resultSet = array_slice($this->resultSet, $start, $limit, false);
    return $this;
  }

  // releases chain
  public function ret() {

    if (isset($this->exception)) {
      throw $this->exception;
    }

    $results = $this->resultSet;

    $this->resultSet = array();

    return $results;
  }

  public function insert($sourceObjectName, $entity) {
    if ($entity['id'] == null) {
      $maxid = 0;

      foreach ($this->data as $row) {
          if ($row['id'] > $maxid) {
            $maxid = $row['id'];
          }
      }

      $entity['id'] = $maxid + 1;
    }

    $this->data[] = $entity;
    return $entity['id'];
  }

  public function insertupdate($sourceObjectName, $entity) {
    $existing = $this->find($sourceObjectName,
        array("id" => $entity['id']))->affected() > 0;

    if (!$existing) {
      $this->insert($sourceObjectName, $entity);
      return 1;
    } else {
      return $this->update($sourceObjectName, $entity);
    }
  }

  public function count($sourceObjectName) {
    return count($this->data);
  }

  public function affected() {

    if (isset($this->exception)) {
      throw $this->exception;
    }

    if (!is_array($this->resultSet))
      return 0;

    return count($this->resultSet);

  }

  public function update($sourceObjectName, $entity) {
    foreach ($this->data as $i=>$row) {
      if ($row['id'] == $entity['id']) {
        if ($this->data[$i] != $entity) {
          $this->data[$i] = array_merge($this->data[$i], $entity);
          return 1;
        }
        break;
      }
    }

    return 0;
  }

  public function delete($sourceObjectName, $entity) {
    foreach ($this->data as $i => $row) {
      if ($row['id'] == $entity['id']) {
        unset($this->data[$i]);

        return 1;
      }
    }

    return 0;
  }

  public function deleteBy($sourceObjectName, $filterArray) {
    $filter = InMemoryDataFilter::Resolve($filterArray);

    $affected = 0;

    foreach ($this->data as $i => $row) {
      if ($filter->matches($row)) {
        unset($this->data[$i]);
        $affected++;
      }
    }

    return $affected ;
  }

  public function getEntityField() {
    return new InMemoryEntityField();
  }

  public function join($sourceObjectName,
                       $refDataDriver,
                       $refObjectName,
                       $resultingFieldName,
                       $joinBy,
                       $fields = null) {
    foreach ($joinBy as $sourceField => $referencingField);

    foreach ($this->resultSet as $i => $row) {
      $refDataDriver->find(
          $refObjectName, array($referencingField => $row[$sourceField]));

      if (is_array($fields) && count($fields) > 0)
        $refDataDriver->select($refObjectName, $fields) ;

      $res = $refDataDriver->ret();
      $this->resultSet[$i][$resultingFieldName] = reset($res);
    }

    return $this;
  }

  public function truncate($entityType) {
    $this->data = array();
    return true;
  }

}

?>