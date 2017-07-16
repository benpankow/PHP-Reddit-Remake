<!DOCTYPE html>
<html>
	<head>
		<?php $pageType = "messages"; require_once("imports.php"); ?>
		<title>Content Aggregator</title>
	</head>
	<body>
		<?php require_once("navbar.php"); ?>
		
		<div id = "main">
			<?php
				$page = 1;

				if (!isLoggedIn())
				{
					header( 'Location: ' . $root . '/index' );
				}
				
				if (isset($_GET['page']))
				{
					$page = $_GET['page'];
				}
				
				$lowerBound = ($page - 1) * 10;
				$uid = username();
				
				$result = mysqli_query($conn, "SELECT * FROM Messages WHERE recipientid = '$uid' OR authorid = '$uid' ORDER BY threadtime DESC, time LIMIT 11 OFFSET $lowerBound");
				
				$numRows = mysqli_num_rows($result);

				$sql = "UPDATE Messages SET wasRead='1' WHERE (wasRead='-' AND recipientid = '$uid')";
				mysqli_query($conn, $sql);
				
				$nextPage = $numRows == 11;
				$prevPage = $page > 1;

				
				if ($numRows == 0)
				{
					//header( 'Location: /index' );
				}
			?>
			
			<div class = "navigation top">
				<?
					if ($prevPage)
					{
						echo "<a href = '/messages/page/" . ($page - 1) . "'><div class = 'changePage prev'></div></a>";
					}
					else
					{
						echo "<div class = 'changePage prev disabled'></div>";
					}
					
					if ($nextPage)
					{
						echo "<a href = '/messages/page/" . ($page + 1) . "'><div class = 'changePage next'></div></a>";
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
					$post = Message::Get($row);
					$post->render($conn, $i == 0, $i == 9);
					$i++;
				}

			?>
			
			<div class = "navigation bottom">
				<?
					if ($prevPage)
					{
						echo "<a href = '/messages/page/" . ($page - 1) . "'><div class = 'changePage prev'></div></a>";
					}
					else
					{
						echo "<div class = 'changePage prev disabled'></div>";
					}
					
					if ($nextPage)
					{
						echo "<a href = '/messages/page/" . ($page + 1) . "'><div class = 'changePage next'></div></a>";
					}
					else
					{
						echo "<div class = 'changePage next disabled'></div>";
					}
					
					echo "<span class = 'pageNum'>Page " . $page . "</span>";
				?>
			</div>
		</div>
		<script>
			function postMessage(messageId)
			{

				var mid = messageId;
				var form = "#commentForm" + messageId;
				var text = $(form + " textarea").val();
				postMessageFinal(mid, form, text, true);
			}

			function postMessageFinal(mid, form, text, toggleForm)
			{
				if (isLoggedIn)
				{

					console.log("#commentForm" + mid + " " + text);
					if (text.length != 0 && text.length <= 2500)
					{
						$.ajax({ url: root + '/action.php',
							data: {action: 'message', parent: mid, text: text},
							type: 'post',
							success: function(data)
							{
								console.log(data);
								if (data != "false")
								{
									$(form + " textarea").val("");

									$(form).before($(data));										
							
								}
								
							}
						});
					}
					else
					{
						var label = $(form + ' label.error > span');
						$(form + " textarea").addClass("invalid");
						label.text('Message text cannot be blank');
					}
				}

				
				
			}
			
			$(document).on("focus change keyup keydown paste", ".commentText", function(e)
			{
				var length = $(this).val().length;
				var label = $('label[for="'+$(this).attr('id')+'"].error > span');
				
				if (length > 2500)
				{
					$(this).addClass("invalid");
					label.text('Comment must be less than 2500 characters');
				}
				else
				{
					$(this).removeClass("invalid");
				}
			});
			
			$(document).on('click', '.showComment', function()
			{
				var id = parseInt($(this).closest('.message').attr('id').substring(7));
				var form = $("#commentForm" + id);
				form.slideToggle();


			});

			var replyPostID;
			var replyOptions = [["SUBMIT", "submit"],["CANCEL", "cancel"]];
			$(document).on('click', '.showComment2', function()
			{
				replyPostID = parseInt($(this).closest('.message').attr('id').substring(7));
				var textarea = '<br><div class="textInput" style="width: 100%;">\n<textarea id="dialogReply" name="text" class = "commentText" validated noResize></textarea>\n<label for="dialogReply" class="label"><span>Comment</span></label>\n<label for="dialogReply" class="error"><span>Invalid comment</span></label>\n</div>';
				confirmWindow("replyComment", "Reply", textarea, replyOptions, true);


			});

			$(document).on('click', "#dialog.replyComment .submit", function()
			{
				var cid = replyPostID;
				var form = "#commentForm" + cid;
				var text = $("#dialogReply").val();
				postMessageFinal(cid, form, text, false);
			});
			

			
			$(document).on('click', '.commentForm .cancel', function()
			{
				var id = parseInt($(this).closest('.commentForm').attr('id').substring(11));
				var form = $("#commentForm" + id);
				form.slideToggle();

			});
		</script>
		
		<?php require_once("updownvote.php"); ?>
		<?php include("inputcode.php"); ?> 
	</body>
</html>