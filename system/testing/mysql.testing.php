<?
include_once("test.module.php");

// testing the MYSQL class;

$mysql_testing = array(
	"backup"=> function() {
		$result = sql() -> backup("articles","testing_articles",true,30);
		return array(
			"result"=>$result
		);
	},
	"sql" => function() {
		$query = "show tables";
		$result = sql("show tables");
		return array(
			"query"=>$query,
			"result" => $result
		);
	},
	"execute1" => function() {
		$query = "show tables";
		$result = sql("show tables",true);
		return array(
			"query"=>$query,
			"result" => $result
		);
	},
	"execute2" => function() {
		$query = "
		CREATE TEMPORARY TABLE  `test_table` (
			`id` INT( 4 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`priority` ENUM(  'low',  'high',  'critical' ) NOT NULL
		);
		";
		$result = sql()->execute($query,true);
		return array(
			"query" => $query,
			"result" => $result
		);		
	},
	"secure" => function() {
		$single = "My name's Kristijan. Here's the test";
		$array = array(
			"Black's cool",
			"' or 1=1"
		);
		
		$single_result = sql() -> secure($single);
		$array_result = sql() -> secure($array);
		
		return array(
			"single"=>$single,
			"single_result"=>$single_result,
			"array"=>$array,
			"array_result"=>$array_result
		);
	},
	"cell1" => function() {
		$query = "select username from users limit 1;";		
		$result = sql($query) -> cell();
		return array(
			"query"=>$query,
			"result"=>$result
		);
	},
	"cell2" => function() {
		$query = "select username from users limit 0;";		
		$result = sql($query) -> cell();
		return array(
			"query"=>$query,
			"result"=>$result
		);
	},
	"cell3" => function() {
		$query = "select * from users limit 5;";		
		$result = sql($query) -> cell();
		return array(
			"query"=>$query,
			"result"=>$result
		);
	},	
	"row1" => function() {
		$query = "select * from users limit 1;";		
		$result = sql($query) -> row();
		return array(
			"query"=>$query,
			"result"=>$result
		);
	},
	"row2" => function() {
		$query = "select * from users limit 2,1;";		
		$result = sql($query) -> row();
		return array(
			"query"=>$query,
			"result"=>$result
		);
	},	
	"arr1" => function() {
		$query = "select * from users limit 2;";		
		$result = sql($query) -> arr();
		return array(
			"query"=>$query,
			"result"=>$result
		);
	},	
	"arr2" => function() {
		$query = "select * from users limit 1;";		
		$result = sql($query) -> arr();
		return array(
			"query"=>$query,
			"result"=>$result
		);
	},	
	"arr3" => function() {
		$query = "select username from users limit 5;";		
		$result = sql($query) -> arr();
		return array(
			"query"=>$query,
			"result"=>$result
		);
	},	
	"arr4" => function() {
		$query = "select username,password from users limit 0;";		
		$result = sql($query) -> arr();
		return array(
			"query"=>$query,
			"result"=>$result
		);
	},	
	"arrmap" => function() {
		$query = "select id_user,username,password from users limit 5;";		
		$result = sql($query) -> arrmap();
		return array(
			"query"=>$query,
			"result"=>$result
		);
	},
	"binmap" => function() {
		$query = "select id_user,username from users limit 5;";		
		$result = sql($query) -> binmap();
		return array(
			"query"=>$query,
			"result"=>$result
		);
	},	
	"arrgroup1" => function() {
		$query = "(select username,password,regdate from users) union all (select username,password,regdate from users);";		
		$result = sql($query) -> arrgroup();
		return array(
			"query"=>$query,
			"result"=>$result
		);
	},
	"arrgroup2" => function() {
		$query = "(select username,password,regdate from users) union all (select username,password,regdate from users);";		
		$result = sql($query) -> arrgroup("password");
		return array(
			"query"=>$query,
			"result"=>$result
		);
	},	
	"arrgroup3" => function() {
		$query = "(select username,password,regdate from users) union all (select username,password,regdate from users);";		
		$result = sql($query) -> arrgroup("username",true);
		return array(
			"query"=>$query,
			"result"=>$result
		);
	},	
	"generate_fields" => function() {
		$data = array(
			"table"=>"My little table",
			"query"=>"My large query",
			"date"=>"A now date"
		);		
		$result = sql() -> generate_fields($data);
		return array(
			"data"=>$data,
			"result"=>$result
		);
	},	
	"filter_fields1" => function() {
		$data = array(
			"table"=>"My little table",
			"query"=>"My large query",
			"username"=>"Kristijan",
			"password"=>md5("1234")
		);		
		$table = "users";
		$result = sql() -> filter_fields($table,$data,$fields);
		return array(
			"table"=>$table,
			"data"=>$data,
			"result"=>$result
		);
	},	
	"filter_fields2" => function() {
		$data = array(
			"table"=>"My little table",
			"query"=>"My large query",
			"username"=>"Kristijan",
			"password"=>md5("1234")
		);		
		$table = "users";
		$fields = array("query");
		$result = sql() -> filter_fields($fields,$data);
		return array(
			"fields"=>$fields,
			"data"=>$data,
			"result"=>$result
		);
	},	
	"exists1" => function() {
		$table = "users";
		$identifier = "username";
		$value = "kburnik";
		$result = sql() -> exists($table,$identifier,$value);
		return array(
			"table"=>$table,
			"identifier"=>$identifier,
			"result"=>$result
		);
	},		
	"exists2" => function() {
		$table = "users";
		$identifier = "username";
		$value = "kburnik22";
		$result = sql() -> exists($table,$identifier,$value);
		return array(
			"table"=>$table,
			"identifier"=>$identifier,
			"result"=>$result
		);
	},
	"insert1" => function() {
		$table = "testing_articles";
		$data = array(
			"title" => "First article of them all",
			"description"=>"An article description",
			"created" => now()
		);
		$result = sql() -> insert($table,$data);
		return array(
			"table"=>$table,
			"data"=>$data,
			"result"=>$result
		);
	},
	"insert2" => function() {
		$table = "testing_articles";
		$data = array(
			"title" => "Second article of them all",
			"description"=>"The second article's description",
			"created" => now(),
			"redundant" => "blah"
		);
		$result = sql() -> insert($table,$data);
		return array(
			"table"=>$table,
			"data"=>$data,
			"result"=>$result
		);
	},
	"insertall" => function() {
		$table = "testing_articles";
		$data = sql("select title,description, now() as created from testing_articles limit 3") -> arr();
		$result = sql() -> insertall($table,$data);
		return array(
			"table"=>$table,
			"data"=>$data,
			"result"=>$result
		);		
	},
	"update" => function() {
		$table = "testing_articles";
		$where = "id_article > 0 and id_article < 10";
		$data = array("title"=>"Updated this title @ ".now(),"description"=>"also updated description");
		$result = sql() -> update($table,$data,$where);
		return array(
			"table"=>$table,
			"where"=>$where,
			"data"=>$data,
			"result"=>$result
		);	
	},
	"insertupdate" => function() {
		$table = "testing_articles";
		$data = array("id_article"=>8, "title"=>"Tried inserting, Updated this title @ ".now(),"description"=>"also updated description");
		$result = sql() -> insertupdate($table,$data);
		return array(
			"table"=>$table,
			"where"=>$where,
			"data"=>$data,
			"result"=>$result
		);	
	},
	"delete" => function() {
		$table = "testing_articles";
		$where = "id_article > 30";
		$limit = 30;
		$result = sql() -> delete($table,$where,$limit);
		return array(
			"table"=>$table,
			"where"=>$where,
			"limit"=>$limit,
			"result"=>$result
		);	
	},
	"affected" => function(){
		$query = "select * from testing_articles";
		$result = sql($query) -> affected();
		return array(
			"query"=>$query,
			"result"=>$result
		);
	},
	"tables1" => function(){		
		$result = sql() -> tables();
		return array(
			"result"=>$result
		);
	},
	"tables2" => function(){		
		$result = sql() -> tables();
		return array(
			"result"=>$result
		);
	},	
	"tables3" => function(){		
		$result = sql() -> refresh() -> tables();
		return array(
			"Note" => "Called sql() -> refresh() -> tables()",
			"result"=>$result
		);
	},
	"table_details1" => function(){		
		$result = sql() -> table_details();
		return array(
			"result count"=>count($result)
		);
	},		
	"table_details2" => function(){		
		$result = sql() -> table_details();
		return array(
			"result count"=>count($result)
		);
	},	
	"table_details3" => function(){		
		$result = sql() -> refresh() -> table_details();
		return array(
			"Note" => "Called sql() -> refresh() -> table_details()",
			"result count"=>count($result)
		);
	},
	"fields1" => function(){		
		$table = "users";
		$result = sql() -> fields($table);
		return array(
			"table"=>$table,
			"result"=>$result
		);
	},
	"fields2" => function(){		
		$table = "users";
		$result = sql() -> fields($table);
		return array(
			"table"=>$table,
			"result"=>$result
		);
	},	
	"fields3" => function(){		
		$table = "users";
		$result = sql() -> refresh() -> fields($table);
		return array(
			"Note"=>"Called sql() -> refresh() -> fields(\$table);",
			"table"=>$table,
			"result"=>$result
		);
	},
	"field_details1" => function(){		
		$table = "testing_articles";
		$result = sql() -> refresh() -> field_details($table);
		return array(			
			"table"=>$table,
			"result_count"=>count($result)
		);
	},
	"field_details2" => function(){		
		$table = "testing_articles";
		$result = sql() -> field_details($table);
		return array(			
			"table"=>$table,
			"result_count"=>count($result)
		);
	},	
	"field_details3" => function(){		
		$table = "testing_articles";
		$result = count(sql() -> refresh() -> field_details($table));
		return array(
			"Note"=>"Called sql() -> refresh() -> fields(\$table);",
			"table"=>$table,
			"result_count"=>count($result)
		);
	},
	"get_enum1" => function(){
		$table = "test_table";
		$field = "priority";
		$result = sql() -> get_enum($table,$field);
		return array(
			"table"=>$table,
			"field"=>$field,
			"result"=>$result
		);
	},	
	"get_enum2" => function(){
		$table = "test_table";
		$field = "priority";
		$result = sql() -> get_enum($table,$field);
		return array(
			"table"=>$table,
			"field"=>$field,
			"result"=>$result
		);
	},
	"get_enum3" => function(){
		$table = "test_table";
		$field = "priority";
		$result = sql() -> refresh() -> get_enum($table,$field);
		return array(
			"Note" => 'Called  sql() -> refresh() -> get_enum($table,$field);',
			"table"=>$table,
			"field"=>$field,
			"result"=>$result
		);
	},	
	"primary_key1" => function(){
		$table = "articles";
		$result = sql() -> refresh() -> primary_key("test_table");
		return array(			
			"Note"=>'Called sql() -> refresh() -> primary_key("test_table");',
			"table"=>$table,
			"result"=>$result
		);
	},		
	"primary_key2" => function(){
		$table = "articles";
		$result = sql() -> primary_key("test_table");
		return array(			
			"table"=>$table,			
			"result"=>$result
		);
	},	
	"db_size1" => function(){
		$result = sql() -> refresh() -> db_size();
		return array(		
			"Note" => 'Called sql() -> refresh() -> db_size();',
			"result"=>$result
		);
	},	
	"db_size2" => function(){
		$result = sql() -> db_size();
		return array(			
			"result"=>$result
		);
	},	
	"truncate" => function(){
		$table = "testing_articles";
		$result = sql() -> truncate("testing_articles");
		return array(			
			"table"=>$table,			
			"result"=>$result
		);
	},
	"drop" => function(){
		$table = "test_table";
		$result = sql() -> drop("test_table");
		return array(			
			"table"=>$table,			
			"result"=>$result
		);
	},		
	"repair" => function(){
		$table = "articles";
		$result = sql() -> repair("articles");
		return array(			
			"table"=>$table,			
			"result"=>$result
		);
	},		
	"optimize" => function(){
		$table = "users";
		$result = sql() -> optimize("articles");
		return array(			
			"table"=>$table,			
			"result"=>$result
		);
	},		
	
);


runtest($mysql_testing);

?>