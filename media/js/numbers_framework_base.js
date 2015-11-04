/**
 * Numbers object
 *
 * @type object
 */
var numbers = {
	/**
	 * Token for communication with backend
	 *
	 * @type string
	 */
	token: null,
	/**
	 * Error handling
	 */
	error: {
		count: 0,
		init: function() {
			window.onerror = function (message, file, line, col, error) {
				numbers.error.count++;
				// if we have toolbar
				if ($('#debuging_toolbar_js_a').length) {
					$('#debuging_toolbar_js_a').html('Js (' + numbers.error.count + ')');
					$('#debuging_toolbar_js_a').css('color', 'red');
					var str = '<br/>';
					str+= 'Message: ' + message + '<br/>';
					str+= 'File: ' + file + '<br/>';
					str+= 'Line: ' + line + '<br/>';
					str+= 'Column: ' + col + '<br/>';
					str+= '<hr/>';
					$('#debuging_toolbar_js_data').append(str);
					alert('Javascript Error: ' + message);
				}
				// todo: send data to server for further processing
				var data = {
					message: message,
					file: file,
					line: line,
					col: col
				};
				numbers.error.send_data(data);
			};
		},
		send_data: function(data) {
			var img = document.createElement('img');
			var src = '/numbers/framework/controller/error.png?token=' + encodeURIComponent(numbers.token) + '&data=' + encodeURIComponent(JSON.stringify(data));
			img.crossOrigin = 'anonymous';
			img.onload = function success() {
				console.log('success', data);
			};
			img.onerror = img.onabort = function failure() {
				console.error('failure', data);
			};
			img.src = src;
		}
	},
	/**
	 * Controller objects
	 */
	controller: {
		base: {
			name: "numbers controller base",
			extend: function(options) {
				return $.extend({}, this, options);
			}
		}
	},
	/**
	 * Ajax calls will be done via these get/post methods
	 */
	ajax: {
		get: function (url, data, callback) {
			$.get(url, data, callback, 'json');
		},
		post: function (url, data, callback) {
			$.post(url, data, callback, 'json');
		}
	}
};

// initializing
numbers.error.init();