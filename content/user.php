<!DOCTYPE html>
<html>
	<head>
		<?php $pageType = "user"; require_once("imports.php"); ?>
		<title>Content Aggregator</title>
	</head>
	<body>
		<?php require_once("navbar.php"); ?>
		
		<div id = "main">
			<?php
				$uid = $_GET['id'];
				$page = 1;

				if (!User::userExists($conn, $uid))
				{
					header( 'Location: /index' );
				}
				
				if (isset($_GET['page']))
				{
					$page = $_GET['page'];
				}
				
				$lowerBound = ($page - 1) * 10;
				
				$result = mysqli_query($conn, "SELECT postid as id, time, score, downvotes, '0' as type FROM Posts WHERE authorid = '$uid'
				UNION ALL SELECT commentid as id, time, score, downvotes, '1' as type FROM Comments WHERE authorid = '$uid' ORDER BY time DESC LIMIT 11 OFFSET $lowerBound");
				
				$numRows = mysqli_num_rows($result);
				
				$nextPage = $numRows == 11;
				$prevPage = $page > 1;

				
				if ($numRows == 0)
				{
					header( 'Location: /index' );
				}
			?>
			
			<div class = "navigation top">
				<?
					if ($prevPage)
					{
						echo "<a href = '/user/" . ($uid) . "/page/" . ($page - 1) . "'><div class = 'changePage prev'></div></a>";
					}
					else
					{
						echo "<div class = 'changePage prev disabled'></div>";
					}
					
					if ($nextPage)
					{
						echo "<a href = '/user/" . ($uid) . "/page/" . ($page + 1) . "'><div class = 'changePage next'></div></a>";
					}
					else
					{
						echo "<div class = 'changePage next disabled'></div>";
					}
					
					echo "<span class = 'pageNum'>Page " . $page . "</span>";
				?>
			</div>
			
			<?
			
				$i = 0;
				while(($row = $result->fetch_array()) && $i < 10)
				{
					$id = $row[0];
					if ($row[4] == 0)
					{
						if (Post::postExists($conn, $id))
						{
							$post = Post::GetID($conn, $id);
							$post->render($conn);
						}
					}
					else
					{
						if (Comment::commentExists($conn, $id))
						{
							$comment = Comment::GetID($conn, $id);
							$comment->render(-1);
							?> </div> <?
						}
					}
					$i++;
				}

			?>
			
			<div class = "navigation bottom">
				<?
					if ($prevPage)
					{
						echo "<a href = '/user/" . ($uid) . "/page/" . ($page - 1) . "'><div class = 'changePage prev'></div></a>";
					}
					else
					{
						echo "<div class = 'changePage prev disabled'></div>";
					}
					
					if ($nextPage)
					{
						echo "<a href = '/user/" . ($uid) . "/page/" . ($page + 1) . "'><div class = 'changePage next'></div></a>";
					}
					else
					{
						echo "<div class = 'changePage next disabled'></div>";
					}
					
					echo "<span class = 'pageNum'>Page " . $page . "</span>";
				?>
			</div>
		</div>
		
		<?php require_once("updownvote.php"); ?> 
	</body>
</html>