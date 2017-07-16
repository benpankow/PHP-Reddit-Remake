<?php
	session_start();
	$conn = new mysqli("IP","benpanko_admin","PASSWORD","benpanko_redditclone");
	
	if (mysqli_connect_errno())
	{
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}

	if ((isset($_SESSION['ip']) || isset($_SESSION['session'])) && getenv ( "REMOTE_ADDR" ) != $_SESSION['ip'])
	{
		session_destroy();
		//echo "invalid ip";
	}
	
	function user()
	{
		return $_SESSION['session'];
	}
	
	function username()
	{
		return user()->name;
	}
	
	function isLoggedIn()
	{
		return isset($_SESSION['session']);
	}
	
	function isUpvoted($id)
	{
		if (!isLoggedIn())
		{
			return false;
		}
		return in_array($id, user()->upvoted);
	}
	
	function isDownvoted($id)
	{
		if (!isLoggedIn())
		{
			return false;
		}
		return in_array($id, user()->downvoted);
	}
	
	function isCommentUpvoted($id)
	{
		if (!isLoggedIn())
		{
			return false;
		}
		return in_array($id, user()->upvotedComments);
	}
	
	function isCommentDownvoted($id)
	{
		if (!isLoggedIn())
		{
			return false;
		}
		return in_array($id, user()->downvotedComments);
	}
	
	function remove($array, $item)
	{
		$del = array_search($item, $array);
        unset($array[$del]);
		return $array;
	}
?>