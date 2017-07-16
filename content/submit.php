<!DOCTYPE html>
<html>
	<head>
		<?php $pageType = "submit"; include("imports.php"); ?>
		<title>Content Aggregator</title>
	</head>
	<body>
		<?php require_once("navbar.php"); ?>
		<?php
			if (!isLoggedIn())
			{
				header( 'Location: ' . $root . '/index.php' );
			}
			
			$title = $_POST['title'];
			$url = htmlspecialchars($_POST['url']);
			$url = htmlspecialchars($url);
			$pattern = '/(?:https?:\/\/)?(?:[a-zA-Z0-9.-]+?\.(?:[a-zA-Z])|\d+\.\d+\.\d+\.\d+)/';


			if (preg_match('/^([a-zA-Z0-9!@#$%^&*_ -])+$/', $title) && preg_match($pattern, $url))
			{
				if (substr($url, 0, 7) != "http://" && substr($url, 0, 8) != "https://" && substr($url, 0, 5) != "ftp://")
				{
					$url = "http://" . $url;
				}
				$id = Post::create($conn, $title, username(), $url);
				array_push(user()->posts, $id);
				array_push(user()->upvoted, $id);
				user()->lkarma = user()->lkarma + 1;
				user()->save($conn);
				header( 'Location: ' . $root . '/post/' . $id );
			}
		?>
		
		<div class = "middleBox">
			<form action="submit.php" method="post" id="submit">
				<div class="textInput" style="width: 100%;">
					<input type="text" id="title" name="title" validated>
					<label for="title" class="label"><span>Title</span></label>
					<label for="title" class="error"><span>Invalid title</span></label>
				</div>
				<br>
				<br>
				<br>
				<br>
				<div class="textInput" style="width: 100%;">
					<input type="text" id="url" name="url" validated class = "<? if (!preg_match($pattern, $url)) { echo "invalid"; }?>">
					<label for="url" class="label"><span>URL</span></label>
					<label for="url" class="error"><span>Invalid URL</span></label>
				</div>
				<br>
				<br>
				<br>
				<input type = "submit" id="submit" tabindex="0"></div>
			</form>
		</div>
			
			
		<script>
			
			$("#submit").submit(function(e) {
				
				var num = $(".invalid").length;
				var blank = true;
				$("input:not('#submit')").each(function(index) {
					if ($(this).val().length == 0)
					{
						blank = false;
						return false;
					}
				});
				
				if (num == 0 && blank)
				{
				}
				else
				{
					e.preventDefault();
				}
				
				checkTitleBlank($("#title"));
				checkURLBlank($("#url"));
			});
			
			function checkTitleBlank(obj)
			{
				var length = obj.val().length;
				var label = $('label[for="' + obj.attr('id') + '"].error > span');
				if (length == 0)
				{
					obj.addClass("invalid");
					label.text('Title cannot be blank');
				}
			}
			
			function checkURLBlank(obj)
			{
				var length = obj.val().length;
				var label = $('label[for="' + obj.attr('id') + '"].error > span');
				if (length == 0)
				{
					obj.addClass("invalid");
					label.text('URL cannot be blank');
				}
			}
			
			$("#title").on("focus change keyup keydown paste",function(e)
			{
				var length = $(this).val().length;
				var label = $('label[for="'+$(this).attr('id')+'"].error > span');
				var regex = /^([a-zA-Z0-9!@#$% ^&*_-])+$/;
				
				if (!regex.test($(this).val()) && length > 0)
				{
					$(this).addClass("invalid");
					label.text('Titles may only contain alphanumeric characters, spaces, a !, @, #, $, %, ^, &, *, _ and -');
				}
				else if (length > 300)
				{
					$(this).addClass("invalid");
					label.text('Title must be less than 300 characters');
				}
				else
				{
					$(this).removeClass("invalid");
				}
			});
			
			$("#url").on("focus change keyup keydown paste",function(e)
			{
				var length = $(this).val().length;
				var label = $('label[for="'+$(this).attr('id')+'"].error > span');
				
				if (length > 1000)
				{
					$(this).addClass("invalid");
					label.text('URLs must be less than 1000 characters');
				}
				else
				{
					$(this).removeClass("invalid");
				}
			});
		</script>

		<?php include("inputcode.php"); ?>
	</body>
</html>