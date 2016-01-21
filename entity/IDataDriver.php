<?php

interface IDataDriver {
  public function update($entityType, $entityArray, $fieldsOnly = array());
  public function insert($entityType, $entityArray);
  public function insertupdate($entityType, $entityArray);
  public function delete($entityType, $entityArray);
  public function deleteBy($sourceObjectName, $filterArray);
  public function count($entityType);
  // chain
  public function find($entityType, $filter);
  // chain
  public function select($entityType, $fields);
  // chain
  public function orderBy($comparisonMixed);
  // chain
  public function limit($start, $limit);
  // chain
  public function join($sourceObjectName,
                       $refDataDriver,
                       $refObjectName,
                       $resultingFieldName,
                       $joinBy,
                       $fields = null);
  // Release the chain : return the result of the lasy operation.
  public function ret();
  // Counts affected entries
  public function affected($sourceObjectName);
  // Return the entity field used for constructing the underlying data structure
  // (e.g. mysql table).
  public function getEntityField();

  // Truncate all entries.
  public function truncate($entityType);
}
