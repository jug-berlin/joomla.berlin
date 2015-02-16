;(function($)
{
	$(window).bind('load resize', function(e) {
	$('#header, #logo').removeAttr('style');
	var footerHeight = $('#footer').height();
	$('.wrapper').css('padding-bottom', footerHeight);
	var headerHeight = $('.main-header .container').height();
	$('#header, #logo').css('height', headerHeight);
});
})(jQuery);
