<?php
/*
 *      dynamic.conf.php 2011-10-27
 *      
 *      Copyright 2011 daryl <daryl@99years.com>
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
	
	/* user levels and privileges */

	/*   this is a bitmask for delegation of user privileges to custom levels.
		sum the numbers corresponding to the value for each privilege you want to 
	assign your level.                              */

	/*
		1: able to post replies
		2: able to make new posts
		4: able to upgrade level 1 users, able to delete posts
	*/

	define('CAN_MAKE_COMMENTS', 1);
	define('CAN_MAKE_NEW', 2);
	define('CAN_DELETE', 4);
	define('CAN_UPGRADE', 4);

	// some sensible defaults

	$USER_LEVELS_ARRAY = array( 
		array('name' => 'reserved', 'privileges' => 0), // reserved or anonymous -- see below
		array('name' => 'basic', 'privileges' => 1),   // able to post replies
		array('name' => 'medium', 'privileges' => 3),  // able to post replies and make new posts
		array('name' => 'admin', 'privileges' => 7)  // able to post replies, make new posts, upgrade users, and delete posts
	);

	// the default user level for new accounts
	define('DEFAULT_USERLEVEL', 1);
	
	// set the following to "1" if you wish to have an anonymous user level
	// note: the anonymous user will ALWAYS have user id "0"

	define('ALLOW_ANONYMOUS', 0);

	// set this to change the anonymous user's privilege level, as above

	define('ANON_PRIVS', 0);

	/* content */

	// format to display time/date strings
	define('DATE_TIME_FORMAT', 'F d, Y @g:iA');
	
	// some sensible defaults
	$contentTypes = array(
		array('name' => 'news')
	);
	
	// maximum length for post heading (set to FALSE to disable)
	define('MAX_TITLE_LEN', 100);
	
	// maximum length for post content (set to FALSE to disable)
	define('MAX_BODY_LEN', FALSE);
	
	/* main page */
	
	// number of posts to display
	define('MAX_NEWS_POSTS', 5);
	
	define('COMMENT_SPILL', 0);
	define('CHRONO_ORDER', 0);
?>
