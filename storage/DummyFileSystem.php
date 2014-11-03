<?

class DummyFileSystem extends EchoFileSystem
{

  public $files = array();

  protected static function __echo()
  {
    parent::__echo( 2 );
  }

  public function glob( $pattern, $flags = 0 )
  {
    self::__echo(); return glob( $pattern , $flags );
  }

  public function is_dir( $filename )
  {
    self::__echo();
    return is_array( $this->files[ $filename ] );
  }

  public function mkdir( $pathname , $mode = 0777 , $recursive = false , $resource = null )
  {
    self::__echo();
    $this->files[ $pathname ] = func_get_args();
    return true;
  }


  public function file_exists( $filename )
  {
    self::__echo();
    return array_key_exists( $filename , $this->files );
  }

  public function copy( $source , $dest , $context = null  )
  {
    self::__echo();

    $this->files[ $dest ] = file_get_contents( $source );

  }

  public function file_get_contents($filename, $flags=0, $context=null, $offset = -1, $maxlen = -1 )
  {
    self::__echo();

    if ( array_key_exists( $filename , $this->files ) )
      return $this->files[ $filename ];
    else
      throw new Exception("File does not exist");

  }

  public function file_put_contents($file, $data, $flags = 0, $context = null)
  {

    $this->files[ $file ] = $data;
    self::__echo();
  }

  public function getcwd()
  {
    self::__echo();
    return getcwd();
  }

}

?>