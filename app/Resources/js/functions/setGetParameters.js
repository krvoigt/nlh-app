function setGetParameters(params) {
    var url = window.location.href;
    var hash = window.location.hash;
    url = url.replace(hash, '');
    for (var key in params) {
        var value = params[key];
        if (url.indexOf(key + '=') >= 0) {
            var prefix = url.substring(0, url.indexOf(key));
            var suffix = url.substring(url.indexOf(key));
            suffix = suffix.substring(suffix.indexOf('=') + 1);
            suffix = (suffix.indexOf('&') >= 0) ? suffix.substring(suffix.indexOf('&')) : '';
            url = prefix + key + "=" + value + suffix;
        } else {
            if (url.indexOf('?') < 0) {
                url += '?';
            } else {
                url += '&';
            }
            url += key + '=' + value;
        }
    }
    window.location.href = url + hash;
}
