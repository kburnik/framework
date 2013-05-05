<?



abstract class Model extends BaseSingleton {

	protected $qdp;
		
	
	function __construct($queryDataProvider = null) {
		
		$useLog = !defined('SKIP_MODEL_LOGGING');
		
		if ($useLog)
			Console::WriteLine('Model :: Constructing abstract class Model for ' . get_class($this) );
		
		
		$this->qdp = ($queryDataProvider == null) ? Project::GetQDP() : $queryDataProvider ;
		if ($useLog)
			Console::WriteLine('Model :: Setting model\'s data provider ' . get_class($this->qdp) );
		
		$resources = $this->getResources();
		if ($useLog)
			Console::WriteLine('Model :: including resources ' . var_export($resources,true));
		Project::getCurrent()->includeResources($resources);
		
		if ($useLog)
			Console::WriteLine('Model :: Binding model\'s auto event handlers for ' . get_class($this) );
			
		Project::getCurrent()->bindProjectAutoEventHandlers( $this );		
		
	}
	
	protected function query($q) {
		return $this->qdp->execute($q);
		
	}
	
	// get js,css and other resources (as mapped arrays) used for this model
	abstract function getResources();
	
	// get an url for base model entity
	abstract function getURL($item);
	
}

// todo : place in seperate file
class CachedResource {
	
	private $name;
	private $key;
	private $keyDate;
	private $getDataMethod;
	private $getDataArguments;
	private $storage;
	private $dir;
	private $duration;
	
	
	public function __construct($name,$key,$getDataMethod,$getDataArguments, $duration = 120) {
		$this->name = $name;
		$this->key = md5(json_encode($key));
		$this->keyDate = $this->key.".Date";
		$this->getDataMethod = $getDataMethod;
		$this->getDataArguments = $getDataArguments;
		$this->duration = $duration;
		
		$this->dir = Project::GetProjectDir("/gen/cache/{$this->name}/");
		
		if (!file_exists($this->dir)) {
			mkdir($this->dir,0755,true);		
		}
		$this->storage = new CrossFileStorage($this->dir."/{$this->name}");
	
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
	
	public static function Touch($name) {
		$dir = Project::GetProjectDir("/gen/cache/{$name}");
		return self::deleteDirectory($dir);
	}
	
	public function read() {
		$date = $this->storage->read( $this->keyDate ) ;
		if ($date != null) $expired = ( strtotime( now() ) - strtotime( $date ) ) > $this->duration  ;
		
		if ( $date == null || $expired || !defined('CACHE_ENABLED') ) {
			Console::WriteLine('Resource expired');
			
			$result = call_user_func_array($this->getDataMethod,$this->getDataArguments);
			$this->storage->write( $this->key, $result );
			$this->storage->write( $this->keyDate, now() );
		
		}
		return $this->storage->read( $this->key );
	}
	
	
}


/*
testing the cached resources

include_once('../base/Base.php');
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