$(function () {
    $('.facets_toggle').click(function () {
        $(this).siblings('.facets_toggle').addBack().toggleClass('hidden');
        $('.facets_body').slideToggle(function () {
            $(this).toggleClass('-visible').css('display', '');
        });
    });

    $('article.facet').each(function () {
        if ($(this).find('.facet_list.-toggle').children().length < 7) {
            $(this).find('.facet_list-toggle.-expand').hide();
        }
    });

    $('.facet_list-toggle').click(function () {
        var $facetList = $(this).siblings('.facet_list');

        // Scroll viewport so toggle button's vertical position doesn't change
        if ($(this).hasClass('-collapse')) {
            expandedListHeight = $facetList.offset().top + $facetList.height() - $facetList.find('li:nth-child(6)').offset().top;
            scrollPos = $('body').scrollTop() - expandedListHeight;
            $('html, body').animate({scrollTop: scrollPos});
        }

        $facetList.find('li:nth-child(n+6)').slideToggle();
        $(this).siblings('.facet_list-toggle').addBack().toggle();
    });
});
