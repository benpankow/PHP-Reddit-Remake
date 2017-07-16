<?php
	require_once("oop.php");
	require_once("session.php");
	
	if (isLoggedIn())
	{
		$action = htmlspecialchars($_POST['action']);
		if ($action == "upvote" && isset($_POST['id']))
		{
			$id = htmlspecialchars($_POST['id']);
			if (Post::postExists($conn, $id))
			{
				$change = 0;
				$post = Post::getID($conn, $id);
				if (!isUpvoted($id))
				{
					if (isDownvoted($id))
					{
						user()->downvoted = remove(user()->downvoted, $id);
						$post->undownvote($conn);
						$change = $change + 1;
					}
					array_push(user()->upvoted, $id);
					
					user()->save($conn);
					echo "true";
					$post->upvote($conn);
					$change = $change + 1;
				}
				else
				{
					user()->upvoted = remove(user()->upvoted, $id);
					user()->save($conn);
					echo "true";
					$post->unupvote($conn);
					$change = $change - 1;
				}
			}
		}
		else if ($action == "downvote" && isset($_POST['id']))
		{
			$id = htmlspecialchars($_POST['id']);
			if (Post::postExists($conn, $id))
			{
				$change = 0;
				$post = Post::getID($conn, $id);
				if (!isDownvoted($id))
				{
					if (isUpvoted($id))
					{
						user()->upvoted = remove(user()->upvoted, $id);
						$post->unupvote($conn);
						$change = $change - 1;
					}
					array_push(user()->downvoted, $id);
					user()->save($conn);
					echo "true";
					$post->downvote($conn);
					$change = $change - 1;
				}
				else
				{
					user()->downvoted = remove(user()->downvoted, $id);
					user()->save($conn);
					echo "true";
					$post->undownvote($conn);
					$change = $change + 1;
				}
			}
		}
		else if ($action == "upvoteComment" && isset($_POST['id']))
		{
			$id = htmlspecialchars($_POST['id']);
			if (Comment::commentExists($conn, $id))
			{
				$change = 0;
				$post = Comment::getID($conn, $id);
				if (!isCommentUpvoted($id))
				{
					if (isCommentDownvoted($id))
					{
						user()->downvotedComments = remove(user()->downvotedComments, $id);
						$post->undownvote($conn);
						$change = $change + 1;
					}
					array_push(user()->upvotedComments, $id);
					
					user()->save($conn);
					echo "true";
					$post->upvote($conn);
					$change = $change + 1;
				}
				else
				{
					user()->upvotedComments = remove(user()->upvotedComments, $id);
					user()->save($conn);
					echo "true";
					$post->unupvote($conn);
					$change = $change - 1;
				}
			}
		}
		else if ($action == "downvoteComment" && isset($_POST['id']))
		{
			$id = htmlspecialchars($_POST['id']);
			if (Comment::commentExists($conn, $id))
			{
				$change = 0;
				$post = Comment::getID($conn, $id);
				if (!isCommentDownvoted($id))
				{
					if (isCommentUpvoted($id))
					{
						user()->upvotedComments = remove(user()->upvotedComments, $id);
						$post->unupvote($conn);
						$change = $change - 1;
					}
					array_push(user()->downvotedComments, $id);
					user()->save($conn);
					echo "true";
					$post->downvote($conn);
					$change = $change - 1;
				}
				else
				{
					user()->downvotedComments = remove(user()->downvotedComments, $id);
					user()->save($conn);
					echo "true";
					$post->undownvote($conn);
					$change = $change + 1;
				}
			}
		}
		else if ($action == "deleteComment" && isset($_POST['target']))
		{
			$target = htmlspecialchars($_POST['target']);
			$comment = Comment::getId($conn, $target);
			if ($comment != null && isLoggedIn() && ($comment->authorID == username()))
			{
				$comment->delete($conn);
				echo "true";
			}
			else
			{
				echo "false";
			}
			
		}
		else if ($action == "userExists" && isset($_POST['user']))
		{
			$un = trim(htmlspecialchars($_POST['user']));
			$us = User::userExists($conn, $un);
			if ($us == true)
			{
				echo "true";
			}
			else
			{
				echo "false";
			}
		}
		else if ($action == "deletePost" && isset($_POST['target']))
		{
			$target = htmlspecialchars($_POST['target']);
			$comment = Post::getId($conn, $target);
			if ($comment != null && isLoggedIn() && ($comment->authorID == username()))
			{
				$comment->delete($conn);
				echo "true";
			}
			else
			{
				echo "false";
			}
			
		}
		else if ($action == "comment" && isset($_POST['text']) && isset($_POST['postId']) && isset($_POST['parent']))
		{
			$parent = htmlspecialchars($_POST['parent']);
			$text = htmlspecialchars($_POST['text']);
			$postId = htmlspecialchars($_POST['postId']);
			$text = nl2br(trim($text));
			if (strlen($text) != 0 && strlen($text) <= 2500 && isLoggedIn())
			{
				$id = Comment::create($conn, username(), $postId, $parent, $text);
				array_push(user()->comments, $id);
				array_push(user()->upvotedComments, $id);
				user()->ckarma = user()->ckarma + 1;
				user()->save($conn);
				Comment::getId($conn, $id)->render();
				if ($parent == 0)
				{
					$parentPost = Post::getId($conn, $postId);
					Message::create($conn, "", $id, "post" . $postId, $parentPost->authorID);
				}
				else
				{
					$parentComment = Comment::getId($conn, $parent);
					Message::create($conn, "", $id, "comment" . $postId, $parentComment->authorID);
				}
			}
			else
			{
				echo "false";
			}
			
		}
		else if ($action == "message" && isset($_POST['text']) && isset($_POST['parent']))
		{
			$parent = htmlspecialchars($_POST['parent']);
			$text = htmlspecialchars($_POST['text']);
			$text = nl2br(trim($text));
			$parentMsg = Message::getID($conn, $parent);
			$authorID = $parentMsg->authorID;
			$recipientID = $parentMsg->recipientID;
			$myName = username();

			if (strlen($text) != 0 && strlen($text) <= 2500 && isLoggedIn() && ($authorID == $myName || $recipientID == $myName))
			{
				$sendTo = $authorID == $myName ? $recipientID : $authorID;

				$threadid = $parentMsg->threadid;
				$id = Message::create($conn, $myName, $text, $parentMsg->data, $sendTo, $threadid);
				$time = time();

				$sql = "UPDATE Messages SET threadtime='$time' WHERE threadid='$threadid'";
				mysqli_query($conn, $sql);
				
				$myMsg = Message::getId($conn, $id);
				$myMsg->renderNewMsg(substr($myMsg->data, 3));

				/**$id = Comment::create($conn, username(), $postId, $parent, $text);
				array_push(user()->comments, $id);
				array_push(user()->upvotedComments, $id);
				user()->ckarma = user()->ckarma + 1;
				user()->save($conn);
				if ($parent == 0)
				{
					$parentPost = Post::getId($conn, $postId);
					Message::create($conn, username(), $id, "post" . $postId, $parentPost->authorID);
				}
				else
				{
					$parentComment = Comment::getId($conn, $parent);
					Message::create($conn, username(), $id, "comment" . $postId, $parentComment->authorID);
				}*/
			}
			else
			{
				echo "false";
			}
			
		}
		else if ($action == "newMessage" && isset($_POST['text']) && isset($_POST['authorID']) && isset($_POST['recipientID']) && isset($_POST['title']))
		{
			$text = htmlspecialchars($_POST['text']);
			$text = nl2br(trim($text));
			$authorID = htmlspecialchars($_POST['authorID']);
			$recipientID = htmlspecialchars($_POST['recipientID']);
			$title = trim(htmlspecialchars($_POST['title']));
			$myName = username();

			if (strlen($text) != 0 && strlen($text) <= 2500 && strlen($title) != 0 && strlen(title) < 30 && isLoggedIn() && ($authorID == $myName || $recipientID == $myName))
			{
				$sendTo = $authorID == $myName ? $recipientID : $authorID;

				$id = Message::create($conn, $myName, $text, $title, $sendTo);
				
				$myMsg = Message::getId($conn, $id);
				$myMsg->renderNewMsg(substr($myMsg->data, 3));

				/**$id = Comment::create($conn, username(), $postId, $parent, $text);
				array_push(user()->comments, $id);
				array_push(user()->upvotedComments, $id);
				user()->ckarma = user()->ckarma + 1;
				user()->save($conn);
				if ($parent == 0)
				{
					$parentPost = Post::getId($conn, $postId);
					Message::create($conn, username(), $id, "post" . $postId, $parentPost->authorID);
				}
				else
				{
					$parentComment = Comment::getId($conn, $parent);
					Message::create($conn, username(), $id, "comment" . $postId, $parentComment->authorID);
				}*/
			}
			else
			{
				echo "false";
			}
			
		}
		else
		{
			echo "false";
		}
	}
	else
	{
		echo "false";
	}

?>