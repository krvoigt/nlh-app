var LEFT_ARROW_KEY = 37;
var RIGHT_ARROW_KEY = 39;

var Viewer = {
    init: function () {
        var $imageContainer = $('#scan_image');
        var initialZoom = 1;
        var isLoaded = false;
        var alwaysShowScan;

        if ($imageContainer.length < 1) {
            return;
        }

        this.checkShowScan();

        this.settings = {}
        this.settings.panel = this.alwaysShowScan ? 'metadata' : 'scan';

        this.loadState.bind(this)();

        this.controls = {
            fullscreen: $('.viewer_control.-fullscreen'),
            nextPage: $('.viewer_control.-next-page'),
            pageSelect: $('.viewer_control.-page-select'),
            previousPage: $('.viewer_control.-previous-page'),
            zoomIn: $('.viewer_control.-zoom-in'),
            zoomOut: $('.viewer_control.-zoom-out'),
        };


        this.spinner = $('.scan_spinner');

        var layerOptions = {
            fitBounds: false,
        };
        this.layer = L.tileLayer.iiif( $imageContainer.data('iiif'), layerOptions );

        this.image = L.map('scan_image', {
            attributionControl: false,
            center: [this.settings.lat || 0, this.settings.lng || 0],
            crs: L.CRS.Simple,
            zoom: (typeof this.settings.zoom === 'undefined' ? initialZoom : this.settings.zoom),
            zoomControl: false,
            maxZoom: 2,
        }).addLayer(this.layer);

        if (typeof this.settings.lat === 'undefined') {
            var that = this;
            // Center image on first load
            this.layer.on('load', function () {
                if (that.isLoaded) {
                    return;
                }
                that.isLoaded = true;

                // Center image horizontally, top-align vertically, leave some room for controls
                var imageSize = this._imageSizes[initialZoom];
                var layerLatLng = that.image.options.crs.pointToLatLng(L.point(imageSize.x, imageSize.y), initialZoom);
                var neBounds = that.image.getBounds()._northEast;
                var latLng = [-neBounds.lat + 26, layerLatLng.lng / 2];
                that.image.panTo(latLng, {animate: false});
            });
        }

        var page = parseInt(this.controls.pageSelect.val());

        this.controls.pageSelect.select2();
        $('.select2-container').addClass('viewer_control');

        this.setZoomButtonStates.bind(this)();
        this.bindEvents();

        if (! this.alwaysShowScan && (this.settings.panel === 'thumbnails' || this.settings.panel === 'toc')) {
            $('.viewer_control.-toggle-panel.-scan').click();
        } else if (this.settings.panel) {
            $('.viewer_control.-toggle-panel.-' + this.settings.panel).click();
        }
    },

    bindEvents: function () {
        var that = this;

        // Add current hash on click to viewer controls
        $('.viewer_controls').click(function (e) {
            var $target = $(e.target);
            $target.attr('href', $target.attr('href') + window.location.hash);
        });

        $('.viewer_control.-toggle-panel').click(function () {
            var panelName = $(this).data('target');
            that.togglePanel.bind(that, $(this), panelName)();
        });

        this.controls.fullscreen.click(this.toggleFullscreen.bind(this));

        this.controls.pageSelect.change(function () {
            // Remove LOG_xxxx part from ID to prevent wrong document section being loaded
            var newUrl = setGetParameters({page: $(this).val()}, false);
            newUrl = decodeURI(newUrl).replace(/\|LOG_[0-9_]+/, '');
            window.location = newUrl;
            return false;
        });

        this.controls.zoomIn.click(function () {
            that.image.zoomIn();
        });

        this.controls.zoomOut.click(function () {
            that.image.zoomOut();
        });

        this.image.on('moveend', this.saveState.bind(this));

        this.image.on('zoomend', function () {
            that.setZoomButtonStates.bind(that)();
            that.saveState.bind(that);
        });

        this.layer.on('loading', function () {
            that.spinner.fadeIn();
        });

        this.layer.on('load', function () {
            that.spinner.hide();
        });

        $('.scan').click(function () {
            that.controls.pageSelect.select2('close');
        });

        $('.scan .select2').click(function () {
            return false;
        });

        $(document).keydown(function (e) {
            if (e.keyCode === LEFT_ARROW_KEY) {
                window.location.href = that.controls.previousPage.attr('href');
                return false;
            }

            if (e.keyCode === RIGHT_ARROW_KEY) {
                window.location.href = that.controls.nextPage.attr('href');
                return false;
            }
        });

        $(window).resize(this.checkShowScan.bind(this));
    },

    checkShowScan: function () {
        this.alwaysShowScan = $('.viewer_control.-toggle-panel.-scan').is(':hidden');
        $('.viewer_panel.-scan').toggleClass('-alwaysActive', this.alwaysShowScan);
        if (! this.alwaysShowScan && $('.viewer_panel.-active').length < 1) {
            $('.viewer_control.-scan').click();
        }
    },

    setZoomButtonStates: function () {
        var zoom = this.image.getZoom();
        this.controls.zoomIn.toggleClass('-disabled', zoom === this.image.getMaxZoom());
        this.controls.zoomOut.toggleClass('-disabled', zoom === this.image.getMinZoom());
    },

    toggleFullscreen: function () {
        this.controls.fullscreen.toggleClass('-active');
        if (document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement) {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            }
        } else {
            var element = ($('#main')[0]);
            if (element.requestFullscreen) {
                element.requestFullscreen();
            } else if (element.mozRequestFullScreen) {
                element.mozRequestFullScreen();
            } else if (element.webkitRequestFullScreen) {
                element.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
            }
        }
    },

    togglePanel: function () {
        var $control = arguments[0];
        var panelName = arguments[1];
        var $panel = $('.viewer_panel.-' + panelName);

        $('.viewer_control.-toggle-panel').not($control).removeClass('-active');
        $('.viewer_panel').not($panel).removeClass('-active');

        var state = ! $control.hasClass('-active') || ! this.alwaysShowScan;

        $control.toggleClass('-active', state);
        $panel.toggleClass('-active', state);

        if (panelName === 'scan') {
            this.image.invalidateSize();
        } else if (panelName === 'toc') {
            if (! TOC.isInited) {
                TOC.init();
            }
        }

        this.image.invalidateSize();

        this.settings.panel = state ? panelName : false;
        this.saveState();
    },

    loadState: function () {
        if ( ! window.location.hash ) {
            return false;
        }

        try {
            this.settings = JSON.parse(decodeURIComponent(window.location.hash.substr(1)));
        } catch(e) {
            this.settings = {};
        }
    },

    saveState: function () {
        this.settings.zoom = this.image.getZoom();

        var latLng = this.image.getCenter();
        this.settings.lat = latLng.lat;
        this.settings.lng = latLng.lng;

        // Using replaceState instead of window.location.hash to prevent a new history step begin added for every change in view settings
        history.replaceState(undefined, undefined, '#' + JSON.stringify(this.settings));
    },
};

Viewer.init();
