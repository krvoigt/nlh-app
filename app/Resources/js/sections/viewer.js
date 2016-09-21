var LEFT_ARROW_KEY = 37;
var RIGHT_ARROW_KEY = 39;

var Viewer = {
    init: function () {
        var $imageContainer = $('#scan_image');

        if ($imageContainer.length < 1) {
            return;
        }

        this.settings = {};
        this.loadState.bind(this)();

        this.$controls = {
            fullscreen: $('.viewer_control.-fullscreen'),
            nextPage: $('.viewer_control.-next-page'),
            pageSelect: $('.viewer_control.-page-select'),
            previousPage: $('.viewer_control.-previous-page'),
            titleToggle: $('.viewer_title-toggle'),
            zoomIn: $('.viewer_control.-zoom-in'),
            zoomOut: $('.viewer_control.-zoom-out'),
        };

        this.$spinner = $('.scan_spinner');

        var layerOptions = {
            fitBounds: false,
        };
        this.layer = L.tileLayer.iiif( $imageContainer.data('iiif'), layerOptions );

        this.image = L.map('scan_image', {
            attributionControl: false,
            center: [this.settings.lat || 0, this.settings.lng || 0],
            crs: L.CRS.Simple,
            zoom: this.settings.zoom || 1,
            zoomControl: false,
            maxZoom: 2,
        }).addLayer(this.layer);

        // TODO: Centering the layer would be nicer
        if ($.isEmptyObject(this.settings)) {
            var neBounds = this.image.getBounds()._northEast;
            this.image.panTo([-neBounds.lat, neBounds.lng], {animate: false});
        }

        var page = parseInt(this.$controls.pageSelect.val());

        this.titleLineHeight = parseInt($('.viewer_title').css('line-height'));
        this.adjustTitleHeight();

        this.$controls.pageSelect.select2();
        $('.select2-container').addClass('viewer_control');

        this.bindEvents();

        $.each(this.settings.panels, function (name, show) {
            var buttonName = '.viewer_control.-toggle-panel' + (show ? ':not(.-active)' : '.-active') + '[data-target=' + name + ']';
            var $panel = $('.viewer_panel.-' + name)
            $panel.css('transitionDuration', '0s');
            $(buttonName).click();
            setTimeout(function () {
                    $panel.css('transitionDuration', '');
            }, 9);
        });
    },

    bindEvents: function () {
        var _this = this;
        var $controls = this.$controls;

        $(window).on('resize', this.onResize.bind(this));

        // Add current hash on click to viewer controls
        $('.viewer_controls').click(function (e) {
            var $target = $(e.target);
            $target.attr('href', $target.attr('href') + window.location.hash);
        });

        $('.viewer_control.-toggle-panel').click(function () {
            var panel = $(this).data('target');
            _this.togglePanel.bind(_this, $(this), panel)();
        });

        $controls.fullscreen.click(this.toggleFullscreen.bind(this));
        $controls.pageSelect.change(function () {
            setGetParameters({page: $(this).val()});
        });
        $controls.titleToggle.click(this.toggleTitle.bind(this));
        $controls.zoomIn.click(this.zoomIn.bind(this));
        $controls.zoomOut.click(this.zoomOut.bind(this));

        this.image.on('moveend', this.saveState.bind(this));
        this.image.on('zoomend', this.saveState.bind(this));

        this.layer.on('loading', this.showSpinner.bind(this));
        this.layer.on('load', this.hideSpinner.bind(this));

        $('.scan').click(this.closePageSelect.bind(this));
        $('.scan .select2').click(function () {
            return false;
        });

        $(document).keydown(function (e) {
            if (e.keyCode === LEFT_ARROW_KEY) {
                window.location.href = $controls.previousPage.attr('href');
                return false;
            }

            if (e.keyCode === RIGHT_ARROW_KEY) {
                window.location.href = $controls.nextPage.attr('href');
                return false;
            }
        });
    },

    toggleFullscreen: function () {
        this.$controls.fullscreen.toggleClass('-active');
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

    toggleTitle: function () {
        this.$controls.titleToggle.toggle();
        $('.viewer_title').toggleClass('-full');
    },

    showSpinner: function () {
        this.$spinner.fadeIn();
    },

    hideSpinner: function () {
        this.$spinner.hide();
    },

    zoomIn: function () {
        this.image.zoomIn();
    },

    zoomOut: function () {
        this.image.zoomOut();
    },

    onResize: function () {
        // this.image.invalidateSize(false);
        this.adjustTitleHeight.bind(this);
    },

    closePageSelect: function () {
        this.$controls.pageSelect.select2('close');
    },

    togglePanel: function () {
        var $control = arguments[0];
        var panelName = arguments[1];

        $control.toggleClass('-active');

        var $panel = $('.viewer_panel.-' + panelName);
        $panel.toggleClass('-hidden');

        // TODO: Don't reload TOC on every toggle
        if (panelName === 'toc') {
            $.get(window.location.origin + window.location.pathname + '/toc/', function (data) {
                $('.toc').html(data);
            });
        }

        this.settings.panels = this.settings.panels || {};
        this.settings.panels[panelName] = $control.hasClass('-active');
        this.saveState();

        // Wait until after panel transition has finished before resetting image size
        var _this = this;
        setTimeout(function () {
            _this.image.invalidateSize(false);
        }, 300);
    },

    adjustTitleHeight: function () {
        var $title = $('.viewer_title');
        $title.height('').removeClass('-cut');
        if ( $title.height() > this.titleLineHeight ) {
            $title.height(this.titleLineHeight).addClass('-cut');
            $title.children('.viewer_title-toggle.-expand').show();
        } else {
            $title.children('.viewer_title-toggle.-expand').hide();
        }
    },

    loadState: function () {
        if ( ! window.location.hash ) {
            return false;
        }

        this.settings = JSON.parse(window.location.hash.substr(1));
    },

    saveState: function () {
        this.settings.zoom = this.image.getZoom();

        var latLng = this.image.getCenter();
        this.settings.lat = latLng.lat;
        this.settings.lng = latLng.lng;

        // Using replaceState instead of window.location.hash to prevent a new history step begin added for every change in view settings
        history.replaceState(undefined, undefined, '#' + JSON.stringify(this.settings));
    }
};

Viewer.init();
