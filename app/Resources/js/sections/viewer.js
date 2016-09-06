$(function () {
    var defaultZoom = 1;
    var maxZoom = 2;
    var settings = {};

    var $viewerControls = $('.viewer_controls');
    var $image = $('.viewer_image');

    var $controls = {
        fullscreen: $viewerControls.find('.viewer_control.-fullscreen'),
        nextPage: $viewerControls.find('.viewer_control.-next-page'),
        pageSelect: $viewerControls.find('.viewer_control.-page-select'),
        previousPage: $viewerControls.find('.viewer_control.-previous-page'),
        zoomIn: $viewerControls.find('.viewer_control.-zoom-in'),
        zoomOut: $viewerControls.find('.viewer_control.-zoom-out'),
        zoomReset: $(),
    };

    var image = L.map('viewer_image', {
        attributionControl: false,
        center: [0, 0],
        crs: L.CRS.Simple,
        zoom: defaultZoom,
        zoomControl: false,
        maxZoom: maxZoom,
    }).addLayer(L.tileLayer.iiif($('#viewer_image').data('iiif')));

    var page = parseInt($controls.pageSelect.val());

    var titleLineHeight = parseInt($('.viewer_title').css('line-height'));

    $(window).on('resize', function () {
        adjustTitleHeight(titleLineHeight);
    });
    adjustTitleHeight(titleLineHeight);

    $('.viewer_title-toggle').click(function () {
        $(this).siblings('.viewer_title-toggle').addBack().toggle();
        $(this).closest('.viewer_title').toggleClass('-full');
    });

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

    $controls.zoomIn.click(function () {
        image.zoomIn();
    });

    $controls.zoomOut.click(function () {
        image.zoomOut();
    });

    $controls.zoomReset.click(function () {
        image.setZoom(defaultZoom);
    });

    $(window).resize(function () {
        image.invalidateSize();
    });

    // NOTE: No point in saving fullscreen since this can only be triggered by the user as a security measure
    $controls.fullscreen.click(function () {
        $(this).toggleClass('-active');
        if (document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement) {
            exitFullscreen();
        } else {
            requestFullscreen($('#main')[0]);
        }
    });

    $controls.pageSelect
        .select2()
        .change(function () {
            setGetParameters({page: $(this).val()});
        });

    $('.select2-container').addClass('viewer_control');

    // Close page select dropdown when clicking scan
    $('.viewer_scan').click(function (e) {
        $controls.pageSelect.select2('close');
    });

    $(document).keydown(function (e) {
        if (e.keyCode == 37) {
            $controls.previousPage.click();
            return false;
        }

        if (e.keyCode == 39) {
            $controls.nextPage.click();
            return false;
        }
    });

    // Add current hash on click to viewer controls
    $viewerControls.click( function (e) {
        var $target = $(e.target);
        $target.attr('href', $target.attr('href') + window.location.hash);
    });

    $('.js-toggle-panel').click(function () {
        $(this).toggleClass('-active');
        var panelName = $(this).data('target');

        var $panel = $('.viewer_panel.-' + panelName);
        $panel.toggleClass('-hidden');

        // TODO: Don't reload TOC on every toggle
        if (panelName === 'toc') {
            $.get(window.location.origin + window.location.pathname + '/toc/', function (data) {
                $('.viewer_toc').append(data);
            });
        }

        settings.panels = settings.panels || {};
        settings.panels[panelName] = $(this).hasClass('-active');
        saveState(settings);
        // Wait until after panel transition has finished before resetting image size
        setTimeout(function () {
            image.invalidateSize();
        }, 300);
    });

    function adjustTitleHeight(maxHeight) {
        var $title = $('.viewer_title');
        $title.height('').removeClass('-cut');
        if ( $title.height() > maxHeight ) {
            $title.height(maxHeight).addClass('-cut');
            $title.children('.viewer_title-toggle.-expand').show();
        } else {
            $title.children('.viewer_title-toggle.-expand').hide();
        }
    }

    function loadState() {
        image.off('viewreset', loadState);
        if ( window.location.hash ) {
            settings = JSON.parse(window.location.hash.substr(1));
            image.setView([settings.lat, settings.lng], settings.zoom);
            $.each(settings.panels, function (name, show) {
                var buttonName = '.js-toggle-panel' + (show ? ':not(.-active)' : '.-active') + '[data-target=' + name + ']';
                var $panel = $('.viewer_panel.-' + name)
                $panel.css('transitionDuration', '0s');
                $(buttonName).click();
                setTimeout(function () {
                    $panel.css('transitionDuration', '');
                }, 10);
            });
        }
    }

    function saveState(settings) {
        // Using replaceState instead of window.location.hash to prevent a new history step begin added for every change in view settings
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
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        }
    }
});
