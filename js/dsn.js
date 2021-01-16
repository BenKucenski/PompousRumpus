var HTTP = {
    Get: function (url, vars, callback) {
        return HTTP.Post(url, vars, callback, 'GET');
    },
    Post: function (url, vars, callback, method) {

        if (!method) {
            method = "POST";
        }

        $.ajax({
            method: method,
            url: url,
            data: vars,
            dataType: "json",
            async: true,
            error: function (XMLHttpRequest) {
                alert(XMLHttpRequest.responseText);
            },
            success: function (data) {
                callback(data);
            }
        });
    }
};

function twoDigits(d) {
    if (0 <= d && d < 10) return "0" + d.toString();
    if (-10 < d && d < 0) return "-0" + (-1 * d).toString();
    return d.toString();
}

function Timestamp() {
    var d = new Date();
    return twoDigits(1 + d.getMonth()) + "/" + twoDigits(d.getDate()) + "/" + d.getFullYear() + " " + twoDigits(d.getHours()) + ":" + twoDigits(d.getMinutes()) + ":00";
}

var QueryString = function () {
    // This function is anonymous, is executed immediately and
    // the return value is assigned to QueryString!
    var query_string = {};
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split("=");
        // If first entry with this name
        if (typeof query_string[pair[0]] === "undefined") {
            query_string[pair[0]] = pair[1];
            // If second entry with this name
        } else if (typeof query_string[pair[0]] === "string") {
            query_string[pair[0]] = [query_string[pair[0]], pair[1]];
            // If third or later entry with this name
        } else {
            query_string[pair[0]].push(pair[1]);
        }
    }
    query_string['base_url'] = window.location.href.split('?')[0];
    return query_string;
}();


function fallbackCopyTextToClipboard(text) {
    var textArea = document.createElement("textarea");
    textArea.value = text;

    // Avoid scrolling to bottom
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";

    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        var successful = document.execCommand('copy');
        var msg = successful ? 'successful' : 'unsuccessful';
        console.log('Fallback: Copying text command was ' + msg);
    } catch (err) {
        console.error('Fallback: Oops, unable to copy', err);
    }

    document.body.removeChild(textArea);
}
function copyTextToClipboard(text) {
    if (!navigator.clipboard) {
        fallbackCopyTextToClipboard(text);
        return;
    }
    navigator.clipboard.writeText(text).then(function() {
        console.log('Async: Copying to clipboard was successful!');
    }, function(err) {
        console.error('Async: Could not copy text: ', err);
    });
}
