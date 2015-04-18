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
		$types = "";
		foreach ($this->order as $o) { $types .= $this->orderbindings[$o]['type']; }
		$a = array($types);
		foreach ($this->order as $o) { $a[] = $this->orderbindings[$o]['val']; }
		call_user_func_array(array($this->stmt, "bind_param"), $this->refValues($a));
	}
	function query() { 
		$this->finishBinds();
		$this->stmt->execute();
		$rs = $this->stmt->get_result();

		$results = array();
		while($arr = $rs->fetch_array(MYSQLI_ASSOC)) { 
			$results[] = $arr; 
		}
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
		$this->db = new mysqli($server, $user, $password, $database);

		if ($this->db->connect_errno) {
			echo "Failed to connect to MySQL: (" . $this->db->connect_errno . ") " . $this->db->connect_error;
			die();
		}
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
		//$count = 0;
		while (($start = strpos($sql, ":", $offset)) !== FALSE) {
			$end = strpos($sql, " ", $start);
			if ($end === FALSE || $end == strlen($sql) - 1) {
				$end = strpos($sql, ";", $start);
			}
			$name = substr($sql, $start+1, $end - ($start+1));
			$sql = str_replace(":" . $name, " ? ", $sql);
			$offset = 0;
			$order[] = $name;
			//echo $sql . "<br/>";	
			//$count++;
			//if ($count >= 100) { break; }
		}
		//print_r($order);

		$stmt = $this->db->prepare($sql);
		return new MysqliDatabase_PreparedStatement($stmt, $order);
	}
	function lastInsertRowID() {
		return $this->db->insert_id;
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
}

?>