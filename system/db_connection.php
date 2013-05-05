<?
return 
	($_SERVER['REMOTE_ADDR']==$_SERVER['SERVER_ADDR'])  ?
		// LOCAL SERVER (for testing)
		array(
			"host" => "localhost",
			"database" => "bimex",
			"username" => "root",
			"password" => ""
		)
	:
		// REMOTE SERVER
		array(
			"host" => "localhost",
			"database" => "bimex",
			"username" => "root",
			"password" => ""
		)
;
?>