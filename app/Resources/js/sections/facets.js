$(function () {
    $('.facets_toggle').click(function () {
        $(this).siblings('.facets_toggle').addBack().toggleClass('hidden');
        $('.facets_body').slideToggle(function () {
            $(this).toggleClass('-visible').css('display', '');
        });
    });

    $('.facet').each(function () {
        if ($(this).find('.facet_item').length < 7) {
            $(this).find('.facet_list-toggle.-expand').hide();
        }
    });

    $('.facet_list-toggle').click(function () {
        var $facetList = $(this).siblings('.facet_list');
        $facetList.find('li:nth-child(n+6)').toggle();
        $(this).siblings('.facet_list-toggle').addBack().toggle();
    });
});
