$(function () {
    var $image = $('.viewer_image');
    var $controls = $('.viewer_controls');
    var settings = {};

    $image.panzoom({
        $zoomIn: $controls.find(".js-zoom-in"),
        $zoomOut: $controls.find(".js-zoom-out"),
        $zoomRange: $controls.find(".js-zoom-range"),
        $reset: $controls.find(".js-zoom-reset")
    });

    $image.parent().on('mousewheel.focal', function (e) {
        e.preventDefault();
        var delta = e.delta || e.originalEvent.wheelDelta;
        var zoomOut = delta ? delta < 0 : e.originalEvent.deltaY > 0;
        $image.panzoom('zoom', zoomOut, {
            increment: .2,
            focal: e
        });
    });

    $image.on('panzoomchange', function (e, panzoom, matrix) {
        settings.zoom = Math.round(matrix[0] * 100) / 100;
        settings.panX = Math.round(matrix[4]);
        settings.panY = Math.round(matrix[5]);
        saveState(settings);
    });


    // NOTE: No point in saving fullscreen since this can only be triggered by the user as a security measure
    $('.js-fullscreen').click(function () {
        $(this).toggleClass('-active');
        if ( document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement ) {
            exitFullscreen();
        } else {
            requestFullscreen($('#main')[0]);
        }
    });

    $('.js-page-view').change(function () {
        setGetParameter('showDoublePage', $(this).val() === 'double');
    });

    var page = parseInt($('.js-select-page').val());
    var showDoublePage = $('.js-page-view').val() === 'double';

    $('.js-select-page')
    .select2()
    .change(function () {
        setGetParameter('page', $(this).val());
    });

    $('.select2-container').addClass('viewer_control');

    // Close page select dropdown when clicking scan
    $('.viewer_scan').click(function (e) {
        $('.js-select-page').select2('close');
    });

    $('.viewer_control').click(function () {
        return false;
    });

    $('.js-previous-page').click(function () {
        setGetParameter('page', showDoublePage ? page - 2 : page - 1);
    });

    $('.js-next-page').click(function () {
        setGetParameter('page', showDoublePage ? page + 2 : page + 1);
    });

    $('.js-search-toggle').click(function () {
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

    $('.js-toggle-panel').click(function () {
        $(this).toggleClass('-active');
        var panelName = $(this).data('target');
        var $panel = $('.viewer_panel.-' + panelName);
        $panel.toggleClass('-hidden');
        settings.panels = settings.panels || {};
        settings.panels[panelName] = $(this).hasClass('-active');
        saveState(settings);
    });

    loadState(settings);

    function loadState(settings) {
        if ( location.hash ) {
            settings = JSON.parse(location.hash.substr(1));
            $image.panzoom('zoom', settings.zoom, {silent: true});
            $image.panzoom('pan', settings.panX, settings.panY);
            $.each(settings.panels, function (name, show) {
                var buttonName = '.js-toggle-panel' + (show ? ':not(.-active)' : '.-active') + '[data-target=' + name + ']';
                $(buttonName).click();
            });
        }
    }

    function saveState(settings) {
        location.hash = JSON.stringify(settings);
    }

    function requestFullscreen(element) {
        if (element.requestFullscreen) {
            element.requestFullscreen();
        } else if (element.mozRequestFullScreen) {
            element.mozRequestFullScreen();
        } else if (element.webkitRequestFullScreen) {
            element.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
        }
    }

    function exitFullscreen() {
        if(document.exitFullscreen) {
            document.exitFullscreen();
        } else if(document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if(document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        }
    }
});
