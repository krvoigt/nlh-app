function setGetParameters(params, changeLocation) {
    var url = window.location.href.replace(hash, '');
    var hash = window.location.hash;

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

    if (changeLocation) {
        window.location.href = url + hash;
    } else {
        return url + hash
    }
}
