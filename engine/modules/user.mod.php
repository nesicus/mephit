<?php
	/*
		project: recovery
		date: 2011-02-19
		version: 0.0.1
		
		file: user.mod.php
		date: 2011-02-19
		version: 0.0.1
		author: daryl
	*/
	
	if (!defined('IN_RECOVERY')) die();
	
	// default action
	$action = 'view';
	if (isset($_GET['action'])) $action = $_GET['action'];
	
	if ("view" == $action) {
		// check for invalid user
		if (!isset($_GET['id']) || !$_user->get(@$_GET['id'])) {
			// redirect them to the main page
			doRedirect('', TRUE);
		} else {
			// obtain profile data
			$user = $_user->get($_GET['id']);
		}	
		
		// render UNIX timestamp; offset for GMT later. this is just a test.
		$user['created'] = date("h:i A, d F Y", $user['created']);
		
		$user['profile' ] = array(
			'level' => $USER_LEVELS_ARRAY[$user['level']]['name'],
			'favorite drink' => 'cat urine',
			'favorite club' => "don's disco and 24 hour diner",
			'age' => 30,
			'number of cats' => 30,
			'description' => 'this is a stupid description of a stupid person.',
		);
	
		$contentTemplate = new h2o(TEMPLATES_DIR . 'user/profile.html');
	
		$page = new h2o(TEMPLATES_DIR . 'index.html');
	
		echo $page->render(array(
			'content' => $contentTemplate->render(compact('user')),
			'user' => $_SESSION['user']
			)
		);
	} else if ("new" == $action) {
		$error = FALSE;
		$status = '';
		
		// is this already a logged in user?
		if (!@$_SESSION['user']['id']) {
			if ("POST" == $_SERVER['REQUEST_METHOD']) {
				if (!@empty($_POST['username']) && !@empty($_POST['password'])) {
					if ($_user->newUser($_POST['username'], $_POST['password'], @$_POST['email'], @$_POST['timezone'])) {
						// try to log in as new user
						if ($uid = $_user->login($_POST['username'], $_POST['password'])) {
							// regenerate session ID upon authentication
							$sid = $_session->genID();
							
							// populate session data
							$_SESSION['user'] = $_user->get($uid);
							
							// update cookie with the new session ID
							$cookie = session_get_cookie_params();
							setcookie(COOKIE_NAME, $sid, $cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure']);
							
							// redirect the user to their profile
							doRedirect("?module=user&id=" . htmlentities($uid), TRUE);
						} else {
							echo "error was logging in.\n";
							$status = $_user->error;
						}
					} else {
						echo "error was creating account.\n";
						$status = $_user->error;
					}
				} else {
					$status = "You must enter a username and password.";
				}
			}
		} else {
			$error = TRUE;
			$status = "You are already logged in.";
		}
		
		$contentTemplate = new h2o(TEMPLATES_DIR . 'user/new.html');
	
		$page = new h2o(TEMPLATES_DIR . 'index.html');
		echo $page->render(array(
			'content' => $contentTemplate->render(array('timezones' => timezone_identifiers_list(), 
													'error' => $error, 
													'status' => $status)),
			'user' => $_SESSION['user'])
		);
	}
?>
