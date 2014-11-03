<?
include_once(dirname(__FILE__).'/../base/Base.php');

class CSVFormater implements IOutputFormater {

  private $separator ;
  
  function __construct($separator = ',') {
    $this->separator = $separator;      
  }
  
  function Initialize() {
  
  }
  
  function Format($data) {
    return implode($this->separator,$data);
  }
}


?>