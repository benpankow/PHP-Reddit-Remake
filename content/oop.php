<?php
	$root = "/aggregator";
	class User
	{
		public $name;
		public $lkarma = 1;
		public $ckarma = 1;
		public $posts = array();
		public $comments = array();
		public $upvoted = array();
		public $downvoted = array();
		public $upvotedComments = array();
		public $downvotedComments = array();
		
		function __construct($name)
		{
			$this->name = $name;
		}
		
		public function save($conn)
		{
			$serial = serialize($this->posts);
			$serial2 = serialize($this->comments);
			$serial3 = serialize($this->upvoted);
			$serial4 = serialize($this->downvoted);
			$serial5 = serialize($this->upvotedComments);
			$serial6 = serialize($this->downvotedComments);

			$sql="UPDATE Users SET lkarma='$this->lkarma', ckarma='$this->ckarma', posts='$serial', comments='$serial2', upvoted='$serial3', downvoted='$serial4', upvotedComments='$serial5', downvotedComments='$serial6' WHERE username='Flaxbeard'";
			mysqli_query($conn, $sql);
		}
		
		public static function get($conn, $name)
		{
			$result = mysqli_query($conn, "SELECT * FROM Users WHERE username='$name'");
			$details = mysqli_fetch_row($result);

			$user = new User($name);
			$user->lkarma    = $details[2];
			$user->ckarma    = $details[3];
			$user->posts     = unserialize($details[4]);
			$user->comments  = unserialize($details[5]);
			$user->upvoted   = unserialize($details[6]);
			$user->downvoted = unserialize($details[7]);
			$user->upvotedComments   = unserialize($details[8]);
			$user->downvotedComments = unserialize($details[9]);
			return $user;
		}
		
		public static function userExists($conn, $id)
		{
			$result = mysqli_query($conn, "SELECT username FROM Users WHERE username='$id'");
			$num = mysqli_num_rows($result);
			return $num > 0;
		}
		
		public function updateKarma($conn)
		{
			$result = mysqli_query($conn, "SELECT * FROM Users WHERE username='$this->name'");
			$details = mysqli_fetch_row($result);

			$lkarma = 0;
			$ckarma = 0;
			
			$this->posts = unserialize($details[4]);
			$result = mysqli_query($conn, "SELECT * FROM Posts WHERE postid IN (" . implode(',', array_map('intval', $this->posts )) . ")");
			if ($result != false && mysqli_num_rows($result) > 0)
			{
				while($row = $result->fetch_array())
				{
					$lkarma = $lkarma + $row[3] - $row[6];
				}
			}
			$this->lkarma = $lkarma;
			
			$this->comments = unserialize($details[5]);
			
			$result2 = mysqli_query($conn, "SELECT * FROM Posts WHERE postid IN (" . implode(',', array_map('intval', $this->comments )) . ")");
			if ($result2 != false && mysqli_num_rows($result2) > 0)
			{
				while($row = $result2->fetch_array())
				{
					$ckarma = $ckarma + $row[4] - $row[6];
				}
			}
			$this->ckarma = $ckarma;
			
			$sql="UPDATE Users SET lkarma='$lkarma', ckarma='$ckarma' WHERE username='$this->name'";
		}
		
	}
			
	class Post
	{
		public $title;
		public $authorID;
		public $postID;
		public $score;
		public $url;
		public $downvotes;
		public $time;
		
		
		function __construct($title, $authorID, $postID, $url, $score, $time, $downvotes)
		{
			$this->title 	 = $title;
			$this->authorID  = $authorID;
			$this->postID 	 = $postID;
			$this->url		 = $url;
			$this->score 	 = $score;
			$this->downvotes 	 = $downvotes;
			$this->time		 = new Timestamp($time);
		}
		
		function getNumComments($conn)
		{
			$sql = "SELECT commentid FROM Comments WHERE postid='$this->postID'";
			$result = mysqli_query($conn, $sql);
			if ($result == false)
			{
				return 0;
			}			
			return mysqli_num_rows($result);
		}
		
		public static function create($conn, $title, $authorID, $url)
		{
			$time = time();
			$sql="INSERT INTO Posts SET authorid='$authorID', title='$title', score='1', url='$url', time='$time', downvotes='0'";
			mysqli_query($conn, $sql);
			return mysqli_insert_id ($conn);
		}
		
		public static function postExists($conn, $id)
		{
			$result = mysqli_query($conn, "SELECT postid FROM Posts WHERE postid='$id'");
			$num = mysqli_num_rows($result);
			return $num > 0;
		}
		
		public static function getID($conn, $id)
		{
			$result = mysqli_query($conn, "SELECT * FROM Posts WHERE postid='$id'");
			$details = mysqli_fetch_row($result);
			
			$post = new Post($details[2], $details[1], $details[0], $details[4], $details[3], $details[5], $details[6]);
			return $post;
		}
		
		public function delete($conn)
		{
			$sql = "UPDATE Posts SET authorid='[deleted]', title='[deleted]', url='[deleted]' WHERE postid='$this->postID'";
			mysqli_query($conn, $sql);
		}
		
		
		public function upvote($conn)
		{
			$this->score = $this->score + 1;
			$sql = "UPDATE Posts SET score='$this->score' WHERE postid='$this->postID'";
			mysqli_query($conn, $sql);
		}
		
		public function unupvote($conn)
		{
			$this->score = $this->score - 1;
			$sql = "UPDATE Posts SET score='$this->score' WHERE postid='$this->postID'";
			mysqli_query($conn, $sql);
		}
		
		public function downvote($conn)
		{
			$this->downvotes = $this->downvotes + 1;
			$sql = "UPDATE Posts SET downvotes='$this->downvotes' WHERE postid='$this->postID'";
			mysqli_query($conn, $sql);
		}
		
		public function undownvote($conn)
		{
			$this->downvotes = $this->downvotes - 1;
			$sql = "UPDATE Posts SET downvotes='$this->downvotes' WHERE postid='$this->postID'";
			mysqli_query($conn, $sql);
		}
		
		public static function get($details)
		{
			$post = new Post($details[2], $details[1], $details[0], $details[4], $details[3], $details[5], $details[6]);
			return $post;
		}
		
		public function render($conn)
		{
			global $root;
			?>
				<div class = "post" id = "post<? echo $this->postID; ?>">
					<div class = "voting">
						<span class = "score <? if (isUpvoted($this->postID)) { echo "upvoted"; } ?> <? if (isDownvoted($this->postID)) { echo "downvoted"; } ?>">
							<?
								echo ($this->score - $this->downvotes);
							?>
						</span>
						<div class = "vote upvote <? if (isUpvoted($this->postID)) { echo "chosen"; } ?>"><div class = "highlight"></div></div>
						<div class = "vote downvote <? if (isDownvoted($this->postID)) { echo "chosen"; } ?>"><div class = "highlight"></div></div>
					</div>
					<p class = "title">
						<a <? if ($this->url != "[deleted]") { ?>  href = "<? echo $this->url; ?>" <? } ?>>
							<?
								echo $this->title;
								$domain = $this->getDomain();
							?>
						</a>
						<a class = "baseURL" href = "http://www.<? echo $domain; ?>">
							<?
								echo "(" . $domain . ")";
							?>
						</a>
					</p>
					<p class = "authorAndTime">
						posted
						<span title = "<? echo $this->time->stringRep(); ?>">
							<?
								echo $this->time->howLongAgo();
							?>
						</span>
						by
						<a <? if ($this->authorID != "[deleted]") { ?> href = "<? echo $root; ?>/user/<? echo $this->authorID; ?>" <? } ?>><?
							echo $this->authorID;
						?></a>
						<span class = "upvotesDownvotes">
							(
							<span class = "upvoteCount">
								<?
									echo "+" . $this->score;
								?>
							</span>
							|
							<span class = "downvoteCount">
								<?
									echo "-" . $this->downvotes;
								?>
							</span>
							)
						</span>
					</p>
					
						<a href = "<? echo $root; ?>/post/<? echo $this->postID; ?>">
							<span class = "numComments postOption">
							<?
								echo $this->getNumComments($conn) . " Comments";
							?>
							</span></a>
						<? if (isLoggedIn() && $this->authorID == username()) { ?>
							<span class = "postOption deletePost">Delete</span>
						<? } ?>
						

				</div>
			<?
		}
		
		public function getDomain()
		{
			$pos1 = strrpos($this->url, "://");
			
			$str = substr($this->url, $pos1 + 3);
			$pos2 = strrpos($str, "/");
			return substr($str, 0, $pos2);
		}
	}
	
	class Comment
	{
		public $authorID;
		public $commentID;
		public $postID;
		public $parent;
		public $score;
		public $downvotes;
		public $time;
		public $text;
		
		
		function __construct($commentID, $authorID, $postID, $parent, $text, $score, $time, $downvotes)
		{
			$this->authorID  = $authorID;
			$this->commentID = $commentID;
			$this->postID 	 = $postID;
			$this->parent	 = $parent;
			$this->text		 = $text;
			$this->score 	 = $score;
			$this->downvotes = $downvotes;
			$this->time		 = new Timestamp($time);
		}
		
		public static function create($conn, $authorID, $postID, $parent, $text)
		{
			$time = time();
			$sql="INSERT INTO Comments SET authorid='$authorID', postID='$postID', text='$text', score='1', parent='$parent', time='$time', downvotes='0'";
			mysqli_query($conn, $sql);
			return mysqli_insert_id ($conn);
		}
		
		public static function commentExists($conn, $id)
		{
			$result = mysqli_query($conn, "SELECT postid FROM Comments WHERE commentid='$id'");
			$num = mysqli_num_rows($result);
			return $num > 0;
		}
		
		public static function getID($conn, $id)
		{
			$result = mysqli_query($conn, "SELECT * FROM Comments WHERE commentid='$id'");
			$details = mysqli_fetch_row($result);
			
			$comment = new Comment($details[0], $details[1], $details[2], $details[3], $details[4], $details[5], $details[6], $details[7]);
			return $comment;
		}
		
		public function upvote($conn)
		{
			$this->score = $this->score + 1;
			$sql = "UPDATE Comments SET score='$this->score' WHERE commentid='$this->commentID'";
			mysqli_query($conn, $sql);
		}
		
		public function unupvote($conn)
		{
			$this->score = $this->score - 1;
			$sql = "UPDATE Comments SET score='$this->score' WHERE commentid='$this->commentID'";
			mysqli_query($conn, $sql);
		}
		
		public function delete($conn)
		{
			$sql = "UPDATE Comments SET authorid='[deleted]', text='[deleted]' WHERE commentid='$this->commentID'";
			mysqli_query($conn, $sql);
		}
		
		public function downvote($conn)
		{
			$this->downvotes = $this->downvotes + 1;
			$sql = "UPDATE Comments SET downvotes='$this->downvotes' WHERE commentid='$this->commentID'";
			mysqli_query($conn, $sql);
		}
		
		public function undownvote($conn)
		{
			$this->downvotes = $this->downvotes - 1;
			$sql = "UPDATE Comments SET downvotes='$this->downvotes' WHERE commentid='$this->commentID'";
			mysqli_query($conn, $sql);
		}
		
		public static function get($details)
		{
			$comment = new Comment($details[0], $details[1], $details[2], $details[3], $details[4], $details[5], $details[6], $details[7]);
			return $comment;
		}
		
		public static function renderComments($conn, $postID, $parent, $levels)
		{
			global $root;
			if ($parent != 0)
			{
				$comment = Comment::getID($conn, $parent);
				$comment->render($levels);
			}
			if ($levels > 0)
			{
				$result = mysqli_query($conn, "SELECT * FROM Comments WHERE parent='$parent' AND postid='$postID' ORDER BY (score-downvotes) DESC, time DESC");
				if ($result != false && mysqli_num_rows($result) > 0)
				{
					while($row = $result->fetch_array())
					{
						$comment = Comment::get($row);
						Comment::renderComments($conn, $postID, $comment->commentID, $levels - 1);
					}
				}
			}
			else if ($levels == 0)
			{
				$result = mysqli_query($conn, "SELECT commentid FROM Comments WHERE parent='$parent' AND postid='$postID' ORDER BY (score-downvotes) DESC, time DESC");
				if ($result != false && mysqli_num_rows($result) > 0)
				{
					while($row = $result->fetch_array())
					{
						?>
							<div class = "commentLink" id = "comment<? echo $row[0]; ?>">
								<a href = "<? echo $root; ?>/post/<? echo $postID; ?>/comment/<? echo $row[0]; ?>">Continue comment thread...</a>
							</div>
						<?
					}
				}
			}
			if ($parent != 0)
			{
				?>
				</div>
				<?
			}
		}
		
		public function render($level=5)
		{
			global $root;
			?>
				
				<div class = "comment<? if ($level == -1) { echo " userPage"; }  ?>" id = "comment<? echo $this->commentID; ?>">
					<div class = "commentVoting">
						<div class = "vote upvote <? if (isCommentUpvoted($this->commentID)) { echo "chosen"; } ?>"><div class = "highlight"></div></div>
						<div class = "vote downvote <? if (isCommentDownvoted($this->commentID)) { echo "chosen"; } ?>"><div class = "highlight"></div></div>
					</div>
					<p class = "commentAuthorPointsTime">
						<a class = "commentUsername" <? if ($this->authorID != "[deleted]") { ?> href = "<? echo $root; ?>/user/<? echo $this->authorID; ?>" <? } ?>><?
							echo $this->authorID;
						?></a>
						<span class = "commentScore">
							<? echo ($this->score - $this->downvotes); ?>
						</span> points
						<span title = "<? echo $this->time->stringRep(); ?>">
							<?
								echo $this->time->howLongAgo();
							?>
						</span>
						<span class = "upvotesDownvotes">
							(
							<span class = "upvoteCount">
								<?
									echo "+" . $this->score;
								?>
							</span>
							|
							<span class = "downvoteCount">
								<?
									echo "-" . $this->downvotes;
								?>
							</span>
							)
						</span>
					</p>
					<span class = "textOfComment"><? echo $this->text; ?></span>
					
					<br>
					<a href = "<? echo $root; ?>/post/<? echo $this->postID; ?>/comment/<? echo $this->commentID; ?>"><span class = "commentOption">Permalink</span></a>
					
					<? if (isLoggedIn() && $this->authorID == username()) { ?>
						<span class = "commentOption deleteComment">Delete</span>
					<? } ?>
					
					<? if ($level != -1 && isLoggedIn()) { ?>
						<span class = "commentOption showComment">Reply</span>
						<span class = "commentOption showComment2">Reply</span>
					<? } ?>
				</div>
				<? if ($level != -1) { ?>
					<form action="javascript:postComment(<? echo $this->commentID; ?>)" class = "commentForm unexpanded" id="commentForm<? echo $this->commentID; ?>" style="display:none">
						<div class="textInput" style="width: 100%;">
							<textarea id="text0" name="text" class = "commentText" validated></textarea>
							<label for="text0" class="label"><span>Comment</span></label>
							<label for="text0" class="error"><span>Invalid username</span></label>
						</div>
						<br>
						<br>
						<div class = "button cancel" tabindex="0">Cancel</div><input type = "submit" tabindex="0">
					</form>
				<? } ?>
				<? if ($level != -1) {?><div class = "commentThread"><? } ?>
			<?
		}
	}

	class Message
	{
		public $authorID;
		public $recipientID;
		public $messageID;
		public $time;
		public $text;
		public $data;
		public $threadtime;
		public $read;
		public $threadid;
		
		
		function __construct($messageID, $authorID, $time, $text, $data, $recipientID, $read, $threadid, $threadtime)
		{
			$this->authorID  = $authorID;
			$this->messageID = $messageID;
			$this->text		 = $text;
			$this->data 	 = $data;
			$this->recipientID = $recipientID;
			$this->read 	 = $read;
			$this->time		 = new Timestamp($time);
			$this->threadid  = $threadid;
			$this->threadtime = new Timestamp($threadtime);
		}

		
		public static function create($conn, $authorID, $text, $data, $recipientID, $threadid = -1)
		{
			$time = time();
			$sql="INSERT INTO Messages SET authorid='$authorID', text='$text', data='$data', recipientid='$recipientID', time='$time', wasRead = '0', threadid = '$threadid', threadtime = '$time'";
			mysqli_query($conn, $sql);
			$id = mysqli_insert_id ($conn);
			if ($threadid == -1)
			{
				$sql = "UPDATE Messages SET threadid = '$id' WHERE messageid = '$id'";
				mysqli_query($conn, $sql);
			}
			return $id;
		}
		
		public static function commentExists($conn, $id)
		{
			$result = mysqli_query($conn, "SELECT messageid FROM Messages WHERE messageid='$id'");
			$num = mysqli_num_rows($result);
			return $num > 0;
		}
		
		public static function getID($conn, $id)
		{
			$result = mysqli_query($conn, "SELECT * FROM Messages WHERE messageid='$id'");
			$details = mysqli_fetch_row($result);
			
			$comment = new Message($details[0], $details[1], $details[2], $details[3], $details[4], $details[5], $details[6], $details[7], $details[8], $details[9]);
			return $comment;
		}
		
		public function delete($conn)
		{
			//$sql = "UPDATE Comments SET authorid='[deleted]', text='[deleted]' WHERE commentid='$this->commentID'";
			//mysqli_query($conn, $sql);
		}
		
		public static function get($details)
		{
			$comment = new Message($details[0], $details[1], $details[2], $details[3], $details[4], $details[5], $details[6], $details[7], $details[8], $details[9]);
			return $comment;
		}
		
		
		public function render($conn, $isFirst = false, $isLast = false)
		{
			global $root;
			if (substr($this->data, 0, 7) == "comment")
			{
				$pid = substr($this->data, 7);
				$post = Post::getID($conn, $pid);
				?>
					<div class = "commentReply">Comment reply - <a href = "<? echo $root; ?>/post/<? echo $pid; ?>"><? echo $post->title; ?></a></div>
				<?
				Comment::getID($conn, $this->text)->render(-1);
			}
			elseif (substr($this->data, 0, 4) == "post")
			{
				$pid = substr($this->data, 4);
				$post = Post::getID($conn, $pid);
				?>
					<div class = "commentReply">Post reply - <a href = "<? echo $root; ?>/post/<? echo $pid; ?>"><? echo $post->title; ?></a></div>
				<?
				Comment::getID($conn, $this->text)->render(-1);
			}
			elseif (substr($this->data, 0, 3) == "msg")
			{
				$this->renderConvo(substr($this->data, 3), $isFirst, $isLast);
				
			}
		}

		public function renderConvo($title, $isFirst = false, $isLast = false)
		{
			global $root;
			$isLastPost = $isLast ? true : ($this->time == $this->threadtime);
			$isFirstPost = $isFirst ? true : ($this->messageID === $this->threadid);
			?>
				
				<? if ($isFirstPost) {?>
					<div class = "commentReply"><? echo $title; ?></div>
					<div class = "commentThread messageThread">
				<? } ?>
				<div class = "message" id = "message<? echo $this->messageID; ?>">

					<p class = "commentAuthorPointsTime">
						<a class = "commentUsername" <? if ($this->authorID != "[deleted]") { ?> href = "<? echo $root; ?>/user/<? echo $this->authorID; ?>" <? } ?>><?
							echo $this->authorID;
						?></a>
						<span title = "<? echo $this->time->stringRep(); ?>"><?
							echo $this->time->howLongAgo();
					?></span>
					</p>
					<? echo $this->text; ?>
					
					<br>

					<? if ($isLastPost) { ?>
						<span class = "commentOption showComment">Reply</span>
						<span class = "commentOption showComment2">Reply</span>
					<? } ?>
				</div>
				<? if ($isLastPost) {?>
					<form action="javascript:postMessage(<? echo $this->messageID; ?>)" class = "commentForm messageForm unexpanded" id="commentForm<? echo $this->messageID; ?>" style="display:none">
						<div class="textInput" style="width: 100%;">
							<textarea id="text0" name="text" class = "commentText" validated></textarea>
							<label for="text0" class="label"><span>Message<span></label>
							<label for="text0" class="error"><span>Invalid username</span></label>
						</div>
						<br>
						<br>
						<div class = "button cancel" tabindex="0">Cancel</div><input type = "submit" tabindex="0">
					</form>
					</div>
				<? } ?>
			<?
		}

		public function renderNewMsg($title)
		{
			global $root;
			$isLastPost = false;
			$isFirstPost = false;
			?>
				
				<? if ($isFirstPost) {?>
					<div class = "commentReply"><? echo $title; ?></div>
					<div class = "commentThread messageThread">
				<? } ?>
				<div class = "message" id = "message<? echo $this->messageID; ?>">

					<p class = "commentAuthorPointsTime">
						<a class = "commentUsername" <? if ($this->authorID != "[deleted]") { ?> href = "<? echo $root; ?>/user/<? echo $this->authorID; ?>" <? } ?>><?
							echo $this->authorID;
						?></a>
						<span title = "<? echo $this->time->stringRep(); ?>"><?
							echo $this->time->howLongAgo();
					?></span>
					</p>
					<span class = "textOfComment"><? echo $this->text; ?></span>
					
					<br>

					<? if ($isLastPost) { ?>
						<span class = "commentOption showComment">Reply</span>
					<? } ?>
				</div>
				<? if ($isLastPost) {?>
					<form action="javascript:postMessage(<? echo $this->messageID; ?>)" class = "commentForm messageForm unexpanded" id="commentForm<? echo $this->messageID; ?>" style="display:none">
						<div class="textInput" style="width: 100%;">
							<textarea id="text0" name="text" class = "commentText" validated></textarea>
							<label for="text0" class="label"><span>Message<span></label>
							<label for="text0" class="error"><span>Invalid username</span></label>
						</div>
						<br>
						<br>
						<div class = "button cancel" tabindex="0">Cancel</div><input type = "submit" tabindex="0">
					</form>
					</div>
				<? } ?>
			<?
		}
	}
	
	class Timestamp
	{
		public $timestamp;
		
		function __construct($timestamp)
		{
			$this->timestamp 	 = $timestamp;
		}
		
		public function stringRep()
		{
			return gmdate("D, M j Y", $this->timestamp) . " at " . gmdate("g:i A e", $this->timestamp) . " " . $timestamp;
		}
		
		public function howLongAgo()
		{
			$second = 1;
			$minute = 60 * 1;
			$hour   = 60 * 60 * 1;
			$day    = 24 * 60 * 60 * 1;
			$week   = 7 * 24 * 60 * 60 * 1;
			$month  = 30 * 24 * 60 * 60 * 1;
			$year   = 365 * 24 * 60 * 60 * 1;
			
			$diff = time() - $this->timestamp;
			if ($diff / $year > 1)
			{
				$num = floor($diff / $year);
				if ($num == 1)
				{
					return  $num . " year ago";
				}
				else
				{
					return  $num . " years ago";
				}
			}
			else if ($diff / $month > 1)
			{
				$num = floor($diff / $month);
				if ($num == 1)
				{
					return  $num . " month ago";
				}
				else
				{
					return  $num . " months ago";
				}
			}
			else if ($diff / $week > 1)
			{
				$num = floor($diff / $week);
				if ($num == 1)
				{
					return  $num . " week ago";
				}
				else
				{
					return  $num . " weeks ago";
				}
			}
			else if ($diff / $day > 1)
			{
				$num = floor($diff / $day);
				if ($num == 1)
				{
					return  $num . " day ago";
				}
				else
				{
					return  $num . " days ago";
				}
			}
			else if ($diff / $hour > 1)
			{
				$num = floor($diff / $hour);
				if ($num == 1)
				{
					return  $num . " hour ago";
				}
				else
				{
					return  $num . " hours ago";
				}
			}
			else if ($diff / $minute > 1)
			{
				$num = floor($diff / $minute);
				if ($num == 1)
				{
					return  $num . " minute ago";
				}
				else
				{
					return  $num . " minutes ago";
				}
			}
			if ($diff == 1)
			{
				return  "just now";
			}
			else
			{
				return  $diff . " seconds ago";
			}
			
		}
	}
?>