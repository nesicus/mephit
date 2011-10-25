<?php
	/* sessions.lib.php
	 * date: 2011-02-22
	 * description: library for handling sessions
	*/

	if (!defined('IN_RECOVERY')) die();
	
	class sessionsClass {
		private $crypto;
		private $sql;
		
		public function __construct() {
			global $_sql;
			
			ini_set('session.use_cookies', TRUE);
			ini_set('session.use_only_cookies', TRUE);
			
			$this->crypto = new cryptoClass();
			$this->sql = $_sql;
			session_set_save_handler(array($this, 'begin'),
				array($this, 'end'),
				array($this, 'read'),
				array($this, 'write'),
				array($this, 'destroy'),
				array($this, 'cleanup')
			);

			
			// check for invalid cookie data
			if (isset($_COOKIE[COOKIE_NAME]) && preg_match('/[^a-zA-Z0-9]/', $_COOKIE[COOKIE_NAME]) || isset($_COOKIE[COOKIE_NAME]) && @$_COOKIE[COOKIE_NAME] == "" || @strlen($_COOKIE[COOKIE_NAME]) != 40) {
				// generate a new, valid ID
				$this->genID();
			}

			session_set_cookie_params(time() + COOKIE_LIFETIME, COOKIE_PATH, COOKIE_DOMAIN, COOKIE_SECURE,
				COOKIE_HTTPONLY);
			
			// set sessions options
			session_name(COOKIE_NAME);
		}
		
		public function genID() {
			// destroy old session first
			if ("" != session_id()) $this->destroy(session_id());
			$newID = $this->crypto->genUniqueID();
			session_id($newID);
			return $newID;
		}
		
		public function begin() {
			return TRUE;
		}
		
		public function end() {
			return TRUE;
		}
		
		public function read($id) {
			$query = sprintf("SELECT data FROM sessions WHERE id = '%s'", $this->sql->sanitize($id));
			$this->sql->query($query, SQL_FIRST, SQL_ASSOC);
			return $this->sql->record['data'];
		}
		
		public function write($id, $data) {
			$query = sprintf("REPLACE INTO sessions (id, activity, data, ipaddr) VALUES('%s', %d, '%s', '%s')",
				mysql_real_escape_string($id),
				mysql_real_escape_string(time()),
				mysql_real_escape_string($data),
				mysql_real_escape_string(getUserIP())
			);
			$this->sql->query($query);
			return TRUE;
		}
		
		public function destroy($id) {
			$query = sprintf("DELETE FROM sessions WHERE id = '%s'", mysql_real_escape_string($id));
			$this->sql->query($query);
			return TRUE;
		}
		
		public function cleanup($grace) {
			// override the default grace setting [bandaid]
			$grace = 259200; // 3 days
			
			$query = sprintf("DELETE FROM sessions WHERE activity < %s", mysql_real_escape_string(time() - $grace));
			$this->sql->query($query);
			return TRUE;
		}
	}
?>
