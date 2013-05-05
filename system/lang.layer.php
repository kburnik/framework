<?
include_once("system.php");
//todo :place to aux:

function wrap_implode($prefix,$delimiter,$suffix,$data) {
	return $prefix.implode($delimiter,$data).$suffix;
}


function level_indent($text,$level = 0) {
	$lines = explode("\n",$text); 
	
	$prefix = "";
	while (trim($lines[0])=="" && count($lines)>0) {
		$prefix .= $lines[0]."\n";
		array_shift($lines);
	}
	
	$excessive_tab_count = 0;
	while ($lines[0][$excessive_tab_count]=="\t") $excessive_tab_count++;
	$excessive_tabs = str_repeat("\t",$excessive_tab_count);
	$level_tabs = str_repeat("\t",$level);
	foreach ($lines as $i=>$line) {
			if ($excessive_tab_count > 0 && substr($line,0,$excessive_tab_count) == $excessive_tabs) $line = substr($line,$excessive_tab_count);
			$lines[$i] = $level_tabs . $line;
	}
	
	
	return $prefix.implode("\n",$lines);
}


// todo: place to auxiliary or template for future version propagation
function tpl_value_checked($x) {
	return ($x) ?  "checked=\"checked\"" : "";
}

function tpl_color_marked($x) {
	return (!$x) ? "background-color:#fff2f2;" : "";
}


class _language_surogate {
	// type of fields which are language differed 
	var $no_lang_context_keyword = _language_layer::no_lang_context_keyword;
	var $language_layer = null;
	
	function __construct($master_db,$db,$language_layer) {
	
		$this->language_layer = $language_layer;
		if (!is_object($language_layer)) {
			trigger_error("Mising language layer for language surogate!",E_USER_ERROR);
		}
		
		// check if both databases exists
		if (!sql()->isdb($master_db)) {
			trigger_error("Master database `$master_db` doesn't exist!",E_USER_ERROR);
		}
		if (!sql()->isdb($db)) {
			trigger_error("Language database `$db` doesn't exist!",E_USER_ERROR);
		}
		
		// check if $master_db != $db;
		if ($master_db == $db) {
			trigger_error("Cannot use master database `{$master_db}` as language database!",E_USER_ERROR);
		}
		
		$this->master_db = $master_db;
		$this->db = $db;
		
		
		// sql()->set_error_handler(array($this,"error_handler"));
	}
	
	function __destruct() {
		// sql()->release_error_handler();
		// sql()->refresh();
	}
	
	function get_lang_fields() {
		return $this->language_layer->get_lang_fields();
	}
	
	function merge_consideratly($original_values,$mixed_values) {
		return array_merge($original_values,array_diff($mixed_values,$original_values));
	}
	
	function translational_enum_definition() {
		
		$db = sql()->database();
		
		$old_enum_tables = sql()->usedb($this->db)->get_enum("translational","tbl");
		$old_enum_fields = sql()->usedb($this->db)->get_enum("translational","col");
		
		sql()->usedb($db);
		
		$lang_fields = $this->get_lang_fields();
		
		$enum_tables = array_keys($lang_fields);
		$enum_fields = array();
		foreach ($lang_fields as $table => $fields) {
				$enum_fields = array_unique(array_merge($enum_fields,$fields));
		}
		
		sort($old_enum_tables);
		sort($old_enum_fields);
		
		sort($enum_tables);
		sort($enum_fields);
		
		if ($old_enum_tables != $enum_tables) {
			$enum_tables = $this->merge_consideratly($old_enum_tables,$enum_tables);
			$enum_tables = wrap_implode("'","','","'",$enum_tables);
			$enum_tables = "ENUM({$enum_tables})";		
		} else {
			$enum_tables = null;
		}
		
		if ($old_enum_fields != $enum_fields) { 
			$enum_fields = $this->merge_consideratly($old_enum_fields,$enum_fields);
			$enum_fields = wrap_implode("'","','","'",$enum_fields);
			$enum_fields = "ENUM({$enum_fields})";
		} else {
			$enum_fields = null;
		}
		
		
		
		return array($enum_tables,$enum_fields);
	}
	
	function update_translational_structure() {
		
		list ($enum_tables,$enum_fields) = $this->translational_enum_definition();
		
		if ($enum_tables !== null) $q .= "ALTER TABLE {$this->db}.translational CHANGE COLUMN `tbl` `tbl` {$enum_tables};;\n";
		if ($enum_tables !== null)  $q .= "ALTER TABLE {$this->db}.translational CHANGE COLUMN `col` `col` {$enum_fields};;\n";
		if ($q == "") {
			$q = "-- NO STRUCTURAL CHANGES DETECTED FOR ALTERING TRANSLATIONAL TABLE ENUMS --\n";
		}
		
		return $q;
	}

	
	function create_surogate_view($base_table) {

		// get table structure
		
		sql()->usedb($this->master_db);
		
		$fields = sql()->fields($base_table);
		$primary_key = sql()->primary_key($base_table);
		
		$lang_fields = $this->get_lang_fields();
		$index = 0;
		foreach ($fields as $field) {
			/* alternate: concat(' <%{$base_table}.{$field}.', bt.`{$primary_key}` ,'|',bt.`$field`,'%>') */
			if (isset($lang_fields[$base_table][$field])) {
				$fields_mixed[] = "IFNULL(tl{$index}.val, bt.`{$field}` ) AS `{$field}`";
				$left_joins .=level_indent("
					LEFT JOIN `{$this->db}`.translational tl{$index} ON (
						tl{$index}.tbl = '{$base_table}' AND tl{$index}.col = '{$field}' AND tl{$index}.id = bt.`{$primary_key}`
					)
				",1);
				$index++;
				
			} else {
				$fields_mixed[] = "bt.`$field`";
			}
		}
		
		$fields_mixed = implode(",\n\t\t\t\t",$fields_mixed);
		
		$translational = ($index > 0) ? "TRANSLATIONAL" : "SIMPLE";
		
		$query = level_indent("
			-- {$translational} SUROGATE VIEW {$base_table} --
			DROP VIEW IF EXISTS `{$this->db}`.`{$base_table}` ;;
			CREATE VIEW `{$this->db}`.`{$base_table}` as
			SELECT
				{$fields_mixed}
			FROM
				`{$this->master_db}`.`{$base_table}` bt
				{$left_joins}
			;;
		");
		
		return $query;
	}
	
	function create_slave_view($view) {
		
		sql()->usedb($this->master_db);
		
		$viewbody = sql()->view_export($view, true);
		
		// make sure the view is not referencing master db:
		$viewbody = str_replace("`{$this->master_db}`","`{$this->db}`",$viewbody).";;\n\n";
		
		$queries .= "-- SLAVE VIEW $view --\n";
		$queries .= "DROP TABLE IF EXISTS `{$view}`;;\n";
		$queries .= $viewbody;
		
		return $queries;
	}
	
	function create_translational_table($drop_if_exists = true) {
		$queries ="-- TRANSLATIONAL TABLE --\n";
		if ($drop_if_exists) $queries .= "DROP TABLE IF EXISTS `translational`;;";
		
		list ($enum_tables,$enum_fields) = $this->translational_enum_definition();

		$queries.=level_indent("
			CREATE TABLE `translational` (
			  `tbl` {$enum_tables} COLLATE utf8_unicode_ci NOT NULL,
			  `col` {$enum_fields} COLLATE utf8_unicode_ci NOT NULL,
			  `id` int(4) NOT NULL DEFAULT '0',
			  `val` text COLLATE utf8_unicode_ci,
			  `chg` int(4) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`tbl`,`col`,`id`),
			  KEY `id` (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;;
			");
			
		//
		return $queries;
	}
	
	function update_translational_table($return = false) {
		if (!isset($this->lang_fields)) {
			$this->lang_fields = $this->get_lang_fields();
		}

		foreach ($this->lang_fields as $table => $fields) {
			$primary_key = sql()->primary_key("{$this->master_db}`.`{$table}");
			$selects = array();
			foreach ($fields as $field) {
				$selects[] = "\t(select '{$table}' as tbl, '{$field}' as col, `{$primary_key}` as id, 1 as chg from `{$this->master_db}`.`{$table}`)";
			}
			
			if (count($selects) > 0) {
				$queries .= level_indent("
				DELETE FROM `{$this->db}`.translational WHERE
				tbl = '{$table}' AND id NOT IN (SELECT `{$primary_key}` FROM `{$this->master_db}`.`{$table}` )
				;;
				");
				$queries .= level_indent("
				INSERT INTO `{$this->db}`.translational (tbl,col,id,chg)
				".implode("\n\tUNION ALL\n",$selects)."
				ON DUPLICATE KEY UPDATE `{$this->db}`.translational.chg = 0
				;;
				");
			}
		}
		return $queries;
	}
	
	
	function truncate_layer() {
		// get all tables
		$queries = "";
		
		sql()->usedb($this->db);
		
		$use = "USE `{$this->db}`;;\n";
		
		$tables = sql()->justtables();
		if (count($tables) > 0) {
			$tables = wrap_implode("`","`, `","`",$tables);
			$queries.="DROP TABLE IF EXISTS $tables;;\n";
		}
		
		// get all views
		$views = sql()->justviews();
		if (count($views) > 0) {
			$views = wrap_implode("`","`, `","`",$views);
			$queries.="DROP VIEW IF EXISTS $views;;\n";
		}
		
		if ($queries == "") {
			$queries = "-- NOTHING TO DROP IN {$this->db} -- \n";
		} else {
			$queries = "-- DROP OBJECTS IN {$this->db} -- \n".$use.$queries;
		}
		
		return $queries;
		
	}
	
	function create_structure($return = false) {
	
		// use language database
		$queries .= "USE `{$this->db}`;;\n";
		
		// get all tables from master_db;
		$all_tables = sql()->usedb($this->master_db)->tables();
		
		// drop all objects
		$queries .= $this->create_translational_table(true);
		$queries .= $this->update_translational_table(true);
		$objects = wrap_implode("`","`, `","`",$all_tables);
		$queries.="DROP VIEW IF EXISTS $objects;;\n";
		
		// handle surogate views and translational entries
		$tables = sql()->justtables();
		foreach ($tables as $table){
			$queries .= $this->create_surogate_view($table)."\n";
		}
	
		// handle slave views
		$views = sql()->usedb($this->master_db)->justviews();
		
		 // stand-ins
		foreach ($views as $view) {
			$queries .= "-- STAND-IN TABLE FOR SLAVE VIEW $view --\n";
			$queries .= sql()->structure_export($view) . ";;\n";
		}
		 // views
		foreach ($views as $view) {
			$queries .= $this->create_slave_view($view);
		}
		
		
		if (!$return) {
			sql()->multi($queries,";;",true);
		}
		
		return $queries;
		
	}
	
	function update_structure($return = false) {
		$queries .= "USE `{$this->db}`;;\n";
		
		sql()->usedb($this->db);
		$lang_objects = sql()->tables();
		$lang_objects = array_combine($lang_objects,$lang_objects);
		
		sql()->usedb($this->master_db);
		$master_objects = sql()->tables();
		$master_objects = array_combine($master_objects,$master_objects);
		
		unset($lang_objects["translational"]);
		
		foreach ($lang_objects as $lang_table) {
			if (!isset($master_objects[$lang_table])) {
				$queries .= "DROP VIEW IF EXISTS `{$this->db}`.`{$lang_table}`;;\n";
			}
		}
		
		
		
		
		$queries .= $this->update_translational_structure( true );
		
		// compare tables and views
		
		// handle tables 
		$tables = sql()->justtables();
		foreach ($tables as $i=>$table) {
			// if (!sql()->compare_structure("`{$this->master_db}`.`{$table}`","`{$this->db}`.`{$table}`",true,false)) {
				$queries .= $this->create_surogate_view($table)."\n";
			//}
		}
		
		// handle views
		$views = sql()->justviews();
		foreach ($views as $i=>$view) {
			if (!sql()->compare_structure("`{$this->master_db}`.`{$view}`","`{$this->db}`.`{$view}`",true,false)) {
				$queries .= $this->create_slave_view($view);
			}
		}
		
		if (!$return) {
			return sql()->multi($queries,";;",true);
		}
		
		return $queries;
		
	}
	
	function error_handler($errno,$error,$query) {
		if ($errno != 1065) {
			trigger_error("$errno: $error<br />$query",E_USER_ERROR);
			return false;
		} else {
			return true;
		}
	}

}


class _language_layer {
	const lang_layer_file = "lang.layer.sql";
	const no_lang_context_keyword = "{lang:no}";
	var $fields_of_interest = "/^varchar|text(.*)$/";
	var $no_lang_context_keyword = self::no_lang_context_keyword;
	var $master_db,$supported;
	
	// get fields which might be differed by language
	function get_candidate_lang_fields($only_used_fields = false) {
		$tables = sql()->usedb($this->master_db)->justtables(true);
		
		$candidates = array();
		
		foreach ($tables as $table) {
			$field_details = sql()->field_details($table);
			// echo $table."<br />".showtable($field_details)."<hr />";
			foreach ($field_details as $field_d) {
				
				$field = $field_d["Field"];
				$type = $field_d["Type"];
				$comment = $field_d["Comment"];
				$null = ($field_d["Null"]=="NO") ? "NOT NULL" : "NULL";
				$use = intval(!(strpos($comment,$this->no_lang_context_keyword) !== false));
				if (preg_match($this->fields_of_interest,$type)) {
					if ($only_used_fields) {
						if ($use) $candidates[$table][$field] = $field;  // used both as map and vector :-)
					} else {
						$candidates[$table][$field] = array("field"=>$field,"type"=>$type,"null"=>$null,"use"=>$use,"comment"=>$comment);
					}
				}
			}
		}
		
		return $candidates;
		
	}
	
	function lang_field_editor() {
		$candidates = $this->get_candidate_lang_fields();
		if ($_POST["lang_field_editor"]) {
			// print_r($_POST);
			foreach ($candidates as $table=>$fields) {
				foreach ($fields as $field_d) {
					$field = $field_d["field"];
					$type = $field_d["type"];
					$null = $field_d["null"];
					$state = isset($_POST["{$table}__{$field}"]);
					
					$comment = str_replace($this->no_lang_context_keyword,"",$comment);
					
					// check if state changed
					if ($state != $field_d["use"]) {
						if (!$state) {
							// add comment
							$comment .= $this->no_lang_context_keyword;
						} else {
							// remove comment
						}
						
						$candidates[$table][$field]["use"] = intval($state);
						$q = "ALTER TABLE `$table` CHANGE COLUMN `{$field}` `{$field}` {$type} {$null}  COMMENT '$comment';;";
						sql($q);
						echo $q."<br />";
						echo mysql_error();
					}
					
					
				}
			}
			
			echo $this->create_structure(true);
		}
		
		
		
		$tpl = '
			<form method="post">
				<input type="hidden" name="lang_field_editor" value="here" />
				<p>Select which columns are language differed:</p>
				<table border="0">
				${
					<tr><td colspan="3" style="background:#dddddd;"><strong>[#]</strong></td></tr>
					${
					<tr style="[use:tpl_color_marked]">
						<td>[field]</td>
						<td>[type]</td>
						<td><input name="[**.#]__[field]" type="checkbox" value="1" [use:tpl_value_checked]></td>
					</tr>
					}
				}
				</table>
				<input type="submit" value="Save" />
			</form>
		';
		
		return produce($tpl,$candidates);
		
		
	}
	
	function get_lang_fields() {
		$candidates = $this->get_candidate_lang_fields(true);
		return $candidates;
	}
	
	function __construct($master_db,$supported_languages) {
		$this->master_db = $master_db;
		$this->supported =  $supported_languages;
	}
	
	function grab_trigger($table,$event) {
		return sql("SHOW TRIGGERS IN `{$this->master_db}` WHERE `Table`='{$table}' AND `Timing`='AFTER' AND `Event`='{$event}';")->row();
	}
	
	function remove_trigger_translational_portion($trigger_code, $tag_start, $tag_end) {
		$tag_start = preg_quote($tag_start);
		$tag_end = preg_quote($tag_end);
		$tag_end = str_replace("/","\\/",$tag_end);
		$pattern = '/^BEGIN(.*)('.$tag_start.')(.*)('.$tag_end.')(.*)END$/msU';
		if (preg_match($pattern,$trigger_code,$matches)) {
			$trigger_code = $matches[1].$matches[5];
		};
		return $trigger_code;
	}
	
	function create_table_triggers($table) {
		if (!isset($this->lang_fields)) {
			$this->lang_fields = $this->get_lang_fields();
		}
		
		if (isset($this->lang_fields[$table])) {
			$fields = $this->lang_fields[$table];
			$primary_key = sql()->usedb($this->master_db)->primary_key($table);
		
			$end_translational_tag = "-- </translational>";
			
			$translational_update_tag = "-- <translational event='UPDATE'>";
			$translational_insert_tag = "-- <translational event='INSERT'>";
			$translational_delete_tag = "-- <translational event='DELETE'>";
			
			$update_trigger_name = "{$table}_lang_update";
			$insert_trigger_name = "{$table}_lang_insert";
			$delete_trigger_name = "{$table}_lang_delete";
		
			// fetch existing trigger code for table, and extract user part of code
			
			$update_trigger = $this->grab_trigger($table,"UPDATE");
			$insert_trigger = $this->grab_trigger($table,"INSERT");
			$delete_trigger = $this->grab_trigger($table,"DELETE");
			
			if (isset($update_trigger["Trigger"])) {
				$update_trigger_name = $update_trigger["Trigger"];
				$user_update_trigger = $update_trigger["Statement"];
				$user_update_trigger = $this->remove_trigger_translational_portion($user_update_trigger,$translational_update_tag,$end_translational_tag);
			}

			if (isset($insert_trigger["Trigger"])) {
				$insert_trigger_name = $insert_trigger["Trigger"];
				$user_insert_trigger = $insert_trigger["Statement"];
				$user_insert_trigger = $this->remove_trigger_translational_portion($user_insert_trigger,$translational_insert_tag,$end_translational_tag);
			}

			if (isset($delete_trigger["Trigger"])) {
				$delete_trigger_name = $delete_trigger["Trigger"];
				$user_delete_trigger = $delete_trigger["Statement"];
				$user_delete_trigger = $this->remove_trigger_translational_portion($user_delete_trigger,$translational_delete_tag,$end_translational_tag);
			}
	
			
			$update = "
			-- UPDATE TRIGGER FOR {$table} --
			DROP TRIGGER IF EXISTS `{$this->master_db}`.`{$update_trigger_name}` ;;
			
			CREATE TRIGGER `{$this->master_db}`.`{$update_trigger_name}`
			AFTER UPDATE ON `{$this->master_db}`.`{$table}`
			FOR EACH ROW
			BEGIN
			{$translational_update_tag}
			";
			
			$insert = "
			-- INSERT TRIGGER FOR {$table} --
			DROP TRIGGER IF EXISTS `{$this->master_db}`.`{$insert_trigger_name}` ;;
			
			CREATE TRIGGER `{$this->master_db}`.`{$insert_trigger_name}`
			AFTER INSERT ON `{$this->master_db}`.`{$table}`
			FOR EACH ROW
			BEGIN
			{$translational_insert_tag}
			";
			
			$delete = "
			-- DELETE TRIGGER FOR {$table} --
			DROP TRIGGER IF EXISTS `{$this->master_db}`.`{$delete_trigger_name}` ;;
			
			CREATE TRIGGER `{$this->master_db}`.`{$delete_trigger_name}`
			AFTER DELETE ON `{$this->master_db}`.`{$table}`
			FOR EACH ROW
			BEGIN
			{$translational_delete_tag}
			";
		
			
			foreach ($fields as $field) {
			
				$update.="
				IF OLD.`{$field}` <> NEW.`{$field}` THEN
					INSERT INTO `{$this->master_db}`.`translational_log` (tbl,col,id,act,date)
					VALUES ('{$table}','{$field}',NEW.`{$primary_key}`,'UPDATE',NOW())
					ON DUPLICATE KEY UPDATE act='UPDATE', date = now() ;
				END IF;
				";
				
				$insert.="
				INSERT INTO `{$this->master_db}`.`translational_log` (tbl,col,id,act,date)
				VALUES ('{$table}','{$field}',NEW.`{$primary_key}`,'INSERT',NOW())
				ON DUPLICATE KEY UPDATE act='INSERT', date = now() ;
				";
				
				$delete.="
				INSERT INTO `{$this->master_db}`.`translational_log` (tbl,col,id,act,date)
				VALUES ('{$table}','{$field}',OLD.`{$primary_key}`,'DELETE',NOW())
				ON DUPLICATE KEY UPDATE act='DELETE', date = now() ;
				";
				
			}
			$eol = "\n";
			
			$end_trigger = $eol."END;;".$eol;
			
			
			$update .= $eol.$end_translational_tag.$user_update_trigger.$end_trigger;
			$insert .= $eol.$end_translational_tag.$user_insert_trigger.$end_trigger;
			$delete .= $eol.$end_translational_tag.$user_delete_trigger.$end_trigger;
			
			$q = $eol."-- TRANSLATIONAL TRIGGERS FOR {$table} --\n-- SET DELIMITER TO ;; -- \n".level_indent($insert.$update.$delete);
			
			
			return $q;
			
		} else {
			return "-- NO TRIGGERS NEEDED FOR $table -- \n";
		}
	}
	
	function create_triggers($return = false) {
		$tables = array_keys($this->get_lang_fields());
		foreach ($tables as $table) {
			$q .= $this->create_table_triggers($table);
		}
		if (!$return) {
			 sql()->multi($q,";;");
		}
		return $q;
	}

	function create_translational_log($return = false) {
		$q = level_indent("
			DROP TABLE IF EXISTS `{$this->master_db}`.`translational_log`;;
			CREATE TABLE IF NOT EXISTS `{$this->master_db}`.`translational_log` (
			  `tbl` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT '{$this->no_lang_context_keyword}',
			  `col` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT '{$this->no_lang_context_keyword}',
			  `id` int(4) NOT NULL,
			  `act` ENUM('UPDATE','INSERT','DELETE') COLLATE utf8_unicode_ci NOT NULL COMMENT '{$this->no_lang_context_keyword}',
			  `date` datetime NOT NULL,
			  PRIMARY KEY(`col`,`tbl`,`id`),
			  KEY `tbl_col` (`tbl`,`col`),
			  KEY `id` (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;;
		");
		
		if (!$return) {
			sql()->multi($q,";;",true);
		}
		return $q;
	}
	
	function create_structure($return = false) {
		// create translational log table and triggers respectfully
		$queries .= $this->create_translational_log( $return );
		
		// create language surogate databases
		foreach ($this->supported as $lang) {
			$$lang = new _language_surogate($this->master_db,"{$this->master_db}_{$lang}",$this);
			$queries .= $$lang->create_structure( $return );
		}
		
		// $queries .= $this->create_triggers( $return );
		
		return $queries;
	}
	
	function update_translational_tables( $return = false ) {		
			
		foreach ($this->supported as $lang) {
			$$lang = new _language_surogate($this->master_db,"{$this->master_db}_{$lang}",$this);
			
			$queries .= $$lang->update_translational_structure( $return )."\n";
			$queries .= $$lang->update_translational_table( $return )."\n";
		}
		
		if (!$return) {
			sql()->multi($queries,";;",true);
		}
		
		return $queries;
	}
	
	function create_structure_with_info() {
		$s = micronow();
		sql()->query_reset();
		$out .= htmlentities($this->create_structure( true ));
		$e = microdiff($s);
		$q = sql()->query_count();
		$t = sql()->query_worktime();
		$out .= "-- Execution took $e ms\n";
		$out .= "-- $q queries executed in $t ms\n";
		return $out;
	}
	
	function update_structure($return = false) {
		
		// update surogate and slave views
		foreach ($this->supported as $lang) {
			$$lang = new _language_surogate($this->master_db,"{$this->master_db}_{$lang}",$this);
			$queries .= $$lang->update_structure( $return )."\n";
		}
		
		$queries .= "USE `{$this->master_db}`;;\n";
		
		if (!$return) {
			sql()->multi($queries,";;");
		}
		
		return $queries;
	}
	
	function remove_comments($return = false) {
	
	}
	
	function drop_translational_log($return = false) {
		$queries .= "-- DROP translational log -- \nDROP TABLE IF EXISTS `{$this->master_db}`.translational_log;;\n";
		if (!$return) {
			sql()->multi($queries,";;");
		}
		return $queries;
	}
	
	function drop_table_triggers($table) {
		if (!isset($this->lang_fields)) {
			$this->lang_fields = $this->get_lang_fields();
		}
		
		if (isset($this->lang_fields[$table])) {
			$fields = $this->lang_fields[$table];
		
			$end_translational_tag = "-- </translational>";
			
			$translational_update_tag = "-- <translational event='UPDATE'>";
			$translational_insert_tag = "-- <translational event='INSERT'>";
			$translational_delete_tag = "-- <translational event='DELETE'>";
			
			$update_trigger_name = "{$table}_lang_update";
			$insert_trigger_name = "{$table}_lang_insert";
			$delete_trigger_name = "{$table}_lang_delete";
		
			// fetch existing trigger code for table, and extract user part of code
			
			$update_trigger = $this->grab_trigger($table,"UPDATE");
			$insert_trigger = $this->grab_trigger($table,"INSERT");
			$delete_trigger = $this->grab_trigger($table,"DELETE");
			
			// assume nothing has to be kept...
			$keep_update = $keep_insert = $keep_delete = false;
			
			if (isset($update_trigger["Trigger"])) {
				if ( $update_trigger["Trigger"] != $update_trigger_name ) {
					$keep_update = true;
				}
				$update_trigger_name = $update_trigger["Trigger"];
				$user_update_trigger = $update_trigger["Statement"];
				$user_update_trigger = $this->remove_trigger_translational_portion($user_update_trigger,$translational_update_tag,$end_translational_tag);
			}

			if (isset($insert_trigger["Trigger"])) {
				if ( $insert_trigger["Trigger"] != $insert_trigger_name ) {
					$keep_insert = true;
				}
				$insert_trigger_name = $insert_trigger["Trigger"];
				$user_insert_trigger = $insert_trigger["Statement"];
				$user_insert_trigger = $this->remove_trigger_translational_portion($user_insert_trigger,$translational_insert_tag,$end_translational_tag);
			}

			if (isset($delete_trigger["Trigger"])) {
				if ( $delete_trigger["Trigger"] != $delete_trigger_name ) {
					$keep_delete = true;
				}
				$delete_trigger_name = $delete_trigger["Trigger"];
				$user_delete_trigger = $delete_trigger["Statement"];
				$user_delete_trigger = $this->remove_trigger_translational_portion($user_delete_trigger,$translational_delete_tag,$end_translational_tag);
			}
	
			
			$update = "
			-- UPDATE TRIGGER FOR {$table} --
			DROP TRIGGER IF EXISTS `{$this->master_db}`.`{$update_trigger_name}` ;;
			";
			
			if ($keep_update) {
				$update .= "
				CREATE TRIGGER `{$this->master_db}`.`{$update_trigger_name}`
				AFTER UPDATE ON `{$this->master_db}`.`{$table}`
				FOR EACH ROW
				BEGIN
				{$user_update_trigger}
				END;;
				";
			}
			
			$insert = "
			-- INSERT TRIGGER FOR {$table} --
			DROP TRIGGER IF EXISTS `{$this->master_db}`.`{$insert_trigger_name}` ;;
			";
			
			if ($keep_insert) {
				$insert .= "
				CREATE TRIGGER `{$this->master_db}`.`{$insert_trigger_name}`
				AFTER INSERT ON `{$this->master_db}`.`{$table}`
				FOR EACH ROW
				BEGIN
				{$user_insert_trigger}
				END;;
				";
			};
			
			
			$delete = "
			-- DELETE TRIGGER FOR {$table} --
			DROP TRIGGER IF EXISTS `{$this->master_db}`.`{$delete_trigger_name}` ;;
			";
			
			if ($keep_delete) {
				$delete .= "
				CREATE TRIGGER `{$this->master_db}`.`{$delete_trigger_name}`
				AFTER DELETE ON `{$this->master_db}`.`{$table}`
				FOR EACH ROW
				BEGIN
				{$user_delete_trigger}
				END;;
				";	
			}			
			
			$q = $eol."-- DROP TRANSLATIONAL TRIGGERS FOR {$table} --\n".level_indent($insert.$update.$delete);
			
			
			return $q;
			
		} else {
			return "-- NO TRIGGERS FOUND FOR $table -- \n";
		}
	}
	
	
	function drop_triggers($return = false) {
		$tables = array_keys($this->get_lang_fields());
		foreach ($tables as $table) {
			$q .= $this->drop_table_triggers($table)."\n";
		}
		if (!$return) {
			 sql()->multi($q,";;");
		}
		return $q;
	}
	
	function drop_language_layers( $return = false ) {
	
		$queries .= $this->drop_triggers( $return )."\n"; 
		$queries .= $this->drop_translational_log( $return )."\n";
		foreach ($this->supported as $lang) {
			$$lang = new _language_surogate($this->master_db,"{$this->master_db}_{$lang}",$this);
			$queries .= $$lang->truncate_layer( $return )."\n";
		}
		
		if (!$return) {
			sql()->multi($queries,";;");
		}
		
		return $queries;
		
	}

	
}

?>