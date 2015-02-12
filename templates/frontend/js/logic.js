;(function($)
{
	$(window).bind('load resize', function(e) {

	var footerHeight = $('#footer').height();
	$('.wrapper').css('padding-bottom', footerHeight);
	
});
})(jQuery);
