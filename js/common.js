/**
 * Cookies handler
 **/

var docCookies = {
    getItem: function (sKey) {
        return decodeURIComponent(document.cookie.replace(new RegExp("(?:(?:^|.*;)\\s*" + encodeURIComponent(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=\\s*([^;]*).*$)|^.*$"), "$1")) || null;
    },
    setItem: function (sKey, sValue, vEnd, sPath, sDomain, bSecure) {
        if(!sKey || /^(?:expires|max\-age|path|domain|secure)$/i.test(sKey)) { return false; }
        var sExpires = "";
        if(vEnd) {
            switch (vEnd.constructor) {
            case Number:
                sExpires = vEnd === Infinity ? "; expires=Fri, 31 Dec 9999 23:59:59 GMT" : "; max-age=" + vEnd;
                break;
            case String:
                sExpires = "; expires=" + vEnd;
                break;
            case Date:
                sExpires = "; expires=" + vEnd.toUTCString();
                break;
            }
        }
        document.cookie = encodeURIComponent(sKey) + "=" + encodeURIComponent(sValue) + sExpires + (sDomain ? "; domain=" + sDomain : "") + (sPath ? "; path=" + sPath : "") + (bSecure ? "; secure" : "");
        return true;
    },
    removeItem: function (sKey, sPath, sDomain) {
        if(!sKey || !this.hasItem(sKey)) { return false; }
        document.cookie = encodeURIComponent(sKey) + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT" + ( sDomain ? "; domain=" + sDomain : "") + ( sPath ? "; path=" + sPath : "");
        return true;
    },
    hasItem: function (sKey) {
        return (new RegExp("(?:^|;\\s*)" + encodeURIComponent(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=")).test(document.cookie);
    }
};

/**
 * Pages routing
 **/

// Current page
var currentPage;

// Handle hash change
window.onhashchange = function() {
    routePage();
};

// Route to page
var xhrPageLoading;
function routePage(loadedCallback) {
    if(currentPage) currentPage.destroy();
    currentPage = null;
    $('#page').html('');
    $('#loading').show();

    if(xhrPageLoading) {
        xhrPageLoading.abort();
    }

    var hash = window.location.hash.split('/')[0];
    $('.hot_link').parent().removeClass('active');
    var $link = $('a.hot_link[href="' + (hash || '#') + '"]');

    $link.parent().addClass('active');
    var page = $link.data('page');

    xhrPageLoading = $.ajax({
        url: 'pages/' + page,
        cache: false,
        success: function (data) {
            $('#menu-content').collapse('hide');
            $('#loading').hide();
            $('#page').show().html(data);
	    if(currentPage) currentPage.update();
	    if(loadedCallback) loadedCallback();
        }
    });
}

/**
 * Strings
 **/

// Add .update() custom jQuery function to update text content
$.fn.update = function(txt) {
    var el = this[0];
    if(el.textContent !== txt)
        el.textContent = txt;
    return this;
};

// Update Text classes
function updateTextClasses(className, text) {
    var els = document.getElementsByClassName(className);
    if(els) {
        for (var i = 0; i < els.length; i++) {
            var el = els[i];
            if(el && el.textContent !== text)
                el.textContent = text;
        }
    }
}

// Update Text content
function updateText(elementId, text) {
    var el = document.getElementById(elementId);
    if(el && el.textContent !== text) {
        el.textContent = text;
    }
    return el;
}

// Convert float to string
function floatToString(float) {
    return float.toFixed(6).replace(/\.0+$|0+$/, '');
}

// Format number
function formatNumber(number, delimiter) {
    if(number != '') {
        number = number.split(delimiter).join('');

        var formatted = '';
        var sign = '';

        if(number < 0) {
            number = -number;
            sign = '-';
        }

        while(number >= 1000) {
            var mod = number % 1000;

            if(formatted != '') formatted = delimiter + formatted;
            if(mod == 0) formatted = '000' + formatted;
            else if(mod < 10) formatted = '00' + mod + formatted;
            else if(mod < 100) formatted = '0' + mod + formatted;
            else formatted = mod + formatted;

            number = parseInt(number / 1000);
        }

        if(formatted != '') formatted = sign + number + delimiter + formatted;
        else formatted = sign + number;
        return formatted;
    }
    return '';
}

// Format date
function formatDate(time) {
    if(!time) return '';
    return new Date(parseInt(time) * 1000).toLocaleString();
}

// Format percentage
function formatPercent(percent) {
    if(!percent && percent !== 0) return '';
    return percent + '%';
}

// Get readable time
function getReadableTime(seconds) {
    var units = [ [60, 'second'], [60, 'minute'], [24, 'hour'],
                  [7, 'day'], [4, 'week'], [12, 'month'], [1, 'year'] ];

    function formatAmounts(amount, unit) {
        var rounded = Math.round(amount);
	var unit = unit + (rounded > 1 ? 's' : '');
        if(getTranslation(unit)) unit = getTranslation(unit);
        return '' + rounded + ' ' + unit;
    }

    var amount = seconds;
    for (var i = 0; i < units.length; i++) {
        if(amount < units[i][0]) {
            return formatAmounts(amount, units[i][1]);
        }
        amount = amount / units[i][0];
    }
    return formatAmounts(amount,  units[units.length - 1][1]);
}

// Get readable hashrate
function getReadableHashRateString(hashrate) {
    if(!hashrate) hashrate = 0;

    var i = 0;
    var byteUnits = [' H', ' kH', ' MH', ' GH', ' TH', ' PH' ];
    if(hashrate > 0) {
        while (hashrate > 1000) {
            hashrate = hashrate / 1000;
            i++;
        }
    }
    return parseFloat(hashrate).toFixed(2) + byteUnits[i];
}

function numberWithCommas(x) {
    var parts = x.toString().split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return parts.join(".");
}

// Get readable coins
function getReadableCoins(coins, atomicUnits=false, withoutSymbol=false) {
    if(atomicUnits)
        var amount = (parseInt(coins || 0) / 10000).toFixed(4);
    else
        var amount = parseFloat(coins || 0).toFixed(4);
    return amount.toString() + (withoutSymbol ? '' : (' PASC'));
}

function convertTimestamp(timestamp) {
    var d = new Date(timestamp * 1),// Convert the passed timestamp to milliseconds
	yyyy = d.getFullYear(),
	mm = ('0' + (d.getMonth() + 1)).slice(-2),// Months are zero based. Add leading 0.
	dd = ('0' + d.getDate()).slice(-2),// Add leading 0.
	hh = d.getHours(),
	h = hh,
	min = ('0' + d.getMinutes()).slice(-2),// Add leading 0.
	ampm = 'AM',
	time;

    if (hh > 12) {
	h = hh - 12;
	ampm = 'PM';
    } else if (hh === 12) {
	h = 12;
	ampm = 'PM';
    } else if (hh == 0) {
	h = 12;
    }

    // ie: 2013-02-18, 8:35 AM
    time = yyyy + '-' + mm + '-' + dd + ', ' + h + ':' + min + ' ' + ampm;

    return time;
}

function slugify(text) {
    return text.toString().toLowerCase()
        .replace(/\s+/g, '-')           // Replace spaces with -
        .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
        .replace(/\-\-+/g, '-')         // Replace multiple - with single -
        .replace(/^-+/, '')             // Trim - from start of text
        .replace(/-+$/, '');            // Trim - from end of text
}

// Navbar
$(".navbar-nav a:not(.dropdown-toggle)").on("click", function(){
    $(".navbar-collapse").collapse("hide");
});


