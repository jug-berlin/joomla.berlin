;(function($)
{
	$(window).bind('load resize', function(e) {
	$('.vertbottom').removeAttr('style');
	var footerHeight = $('#footer').height();
	$('.wrapper').css('padding-bottom', footerHeight);
	if ($('.visible-xs').css('display') != 'block') {
	var headerHeight = $('.main-header').height();
	$('.vertbottom').css('height', headerHeight);
	}
});
})(jQuery);
