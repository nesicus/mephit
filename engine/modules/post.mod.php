<?php
	/* post.mod.php
	 * date: 2011-03-02
	 * description: module for handling posts and comments
	*/
	
	if (!defined('IN_RECOVERY')) die();

	// default action
	$action = 'view';
	if (isset($_GET['action']))  $action = $_GET['action'];
	
	$auth = 0;
	$status = '';
	$title = '';
	$body = '';
	$thread = 0;
	
	if ("view" == $action) {
		$posts = FALSE;
		$content = new contentClass();
		
		if (!@$_GET['id'] || !is_numeric($_GET['id'])) {
			$status = 'Invalid post id.';
		} else {
			$thread = $_GET['id'];
			
			$posts = $content->getPost($_GET['id'], TRUE);

			if (NULL != $content->error) $status = $content->error;
		}

		$contentTemplate = new h2o(TEMPLATES_DIR . 'post/common.html');
		$render = $contentTemplate->render(array('content' => $posts['post'], 'error' => $status));
		
		$auth = $_user->getPrivileges($_SESSION['user']['id'], CAN_MAKE_COMMENTS);
		
		$comments = new h2o(TEMPLATES_DIR . 'post/comments.html');
		$render.= $comments->render(array('comments' => $posts['comments'], 'auth' => $auth, 'thread' => $thread));
		
		$page = new h2o(TEMPLATES_DIR . 'index.html');
		echo $page->render(array(
				'content' => $render,
				'user' => $_SESSION['user'],
			)
		);
					
	// create a new post
	} else if ("new" == $action) {
		// check to see if user is allowed to make posts
		if ($_user->getPrivileges($_SESSION['user']['id'], CAN_MAKE_NEW)) {
			$auth = 1;
			if ("POST" == $_SERVER['REQUEST_METHOD']) {
				if (!@empty($_POST['title']) && !@empty($_POST['body'])) {
					$content = new contentClass();
		
					// need to make sure these post variables are set
					if (FALSE != ($pid = $content->newPost($_SESSION['user']['id'], $_POST['type'], $_POST['title'], $_POST['body']))) {
						doRedirect('?module=post&id=' . $pid, TRUE);
					} else {
						$status = $content->error;
					}
				} else {
					$title = @htmlentities($_POST['title']);
					$body = @htmlentities($_POST['body']);
					$status = "You must enter some text.";
				}
			}
		} else {
				$auth = 0;
				$status = 'You are not allowed to make this kind of contribution.';
		}
		
		$contentTemplate = new h2o(TEMPLATES_DIR . 'post/form.html');

		$page = new h2o(TEMPLATES_DIR . 'index.html');
		echo $page->render(array(
				'content' => $contentTemplate->render(array('types' => $contentTypes, 'auth' => $auth, 'error' => $status)),
				'user' => $_SESSION['user']
			)
		);
	} else if ("comment" == $action) {
		// check to see if user is allowed to make posts
		if ($_user->getPrivileges($_SESSION['user']['id'], CAN_MAKE_COMMENTS)) {
			$auth = 1;
			if ("POST" == $_SERVER['REQUEST_METHOD']) {
				if (!@empty($_POST['body'])) {
					if (isset($_POST['thread']) && is_numeric(@$_POST['thread'])) {
						if (!isset($_POST['parent']) || !is_numeric(@$_POST['parent'])) {
							$parent = 0;
						} else {
							$parent = $_POST['parent'];
						}
						
						$content = new contentClass();
						if (FALSE != ($pid = $content->newComment($_SESSION['user']['id'], $_POST['thread'], $parent, $_POST['body']))) {
							doRedirect('?module=post&id=' . $_POST['thread'] . '#comment-' . $pid, TRUE);
						} else {
							doRedirect('?module=post&id=' . $_POST['thread'] . '#comment-' . $parent, TRUE);
						}
					} else {
						$status = "Invalid post id.";
					}
				} else {
					$status = "You must enter some text.";
				}
			}
		} else {
			$status = "You are not allowed to make this kind of contribution.";
		}
	}
?>
