$(function () {
    $('.header_search-toggle').click(function () {
        $('.search').addClass('-show-popup').fadeIn();
        setTimeout(function () {
            $('.search_input:visible').focus();
        }, 10);
        return false;
    });

    $('.fixed .search_close').click(function () {
        $('.search').fadeOut();
        return false;
    });

    $('.fixed').click(function () {
        $('.fixed .search_close').click();
    });

    $('.search').click(function (e) {
        e.stopPropagation();
    });
});
