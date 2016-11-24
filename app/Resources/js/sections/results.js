var Results = {
    minTitleHeight: parseInt($('.result_title').css('line-height')) * 2,

    init: function () {
        var that = this;

        $('.result_thumbnail').each( function() {
            var $parent = $(this).closest('.result_item');
            if ($(this).height() > 0) {
                that.adjustTitleHeight($parent);
            } else {
                $(this).one('load', function () {
                    that.adjustTitleHeight($parent);
                });
            }
        });

        this.bindEvents();
    },

    bindEvents: function () {
        $(window).on('resize', this.adjustTitleHeight.bind(this, $('.result_item')));

        $('.result_title-toggle').click(function () {
            $(this).siblings('.result_title-toggle').addBack().toggle();
            $(this).closest('.result_title').toggleClass('-full');
        });
    },

    adjustTitleHeight: function (item) {
        var that = this;
        return item.each(function () {
            var $this = $(this);
            var $title = $this.find('.result_title');
            $title.height( '' ).removeClass('-cut');
            var metadataHeight = $this.find('.result_metadata').height();
            var thumbnailHeight = $this.find('.result_thumbnail').height();
            var excessHeight = Math.floor(metadataHeight - thumbnailHeight);
            if ( $title.height() > that.minTitleHeight && excessHeight > 0 ) {
                $title.height( Math.max($title.height() - Math.ceil(excessHeight / that.minTitleHeight) * that.minTitleHeight, that.minTitleHeight) ).addClass('-cut');
                $this.find('.result_title-toggle.-expand').show();
            } else {
                $this.find('.result_title-toggle.-expand').hide();
            }
        });
    },
};
