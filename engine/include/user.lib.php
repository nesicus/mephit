<?php
	/*
		project: recovery
		date: 2011-02-18
		version: 0.0.1
		
		file: user.lib.php
		date: 2011-02-18
		version: 0.0.1
		author: daryl
		description: class for handling user and authentication structures
	*/
	
	if (!defined('IN_RECOVERY')) die();
	
	define('USERNAME', 1);
	define('TIMEZONE', 2);
	
	// user class
	class userClass {
		private $sql = NULL;
		public $error;
		
		// class constructor
		public function __construct() {
			global $_sql;
			$this->sql = $_sql;
		}
		
		public function getUID($user) {
			$query = sprintf("SELECT id FROM users WHERE name = '%s'", $this->sql->sanitize($user));
			$this->sql->query($query, SQL_FIRST);
			return $this->sql->record[0];
			return FALSE;
		}
		
		public function deactivatePassword($uid) {
			// do nothing
		}
		
		public function login($user, $pass) {
			$success = FALSE;
			$crypto = new cryptoClass();
			
			if ($uid = $this->getUID($user)) {
				$query = sprintf("SELECT password, newpass FROM users WHERE id = %s", 
					$this->sql->sanitize($uid));
					
				$this->sql->query($query, SQL_FIRST, SQL_ASSOC);
				$userSecret = $this->sql->record;

				if ($crypto->checkPassword($pass, $userSecret['password'])) {
					$success = TRUE;
				} else if ($crypto->checkPassword($pass, $userSecret['newpass'])) {
					$this->deactivatePassword($uid);
					$success = TRUE;
				}
				
				if ($success) {
					$query = sprintf("UPDATE users SET activity = %d WHERE id = %d", 
						$this->sql->sanitize(time()), $this->sql->sanitize($uid));
					$this->sql->query($query);
					return $uid;
				}
			}
			return FALSE;
		}
		
		public function get($uid = 0, $type = FALSE) {
			// check for valid user id
			if (!is_numeric($uid)) return FALSE;
			
			// if no type set, return user object
			if (FALSE == $type) {
				// return anonymous user object
				if (0 == $uid) return array('id' => 0, 'name' => 'anonymous', 'level' => 0);
				
				$query = sprintf("SELECT id, name, level, created FROM users WHERE id = %d", $this->sql->sanitize($uid));
				$this->sql->query($query, SQL_FIRST, SQL_ASSOC);
				
				return $this->sql->record;
				// else return specific attribute
			} else {
				if (0 == $uid) return FALSE;
				
				switch ($type) {
					case USERNAME:
						$query = sprintf("SELECT name FROM users WHERE id = %d", $this->sql->sanitize($uid));
						$this->sql->query($query, SQL_FIRST, SQL_ASSOC);
						return $this->sql->record['name'];
						break;
						
					case TIMEZONE:
						$query = sprintf("SELECT timezone FROM users WHERE id = %d", $this->sql->sanitize($uid));
						$this->sql->query($query, SQL_FIRST, SQL_ASSOC);
						return $this->sql->record['timezone'];
						break;
						
					default:
						break;
				}
			}
			return FALSE;
		}
		
		public function getPrivileges($uid = 0, $type = FALSE) {
			global $USER_LEVELS_ARRAY;
			if (!is_numeric($uid) || 0 == $uid) return FALSE;

			$query = sprintf("SELECT level FROM users WHERE id = %d", $this->sql->sanitize($uid));
			$this->sql->query($query, SQL_FIRST, SQL_ASSOC);
			
			// if type not set, return user array
			if (FALSE == $type) {
				return $USER_LEVELS_ARRAY[$this->sql->record['level']]['privileges'];
			// else determine if user has $type privilege
			} else {
				return $USER_LEVELS_ARRAY[$this->sql->record['level']]['privileges'] & $type;
			}
			
			return FALSE;
		}
		
		public function newUser($name, $password, $email = NULL, $timezone = DEFAULT_TZ) {
			define('MIN_USERNAME_LEN', 2);
			define('MIN_PASSWORD_LEN', 6);
			
			if (strlen($name) < MIN_USERNAME_LEN) {
				$this->error = "Username too short.";
				return FALSE;
			}
			
			if (strlen($password) < MIN_PASSWORD_LEN) {
				$this->error = "Password too short.";
				return FALSE;
			}
			
			if (preg_match('/[^a-zA-Z0-9\._\-]/', $name)) {
				$this->error = "Username contains invalid characters.";
				return FALSE;
			}
			
			if (NULL != $email) {
				// just check if e-mail is an IP or has an MX record; leave rest up to user
				/*if (!isValidEmail($email)) { 
					$this->error = "E-mail is invalid."; 
					return FALSE;
				}*/
			}
			
			$crypto = new cryptoClass();
			$hash = $crypto->hashPassword($password);
			
			$query = sprintf("INSERT INTO users (name, password, level, timezone) values('%s', '%s', %d, '%s')", 
				$this->sql->sanitize($name), $this->sql->sanitize($hash), DEFAULT_USERLEVEL, $this->sql->sanitize($timezone));
				
			$this->sql->query($query);
			
			if (NULL != $this->sql->error) {
				$query = sprintf("SELECT FROM users WHERE name = '%s'", $name);
				$this->sql->query($query);
				
				if ($this->sql->result) {
					$this->error = "Username is already taken.";
					return FALSE;
				}
				
				$this->error = "Error creating user.";
				return FALSE;
			}
			
			return mysql_insert_id();
		}
	}
?>
