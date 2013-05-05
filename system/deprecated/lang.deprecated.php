<?
// include_once(dirname(__FILE__)."/lang.layer.php");

class _lang {
	var $default_language = "hr";
	var $supported = array();
	var $language = "hr"; // current language
	var $language_pack=array();
	var $languages = array(
		"hr"=>"Hrvatski",
		"en"=>"English",
		"de"=>"Deutsch",
		"it"=>"Italiano"
	);
	
	function __construct($lang=null) {
		if ($lang===null) $lang = __LANG__;
		$language_file = __ROOT__."/lang/".$lang.".php";
		
		// default language
		if (!file_exists($language_file)) {
			$language_file = __ROOT__."/lang/".$this->default_language.".php";
			$this->language = $this->default_language;
		} 
		
		// get supported languages
		$files = filesys(__ROOT__."/lang/")->getfiles("*.php");
		foreach($files as $i=>$file) {
			$lang = reset(explode(".",$file));
			if ($lang!=$this->default_language) $this->supported[] = $lang;
		}
		
		if (!file_exists($language_file)) { die("No language pack found!");}
		
		// rewrite the language pack
		
		$pack = $this->read_file($language_file);
		
		$this->language_pack = array_combine(
			explode(",",'<%'.implode("%>,<%",array_keys($pack)).'%>'),
			array_values($pack)
		);
		
	}
	
	function read_file($php_file) {
		return eval(strtr(file_get_contents($php_file),array("<?"=>"","?>"=>"")));
	}
	
	function translate($text) {
		// $text = strtr($text,$this->language_pack2);
		return strtr($text,$this->language_pack);
	}
	
	function transform_untranslated_tokens($content) { //return $content;
		$pattern = '/\<\%(.+)\|(.+)\%\>/msU';
		$replacement = '<span class="untranslated" title="${1}">${2}</span>';
		return preg_replace($pattern,$replacement,$content);
	}
	
	function navigation() {
		$tpl = '${<a href="[@%%]/[lang_suffix]" title="[language]" class="[class]"><img src="[@%%]/images/flags/[#].png" alt="[#]" /></a>}';
		
		$available = array_merge(array($this->default_language),$this->supported);
		$langmap = array_intersect_key($this->languages,array_combine($available,$available));
		foreach ($langmap as $ln=>$language) {
			$langmap[$ln] = array(
				"language"=>$language,
				"class" => (__LANG__ == $ln) ? "current" : "not-current",
				"lang_suffix" => ( ($ln == $this->default_language) ? "" : "{$ln}/" )
			);
		}
		// $langmap = array_pick($this->languages,array_merge(array($this->default_language),$this->supported));
		return produce($tpl,$langmap);
	}
}


function lang($text=null) {
	global $_lang;
	if (!isset($_lang)) {
		$_lang = new _lang();
	}
	
	if ($text!==null) {
		return $_lang->translate($text);	
	} else {
		return $_lang;
	}
}
lang();

function langword($text){
	global $_lang;
	if (!isset($_lang)) {
		$_lang = new _lang();
	}
	$item = '<%'.$text.'%>';
	return (isset($_lang->language_pack[$item])) ? $_lang->language_pack[$item] : $text;
}


?>