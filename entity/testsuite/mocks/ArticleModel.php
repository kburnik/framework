<?


class ArticleModel extends EntityModel 
{
	
	
	public function getArticlesWithIDInRange($lo,$hi) 
	{
		
		return $this->getDataDriver()->getArticlesWithIDInRange( $lo , $hi );
	
	
	}
	

}



?>