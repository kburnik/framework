<?

class ArticleModel extends EntityModel
{


  public function getArticlesWithIDInRange($lo,$hi)
  {

    $data = $this->dataDriver->getArticlesWithIDInRange( $lo , $hi );

    return $this->toObjectArray( $data );

  }


  public function setDiscount50Percent( $responseObject )
  {

    $this->onDiscount( $responseObject );

  }


}








?>