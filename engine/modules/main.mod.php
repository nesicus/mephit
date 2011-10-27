<?php
	/* main.mod.php
	 * date: 2011-02-22
	 * description: front page
	*/

	if (!defined('IN_RECOVERY')) die();

	$content = new contentClass();
	$posts = $content->getFrontPage(MAX_NEWS_POSTS, USE_CACHING ? CACHE_DIR : NULL);
	
	$contentTemplate = new h2o(TEMPLATES_DIR . 'post/main.html');
	$page = new h2o(TEMPLATES_DIR . 'index.html');

	echo $page->render(array(
		'content' => $contentTemplate->render(compact('posts')),
		'user' => $_SESSION['user']
		)
	);
?>
