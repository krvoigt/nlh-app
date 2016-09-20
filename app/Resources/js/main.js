$.fx.speeds._default = 250;

$('.sort_item_input').change(function(){

    var url = location.href,
        paramName = 'sort',
        paramValue = $(this).attr('id');
    url = url.replace(/sort\=[a-z]*&?/, '');

    if (url.indexOf('?') < 0) {
        url += '?' + paramName + '=' + paramValue;
    }
    else {
        url += '&' + paramName + '=' + paramValue;
    }
    history.pushState(null, null, url);
    location.href = url;
});
