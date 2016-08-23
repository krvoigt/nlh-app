$(function () {
    $('.facets_toggle').click(function () {
        $(this).siblings('.facets_toggle').addBack().toggleClass('hidden');
        $('.facets_body').slideToggle(function () {
            $(this).toggleClass('-visible').css('display', '');
        });
    });

    $('.facet').each(function () {
        if ($(this).find('.facet_item').length < 7) {
            $(this).find('.facet_list-toggle.-expand').hide();
        }
    });

    $('.facet_list-toggle').click(function () {
        var $facetList = $(this).siblings('.facet_list');
        $facetList.find('li:nth-child(n+6)').toggle();
        $(this).siblings('.facet_list-toggle').addBack().toggle();
    });

    $histogramContainer = $('.facet_histogram');
    if ( $histogramContainer.length > 0 ) {
        createHistogramForTermsInContainer($histogramContainer.closest('.facet'), $histogramContainer, {barWidth: 1}); // TODO
    }
    function createHistogramForTermsInContainer($parent, $graphDiv, config) {
        // TODO: Show all values even if facet selected
        var graphWidth = $parent.width() + 20; // TODO
        var canvasHeight = 150;
        $graphDiv.css({'width': graphWidth + 'px', 'height': canvasHeight + 'px', 'position': 'relative'});

        // TODO: Load query with yearpublish range (does only accept numbers so far)
        var startSearchWithNewFacet = function (range) {
            var queryString = window.location.search || '?';
            queryString = queryString.replace(/filter%5B\d+%5D%5Byearpublish%5D=%5B\d+%20TO%20\d+%5D.*?(\&.*)?$/g, '$1');
            var index = (queryString.match(/filter%5B\d+%5D/g) || []).length;
            queryString += ( queryString.length > 1 ? '&' : '' );
            queryString += encodeURIComponent('filter[' + index + '][yearpublish]') + '=' + encodeURIComponent('[' + range.from + ' TO ' + range.to + ']');
            window.location.href = window.location.href.split('?')[0] + queryString;
        };

        var graphData = $graphDiv.data('years');

        /**
         * Set up xaxis with two labelled ticks, one at each end.
         * Dodgy: Use whitespace to approximately position the labels in a way that they don’t
         * extend beyond the end of the graph (by default they are centered at the point of
         * their axis, thus extending beyond the width of the graph on one site.
         *
         * @param {object} axis
         * @returns {array}
         */
        var xaxisTicks = function (axis) {
            return [[axis.datamin, '      ' + axis.datamin], [axis.datamax, axis.datamax + '      ']];
        };

        // Use the color of term list titles for the histogram.
        var graphColor = '#e4e4e4'; // border color
        var barColor = '#1a3771'; // brand color
        var selectionColor = '#93a8cf' // brand color light

        var graphOptions = {
            series: {
                bars: {
                    show: true,
                    fill: true,
                    lineWidth: 0,
                    barWidth: config.barWidth,
                    fillColor: barColor,
                }
            },
            xaxis:  {
                tickDecimals: 0,
                ticks: xaxisTicks,
            },
            yaxis: {
                position: 'right',
                tickDecimals: 0,
                labelWidth: 30,
            },
            grid: {
                backgroundColor: '#fff',
                borderWidth: 0,
                hoverable: true,
            },
            selection: {
                mode: 'x',
                color: selectionColor,
                minSize: 0,
            }
        };

        // Create plot
        var plot = jQuery.plot($graphDiv, [{'data': graphData, 'color': graphColor}], graphOptions);

        // Create tooltip
        var $tooltipDiv = $('<div class="facet_tooltip"/>');
        $toolTip = $tooltipDiv.appendTo(document.body);

        // TODO
        // If range is set, highlight in plot
        selection = {
            from: null,
            to: null,
        };
        plot.setSelection({'xaxis': selection});

        $graphDiv.bind('plotclick', function (event, pos, item) {
            return true;
        });

        $graphDiv.bind('plotselected', function(event, ranges) {
            selectRanges(ranges);
        });

        $graphDiv.bind('plotunselected', function() {
            return false;
        });

        var roundedRange = function (range) {
            var outputRange = {};
            var from = Math.floor(range.from);
            outputRange.from = from - (from % config.barWidth);
            var to = Math.ceil(range.to);
            outputRange.to = to - (to % config.barWidth) + config.barWidth - 1;
            return outputRange;
        };

        var selectRanges = function (ranges) {
            var newRange = roundedRange(ranges.xaxis);
            plot.setSelection({'xaxis': newRange}, true);
            hideTooltip();
            startSearchWithNewFacet(newRange);
        };

        var hideTooltip = function () {
            $toolTip.hide();
        };

        var updateTooltip = function (event, ranges, pageX) {
            var showTooltip = function(x, y, contents) {
                $toolTip.text(contents);
                if (x) {
                    $toolTip.css( {
                        'top': y - 20,
                        'left': x + 5
                    });
                }
                $toolTip.show();
            };

            var tooltipY = $graphDiv.offset().top + canvasHeight - 20;
            var displayString;

            if (ranges) {
                if ($graphDiv.currentSelection && $graphDiv.currentSelection.xaxis) {
                    var range = roundedRange(ranges.xaxis);
                    displayString = range.from.toString() + '–' + range.to.toString(); // That's an n-dash, not a minus
                }
                else {
                    var year = Math.floor(ranges.xaxis.from);
                    year = year - (year % config.barWidth);
                    var dataIndex = year - plot.getData()[0].xaxis.min;
                    if ( dataIndex >= 0 && dataIndex < graphData.length ) {
                        count = graphData[dataIndex][1];
                        var displayString = year.toString() + ' (' + count + ')';
                    }
                }
            }

            if (displayString) {
                showTooltip(pageX, tooltipY, displayString);
            } else {
                hideTooltip();
            }
        };

        $graphDiv.bind('plothover', function(event, ranges, item) {
            updateTooltip(event, {'xaxis': {'from': ranges.x, 'to': ranges.x}}, ranges.pageX);
        });

        $graphDiv.bind('plotselecting', function (event, info) {
            $graphDiv.currentSelection = info;
            updateTooltip(event, info);
        });

        $graphDiv.mouseout(hideTooltip);
    };
});
