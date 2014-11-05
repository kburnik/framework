<?


interface IQueriedDataTransformer {

  // get single cell from result
  public function toCell();
  
  // get first row of result
  public function toRow();

  // get vector of the result (first cells of each row)
  public function toVector();
  
  // get the array of the result
  public function toArray();
  
  // get the array with keys matching the primary key
  public function toArrayMap();
    
  // get the array group by fields
  public function toArrayGroup($field=null,$remove_grouped_field = false);
  
  // get an associative array with first field as key and second as value
  public function toPairMap();
  
  
  
}

?>