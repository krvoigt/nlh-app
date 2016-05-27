$(function () {
	var $coverflow = $('#coverflow');

	$('img').lazyload({
		container: $coverflow,
		effect: 'fadeIn',
	});

	var currentPos = $coverflow.find('.coverflow_link.-current').offset().left - $coverflow.width() / 2.5;
	$coverflow.scrollLeft(currentPos).mousewheel( function(event, delta) {
		this.scrollLeft -= (delta * 100);
		event.preventDefault();
	});
});
