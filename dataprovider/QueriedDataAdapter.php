<?php


abstract class QueriedDataAdapter implements IDataAdapter {

  private $queriedDataProvider;
  private $data;

  abstract function getView($position,$id);

  function load($queriedDataProvider,$query) {
    $this->queriedDataProvider = $queriedDataProvider;
    $this->data = $this->queriedDataProvider->execute($query)->toArray();
  }

  function getCount() {
    return count($this->data);
  }

  function getItem($position) {
    return $data[$this->position];
  }

  function getItemID($position) {
    return reset($this->data[$position]);
  }


}

