<?php

abstract class PreparedStatement {
	abstract function bindValue($name, $value, $type);
}
class SQLiteDatabase_PreparedStatement extends PreparedStatement {
	
	private $stmt = null;
	function __construct($stmt) {
		$this->stmt = $stmt;
	}
	function bindValue($name, $value, $type) {
		$type = SQLiteDatabase::vartypetosqlite( $type );
		$this->stmt->bindValue($name, $value, $type);
	}
	function query() { 
		$rs = $this->stmt->execute();
		$results = array();
		while($arr = $rs->fetchArray(SQLITE3_ASSOC)) { 
			$results[] = $arr; 
		}
		$rs->finalize();
		$this->stmt->close();
		return $results;
	}
	function execute() {
		$rs = $this->stmt->execute();
		$rs->finalize();
		$this->stmt->close();
	}
}
class MysqliDatabase_PreparedStatement extends PreparedStatement {
	
	private $stmt = null;
	private $order = null;
	private $orderbindings = null;
	function __construct($stmt, $order) {
		$this->stmt = $stmt;
		$this->order = $order;
		$this->orderbindings = array();
	}
	function bindValue($name, $value, $type) {
		$this->orderbindings[$name] = array(
			"type" => MysqliDatabase::vartypetoletter($type),
			"val" => $value
		);
	}
	private function refValues($arr){
		if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
		{
			$refs = array();
			foreach($arr as $key => $value)
				$refs[$key] = &$arr[$key];
			return $refs;
		}
		return $arr;
	}
	private function finishBinds() {
		
		if (count($this->order) == 0) { return; }
		$types = "";
		foreach ($this->order as $key => $o) { $types .= $this->orderbindings[$o]['type']; }

		$a = array($types);
		foreach ($this->order as $key => $o) { $a[] = $this->orderbindings[$o]['val']; }

		//$r = $this->refValues($a);
		//print_r($r);
		//print_r($this->order);
		//print_r($this->orderbindings);
		//print_r($a);
		call_user_func_array(array($this->stmt, "bind_param"), $this->refValues($a));

		//$ref    = new ReflectionClass('mysqli_stmt'); 
		//$method = $ref->getMethod("bind_param"); 
		//$method->invokeArgs($this->stmt, $this->refValues($a)); 
	}
	function query() { 
		$this->finishBinds();
		$b = $this->stmt->execute();
		if (!$b) { return array(); }
		
		// PHP 5.3.0 only...
		//$rs = $this->stmt->get_result();
		//$results = array();
		//while($arr = $rs->fetch_array(MYSQLI_ASSOC)) { 
		//	$results[] = $arr; 
		//}
		//return $results;
		
		// Older PHP...
		$row = array();
		$params = array();
		$meta = $this->stmt->result_metadata(); 
		while ($field = $meta->fetch_field()) { 
			$params[] = &$row[$field->name]; 
		} 
		call_user_func_array(array($this->stmt, 'bind_result'), $params); 

		$results = array();
		while ($this->stmt->fetch()) { 
			$c = array();
			foreach($row as $key => $val) {
				$c[$key] = $val; 
			} 
			$results[] = $c; 
		}
		//$this->stmt->free_result();
		$this->stmt->close();
		return $results;
	}
	function execute() {
		$this->finishBinds();
		$this->stmt->execute();
		$this->stmt->free_result();
		$this->stmt->close();
	}
}
class SQLiteDatabase extends Database {
	private $db;
	public $type = Database::TYPE_SQLITE;

	function __construct($name) {
		$this->db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . "/" . $name, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
	}

	function query($sql) 
	{
		$results = array();
		$rs = $this->db->query($sql);
		while($row = $rs->fetchArray(SQLITE3_ASSOC)) { 
			$results[] = $row;
		}
		$rs->finalize();
		return $results;
	}
	function exec($sql) {
		return $this->db->exec($sql);
	}
	function prepare($sql) {
		$stmt = $this->db->prepare($sql);
		if (!$stmt) {
			//echo "wut";
		}
		return new SQLiteDatabase_PreparedStatement($stmt);
	}
	function lastInsertRowID() {
		return $this->db->lastInsertRowID();
	}
	function close() {
		$this->db->close();
	}

	static function vartypetosqlite($v) {
		if ($v == Database::VARTYPE_STRING) {
			return SQLITE3_TEXT;
		} else if ($v == Database::VARTYPE_INTEGER) {
			return SQLITE3_INTEGER;
		}
		return SQLITE3_TEXT;
	}
}

class MysqliDatabase extends Database {
	private $db;
	public $type = Database::TYPE_MYSQL;
	
	function __construct($server, $user, $password, $database) {
		$this->db = new mysqli($server, $user, $password);

		if ($this->db->connect_errno) {
			echo "Failed to connect to MySQL: (" . $this->db->connect_errno . ") " . $this->db->connect_error;
			die();
		}

		//echo $_SERVER['REQUEST_URI'];
		if ($_SERVER['REQUEST_URI'] == "/install.php") {
			$this->exec("CREATE DATABASE IF NOT EXISTS " . $database);
		}

		$selected = $this->db->select_db( $database );
		if (!$selected) {
			echo ("Could not select MySQL database: " . $database . ". Did you run the install script? " );
		}

		// set charset unicode
		$this->db->set_charset("utf8");
	}

	function query($sql) 
	{
		$results = array();
		$rs = $this->db->query($sql);
		if (!$rs) { echo "mysqli error: " . $this->db->error; die(); }
		while ($row = $rs->fetch_assoc()) {
			$results[] = $row;
		}
		return $results;
	}
	function exec($sql) {
		$rs = $this->db->query($sql);
		if (!$rs) { echo "mysqli error: " . $this->db->error; die(); }
		return $rs;
	}
	function prepare($sql) {
		$order = array();
		$offset = 0;
		$count = 0;
		$osql = $sql;
		while (($start = strpos($sql, ":", $offset)) !== FALSE) {
			$end1 = strpos($sql, ",", $start);
			if ($end1 === FALSE) { $end1 = PHP_INT_MAX; }
		
			$end2 = strpos($sql, " ", $start); // space
			if ($end2 === FALSE) { $end2 = PHP_INT_MAX; }

			$end3 = strpos($sql, "\n", $start); // tab
			if ($end3 === FALSE) { $end3 = PHP_INT_MAX; }

			$end4 = strpos($sql, ")", $start);
			if ($end4 === FALSE) { $end4 = PHP_INT_MAX; }
			
			$end5 = strpos($sql, ";", $start);
			if ($end5 === FALSE) { $end5 = PHP_INT_MAX; }
	
			$end = min(array($end1, $end2, $end3, $end4, $end5));
			$len = $end - $start;

			$name = substr($sql, $start, $len);
			//echo $name . "<br/>";
			//echo "len: " . $len . "<br/>";;
			
			//$sql = str_replace($name, " ? ", $sql);

			$pos = strpos($sql, $name);
			$sql = substr_replace($sql, " ? ", $pos, strlen($name));

			$offset = 0;
			$order[] = $name;

			$count++;
			if ($count >= 100) { break; }
		}
		//print_r($order);

		$stmt = $this->db->prepare($sql);
		if ($stmt == false) {
			echo "error: " . $sql;
			echo " " . $osql;
			print_r($order);
			die();
		}
		return new MysqliDatabase_PreparedStatement($stmt, $order);
	}
	function lastInsertRowID() {
		return $this->db->insert_id;
	}
	function escape_string($str) { 
		return $this->db->escape_string($str); 
	}

	function close() {

	}

	static function vartypetoletter($v) {
		if ($v == Database::VARTYPE_STRING) {
			return "s";
		} else if ($v == Database::VARTYPE_INTEGER) {
			return "i";
		}
		return "s";
	}
}

class Database
{
	const TYPE_SQLITE = 0;
	const TYPE_MYSQL = 1;

	const VARTYPE_STRING = 100;
	const VARTYPE_INTEGER = 101;

	function __construct() {

	}

	function query($sql) { }
	function escape_string($str) { return $str; }

	public static $s_instance = null;
	public static function getInstance() 
	{
		if (Database::$s_instance == null) 
		{ 
			global $impresslist_databaseType;
			if ($impresslist_databaseType == Database::TYPE_SQLITE) 
			{
				global $impresslist_sqliteDatabaseName;
				Database::$s_instance = new SQLiteDatabase($impresslist_sqliteDatabaseName);
			} 
			else if ($impresslist_databaseType == Database::TYPE_MYSQL) 
			{
				global $impresslist_mysqlServer;
				global $impresslist_mysqlUsername;
				global $impresslist_mysqlPassword;
				global $impresslist_mysqlDatabaseName;
				Database::$s_instance = new MysqliDatabase($impresslist_mysqlServer, $impresslist_mysqlUsername, $impresslist_mysqlPassword, $impresslist_mysqlDatabaseName);


			}
		}
		return Database::$s_instance;
	}



	public function sql() {
		global $impresslist_mysqlDatabaseName;

		$sql = "";
		$sql .= "/*\n";
		$sql .= "  impress[] backup.\n";
		$sql .= "*/\n\n";

		if ($this->type == Database::TYPE_SQLITE) { 

			$convert_to_mysql = true;

			$tables = $this->query("SELECT name FROM sqlite_master WHERE type='table';");
			foreach ($tables as $table) {
				$name = $table['name'];
				if (strpos($name, "sqlite_", 0) !== FALSE) { continue; }
				
				$sql .= "CREATE TABLE IF NOT EXISTS {$name} (\n";

					$fields = $this->query("PRAGMA table_info({$name})");
					$count = 0;
					foreach ($fields as $field) {
						//echo $field['name'];
						//echo "<br/>";

						$fname = $field['name'];
						$ftype = $field['type'];
						$fnn = ($field['notnull']==1)?"NOT NULL":"";
						$fdefault = ($field['dflt_value'] != "")?("DEFAULT " . $field['dflt_value']): "";
						$fpk = ($field['pk']==1)?"PRIMARY KEY":"";

						if ($convert_to_mysql) { 
							
							if ($ftype == "TIMESTAMP") {
								$ftype = "INT(11)";
							}
							if ($ftype == "TEXT" && strlen($fdefault) > 0) {
								$fdefault = "";
							}
						}


						if ($count > 0) { $sql .= ",\n"; }
						$sql .= "	`{$fname}` {$ftype} {$fpk} {$fnn} {$fdefault}";
						$count++;
					}

				$sql .= "\n);\n\n";

				$rows = $this->query("SELECT * FROM {$name};");
				foreach ($rows as $row) {
					$values = "";
					$count = 0;
					foreach ($row as $key => $val) { 
						if ($count > 0) {
							$values .= ",";
						}
						$values .= "'" . addslashes($val) . "'";
						$count++;
					}
					$sql .= "INSERT IGNORE INTO {$name} VALUES (" . $values . " ); \n";
				}
				$sql .= "\n";

				//print_r($fields);
				
				
			}
			//echo $sql;

			
			
		} else if ($this->type == Database::TYPE_MYSQL) {



			$tables = $this->query("SHOW TABLES;");

			foreach ($tables as $table) {

				$name = $table['Tables_in_' . $impresslist_mysqlDatabaseName];

				$columnSql = "SHOW COLUMNS FROM " . $impresslist_mysqlDatabaseName . "." . $name . ";";
				//echo $columnSql;
				$columns = $this->query($columnSql);
				
				$sql .= "CREATE TABLE IF NOT EXISTS {$name} (\n";
					$count = 0;
					foreach ($columns as $column) {
						$fname = $column['Field'];
						$ftype = $column['Type'];
						$fpk = ($column['Key'] == "PRI")?"PRIMARY KEY":"";
						$fnn = ($column['Null'] == "NO")?"NOT NULL":"";
						$fdefault = (strlen($column['Default'])>0)?("DEFAULT ".$column['Default']):"";

						if ($count > 0) { $sql .= ",\n"; }
						$sql .= "	`{$fname}` {$ftype} {$fpk} {$fnn} {$fdefault}";
						$count++;
					}
				$sql .= "\n);\n\n";

				// content
				$rows = $this->query("SELECT * FROM {$name};");
				foreach ($rows as $row) {
					$values = "";
					$count = 0;
					foreach ($row as $key => $val) { 
						if ($count > 0) {
							$values .= ",";
						}
						$values .= '"' . addslashes($val) . '"';
						$count++;
					}
					$sql .= "INSERT IGNORE INTO {$name} VALUES (" . $values . " ); \n";
				}
				$sql .= "\n";
			}


			
			//echo $sql;
			//$result = $sql;
			//serve_file("impresslist-backup-sql-" . date("c") . ".sql", $sql, "txt");


		}
		return $sql;
	}

}

?>