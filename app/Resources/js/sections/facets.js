$(function () {
    $('.facetsToggle').click(function () {
        $(this).siblings('.facetsToggle').addBack().toggleClass('hidden');
        $('.facetsBody').slideToggle(function () {
            $(this).toggleClass('-visible').css('display', '');
        });
    });

    $('article.facet').each(function () {
        if ($(this).find('.facetList').children().length < 7) {
            $(this).find('.facetListExpand').hide();
        }
    });

    $('.facetListExpand, .facetListCollapse').click(function () {
        var $facetList = $(this).siblings('.facetList');

        // Scroll viewport so toggle button's vertical position doesn't change
        if ($(this).hasClass('facetListCollapse')) {
            expandedListHeight = $facetList.offset().top + $facetList.height() - $facetList.find('li:nth-child(6)').offset().top;
            scrollPos = $('body').scrollTop() - expandedListHeight;
            $('html, body').animate({scrollTop: scrollPos});
        }

        $facetList.find('li:nth-child(n+6)').slideToggle();
        $(this).siblings('.facetListExpand, .facetListCollapse').addBack().toggle();
    });
});
