<?

interface IEncoding {
	 function getLowerCaseLetters();
	 function getUpperCaseLetters();
	 function getSpecialLetters();
	 function getDigits();
}

abstract class Encoding extends BaseSingleton {

	public function getLetters() {
		return $this->getUpperCaseLetters() . $this->getLowerCaseLetters() ;
	}
	
	public function getSpecialLetters() {
		return "";
	}
	
	public function getDigits() {
		return "0123456789";
	}
	
	public  function toLowerCase( $string  ) {
		return strtr( $string, $this->getUpperCaseLetters(), $this->getLowerCaseLetters()  );
	}
	
	public  function toUpperCase( $string ) {
		return strtr( $string, $this->getLowerCaseLetters() , $this->getUpperCaseLetters() );
	}
	
	public function toANSI( $string ) {
		return strtr( $string,  array_combine( $this->getSpecialLetters() , $this->getSpecialLettersANSI()  ) );
	}
	
}


class UTF8Encoding extends Encoding {	
	
	public  function getUpperCaseLetters() {
		return "ABCDEFGHIJKLMNOPQRSTUVWXYZČĆŽŠĐÑ";
	}
	
	public  function getLowerCaseLetters() {
		return "abcdefghijklmnopqrstuvwxyzčćžšđñ";
	}
		
	
	public  function getSpecialLetters() {
		return explode(',','Č,Ć,Ž,Š,Đ,č,ć,ž,š,đ,Ñ,ñ');
	}
	
	public  function getSpecialLettersANSI() {
		return explode(',','C,C,Z,S,D,c,c,z,s,d,N,n');
	}
	
	
	

}


class WordList {

	private $encoding;
	
	private $terms;
	
	function __construct( $string , $encoding = null ) {
		if ($encoding == null ) {
			$encoding = new UTF8Encoding();
		}
		$this->encoding = $encoding;
		
		// special letters
		$special = implode('',$encoding->getSpecialLetters());
		
		$term_pattern = "([A-Za-z{$special}]{1,}|[0-9]{1,})";
		preg_match_all($term_pattern,$string,$results);		
		$this->terms = reset($results);
		
	}
	
	public function toLowerCase() {
		$this->terms = array_map(array($this->encoding,'toLowerCase'), $this->terms );
		return $this;
	}
	
	public function toUpperCase() {
		$this->terms = array_map(array($this->encoding,'toUpperCase'), $this->terms );
		return $this;
	}
	
	public function toANSI() {
		$this->terms = array_map(array($this->encoding,'toANSI'), $this->terms );	
		return $this;
	}
	
	public function toSet() {
		$this->terms = array_unique($this->terms);			
		return $this;
	}
	
	public function sort() {
		sort($this->terms);		
		return $this;
	}
	

	function getTerms(){
		return $this->terms;
	}
	
}


?>