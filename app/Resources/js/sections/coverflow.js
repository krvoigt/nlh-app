$(function () {
    var $coverflow = $('#coverflow');

    if ($coverflow.length > 0) {
        $('img').lazyload({
            container: $coverflow,
            effect: 'fadeIn',
        });

        var currentPos = $coverflow.find('.coverflow_link.-current').offset().left - $coverflow.width() / 2.5;
        $coverflow.scrollLeft(currentPos).mousewheel(function (event, delta) {
            this.scrollLeft -= (delta * 100);
            event.preventDefault();
        });

        var $container = $('.coverflow');
        var $items = $('.coverflow_item');
        var sizingFactor = 2;
        var initialWidth = $items.first().width();
        var initialHeight = $items.first().height();
        var height = $coverflow.height() - 4;
        var maxDistance = 200;
        $coverflow.css({bottom: -height});
        $('body').mousemove(function (e) {
            verticalDistance = Math.max(0, $(window).height() - e.pageY - height * 3);
            $coverflow.css({
                bottom: verticalDistance < height ? -height + height * (height - verticalDistance) / height : -height
            });

            $items.each(function (i) {
                distance = getMouseDistance($(this), e);
                $(this).css({
                    width: distance < maxDistance ? Math.round(initialWidth * (1 + (maxDistance - distance) / maxDistance)) : ''
                });
            });
        });
        // TODO: This doesn't seem to work.
        $('body').trigger('mousemove');

        $('.coverflow_link').click(function () {
            window.location = $(this).attr('href') + location.hash;
            return false;
        })
    }

    function getMouseDistance(el, e) {
        var x = Math.pow(e.pageX - (el.offset().left + (el.width() / 2)), 2);
        var y = Math.max(3000, Math.pow(e.pageY - (el.offset().top + (el.height() / 2)), 2));
        return Math.sqrt(x + y);
    }
});
