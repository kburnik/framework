<?

interface IQueriedDataTransformer {
  public function toCell();
  public function toRow();
  public function toVector();
  public function toArray();
  public function toArrayMap();
  public function toArrayGroup($field = null, $remove_grouped_field = false);
  public function toPairMap();
}

?>