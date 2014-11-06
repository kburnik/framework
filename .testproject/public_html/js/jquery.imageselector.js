
$.fn.extend({
	imageSelector:function(options){
		var o = options;
		$(this).each(function(){
			var $t = $(this);
			var $a = $t.find("a");
			$a.each(function(){
				var id = $(this).attr('rel');
				$('#'+id).hide();
			});
			
			$($a[0]).addClass('current');
			var id = $($a[0]).attr('rel');
			$('#'+id).show();
				
			$a.click(function(){
				$a.siblings().removeClass('current').each(function(){
					var id = $(this).attr('rel');
					$('#'+id).hide();
				});
				$(this).addClass('current');				
				var id = $(this).attr('rel');
				$('#'+id).show();
			});
		
		});
		return $(this);
	}
});

$(function(){
	// testing:
	$('.image-selector').imageSelector();
	console.log("Image selector applied as test in jquery.imageselector.js");
});