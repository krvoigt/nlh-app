$(function () {
    $('.header_search-toggle').click(function () {
        $('.search').addClass('-show-popup').fadeIn();
        setTimeout(function () {
            $('.search_input:visible').focus();
        }, 10);
        return false;
    });

    $('.root.-fixed .search_close').click(function () {
        $('.search').fadeOut();
        return false;
    });

    $('.root.-fixed').click(function () {
        $('.root.-fixed .search_close').click();
    });

    $('.search').click(function (e) {
        e.stopPropagation();
    });
});
