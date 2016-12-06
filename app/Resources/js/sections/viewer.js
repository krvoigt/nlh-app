var Viewer = {
    container: $('.viewer'),
    controls: {
        changeView: $('.viewer_control.-change-view', this.container),
        nextPage: $('.viewer_control.-pagination.-next', this.container),
        pageSelect: $('.viewer_control.-pagination.-select', this.container),
        previousPage: $('.viewer_control.-pagination.-previous', this.container),
        togglePanel: $('.viewer_control.-toggle-panel', this.container),
        zoomIn: $('.viewer_control.-zoom-in', this.container),
        zoomOut: $('.viewer_control.-zoom-out', this.container),
    },
    keys: {
        leftArrow: 37,
        rightArrow: 39,
    },
    settings: {},
    spinner: $('.scan_spinner'),

    init: function () {
        var alwaysShowScan;
        var imageContainer = $('#scan_image');
        var initialZoom = 1;
        var isLoaded = false;

        if (this.container.length < 1) {
            return;
        }

        ViewerThumbnails.init();

        this.checkShowScan();
        this.settings.panel = this.alwaysShowScan ? 'metadata' : 'scan';
        this.loadState.bind(this)();

        this.layer = L.tileLayer.iiif(imageContainer.data('iiif'), {
            fitBounds: false,
        });

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
                var latLng = [-neBounds.lat + 19, layerLatLng.lng / 2]; // 19 equals rougly 24px
                that.image.panTo(latLng, {animate: false});
            });
        }

        var page = parseInt(this.controls.pageSelect.val());

        this.controls.pageSelect.select2();
        $('.select2-container', this.container).addClass('viewer_control');

        this.setZoomButtonStates.bind(this)();
        this.bindEvents();

        if (! this.alwaysShowScan && (this.settings.panel === 'thumbnails' || this.settings.panel === 'toc')) {
            // When page is changed via thumbnails or TOC, go back to scan on small screens
            this.controls.togglePanel.filter('[data-target=scan]').click();
        } else if (this.settings.panel) {
            // Open last shown panel
            this.controls.togglePanel.filter('[data-target=' + this.settings.panel + ']').click();
        }
    },

    bindEvents: function () {
        var that = this;

        // Add current hash on click to viewer controls
        $('.viewer_control.-pagination', this.container).click(function () {
            var origin = $('#origin').attr('href');
            window.location = $(this).attr('href') + (origin ? '&origin=' + encodeURIComponent(origin) : '') + window.location.hash;
            return false;
        });

        this.controls.togglePanel.click(function () {
            var panelName = $(this).data('target');
            that.togglePanel.bind(that, $(this), panelName)();
        });

        this.controls.pageSelect.change(function () {
            // Remove LOG_xxxx part from ID to prevent loading of wrong document section
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

        this.controls.changeView.click(function () {
            $('.viewer_controls.-right').toggleClass('-open');
            return false;
        });

        $('.scan .select2', this.container).click(function () {
            $('.viewer_controls.-open').removeClass('-open');
            return false;
        });

        $('body').click(function () {
            $('.viewer_controls.-open').removeClass('-open');
            that.controls.pageSelect.select2('close');
        });

        $(document).keydown(function (e) {
            if (e.keyCode === that.keys.leftArrow) {
                window.location.href = that.controls.previousPage.attr('href');
                return false;
            } else if (e.keyCode === that.keys.rightArrow) {
                window.location.href = that.controls.nextPage.attr('href');
                return false;
            }
        });

        $(window).resize(this.checkShowScan.bind(this));
    },

    checkShowScan: function () {
        this.alwaysShowScan = this.controls.changeView.is(':hidden');
        $('.viewer_panel.-scan', this.container).toggleClass('-always-active', this.alwaysShowScan);
        if (! this.alwaysShowScan && $('.viewer_panel.-active').length < 1) {
            $('.viewer_control.-scan').click();
        }
    },

    setZoomButtonStates: function () {
        var zoom = this.image.getZoom();
        this.controls.zoomIn.toggleClass('-disabled', zoom === this.image.getMaxZoom());
        this.controls.zoomOut.toggleClass('-disabled', zoom === this.image.getMinZoom());
    },

    togglePanel: function () {
        var $control = arguments[0];
        var panelName = arguments[1];
        var $panel = $('.viewer_panel.-' + panelName, this.container);

        this.controls.togglePanel.not($control).removeClass('-active');
        $('.viewer_panel', this.container).not($panel).removeClass('-active');

        var state = ! $control.hasClass('-active') || ! this.alwaysShowScan;

        $control.toggleClass('-active', state);
        $panel.toggleClass('-active', state);

        if (panelName === 'scan') {
            this.image.invalidateSize();
        } else if (panelName === 'toc') {
            if (! ViewerToc.isInited) {
                ViewerToc.init();
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
