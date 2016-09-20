$(function () {
    $('.find_sort-order').change(function(){
        var order = $(this).val();
        setGetParameters({sort: order});
    });
});
