<?php

interface IDataAdapter {
  function getCount();
  function getItem($position);
  function getItemID($position);
  function getView($position, $id);
}

