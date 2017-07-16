<!DOCTYPE html>
<html>
	<head>
		<?php require_once("imports.php"); ?>
		<title>Content Aggregator</title>
	</head>
	<body>
		<?php $pageType = "post"; require_once("navbar.php"); ?>
		<div id = "main">
			<?php
				$id = $_GET['id'];
				$comment = 0;
				
				?>
					<script>var postId = <?php echo $id; ?>;</script>
				<?
			
				if (isset($_GET['comment']))
				{
					if (Comment::commentExists($conn, $_GET['comment']))
					{
						$comment = $_GET['comment'];
					}
					else
					{
						header( 'Location: /post/' . $id );
					}
					
				}
				if (!Post::postExists($conn, $id))
				{
					header( 'Location: /index' );
				}
				else
				{
					$result = mysqli_query($conn, "SELECT * FROM Posts WHERE postid = '$id'");
					$details = mysqli_fetch_row($result);
					$post = Post::Get($details);
					$post->render($conn);
					
					$doRenderParent = 0;
					
					?>
						<div class = "postComments">
					<?
					
					if ($comment == 0)
					{
						if (isLoggedIn())
						{
							?>
								<form action="javascript:postComment(0)" class = "commentForm" id="commentForm0">
									<div class="textInput" style="width: 100%;">
										<textarea id="text0" name="text" class = "commentText" validated></textarea>
										<label for="text0" class="label"><span>Comment</span></label>
										<label for="text0" class="error"><span>Invalid comment</span></label>
									</div>
									<br>
									<br>
									<input type = "submit" tabindex="0">
								</form>
							<?
						}
					}
					else
					{
						?>
							<div class = "viewEntireThread">
								<a href = "<? echo $root; ?>/post/<? echo $id; ?>">< View all comments</a>
							</div>
						<?
						$commentR = Comment::getID($conn, $comment);
						$parent = $commentR->parent;
						if ($parent != 0)
						{
							$comment2 = Comment::getID($conn, $parent);
							$comment2->render();
							$doRenderParent = 1;
						}
					}
					Comment::renderComments($conn, $id, $comment, 7);
					if ($doRenderParent)
					{
						?> </div> <?
					}
					?> </div> <?
				}
			?>
			<script>
				function postComment(commentId)
				{

					var cid = commentId;
					var form = "#commentForm" + commentId;
					var text = $(form + " textarea").val();
					postCommentFinal(cid, form, text, true);
				}

				function postCommentFinal(cid, form, text, toggleForm)
				{
					if (isLoggedIn)
					{
						console.log("#commentForm" + cid + " " + text);
						if (text.length != 0 && text.length <= 2500)
						{
							$.ajax({ url: root + '/action.php',
								data: {action: 'comment', postId: postId, parent: cid, text: text},
								type: 'post',
								success: function(data)
								{
									console.log(data);
									if (data != "false")
									{
										if (cid != 0 && toggleForm)
										{
											$(form).slideToggle();
										}
										$(form + " textarea").val("");
										setTimeout(function()
										{
											if (cid == 0)
											{
												$(data).insertAfter($(form));
											}
											else
											{

												$(form).next(".commentThread").prepend($(data));
											}
											
										}, 400);
										
									}
									
								}
							});
						}
						else
						{
							var label = $(form + ' label.error > span');
							$(form + " textarea").addClass("invalid");
							label.text('Comment text cannot be blank');
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
					var id = parseInt($(this).closest('.comment').attr('id').substring(7));
					var form = $("#commentForm" + id);
					form.slideToggle();


				});
				
				var replyPostID;
				var replyOptions = [["SUBMIT", "submit"],["CANCEL", "cancel"]];
				$(document).on('click', '.showComment2', function()
				{
					replyPostID = parseInt($(this).closest('.comment').attr('id').substring(7));
					var textarea = '<br><div class="textInput" style="width: 100%;">\n<textarea id="dialogReply" name="text" class = "commentText" validated noResize></textarea>\n<label for="dialogReply" class="label"><span>Comment</span></label>\n<label for="dialogReply" class="error"><span>Invalid comment</span></label>\n</div>';
					confirmWindow("replyComment", "Reply", textarea, replyOptions, true);


				});

				$(document).on('click', "#dialog.replyComment .submit", function()
				{
					var cid = replyPostID;
					var form = "#commentForm" + cid;
					var text = $("#dialogReply").val();
					postCommentFinal(cid, form, text, false);
				});

				
				$(document).on('click', '.commentForm .cancel', function()
				{
					var id = parseInt($(this).closest('.commentForm').attr('id').substring(11));
					var form = $("#commentForm" + id);
					form.slideToggle();

				});


			</script>
		</div>
		
		<?php require_once("updownvote.php"); ?> 
		<?php include("inputcode.php"); ?>
	</body>
</html>