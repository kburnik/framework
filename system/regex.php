<?
class _regex {
	var $regex=array( 
		  "entry" => "/^([A-Za-zČčĆćĐđŠšŽž-]{2,}){1,}/u",
		  "name" => "/^([ ]{0,}[A-Za-zČčĆćĐđŠšŽž-]{2,}[ ]{0,}){1,}$/u",
		  "address" => "/^(([^ ]{2,})([ ]{1,}))([^ ]{1,})/u",
		  "phone" => "/^[0-9 \-\/\(\)\+]{8,25}$/u",
		  "number" => "/^[0-9 ]{5,12}$/u",
		  "mail" => "/[.+a-zA-Z0-9_-]+@[a-zA-Z0-9-]+.[a-zA-Z]+/",
		  "username" => "/^[A-Za-z_.\@\-0-9]{4,}$/u",
		  "password" => "/^[A-Za-z_.\@\-0-9]{6,}$/u"
	);
	var $validation = array(
		"name" => array("name","Upišite ispravno ime!","ucfirst_utf8"),
		"surname" => array("name","Upišite ispravno prezime!","ucfirst_utf8"),
		"address" => array("address","Upišite ispravnu adresu (ulicu i broj)!"),
		"city" => array("entry","Upišite vaš grad/mjesto stanovanja"),
		"country" => array("entry","Upišite državu"),
		"zipcode" => array("number","Upišite poštanski broj"),
		"phone" => array("phone","Upišite ispravan broj telefona"),
		"mail" => array("mail","Upišite ispravno vašu primarnu e-mail adresu","strtolower"),
		"username" => array("username","Upišite ispravno korisničko ime (min 4 slova i/ili znamenke)","strtolower"),
		"password" => array("password","Upišite ispravnu lozinku (min 6 slova i/ili znamenke)","strtolower"),
	);
	
	var $errors = array(); // last validation errors are stored here

	function __construct() {
	
	}
	
	// sets the validation array 
	function setvalidation($array) {
		if (is_array($array)) {
			$this->validation = $array;
		}
		return $this;
	}
	
	function validate($data,$validation=null,$errors=array()) {
		// "clear" the errors
		$this->errors = $errors;
		
		// errors array should be passed as reference
		if (is_array($data)) {
			// assume default validation used if none defined
			if ($validation==null) $validation = $this->validation;
			// assure errors are in an array
			if (!is_array($errors))  $errors = array();
			// go thru data and validate
			foreach ($data as $entry=>$value) {
				// test entry value only if it exists in the validation array
				if (isset($validation[$entry])) {
					// get the current validator
					$validator = $validation[$entry];
					// assume the first part of validator is an REGEXP
					$pattern = $validator[0];
					// still it may be a reference to the $regex array
					if (isset($this->regex[ $validator[0] ])) $pattern = $this->regex[$patern];
					// if the validation fails, note the error
					if (!preg_match($pattern, $value)) $errors[ $entry ] = $validator[1];
				}
			}
			
			$this->errors = $errors;
			if (count($errors)>0) {
				return false;
			} else {
				return true;
			}
		} else {
			$pattern = $validation;
			if (isset($this->regex[ $validator[0] ])) $pattern = $this->regex[$patern];
			if ($pattern!="") {
				return preg_match($pattern,$data);
			} else {
				return true;
			}
		}
	}
	
	function errors() {
		return $this->errors;
	}
}

function regex() {
	global $_regex;
	if (!isset($_regex)) {
		$_regex = new _regex();
	}
	return $_regex;
}
// SAMPLE OF USAGE
/*
regex() -> setvalidation(array(
	"name"=> array("name","Enter your name!")
));
if ( regex() -> validate($_POST) ) {
	echo "All is good"
} else {
	print_r(regex() -> errors());
}
if ( regex() -> validate("kburnik@gmail.com","mail") ) {
	echo "You're mail is good".
} else {
	echo "Incorrect mail address, plesase correct it.";
}
*/
?>