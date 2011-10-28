<?php
/*
 *      user.lib.php 2011-10-27
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
	
	// type identifiers for user preferences
	define('USER_NAME', 1);
	define('USER_TIMEZONE', 2);
	define('USER_EMAIL', 3);
	
	// type identifiers for confirmation tokens
	define('USER_RESETPW', 1);
	
	// user class
	class userClass {
		private $crypto;
		private $sql = NULL;
		public $error;
		
		// class constructor
		public function __construct() {
			global $_sql;
			
			$this->crypto = new cryptoClass();
			$this->sql = $_sql;
		}
		
		public function getUID($user) {
			$query = sprintf("SELECT id FROM users WHERE name = '%s'", $this->sql->sanitize($user));
			$this->sql->query($query, SQL_FIRST);
			if (NULL == $this->sql->error) {
				return $this->sql->record[0];
			} else {
				return FALSE;
			}
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
					// if the user logs in with a reset password, deactivate the old one
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
					case USER_NAME:
						$query = sprintf("SELECT name FROM users WHERE id = %d", $this->sql->sanitize($uid));
						$this->sql->query($query, SQL_FIRST, SQL_ASSOC);
						return $this->sql->record['name'];
						break;
						
					case USER_TIMEZONE:
						$query = sprintf("SELECT timezone FROM users WHERE id = %d", $this->sql->sanitize($uid));
						$this->sql->query($query, SQL_FIRST, SQL_ASSOC);
						return $this->sql->record['timezone'];
						break;
						
					case USER_EMAIL:
						$query = sprintf("SELECT email FROM users WHERE id = %d", $this->sql->sanitize($uid));
						$this->sql->query($query, SQL_FIRST, SQL_ASSOC);
						return $this->sql->record['email'];
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
				$this->sql->sanitize($name), $this->sql->sanitize($hash), $this->sql->sanitize(DEFAULT_USERLEVEL),
					$this->sql->sanitize($timezone));
				
			$this->sql->query($query);
			
			if (NULL != $this->sql->error) {
				$query = sprintf("SELECT FROM users WHERE name = '%s'", $this->sql->sanitize($name));
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
		
		public function resetPassword($username) {
			if (!empty($username)) {
				if ($uid = getUID($username)) {
					$query = sprintf("SELECT time FROM tokens WHERE affects = %d AND type = %d", 
						$this->sql->sanitize($uid), $this->sql->sanitize(USER_RESETPW));
						
					$this->sql->query($query, SQL_FIRST);
					
					if (NULL == $this->sql->error) {
						if (!empty($this->sql->record[0])) {
							$this->error = "A reset request for this user has already been sent.";
							return FALSE;
						}
						
						$passwordHash = $this->crypto->genPassword();
						$passwordHash = explode(':::::', $passwordHash);
						
						if ("" != $email = $this->get($uid, USER_EMAIL)) {
							$token = $this->crypto->genUniqueID();
							$query = sprintf("INSERT INTO tokens (id, type, time, affects) values ('%s', %d, %d, %d",
								$this->sql->sanitize($token),
								$this->sql->sanitize(USER_RESETPW),
								$this->sql->sanitize(time()),
								$this->sql->sanitize($uid)
							);
							$this->sql->query($query);
							
							if (NULL == $this->sql->error) {
								$envelope = array('dest' => $email,
													'subject' => 'Your password has been reset.',
													'message' => 'To retrieve your new password and disable your old'
													. 'passwords, please visit the following link: '
													. WEBSITE_URL . '?module=auth&action=confirm&id=' . $token
											);
								if (emailAdmin($envelope)) {
									return TRUE;
								} else {
									$this->error = "There was a problem sending the confirmation e-mail.";
									return FALSE;
								}
							}
						} else {
							$this->error = "There is no e-mail assoiated with that user.";
							return FALSE;
						}					
					}
					$this->error = "Query error.";
					return FALSE;
				} else {
					$this->error = "Invalid username.";
					return FALSE;
				}
			} else {
				$this->error = "You must enter a username.";
				return FALSE;
			}
		}
	}
?>
