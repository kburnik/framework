<?

class Article extends Entity {

  public
    $id,
    $title,
    $created,
    $modified,
    $id_category
    ;


  private $mockCategoryModel = array();


  public function getCategory()
  {
    return $this->mockCategoryModel[ $this->id_category ];
  }


  public function setCategory( $category )
  {
    $this->mockCategoryModel[ $category->id ] = $category;
    $this->id_category = $category->id;
  }



}

?>