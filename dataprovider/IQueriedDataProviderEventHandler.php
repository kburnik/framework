<?php

interface IQueriedDataProviderEventHandler {
  public function onConnect($host, $username, $password, $database);
  public function onDisconnect();
  public function onExecuteStart($query);
  public function onExecuteComplete($query, $result);
  public function onInsert($table, $data);
  public function onUpdate($table, $data, $filter);
  public function onInsertUpdate($table, $data);
  public function onDelete($table, $filter);
  public function onDrop($tables);
  public function onTruncate($tables);
  public function onRepair($tables);
  public function onOptimize($tables);
  public function onError($query, $error, $errno);
}

