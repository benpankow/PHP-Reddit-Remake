<!DOCTYPE html>
<html>
	<head>
		<?php include("imports.php"); ?>
		<title>Content Aggregator</title>
	</head>
	<body>
		<?php require_once("navbar.php"); ?>
		<?php
			if (isLoggedIn())
			{
				header( 'Location: ' . $root . '/index.php' );
			}
			
			$user = $_POST['user'];
			$pass = $_POST['pass'];
			
			if (preg_match('/^([a-zA-Z0-9_-])+$/', $user) &&
				preg_match('/^([a-zA-Z0-9!@#$%^&*_-])+$/', $pass))
			{
				
				
				$result = mysqli_query($conn, "SELECT * FROM Users WHERE username='$user'");
				$amount_users = mysqli_num_rows($result);
				if ($amount_users == "1")
				{
					$details = mysqli_fetch_row($result);
					$comparePass = $details[1];
				
					$length = strlen($pass);
					$hashPass = hash('sha512', $pass);
					$salt = substr($comparePass, $length, 128);
					$hashPass = substr($hashPass, 0, $length) . $salt . substr($hashPass, $length);
					
					if ($comparePass == $hashPass)
					{
						$_SESSION['session'] = User::get($conn, $user);
						$_SESSION['ip'] = getenv ( "REMOTE_ADDR" );
						header( 'Location: index' );
					}
				}
				else
				{
					ECHO "ERROR: Username doesn't exist!";
				}
			}
		?>
		
		<div class = "middleBox">
			<form action="login.php" method="post" id="register">
				<div class="textInput" style="width: 100%;">
					<input type="text" id="user" name="user" validated>
					<label for="user" class="label"><span>Username</span></label>
					<label for="user" class="error"><span>Invalid username</span></label>
				</div>
				<br>
				<br>
				<br>
				<br>
				<div class="textInput" style="width: 100%;">
					<input type="password" id="pass" name="pass" validated>
					<label for="pass" class="label"><span>Password</span></label>
					<label for="pass" class="error"><span>Invalid password</span></label>
				</div>
				<br>
				<br>
				<br>
				<input type = "submit" id="submit" tabindex="0"></div>
			</form>
		</div>
			
			
		<script>
			
			$("#register").submit(function(e) {
				
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
				
				checkPassBlank($("#pass"));
				checkUserBlank($("#user"));
			});
			
			function checkPassBlank(obj)
			{
				var length = obj.val().length;
				var label = $('label[for="' + obj.attr('id') + '"].error > span');
				if (length == 0)
				{
					obj.addClass("invalid");
					label.text('Password cannot be blank');
				}
			}
			
			function checkUserBlank(obj)
			{
				var length = obj.val().length;
				var label = $('label[for="' + obj.attr('id') + '"].error > span');
				if (length == 0)
				{
					obj.addClass("invalid");
					label.text('Username cannot be blank');
				}
			}
			
			$("#pass").on("focus change keyup keydown paste",function(e)
			{
				var length = $(this).val().length;
				var label = $('label[for="'+$(this).attr('id')+'"].error > span');
				var regex = /^([a-zA-Z0-9!@#$%^&*_-])+$/;
				
				if (!regex.test($(this).val()) && length > 0)
				{
					$(this).addClass("invalid");
					label.text('Password may only contain alphanumeric characters and !, @, #, $, %, ^, &, *, _ and -');
				}
				else if (length < 8 && length > 0)
				{
					$(this).addClass("invalid");
					label.text('Password cannot be less than 8 characters');
				}
				else if (length > 64)
				{
					$(this).addClass("invalid");
					label.text('Password must be less than 64 characters');
				}
				else
				{
					$(this).removeClass("invalid");
				}
			});
			
			$("#user").on("focus change keyup keydown paste",function(e)
			{
				var length = $(this).val().length;
				var label = $('label[for="'+$(this).attr('id')+'"].error > span');
				var regex = /^([a-zA-Z0-9_-])+$/;
				
				if (!regex.test($(this).val()) && length > 0)
				{
					$(this).addClass("invalid");
					label.text('Username may only contain alphanumeric characters, _ and -');
				}
				else if (length < 8 && length > 0)
				{
					$(this).addClass("invalid");
					label.text('Username must be more than 8 characters');
				}
				else if (length > 24)
				{
					$(this).addClass("invalid");
					label.text('Username must be less than 24 characters');
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