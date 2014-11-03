<?

class ArticleModelEventHandler implements IArticleModelEventHandler
{

  public function onDiscount( $eventResponseObject )
  {
    $eventResponseObject->__eventRaised = true;
  }

}

?>