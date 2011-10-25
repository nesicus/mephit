<?php
	/*
	 *      project: recovery
	 *      date: 2011-03-02
	 *      version: 0.0.1
	 *      
	 *      file: content.lib.php
	 *      date: 2011-03-02
	 *      version: 0.0.1
	 *      author: daryl
	 *      description:
	 */
	
	
	class contentClass {
		private $sql;
		private $user;
		private $timezone;
		public $error;
		
		public function __construct($tz = FALSE) {
			global $_sql;
			$this->sql = $_sql;
			$this->user = new userClass();
			
			if (!$tz) { 
				$tz = $this->user->get($_SESSION['user']['id'], TIMEZONE);
				$this->timezone = $tz ? $tz : DEFAULT_TZ;
			}
		}
		
		public function beautify($post) {
			// convert timestamp to RFC date string in local timezone
			$time = date('r', $post['time']);
			
			// create date time object from local time string
			$dtime = new DateTime($time);
			
			// create date time object for user's time zone
			$dtzone = new DateTimeZone($this->timezone);
			
			// convert to user's time zone
			$dtime->setTimeZone($dtzone);
			
			// format post title if necessary
			if (isset($post['title'])) $post['title'] = htmlentities($post['title']);
			
			// format post body
			$post['body'] = nl2br(htmlentities($post['body']));
			
			// format post time
			$post['time'] = $dtime->format(DATE_TIME_FORMAT);
			
			return $post;
		}
		
		public function newPost($uid, $type, $title, $body, $sticky = FALSE, $major = FALSE) {
			// check to see if user is allowed to create posts
			if (!$this->user->getPrivileges($uid, CAN_MAKE_NEW)) {
				$this->error = "You are not allowed to make this kind of contribution.";
				return FALSE;
			}
			
			// perform some basic sanity checks
			if (empty($_POST['title'])) {
				$this->error = "No title given.";
				return FALSE;
			}
			
			if (empty($_POST['body'])) {
				$this->error = "No content given.";
				return FALSE;
			}
			
			if (strlen($_POST['title']) > MAX_TITLE_LEN) {
				$this->error = "Post heading too long.";
				return FALSE;
			}
			
			if (MAX_BODY_LEN && strlen($_POST['body']) > MAX_BODY_LEN) {
				$this->error = "Post content too long.";
				return FALSE;
			}

			// add check for sticky/major privileges here
			
			$query = sprintf("INSERT INTO posts (uid, time, type, title, body) values(%d, %d, %d, '%s', '%s')", 
				$this->sql->sanitize($uid), $this->sql->sanitize(time()), $this->sql->sanitize($type), 
				$this->sql->sanitize($title), $this->sql->sanitize($body));
			$this->sql->query($query);
			if (NULL != $this->sql->error) {
				$this->error = "Unable to create post.";
				return FALSE;
			} else {
				return mysql_insert_id();
			}
		}
		
		public function newComment($uid, $thread, $parent, $body) {
			// check to see if user is allowed to create posts
			if (!$this->user->getPrivileges($uid, CAN_MAKE_NEW)) {
				$this->error = "You are not allowed to make this kind of contribution.";
				return FALSE;
			}
			
			// perform some basic sanity checks
			if (empty($thread) || !is_numeric($thread)) {
				$this->error = "Invalid post id.";
			}
			
			$parent = @is_numeric($parent) ? $parent : 0;
			
			if (empty($_POST['body'])) {
				$this->error = "No content given.";
				return FALSE;
			}
			
			if (MAX_BODY_LEN && strlen($_POST['body']) > MAX_BODY_LEN) {
				$this->error = "Post content too long.";
				return FALSE;
			}

			$query = sprintf("INSERT INTO comments (uid, time, thread, parent, body) values(%d, %d, %d, %d, '%s')", 
				$this->sql->sanitize($uid), $this->sql->sanitize(time()), $this->sql->sanitize($thread), $this->sql->sanitize($parent),
				$this->sql->sanitize($body));
			$this->sql->query($query);
			if (NULL != $this->sql->error) {
				$this->error = "Unable to create post.";
				return FALSE;
			} else {
				return mysql_insert_id();
			}
		}
		
		public function editPost() {
		}
		
		public function deletePost() {
		}
		
		public function getPost($id, $thread = FALSE, $level = 0) {
			static $posts = array('post' => array(), 'comments' => array());
			
			if (!is_numeric($id) || !$id) {
				$this->error = "Invalid post id.";
				return FALSE;
			}
		
			// get the post itself
			if (0 == $level) {
				$query = sprintf("SELECT u.name AS username, p.* FROM posts p, users u WHERE p.id = %d AND u.id = p.uid", $this->sql->sanitize($id));
				$this->sql->query($query, SQL_FIRST, SQL_ASSOC);
			
				// post doesn't exist or there was an error
				if (NULL != $this->sql->error || !is_array(@$this->sql->record) || count(@$this->sql->record) <= 0) {
					$this->error = "Invalid post id.";
					return FALSE;
				}
				
				// return the post if we don't need the rest of the thread
				if (FALSE == $thread) return $this->sql->record;
				$posts['post'] = $this->sql->record;
				$posts['post'] = $this->beautify($posts['post']);
				
				$query = sprintf("SELECT u.name AS username, c.* FROM users u, comments c WHERE thread = %d AND u.id = c.uid ORDER BY c.time ASC", 
					$this->sql->sanitize($id));
			} else {
				$query = sprintf("SELECT u.name AS username, c.* FROM users u, comments c WHERE parent = %d AND u.id = c.uid ORDER BY c.time ASC", 
					$this->sql->sanitize($id));
			}

			$this->sql->query($query, SQL_ALL, SQL_ASSOC);
			if (!$this->sql->error && count(@$this->sql->record) > 0) {
				foreach($this->sql->record as $post) {
					$root = 0;

					// this only occurs if we have a comment with the same id as the original thread
					if (0 == $level && $post['parent'] > 0) $root = 1;
					if ($root) continue;

					$post = $this->beautify($post);
					
					$post['width'] = 100 - ($level + 4.8);
					$posts['comments'][] = $post;
					$this->getPost($post['id'], TRUE, $level + 2);
				}
			}
			return $posts;
		}
		
		public function getFrontPage($limit = 5, $cache = NULL) {
			$mtime = 0;
			
			if (NULL != $cache) {
				if ($buf = @file($cache . 'fp_cache')) {
					if (time() - trim($buf[0]) < CACHE_EXPIRE) {
						$posts = unserialize(urldecode($buf[1]));
						return $posts;
					}
				}
			}
			
			$query = sprintf("SELECT u.name AS username, p.* FROM posts p, users u WHERE u.id = p.uid ORDER BY time DESC LIMIT %d", $limit);
			$this->sql->query($query, SQL_ALL, SQL_ASSOC);
			$posts = $this->sql->record;
			
			if (!$this->sql->error) {
				foreach($posts as $key => $post) $posts[$key] = $this->beautify($posts[$key]);
				
				if (NULL != $cache) {
					$fd = fopen($cache . 'fp_cache', 'w');
					@fwrite($fd, time() . "\n" . urlencode(serialize($posts)));
				}
				return $posts;
			} else {
				$this->error = "There was an error.";
				return FALSE;
			}
			
		}
	}
?>
