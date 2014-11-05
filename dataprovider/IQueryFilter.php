<?

interface IQueryFilter {
  public static function Create();
  public static function Merge($filterA, $filterB);
  public function mergeWith($mixed = array());
  public function setWhere($where);
  public function appendWhere($where);
  public function getWhere();
  public function setFields($fields);
  public function getFields();
  public function setOrder($order);
  public function getOrder();
  public function setGroup($group);
  public function getGroup();
  public function setLimit($limit);
  public function getLimit();
  function toString();
  function toArray();
}

?>