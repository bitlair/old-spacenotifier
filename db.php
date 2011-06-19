<?php
class DB extends PDO {
	
	private static $instance_app;
	private $rows;
	private $last_error;
	protected static $db_driver = DB_DRIVER;
	protected static $db_database = DB_DATABASE;
	protected static $db_host = DB_HOST;
	protected static $db_username = DB_USERNAME;
	protected static $db_password = DB_PASSWORD;
	
	/**
	 * Initialize the connection to the database server.
	 *
	 * @param string $driver
	 * @param string $database
	 * @param string $host
	 * @param string $username
	 * @param string $password
	 * @return DB
	 */
	public static function init($driver, $database, $host, $username, $password) {
		
		$dsn = $driver . ":dbname=" . $database . ";host=" . $host;
		
		try {
		    $instance = new self($dsn, $username, $password);
		}
		catch (PDOException $e) {
			if(IS_DEV == 1) {
		    	echo 'Connection failed: ' . $e->getMessage();
			}
			else {
				echo 'Connection failed.';
			}
		    exit;
		}
		
		/**
		 * Set connection type to the type of the database.
		 */
		$res = $instance->row("SHOW VARIABLES LIKE 'character_set_database'");
		if($res !== false) {
			$instance->query("SET NAMES '".$res->Value."'");
		}
		
		return $instance;
	}
	
	/**
	 * Returns an instance of this class (singleton)
	 *
	 * @return DB
	 */
	public static function & get($type = 'app') {
		
		if($type == 'app') {
			if(!isset(self::$instance_app)) {
				self::$instance_app = self::init(DB_DRIVER, DB_DATABASE, DB_HOST, DB_USERNAME, DB_PASSWORD);
			}
			return self::$instance_app;
		}
		
		echo "Connection failed.";
		exit;
	}

	/**
	 * Returns one or more rows with objects, example usage:
	 * 
	 * foreach ($db->query("SELECT * FROM table") as $o) {
	 * 		echo $o->field;
	 * } 
	 *
	 * This method will use generate a prepared query, example usage:
	 * 
	 * foreach ($db->query("SELECT * FROM table WHERE value1 > ? AND value2 < ?", array (13,37)) as $o) {
	 * 		echo $o->field;
	 * } 
	 * 
	 * Instead of an array, you can also use a single value and it will be automaticly wrapped for you
	 * 
	 * @param string $query
	 * @param array $values to be used in prepared statement
	 * @return bool|array with stdObj's (to be used in foreach for example)
	 */
	public function query($query, $values = array()) {
		$q = $this->queryPrepared($query, $values);
				
		$tmp = $q->fetchAll(PDO::FETCH_OBJ);
		$this->rows = count($tmp);
		
		return $tmp;
	}
	
	/**
	 * Will only return one object for the given query
	 *
	 * @param string $query
	 * @param array $values to be used in prepared statement
	 * @return stdObj|boolean
	 */
	public function row($query, $values = array()) {
		/**
		 * TODO: logging
		 */
		
		$q = $this->queryPrepared($query, $values);
		$res = $q->fetchAll(PDO::FETCH_OBJ);
		if(count($res) === 1) {
			return $res[0];
		}
		else {
			return false;
		}
	}
	
	/**
	 * Generate prepared query/statement
	 *
	 * @param string $query
	 * @param array $values
	 * @return PDOStatement
	 */
	private function queryPrepared($query, $values = array(), $handle_error = true) {
		$q = parent::prepare($query);
		$q->execute($this->fixPreparedValuesArray($values));
		
		if($handle_error && intval($q->errorCode()) != 0) {
			$this->handleError($q->errorInfo());
		}
		
		return $q;
	}
	
	/**
	 * Returns the value of one column
	 *
	 * @param string $query
	 * @param array $values
	 * @return string
	 */
	public function column($query, $values = array()) {
		$q = $this->queryPrepared($query, $values);
				
		$tmp = $q->fetchAll(PDO::FETCH_COLUMN);
		return $tmp[0];
	}
	
	/**
	 * Insert fields into a given table
	 *
	 * Example usage:
	 * 
	 * $ar = array ("foo" => $something, "bar" => "wooyeah");
	 * 
	 * $db->insert("sometable", $ar);
	 * 
	 * @param string $table
	 * @param array $insert_array
	 * @return PDOStatement
	 */
	public function insert($table, $insert_array) {		
		$insert_fields = '';
		$insert_values = '';
		$prepare_values = array();
		
		foreach($insert_array as $_key => $_value) {
			$insert_fields .= (empty($insert_fields) ? '' : ',') . "`{$_key}`";
			$insert_values .= (empty($insert_values) ? '' : ',') . "?";
			$prepare_values[] = $_value;
		}

		$q = "INSERT INTO $table ($insert_fields) VALUES ($insert_values)";
		return $this->queryPrepared($q, $prepare_values);
	}
	
	/**
	 * Update a row
	 *
	 * Example usage:
	 * 
	 * $ar = array ("foo" => $something, "bar" => "wooyeah");
	 * 
	 * $db->update("sometable", $ar, "id = ?", array(1337));
	 * 
	 * @param string $table
	 * @param array $update_array
	 * @param string $where
	 * @param array $where_values
	 * @return PDOStatement
	 */
	public function update($table, $update_array, $where, $where_values = array()) {
		/**
		 * TODO: logging
		 */
		
		$update_fields = '';
		$prepare_values = array();
		
		foreach($update_array as $_key => $_value) {
			$update_fields .= (empty($update_fields) ? '' : ',') . "`{$_key}`=?";
			$prepare_values[] = $_value;
		}
		
		$prepare_values = array_merge($prepare_values, $this->fixPreparedValuesArray($where_values));

		$q = "UPDATE $table SET $update_fields WHERE $where";
		return $this->queryPrepared($q, $prepare_values);
	}
	
	/**
	 * Delete a row
	 * 
	 * Example usage:
	 * 
	 * $db->delete("sometable", "id = ?", array(1337));
	 * 
	 * or $db->delete("sometable, "id = ?", 1337);
	 * 
	 * @param string $table
	 * @param string $where
	 * @param array $where_values
	 * @return PDOStatement
	 */
	public function delete($table, $where, $where_values = array()) {
		/**
		 * TODO: logging
		 */
		
		$q = "DELETE FROM $table WHERE $where";
		
		return $this->queryPrepared($q, $where_values);
	}
	
	/**
	 * Fix fields
	 *
	 * @param mixed $fix
	 * @return array
	 */
	private function fixPreparedValuesArray($fix) {
		if(!is_array($fix)) {
			return array($fix);
		}
		else {
			return $fix;
		}
	}
	
	/**
	 * Gets the amount of rows which matched the SELECT query.
	 *
	 * @return int
	 */
	public function rows() {
		return $this->rows;
	}
	
	/**
	 * Determines whether a query matches with at least one row.
	 *
	 * @param string $query
	 * @param array $values
	 * @return bool
	 */
	public function exists($query, $values = array()) {
		$q = $this->query($query . " LIMIT 1", $values);
		
		return ($this->rows() >= 1);
	}

	/**
	 * Get the last error
	 *
	 * @return $error
	 */
	public function getError() {
		$e = $this->last_error;
		
		if ($e[1] == null) {
			return false;
		}
		else {
			return $e[0] . " :: " . $e[1] . " :: " . $e[2];
		}
	}
	
	
	public function handleError($e) {
		$this->last_error = $e;
	}
}
?>
