<?

// todo: write tests for the CachedResource class
class CachedResource {

  private $resourceName;
  private $resourceVariable;
  private $resourceVariableDate;
  private $getDataMethod;
  private $getDataArguments;
  private $storage;
  private $dir;
  private $duration;

  // default duration for each resource
  public static $defaultDuration = 120;
  public static $defaultVariable = "DEFAULT";

  public function __construct($resourceName , $resourceVariable , $getDataMethod , $getDataArguments, $duration = null) {
    $this->resourceName = $resourceName;
    $this->resourceVariable = md5(json_encode($resourceVariable));
    $this->resourceVariableDate = $this->resourceVariable.".Date";
    $this->getDataMethod = $getDataMethod;
    $this->getDataArguments = $getDataArguments;

    if ($duration === null) $duration = self::$defaultDuration;
    $this->duration = $duration;

    $this->dir = Project::GetProjectDir("/gen/cache/{$this->resourceName}/");

    if (!file_exists($this->dir)) {
      mkdir($this->dir,0755,true);
    }

    $this->storage = new CrossFileStorage($this->dir."/{$this->resourceName}");

  }

  private function deleteDirectory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir) || is_link($dir)) return unlink($dir);
      foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!self::deleteDirectory($dir . "/" . $item)) {
          chmod($dir . "/" . $item, 0777);
          if (!self::deleteDirectory($dir . "/" . $item)) return false;
        };
      }
      return rmdir($dir);
    }

  // Dissrupt the cache for a given resource
  public static function Touch( $resourceName ) {
    $dir = Project::GetProjectDir("/gen/cache/{$resourceName}");
    return self::deleteDirectory($dir);
  }


  // Read a variable from cache or source
  public function read() {
    $date = $this->storage->read( $this->resourceVariableDate ) ;
    if ($date != null) $expired = ( strtotime( now() ) - strtotime( $date ) ) > $this->duration  ;

    if ( $date == null || $expired || !defined('CACHE_ENABLED') ) {
      Console::WriteLine('Resource expired or CACHE_ENABLED not defined');

      $result = call_user_func_array($this->getDataMethod,$this->getDataArguments);
      $this->storage->write( $this->resourceVariable, $result );
      $this->storage->write( $this->resourceVariableDate, now() );

    }
    return $this->storage->read( $this->resourceVariable );
  }

}


/*
testing the cached resources


include_once('/home/zerinera/test_new_public_html/kolekcionar/project.php');

if ($argv[1] == 'touch') {
  $res = CachedResource::Touch('random');
  echo "touched : $res\n";
  return;
}

function getData() {
  return rand(0,100);
}

$cr[1] = new CachedResource("random","m1",'getData',array(),5);
$cr[2] = new CachedResource("random","m2",'getData',array(),10);
$cr[3] = new CachedResource("random","m3",'getData',array(),15);

echo $cr[1]->read(). "\n";
echo $cr[2]->read(). "\n";
echo $cr[3]->read(). "\n";

*/

?>