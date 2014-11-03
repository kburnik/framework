<?

abstract class ApiInterface
{

  protected $url;

  public function __construct( $url )
  {
    $this->url = $url;
  }

  public function getUrl()
  {
    return $this->url;
  }

  public function setUrl( $url )
  {
     $this->url = $url;
    return $this;
  }

  protected static function standIn()
  {
    throw new Exception("Not to be used outside of Api");
  }

}


?>