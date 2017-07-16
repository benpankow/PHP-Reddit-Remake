<script>
	$(document).on('click', '.post .upvote', function()
	{
		if (isLoggedIn)
		{
			var button = $(this);
			var id = parseInt(button.closest('.post').attr('id').substring(4));
			$.ajax({ url: root + '/action.php',
				data: {action: 'upvote', id: id},
				type: 'post'
			});
			
			if (button.hasClass("chosen"))
			{
				changeValueUpvote(id, -1);
			}
			else
			{
				changeValueUpvote(id, 1);
			}
			
			button.toggleClass("chosen");
			$("#post" + id + " .score").toggleClass("upvoted");
			
			if ($("#post" + id + " .downvote").hasClass("chosen"))
			{
				$("#post" + id + " .downvote").removeClass("chosen");
				$("#post" + id + " .score").removeClass("downvoted");
				changeValueDownvote(id, 1);
			}
		}
	});
	
	$(document).on('click', '.post .downvote', function()
	{
		if (isLoggedIn)
		{
			var button = $(this);
			var id = parseInt(button.closest('.post').attr('id').substring(4));
			$.ajax({ url: root + '/action.php',
				data: {action: 'downvote', id: id},
				type: 'post'
			});
			
			
			if (button.hasClass("chosen"))
			{
				changeValueDownvote(id, 1);
			}
			else
			{
				changeValueDownvote(id, -1);
			}
			
			button.toggleClass("chosen");
			$("#post" + id + " .score").toggleClass("downvoted");

			if ($("#post" + id + " .upvote").hasClass("chosen"))
			{
				$("#post" + id + " .upvote").removeClass("chosen");
				$("#post" + id + " .score").removeClass("upvoted");
				changeValueUpvote(id, -1);
			}
		}
	});
	
	$(document).on('click', '.comment .upvote', function()
	{
		
		if (isLoggedIn)
		{
			var button = $(this);
			var id = parseInt(button.closest('.comment').attr('id').substring(7));
			$.ajax({ url: root + '/action.php',
				data: {action: 'upvoteComment', id: id},
				type: 'post',
				success: function(data) {
			    	console.log(data);
			    }
			});
			
			if (button.hasClass("chosen"))
			{
				changeValueCommentUpvote(id, -1);
			}
			else
			{
				changeValueCommentUpvote(id, 1);
			}
			
			button.toggleClass("chosen");
			
			if ($("#comment" + id + " .downvote").hasClass("chosen"))
			{
				$("#comment" + id + " .downvote").removeClass("chosen");
				changeValueCommentDownvote(id, 1);
			}
		}
	});
	
	$(document).on('click', '.comment .downvote', function()
	{
		if (isLoggedIn)
		{
			var button = $(this);
			var id = parseInt(button.closest('.comment').attr('id').substring(7));
			$.ajax({ url: root + '/action.php',
				data: {action: 'downvoteComment', id: id},
				type: 'post',
				success: function(data) {
			    	console.log(data);
			    }
			});
			
			
			if (button.hasClass("chosen"))
			{
				changeValueCommentDownvote(id, 1);
			}
			else
			{
				changeValueCommentDownvote(id, -1);
			}
			
			button.toggleClass("chosen");

			if ($("#comment" + id + " .upvote").hasClass("chosen"))
			{
				$("#comment" + id + " .upvote").removeClass("chosen");
				changeValueCommentUpvote(id, -1);
			}
		}
	});
	
	var deleteOptions = [["DELETE IT", "cancel"],["CANCEL", ""]];
	var delCommentID = 0;
	var delPostID = 0;
	$(document).on('click', '.deleteComment', function()
	{
		delCommentID = parseInt($(this).closest('.comment').attr('id').substring(7));
		confirmWindow("delComment", "Delete this comment?", "Are you sure? This comment will be deleted forever (a long time!)", deleteOptions, true);
	});
	
	$(document).on('click', "#dialog.delComment .cancel", function()
	{
		console.log("test");
		$.ajax({ url: root + '/action.php',
			data: {action: 'deleteComment', target: delCommentID},
			type: 'post',
			success: function(data)
			{
				console.log(data);
				if (data == "true")
				{
					$("#comment" + delCommentID).find(".commentUsername").text("[deleted]");
					$("#comment" + delCommentID).find(".commentUsername").removeAttr("href");
					$("#comment" + delCommentID).find(".textOfComment").text("[deleted]");
				}
			}
		});
	});
	
	
	$(document).on('click', '.deletePost', function()
	{
		delPostID = parseInt($(this).closest('.post').attr('id').substring(4));
		confirmWindow("delPost", "Delete this post?", "Are you sure? This post will be deleted forever (a long time!)", deleteOptions, true);
	});
	
	$(document).on('click', "#dialog.delPost .cancel", function()
	{
		console.log("test");
		$.ajax({ url: root + '/action.php',
			data: {action: 'deletePost', target: delPostID},
			type: 'post',
			success: function(data)
			{
				console.log(data);
				if (data == "true")
				{
					location.reload();
				}
			}
		});
	});

	function changeValueUpvote(id, amount)
	{
		var num = parseInt($("#post" + id + " .score").text());
		num = num + amount;
		$("#post" + id + " .score").text(num);
		
		var num2 = parseInt($("#post" + id + " .upvoteCount").text().substring(1));
		num2 = num2 + amount;
		$("#post" + id + " .upvoteCount").text("+" + num2);
	}
	
	function changeValueDownvote(id, amount)
	{
		var num = parseInt($("#post" + id + " .score").text());
		num = num + amount;
		$("#post" + id + " .score").text(num);
		
		var num2 = parseInt($("#post" + id + " .downvoteCount").text());
		num2 = num2 + amount;
		$("#post" + id + " .downvoteCount").text(num2);
	}
	
	function changeValueCommentUpvote(id, amount)
	{
		var num = parseInt($("#comment" + id + " .commentScore").text());
		num = num + amount;
		$("#comment" + id + " .commentScore").text(num);
		
		var num2 = parseInt($("#comment" + id + " .upvoteCount").text().substring(1));
		num2 = num2 + amount;
		$("#comment" + id + " .upvoteCount").text("+" + num2);
	}
	
	function changeValueCommentDownvote(id, amount)
	{
		var num = parseInt($("#comment" + id + " .commentScore").text());
		num = num + amount;
		$("#comment" + id + " .commentScore").text(num);
		
		var num2 = parseInt($("#comment" + id + " .downvoteCount").text());
		num2 = num2 + amount;
		$("#comment" + id + " .downvoteCount").text(num2);
	}
</script>