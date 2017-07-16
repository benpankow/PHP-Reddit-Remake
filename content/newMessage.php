<!DOCTYPE html>
<html>
	<head>
		<?php include("imports.php"); ?>
		<title>Content Aggregator</title>
	</head>
	<body>
		<?php require_once("navbar.php"); ?>
		<?php
			if (!isLoggedIn())
			{
				header( 'Location: ' . $root . '/index.php' );
			}
			
			$to = htmlspecialchars($_POST['to']);
			$title = htmlspecialchars($_POST['title']);
			$text = htmlspecialchars($_POST['text']);
			$text = nl2br(trim($text));
			$uex = User::userExists($conn, $to);
			
			if ($uex && strlen($title) > 0 && strlen($text) > 0 && strlen($text) <= 2500 && strlen($title) < 30 && isLoggedIn())
			{
				$id = Message::create($conn, username(), $text, "msg" . $title, $to);
				header( 'Location: ' . $root . '/messages' );
			}
		?>
		
		<div class = "middleBox">
			<form action="newMessage.php" method="post" id="submit">
				<div class="textInput" style="width: 100%;">
					<input type="text" id="to" name="to" validated value = "<? echo $to; ?>" class = "<? if (isset($_POST['to'])) { echo "invalid"; }?>">
					<label for="to" class="label"><span>Recipient</span></label>
					<label for="to" class="error"><span>Invalid recipient</span></label>
				</div>
				<br>
				<br>
				<br>
				<div class="textInput" style="width: 100%;" >
					<input type="text" id="title" name="title" validated value = "<? echo $title; ?>">
					<label for="title" class="label"><span>Subject</span></label>
					<label for="title" class="error"><span>Invalid subject</span></label>
				</div>
				<br>
				<br>
				<br>
				<div class="textInput" style="width: 100%;">
					<textarea id="text" name="text" validated><? echo $text; ?></textarea>
					<label for="text" class="label"><span>Text</span></label>
					<label for="text" class="error"><span>Invalid text</span></label>
				</div>
				<br>
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
				checkTextBlank($("#text"));
				checkRecipientBlank($("#to"));
			});
			
			function checkTitleBlank(obj)
			{
				var length = obj.val().length;
				var label = $('label[for="' + obj.attr('id') + '"].error > span');
				if (length == 0)
				{
					obj.addClass("invalid");
					label.text('Subject cannot be blank');
				}
			}

			function checkRecipientBlank(obj)
			{
				var length = obj.val().length;
				var label = $('label[for="' + obj.attr('id') + '"].error > span');
				if (length == 0)
				{
					obj.addClass("invalid");
					label.text('Recipient cannot be blank');
				}
				else
				{
					$.ajax({ url: root + '/action.php',
						data: {action: 'userExists', user: obj.val()},
						type: 'post',
						success: function(data)
						{
							if (data == "false")
							{
								obj.addClass("invalid");
								label.text('Invalid recipient');
							}
						}	
					});
				}

			}
			
			function checkTextBlank(obj)
			{
				var length = obj.val().length;
				var label = $('label[for="' + obj.attr('id') + '"].error > span');
				if (length == 0)
				{
					obj.addClass("invalid");
					label.text('Text cannot be blank');
				}
			}
			
			$("#title").on("focus change keyup keydown paste",function(e)
			{
				var length = $(this).val().length;
				var label = $('label[for="'+$(this).attr('id')+'"].error > span');
				
				if (length > 30)
				{
					$(this).addClass("invalid");
					label.text('Subjects must be less than 31 characters');
				}
				else
				{
					$(this).removeClass("invalid");
				}
			});

			$("#to").on("focus change keyup keydown paste",function(e)
			{
				$(this).removeClass("invalid");
			});
			
			$("#text").on("focus change keyup keydown paste",function(e)
			{
				var length = $(this).val().length;
				var label = $('label[for="'+$(this).attr('id')+'"].error > span');
				
				if (length > 2500)
				{
					$(this).addClass("invalid");
					label.text('URLs must be less than 2500 characters');
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