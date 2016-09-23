var Find = {
    init: function () {
        this.bindEvents();
    },

    bindEvents: function () {
        $('.find_sort-order').change(function(){
            var order = $(this).val();
            var sortby = _.split(order, '_');
            setGetParameters({sort: order, direction: sortby[1]}, true);
        });
    },
}

Find.init();
