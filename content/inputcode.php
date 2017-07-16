<script>
	$( document ).ready()
	{
		//$("textarea").height(18);

		
		$("[limit]").each(function(e)
		{
			var length = $(this).val().length;
			var max = $(this).attr("limit");
			var label = $('label[for="'+$(this).attr('id')+'"].counter > span');
			label.text(length + "/" + max);
			
			if (length > max)
			{
				$(this).addClass("invalid");
			}
			else
			{
				$(this).removeClass("invalid");
			}
		});

		$("input[type=text], input[type=email], input[type=password], textarea").each(function(e)
		{
			if ($(this).val().length > 0)
			{
				$(this).addClass("notBlank");
			}
			else
			{
				$(this).removeClass("notBlank");
			}
		});
	}


	
	setInterval(function() {
		$("textarea:not([noResize])").each(function(){
			$(this).scrollTop(0);
    	});   
	}, 1);
	
	$(document).on("focus change keyup keydown paste", "input[type=text], input[type=email], input[type=password], textarea", function(e)
	{
		if ($(this).val().length > 0)
		{
			$(this).addClass("notBlank");
		}
		else
		{
			$(this).removeClass("notBlank");
		}
	});
	
	$(document).on("focus change keyup keydown paste", "textarea:not([noResize])", function(e)
	{
			var currentHeight = $(this).height();
			$(this).height(0);
			var height = $(this)[0].scrollHeight-3;
			var moveHeight = currentHeight + Math.ceil((height - currentHeight) / 3)
			height = Math.max(height, 77);
			$(this).height(height)
	});
	
	$(document).on("focus change keyup keydown paste", "[limit]", function(e)
	{
		var length = $(this).val().length;
		var max = $(this).attr("limit");
		var label = $('label[for="'+$(this).attr('id')+'"].counter > span');
		label.text(length + "/" + max);
		
		if (length >= max)
		{
			$(this).addClass("invalid");
		}
		else
		{
			$(this).removeClass("invalid");
		}
	});
	
	$("input[type=email]").attr('validated', '');
	
	$(document).on("focus change keyup keydown paste", "input[type=email]" ,function(e)
	{
		var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		
		if (regex.test($(this).val()))
		{
			$(this).removeClass("invalid");
		}
		else
		{
			$(this).addClass("invalid");
		}
	});
</script>