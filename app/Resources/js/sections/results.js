$(function () {
    $(window).on('resize', function () {
        $('.result_title').each(function () {
            var hasLongTitle = $(this).find('.result_link').height() > $(this).height();
            $(this).find('.result_title-toggle.-expand').toggle(hasLongTitle);
        });
    }).trigger('resize');

    $('.result_title-toggle').click(function () {
        $(this).siblings('.result_title-toggle').addBack().toggle();
        $(this).closest('.result_title').toggleClass('-full');
    });
});
