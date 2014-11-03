<?
include_once(dirname(__FILE__)."/../base/Base.php");

interface IStorage extends ArrayAccess, IteratorAggregate, Countable, Serializable {

  function read($variable);
  
  function write($variable,$value);
  
  function exists($variable);
  
  function clear($variable);
  
}

?>