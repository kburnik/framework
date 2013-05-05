<?

class _filesystem {
	var $root='';
	
	var $directory_list=array();
	
	function __construct($root=':default:') {
		if ($root==':default:') $root=dirname(__FILE__);
		
		// adjust root
		$root=trim($root);
		if (!in_array(substr($root,-1),array('/','\\'))) $root.="/";
		$this->root=$root;
		
		
		$d = dir($this->root);
		while (false !== ($entry = $d->read())) {
			$this->directory_list[]=$entry;
		}
	}
	
	function is_matching($filename,$filters) {
		$is_matching=false;
		$filters = explode(",",$filters);
		
		if (count($filters)==0) return false;
		
		foreach ($filters as $filter) {
			
			$replaces=array(
				"." => "[\\.]",
				"*" => "(.*)"
			);
			
			foreach ($replaces as $match=>$replace) {
				$filter=str_replace($match,$replace,$filter);
			}
			$filter="/$filter/";
			
			$match = preg_match($filter,$filename);
			
			if ($match) {
				$is_matching = true;
				break;
			}
		
		}
		
		return $is_matching;
	}
	
	function is_relativedir($dirname) {
		return in_array($dirname,array(".",".."));
	}
	
	function getfiles($filters='',$fullpath=false) {
		$dir = $this->directory_list;
		$out=array();
		foreach ($dir as $item ) {
			if (is_file( $this->root . $item )) {
				if ($this->is_matching($item,$filters)) {
					if ($fullpath) $item = $this->root . $item;
					$out[]=$item;
				}
			}
		}
		return $out;
	}
	
	function getdirs($filters='',$fullpath=false,$use_relative_dirs=false) {
		$dir = $this->directory_list;
		$out=array();
		foreach ($dir as $item ) {
			if (
				is_dir( $this->root . $item ) && 
				$this->is_matching($item,$filters) &&
				( $use_relative_dirs >= $this->is_relativedir($item) ) // NOTE: using >= with logical operators
			) {
				if ($fullpath) $item = $this->root . $item;
				$item = str_replace("\\","/",$item);
				$out[]=$item;
				
			}
		}
		return $out;
	}
	
	function gettree() {
	
	}
	
	function write($filename,$data) {}
	
	function append($filename,$data) {}
	
	function prepend($filename,$data) {}
	
	
}

class _dir extends _filesystem {}


function filesys($root=__ROOT__) {
	return new _filesystem($root);
}
?>