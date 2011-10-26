<?php
	/* sessions.lib.php
	 * date: 2011-10-25
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

			
			if (!empty($_COOKIE[COOKIE_NAME])) {
				// generate a new session ID if cookie data is invalid
				if (preg_match('/[^a-zA-Z0-9]/', $_COOKIE[COOKIE_NAME]) || strlen($_COOKIE[COOKIE_NAME]) != 40)
					$this->genID();
			} else {
				// generate a new session ID for fresh session
				$this->genID();
			}

			// set sessions options
			session_set_cookie_params(time() + COOKIE_LIFETIME, COOKIE_PATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);
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
				$this->sql->sanitize($id),
				$this->sql->sanitize(time()),
				$this->sql->sanitize($data),
				$this->sql->sanitize(getUserIP())
			);
			$this->sql->query($query);
			return TRUE;
		}
		
		public function destroy($id) {
			$query = sprintf("DELETE FROM sessions WHERE id = '%s'", $this->sql->sanitize($id));
			$this->sql->query($query);
			return TRUE;
		}
		
		public function cleanup($grace) {
			// override the default grace setting [bandaid]
			$grace = 259200; // 3 days
			
			$query = sprintf("DELETE FROM sessions WHERE activity < %d", $this->sql->sanitize(time() - $grace));
			$this->sql->query($query);
			return TRUE;
		}
	}
?>
