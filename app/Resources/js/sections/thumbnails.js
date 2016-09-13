$(function () {
    var $thumbnails = $('#thumbnails');

    if ($thumbnails.length === 0) {
        return;
    }

    $('img').lazyload({
        container: $thumbnails,
        effect: 'fadeIn',
    });

    $('.thumbnails_link:not(.-current)').click(function () {
        window.location = $(this).attr('href') + window.location.hash;
        return false;
    });
});
