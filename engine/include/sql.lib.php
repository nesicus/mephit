<?php
	/* sql.lib.php
	 * date: 2011-02-16
	 * description: simple SQL wrapper and abstraction
	*/
	
	if (!defined('IN_RECOVERY')) die();
	
	// define the query types
	define('SQL_NONE', 1);
	define('SQL_ALL', 2);
	define('SQL_FIRST', 3);

	// define the query formats
	define('SQL_ASSOC', 1);
	define('SQL_INDEX', 2);

	class sqlClass {
		private $db = NULL;
		public $result = NULL;
		public $error = NULL;
		public $record = NULL;
		public $connected = FALSE;
		
		// class constructor
		function __construct() {
		}

		/***** function: connect()
		*****/
		function connect($dsn) {
			if (is_resource($this->db)) return $this->db;
			$db = mysql_connect($dsn['hostname'], $dsn['username'], $dsn['password']);
			if (is_resource($db)) {
				$this->db = $db;
			} else {
				return FALSE;
			}
				
			if (mysql_select_db($dsn['database'], $this->db)) {
				$this->connected = TRUE;
				return $this->db;
			} else {
				return FALSE;
			}
		}
		/***** function: disconnect()
		*****/
		function disconnect() {
			@mysql_close($this->db);
			return TRUE;
		}
		
		/***** function: sanitize()
		*****/
		function sanitize($str) {
			return mysql_real_escape_string($str, $this->db);
		}
		
		/***** function: query()
		*****/
		function query($query, $type = SQL_NONE, $format = SQL_INDEX) {
			$this->record = array();
			$_data = array();
			
			// determine fetch mode (index or associative)
			$_fetchmode = ($format == SQL_ASSOC) ? MYSQL_ASSOC : MYSQL_NUM;
        
			$this->result = mysql_query($query, $this->db);
			if (FALSE == $this->result) {
				$this->error = mysql_error();
				return FALSE;
			}

			switch ($type) {
				case SQL_ALL:
					// obtain all records
					while($_row = mysql_fetch_array($this->result, $_fetchmode)) {
						$_data[] = $_row;   
					}
					mysql_free_result($this->result);            
					$this->record = $_data;
					break;

				case SQL_FIRST:
					// obtain the first record
					$this->record = mysql_fetch_array($this->result, $_fetchmode);
					break;

				case SQL_NONE:
					// do nothing
				default:
					// records will be looped over with next()
					break;   
			}
			
			return TRUE;
		}
		
		/***** function: next
		*****/
		function next($format = SQL_INDEX) {
			// fetch mode (index or associative)
			$_fetchmode = ($format == SQL_ASSOC) ? MYSQL_ASSOC : MYSQL_NUM;
			if ($this->record = mysql_fetch_array($this->result, $_fetchmode)) {
				return TRUE;
			} else {
				mysql_free_result($this->result);
				return FALSE;
			}
		}
	}

?>
