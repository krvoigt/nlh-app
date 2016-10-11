$(function () {
    var titleLineHeight = parseInt($('.result_title').css('line-height'));

    $('.result_thumbnail').each( function() {
        var $parent = $(this).closest('.result_item');
        if ($(this).height() > 0) {
            $parent.adjustHeight(titleLineHeight);
        } else {
            $(this).one('load', function () {
                $parent.adjustHeight(titleLineHeight);
            });
        }
    });

    $(window).on('resize', function () {
        $('.result_item').adjustHeight(titleLineHeight);
    });

    $('.result_title-toggle').click(function () {
        $(this).siblings('.result_title-toggle').addBack().toggle();
        $(this).closest('.result_title').toggleClass('-full');
    });
});

// TODO: Fix scope
$.fn.extend({
    adjustHeight: function (minHeight) {
        return this.each(function () {
            var $this = $(this);
            var $title = $this.find('.result_title');
            $title.height( '' ).removeClass('-cut');
            var metadataHeight = $this.find('.result_metadata').height();
            var thumbnailHeight = $this.find('.result_thumbnail').height();
            var excessHeight = Math.floor(metadataHeight - thumbnailHeight);
            if ( $title.height() > minHeight && excessHeight > 0 ) {
                $title.height( Math.max($title.height() - Math.ceil(excessHeight / minHeight) * minHeight, minHeight) ).addClass('-cut');
                $this.find('.result_title-toggle.-expand').show();
            } else {
                $this.find('.result_title-toggle.-expand').hide();
            }
        });
    },
});
