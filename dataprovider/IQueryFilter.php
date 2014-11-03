<?
include_once(dirname(__FILE__)."/../base/Base.php");

interface IQueryFilter {


  public static function Create();

  public static function Merge( $filterA, $filterB );

  public function mergeWith( $mixed = array() );

  // where condition
  public function setWhere( $where );
  public function appendWhere( $where );
  public function getWhere();


  // fields being selected
  public function setFields( $fields );
  public function getFields();

  // order by
  public function setOrder( $order );
  public function getOrder();


  // group by
  public function setGroup( $group );
  public function getGroup();


  // limit
  public function setLimit( $limit );
  public function getLimit();

  // create query part from string
  function toString();

  // return array representation of the filter
  function toArray();

}

?>