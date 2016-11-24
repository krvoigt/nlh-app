var Find = {
    init: function () {
        this.bindEvents();
    },

    bindEvents: function () {
        $('.find_sort-order').change(function(){
            var params = $(this).val().split('-');
            var sort = params[0];
            var direction = params[1];
            setGetParameters({sort: sort, direction: direction}, true);
        });
    },
}
