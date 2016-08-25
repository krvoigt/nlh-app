$(function () {
    $('.header_search-toggle').click(function () {
        $('.search').addClass('-show-popup').fadeIn();
        setTimeout(function () {
            $('.search_input:visible').focus();
        }, 10);
        return false;
    });

    $('.site.-fixed .search_close').click(function () {
        $('.search').fadeOut();
        return false;
    });

    $('.site.-fixed').click(function () {
        $('.site.-fixed .search_close').click();
    });

    $('.search').click(function (e) {
        e.stopPropagation();
    });
});
