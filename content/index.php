<!DOCTYPE html>
<html>
	<head>
		<?php $pageType = "main"; require_once("imports.php"); ?>
		<title>Content Aggregator</title>
	</head>
	<body>
		<?php require_once("navbar.php"); ?>
		
		<div id = "main">
		
			<?php
				$page = 1;
				
				if (isset($_GET['page']))
				{
					$page = $_GET['page'];
				}

				$lowerBound = ($page - 1) * 10;

				$result = mysqli_query($conn, "SELECT * FROM Posts WHERE authorid <> '[deleted]' ORDER BY (score - downvotes) DESC, time DESC LIMIT 11 OFFSET $lowerBound");
				
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
						echo "<a href = '" . $root . "/page/" . ($page - 1) . "'><div class = 'changePage prev'></div></a>";
					}
					else
					{
						echo "<div class = 'changePage prev disabled'></div>";
					}
					
					if ($nextPage)
					{
						echo "<a href = '" . $root . "/page/" . ($page + 1) . "'><div class = 'changePage next'></div></a>";
					}
					else
					{
						echo "<div class = 'changePage next disabled'></div>";
					}
					
					echo "<span class = 'pageNum'>Page " . $page . "</span>";
				?>
			</div>
						
			<?
				
				
				$k = 0;
				while(($row = $result->fetch_array()) && ($k < 10))
				{
					$post = Post::Get($row);
					$post->render($conn);
					$k++;
				}
			?>
			
			<div class = "navigation bottom">
				<?
					if ($prevPage)
					{
						echo "<a href = '" . $root . "/page/" . ($page - 1) . "'><div class = 'changePage prev'></div></a>";
					}
					else
					{
						echo "<div class = 'changePage prev disabled'></div>";
					}
					
					if ($nextPage)
					{
						echo "<a href = '" . $root . "/page/" . ($page + 1) . "'><div class = 'changePage next'></div></a>";
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