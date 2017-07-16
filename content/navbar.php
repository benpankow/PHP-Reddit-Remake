<?php 	
	if (isLoggedIn())
	{
		?>
		<script>
			var isLoggedIn = true;
		</script>
		<?
	}
	else
	{
		?>
		<script>
			var isLoggedIn = false;
		</script>
		<?
	}
?>
<div id = "coverScreen">
	<div id = "dialog">
		<h2>Delete this comment?</h2>
		<span>This comment will be deleted forever (a long time!)</span>
		<div class = "buttons">
			<div class = "button compact">CANCEL</div>
			<div class = "button compact cancel">DELETE IT</div>
		</div>
	</div>
</div>

<script>
	$(document).on('click', "#dialog .button", function()
	{
		$("#coverScreen").hide();
	});
	
	function confirmWindow(identifier, title, text, buttons, inline)
	{
		$("#dialog").attr("class", "");
		$("#dialog").addClass(identifier);
		$("#coverScreen").show();
		$("#dialog h2").text(title);
		$("#dialog span").html(text);
	
		$("#dialog .buttons").html("");
		
		$.each(buttons, function(i, button)
		{
			$("#dialog .buttons").append("<div class = 'button " + button[1] + "'>" + button[0] + "</div>");
		});
		
		if (inline)
		{
			$("#dialog .button").addClass("compact");
		}
		else
		{
			$("#dialog .button").removeClass("compact");
		}
		
	}
</script>

<div id = "navbar">
	<?php if (isLoggedIn()) {?>
		<a href = "<? echo $root; ?>/user/<? echo user()->name; ?>">
			<div class = "navitem right userInfo">
				<div class = "highlightOuter"><div class = "buttonHighlight"></div></div>
				<? 
					user()->updateKarma($conn);
					echo user()->name . " (" . user()->lkarma . ")";
				
				?>
			</div>
		</a>
		<a href = "<? echo $root; ?>/messages">
			<?
				$uid = username();
				$result = mysqli_query($conn, "SELECT * FROM Messages WHERE (recipientid = '$uid' AND wasRead = '0') LIMIT 1");
				$numRows = mysqli_num_rows($result);				
			?>
			<div class = "navitem right notifications <? if ($numRows > 0 && $pageType != "messages") { echo "new"; } ?>">
				<div class = "highlightOuter"><div class = "buttonHighlight"></div></div>
				!
			</div>
		</a>
	<? } else { ?>
		<a href = "<? echo $root; ?>/login">
			<div class = "navitem right">
				<div class = "highlightOuter"><div class = "buttonHighlight"></div></div>
				Login
			</div>
		</a>
		<a href = "<? echo $root; ?>/register">
			<div class = "navitem right">
				<div class = "highlightOuter"><div class = "buttonHighlight"></div></div>
				Register
			</div>
		</a>
	<? } ?>
	<a href = "<? echo $root; ?>/">
		<div class = "navitem">
			<div class = "highlightOuter"><div class = "buttonHighlight"></div></div>
			Home
		</div>
	</a>
	<?php if (isLoggedIn()) {?>
		<? if ($pageType == "main" || $pageType == "submit" || $pageType == "post") {?>
			<a href = "<? echo $root; ?>/submit">
				<div class = "navitem">
					<div class = "highlightOuter"><div class = "buttonHighlight"></div></div>
					Submit
				</div>
			</a>
		<? } elseif ($pageType == "messages") {?>
			<a href = "<? echo $root; ?>/newMessage">
				<div class = "navitem large">
					<div class = "highlightOuter"><div class = "buttonHighlight"></div></div>
					New Message
				</div>
			</a>
		<? } ?>
	<? } ?>

</div>
<script>
		
	var mousePos = { x: -1, y: -1 };
	$(document).mousemove(function(event) {
		mousePos.x = event.pageX;
		mousePos.y = event.pageY;
	});
	
	$(".navitem").mousedown(function()
	{
		if (!$(this).hasClass("clicked"))
		{
			var left = mousePos.x - $(this).offset().left;
			var top = mousePos.y - $(this).offset().top;
			$(this).find(".buttonHighlight").css("margin-left", left);
			$(this).find(".buttonHighlight").css("margin-top", top);
			$(this).addClass("clicked");
			
			var button = $(this);
			
			setTimeout(function()
			{
				removeClicked(button);
			}, 500);
		}
		else
		{
			$(this).removeClass("clicked");
		}

	});
	
	function removeClicked(button)
	{
		button.removeClass("clicked");
	}
			
			
	$( document ).ready(function() {
		var url = window.location.href;
		url = url.substring(url.indexOf("benpankow.com") + 13);
		if (url.lastIndexOf(".") != -1)
		{
			url = url.substring(0, url.lastIndexOf("."));
		}
		if (url == "/index")
		{
			url = "/";
		}
		
		var index = url.indexOf("/page");
		if (index != -1)
		{
			url = url.substring(0, index);
		}

		if (url == "")
		{
			url = "/";
		} 

		$('.navitem').filter(function()
		{
			var target = $(this).parent().attr('href');
			return target == url;
		}).addClass('selected');
	});
</script>