<?
define("SESSION_COOKIE_NAME","__".__PROJECT__."_session__");
define("SESSION_USER_LOGGED_OUT",0);
define("SESSION_USER_LOGGED_IN",1);
define("SESSION_USER_IN_SESSION",2);
define("SESSION_USER_INVALID_CREDENTIALS",3);
define("SESSION_USER_BLOCKED",4);
define("SESSION_USER_CSRF_ATTEMPT",5);
define("SESSION_USER_INVALID_TURING",6);
define("SESSION_USER_NO_COOKIE",7);
define("SESSION_USER_EMPTY_USERNAME",8);
define("SESSION_USER_EMPTY_PASSWORD",9);

class _session {
	// TO DO:  add subscriber functions
	var $id_subscriber=0;
	var $id_page, $id_visitor, $id_referer, $id_useragent, $id_session, $id_hit, $ip, $sid, $regdate, $lastaction, $worktime;
	var $id_usertype=0,$id_user=0,$loggedin=false;
	var $cookie_duration=2592000,$login_duration=3600,$csrf_cookie_duration=18000;
	var $userdata=array();
	var $csrf_name,
		$csrf_unique_id,
		$csrf_name_hash,
		$csrf_unique_id_hash
	;
	var $max_login_attempts = 3; // when 3 login attempts occur without succes, turing test appears
	
	function csrf_generate_name() {
		if (isset($_SESSION['csrf_name'])) {
			return $_SESSION['csrf_name'];
		} else {
			$name = passwordhash(randomhash());
			$_SESSION['csrf_name'] = $name;
			return $name;
		}
	}
	
	function csrf_generate_id() {
		if (isset($_SESSION['csrf_unique_id'])) {
			return $_SESSION['csrf_unique_id'];
		} else {
			$_SESSION['csrf_unique_id'] = $unique_id = randomhash();
			return $unique_id;
		}
	}
	
	function csrf_prevent_input() {
		return "<input type='hidden' name='{$this->csrf_name_hash}' value='{$this->csrf_unique_id_hash}' />";
	}
	
	function csrf_check($type='POST') {
		$data = ($type=='POST') ? $_POST : $_GET;
		return ($data[passwordhash($_SESSION['csrf_name'])]==passwordhash($_COOKIE[$_SESSION['csrf_name']]));
	}	
	
	function exceded_login_attempts() {
		return ( $_SESSION['session_login_attempts'] > $this->max_login_attempts ) ;
	}
	
	function login_turing_test($path='',$msg='Please enter the text from the image') {
		global $_turing;
		if ($this->exceded_login_attempts()) {
			$microtime = micronow();
			$turing_image_url = $path."/system/turing.php?turing_image={$microtime}";
			$out ="
				<br />
				<img src='{$turing_image_url}' alt='Turing test' style='border:1px solid #aaaaaa;' /><br />
				{$msg}<br />
				<input type='text' name = '{$_turing->post_variable}' />
				
			";
		}
		return $out;
	}
	
	function __construct(){
		// $this->login_user();
		$this->register();
		
		// CSRF prevention
		$this->csrf_name = $this->csrf_generate_name();
		$this->csrf_unique_id = $this->csrf_generate_id();
		
		$this->csrf_name_hash = passwordhash($this->csrf_name);
		$this->csrf_unique_id_hash = passwordhash($this->csrf_unique_id);
		
		setcookie($this->csrf_name,$this->csrf_unique_id,time()+$this->csrf_cookie_duration,'/');
	}

	function logout_user($id=0,$sid='',$cookie_name=SESSION_COOKIE_NAME) {
		if ($sid=='') $sid=secure($_COOKIE[$cookie_name]);
		$condition = ($id==0) ? "`sid`='$sid'" : "`id_user`='$id'";

		$data=sql("select * from users where $condition limit 1;")->row();
		
		$_SESSION = array();
		$newsid=randomhash();
		sql("UPDATE `users` SET `sid`='$newsid' WHERE $condition;");
		return true;
	}
	
	
	function getuserdata($id_user) {
		$id_user=intval($id_user);
		$data=sql("select * from users where id_user=$id_user")->arr();
		if (count($data)) {
			$this->userdata=reset($data);
			return true;
		} else {
			return false;
		}
	}
	
	function login_user($id_usertype=1,$cookie_name=SESSION_COOKIE_NAME) {
		$id_usertype = intval($id_usertype);
		
		$this->loggedin = false;
		$cookie_sid = secure($_COOKIE[$cookie_name],true,true);
		
		// login by cookie
		if ($cookie_sid!='') {
			$id_user = sql("SELECT id_user FROM `users` WHERE `sid`='{$cookie_sid}' and id_usertype='{$id_usertype}';")->cell();
			$exist = ($id_user!=0);
			if ($exist) {
				if ($_GET['logout']==1) {
					$this->logout_user(0,$cookie_sid);
					return false;
				}
				
				$this->getuserdata($id_user);
				if (!$this->userdata['approved']) { // user has been blocked
					$this->state = SESSION_USER_BLOCKED;
					$this->logout_user(0,$cookie_sid); 
					return false;
				}
				
				// update cookie
				$expires=$this->login_duration;
				setcookie($cookie_name, $cookie_sid, time()+$expires,'/');
				
				// update last active
				sql("UPDATE `users` SET `lastactive`=now() WHERE id_user = $id_user limit 1;");
				
				$this->loggedin = true;
				$this->state = SESSION_USER_IN_SESSION;
				return true;
			} 
		} else {
			$this->state = SESSION_USER_LOGGED_OUT;
		} 
		
		// login by form
		if (isset($_POST['username'])) {
		
			// cannot login if no SID cookie was set;
			if (!isset($_COOKIE['sid'])) {
				$this->state = SESSION_USER_NO_COOKIE;
				return false;
			}
			
			// check csrf
			if (!$this->csrf_check()) {
				$this->state = SESSION_USER_CSRF_ATTEMPT;
				return false;
			}
			
			// check login attempt count
			if ($this->exceded_login_attempts()) {
				global $_turing;
				if (!turing()->passed()){
					$_SESSION['session_login_attempts']++;
					$this->state = SESSION_USER_INVALID_TURING;
					return false;
				}
			}
			
			// check username;
			if ($_POST['username']=='') {
				$this->state = SESSION_USER_EMPTY_USERNAME; 
				return false;
			}
			
			// check password
			if ($_POST['password']=='') {
				$this->state = SESSION_USER_EMPTY_PASSWORD; 
				return false;
			}
			


			$username = secure($_POST['username'],true,true);
			$password = passwordhash($_POST['password']);
			$id_user = sql("select id_user from users WHERE `username`='$username' AND `password`='$password' and id_usertype=$id_usertype;")->cell();
			
			$exist = ($id_user!=0);
			if ($exist) {
				$this->getuserdata($id_user);
				if (!$this->userdata['approved']) {
					$this->state = SESSION_USER_BLOCKED;
					$_SESSION['session_login_attempts']++;
					return false; // user has been blocked
				}
				
				$newsid = randomhash();
				sql("UPDATE `users` SET `sid`='$newsid' WHERE `id_user`='$id_user';");
				setcookie($cookie_name, $newsid, time()+$this->login_duration,'/');

				$this->loggedin=true;
				$this->state = SESSION_USER_LOGGED_IN;
				$_SESSION['session_login_attempts'] = 0;
				return true;
			} else {
				$_SESSION['session_login_attempts']++;
				$this->state = SESSION_USER_INVALID_CREDENTIALS;
				$output=false;
			}
		}

		return false;

	}
	
	
	
	
	function register($register_hit=true,$external_worktime=false) {
		global $_db;
		if (!$external_worktime) $worktime=microtime(true);
		
		$ip=$_SERVER['REMOTE_ADDR'];
		$hostname=secure(gethostbyaddr($ip));
		$url=substr(secure($_SERVER['REQUEST_URI']),0,256);
		$useragent=substr(secure($_SERVER['HTTP_USER_AGENT']),0,256);
		$referer=substr(secure($_SERVER['HTTP_REFERER']),0,256);
		$title=secure($_pagecontent['title']);
		$category=$_pagecontent['category'];
		
		// register page
		sql("insert into `pages` (url,title,category) values ('$url','$title','$category') on duplicate key update id_page=id_page;");
		$id_page = sql("select id_page from `pages` where url='$url'")->cell();
		// register referer
		
		 
		if ($_SERVER['HTTP_REFERER']!='') {
			$local_host=$_SERVER['SERVER_NAME'];
			$referer_host=parse_url($_SERVER['HTTP_REFERER']);
			$referer_host=$referer_host['host'];
			
			if ($local_host!=$referer_host) {
				sql("insert into `referers` (title) values ('$referer') on duplicate key update id_referer=id_referer;");
				$id_referer = sql("select id_referer from `referers` where title='$referer'")->cell();
			} else {
				$id_referer=0;
			}
		} else {
			$id_referer=0;
		}
		
		// register user agent
		sql("insert into `useragents` (title) values ('$useragent') on duplicate key update id_useragent=id_useragent;");
		$id_useragent = sql("select id_useragent from `useragents` where title='$useragent'")->cell();
		
		// register visitor
		$sid=secure($_COOKIE['sid']);
		$visitor_query="
			select count(*) num, ifnull(sid,'') as sid 
			from `visitors`
			where sid='$sid' OR (ip='$ip' and id_useragent=$id_useragent)
			group by `sid`
			order by (sid='$sid') desc 
			limit 1";
		extract(sql($visitor_query)->row());
		
		
		if ($num==0 || $sid=='') {
			$id_visitor=sql("select ifnull(max(id_visitor),0) from `visitors` limit 1;")->cell();
			$sid=md5(++$id_visitor);
			sql("
				insert into `visitors` (sid,ip,hostname,id_useragent,regdate,lastaction) values ('$sid','$ip','$hostname','$id_useragent',now(),now());
			");
			$regdate=$lastaction=date("Y-m-d H:i:s");		
		} else {			
			$human=(isset($_COOKIE['sid'])) ? 1 : 0;
 			sql("update `visitors` set lastaction=now(), human=$human where sid='$sid'");
			extract(sql("select 
			id_visitor,sid,ip,id_useragent,regdate,lastaction
			from `visitors` where sid='$sid' limit 1;")->row());
		}
		setcookie("sid",$sid,time()+8640000,"/"); // 100 days
		
		// register session
		$id_referer=intval($id_referer);
		$session_query="select ifnull(id_session,0) as id_session,count(*) c from `sessions` where id_visitor=$id_visitor and id_page=$id_page and id_referer=$id_referer group by id_session;";
		extract(sql($session_query)->row());
		if ($id_session==0) sql("insert into `sessions` (id_visitor,id_page,id_referer) values ($id_visitor,$id_page,$id_referer);");
		
		foreach($this as $var=>$none) if (isset($$var)) $this->$var=$$var;
	}
	
	function get_work_time() {
		return round((microtime(true)-($this->worktime))*1000);
	}
	
	function end($register_page=true) {
		global $_db,$_pagecontent;
		if ($register_page) {
			$title=secure($_pagecontent['title']);
			$category=secure($_pagecontent['category']);
			sql("update pages set title='$title',category='$category' where id_page={$this->id_page} limit 1;");
		}
	}
	

	

	
	
}

function session() {
	global $_session;
	if (!isset($_session)){
		session_start();
		$_session = new _session();
	}
	
	return $_session;
}

if (!__IN_SHELL__) {
	header(__AUTHOR_HEADER_SIGNATURE__);
	// session();
}
?>