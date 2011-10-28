<?php
/*
 *      post.mod.php 2011-10-27
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
		
		$templates[] = array('name' => 'post.common',
								'vars' => array('content' => $posts['post'],
											'error' => $status)
							);
									
		$templates[] = array('name' => 'post.comments',
								'vars' => array('comments' => $posts['comments'],
											'auth' => $auth,
											'thread' => $thread)
							);
						
		renderPage($templates);
					
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
		
		
		$templates[] = array('name' => 'post.form',
									'vars' => array('types' => $contentTypes,
													'auth' => $auth,
													'error' => $status)
							);

		renderPage($templates);
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
