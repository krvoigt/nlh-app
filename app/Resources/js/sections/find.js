$(function () {
    $('.find_sort-order').change(function(){
        var order = $(this).val();
        var sortby = _.split(order, '_');

        setGetParameters({sort: sortby[0], direction: sortby[1]}, true);
    });
});
