<?php
/*
 *      sql.lib.php 2011-10-27
 *      
 *      Copyright 2011 Daryl Fain <daryl@99years.com>
 *      
 *      Redistribution and use in source and binary forms, with or without
 *      modification, are permitted provided that the following conditions are
 *      met:
 *      
 *      * Redistributions of source code must retain the above copyright
 *        notice, this list of conditions and the following disclaimer.
 *      * Redistributions in binary form must reproduce the above
 *        copyright notice, this list of conditions and the following disclaimer
 *        in the documentation and/or other materials provided with the
 *        distribution.
 *      * Neither the name of the  nor the names of its
 *        contributors may be used to endorse or promote products derived from
 *        this software without specific prior written permission.
 *      
 *      THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 *      "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 *      LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 *      A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 *      OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 *      SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 *      LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 *      DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 *      THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 *      (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 *      OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *      
 *      
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
