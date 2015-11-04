/**
 * Numbers functions
 */

function is_numeric(mixed_var) {
    if (mixed_var === "") return false;
    return !isNaN(mixed_var * 1);
}

function in_array(needle, haystack, argStrict) {
    var key = '', strict = !!argStrict;
    if (strict) {
		for (key in haystack) {
			if (haystack[key] === needle) {
				return true;
			}
		}
	} else {
		for (key in haystack) {
			if (haystack[key] == needle) {
				return true;
			}
		}
	}
    return false;
}

function empty(mixed_var) {
    var key;
	if (mixed_var === "" || mixed_var === 0 || mixed_var === "0" || mixed_var === null || mixed_var === false || typeof mixed_var === 'undefined') {
        return true;
    }
    if (typeof mixed_var == 'object') {
		for (key in mixed_var) {
            return false;
        }
        return true;
    }
    return false;
}

function isset() {
    var a = arguments, l = a.length, i = 0, undef;
    if (l === 0) {
        throw new Error('Empty isset');
    }
     while (i !== l) {
        if (a[i] === undef || a[i] === null) {
            return false;
		}
        i++;
    }
    return true;
}

function print_r(x, max, sep, l) {
    l = l || 0;
    max = max || 99;
    sep = sep || ' ';
    if (l > max) {
        return "[WARNING: Too much recursion]\n";
    }
    var i, r = '', t = typeof x, tab = '';
    if (x === null) {
        r += "(null)\n";
    } else if (t == 'object') {
        l++;
        for (i = 0; i < l; i++) {
            tab += sep;
        }
        if (x && x.length) {
            t = 'array';
        }
        r += '(' + t + ") :\n";
        for (i in x) {
            try {
                r += tab + '[' + i + '] : ' + print_r(x[i], max, sep, (l + 1));
            } catch(e) {
                return "[ERROR: " + e + "]\n";
            }
        }
    } else {
        if (t == 'string') {
            if (x == '') {
                x = '(empty)';
            }
        }
        r += '(' + t + ') ' + x + "\n";
    }
    return r;
}

function print_r2(x) {
    alert(print_r(x));
}