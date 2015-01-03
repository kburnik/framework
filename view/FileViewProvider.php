<?

class FileViewProvider extends ViewProvider
{

  private $map = array();

  function __construct( $map = array() )
  {
    $this->map = $map;
  }

  function getTemplate( $viewKey )
  {
    $filename = $this->map[ $viewKey ];

    // fallback to default view location
    if ( !file_exists ( $filename ) )
    {
      $filename = view( $filename );
    }

    if ( ! file_exists( $filename ) )
    {
      throw new Exception("Missing view file: $filename");
    }

    if ($template = file_get_contents( $filename ))
    {
      return $template;
    }
    else
    {
      throw new Exception("Cannot open view file: $filename");
    }

  }

  function containsTemplate( $viewKey )
  {
    return array_key_exists( $viewKey , $this->map );
  }


}

?>