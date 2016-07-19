$(function () {
    var defaultZoom = 1;
    var maxZoom = 2;
    var settings = {};
    var $controls = $('.viewer_controls');
    var $image = $('.viewer_image');
    var $zoomInControl = $controls.find('.js-zoom-in');
    var $zoomOutControl = $controls.find('.js-zoom-out');
    var $zoomResetControl = $controls.find('.js-zoom-reset');

    var image = L.map('viewer_image', {
        attributionControl: false,
        center: [0, 0],
        crs: L.CRS.Simple,
        zoom: defaultZoom,
        zoomControl: false,
        maxZoom: maxZoom,
    }).addLayer(L.tileLayer.iiif( $('#viewer_image').data('iiif') ));

    // TODO: 'load' event does not fire for unknown reasons, so we're using 'viewreset' as a workaround.
    // Because this can fire multiple times, event binding is disabled in loadState function.
    image.on('viewreset', loadState);

    image.on('zoomend', function () {
        settings.zoom = image.getZoom();
        saveState(settings);
    });

    image.on('moveend', function () {
        var latLng = image.getCenter();
        settings.lat = latLng.lat;
        settings.lng = latLng.lng;
        saveState(settings);
    });

    $zoomInControl.click(function () {
        image.zoomIn();
    });

    $zoomOutControl.click(function () {
        image.zoomOut();
    });

    $zoomResetControl.click(function () {
        image.setZoom(defaultZoom);
    });

    $(window).resize(function () {
        image.invalidateSize();
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

    var page = parseInt($('.js-select-page').val());

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
        setGetParameter('page', page - 1);
    });

    $('.js-next-page').click(function () {
        setGetParameter('page', page + 1);
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
        // Wait until after panel transition has finished before resetting image size
        setTimeout(function () {
            image.invalidateSize();
        }, 300);
    });

    function loadState() {
        image.off('viewreset', loadState);
        if ( location.hash ) {
            settings = JSON.parse(location.hash.substr(1));
            image.setView([settings.lat, settings.lng], settings.zoom);
            $.each(settings.panels, function (name, show) {
                var buttonName = '.js-toggle-panel' + (show ? ':not(.-active)' : '.-active') + '[data-target=' + name + ']';
                $(buttonName).click();
            });
        }
    }

    function saveState(settings) {
        // Using replaceState instead of location.hash to prevent a new history step begin added for every change in view settings
        history.replaceState(undefined, undefined, '#' + JSON.stringify(settings));
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
