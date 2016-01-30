/**
 * Numbers wrappers for format functions
 *
 * @type object
 */
numbers.format = {

	/**
	 * Format date based on format
	 *
	 * @param string value
	 * @param string type
	 * @param array options
	 * @returns string
	 */
	date_format: function(value, type, options) {
		if (!value) return null;
		if (!type) type = 'date';
		if (!options) options = {};
		// processing format
		var format;
		if (options.format) {
			format = options.format;
		} else {
			// todo: we need to load format from global flags
			if (type == 'time') {
				format = 'H:i:s';
			} else if (type == 'datetime') {
				format = 'Y-m-d H:i:s';
			} else {
				format = 'Y-m-d';
			}
		}
		// formatting string
		if (typeof value == 'object') {
			var datetime = value;
		} else {
			var datetime = new Date(value);
		}
		var result = format;
		result = result.replace('Y', datetime.getFullYear());
		result = result.replace('d', datetime.getDate() < 10 ? ('0'+ datetime.getDate()) : datetime.getDate());
		result = result.replace('m', (datetime.getMonth() + 1 < 10) ? ('0'+ (datetime.getMonth() + 1)) : (datetime.getMonth() + 1));
		result = result.replace('H', (datetime.getHours() < 10) ? ('0'+ datetime.getHours()) : datetime.getHours());
		result = result.replace('i', (datetime.getMinutes() < 10) ? ('0'+ datetime.getMinutes()) : datetime.getMinutes());
		result = result.replace('s', (datetime.getSeconds() < 10) ? ('0'+ datetime.getSeconds()) : datetime.getSeconds());
		var hours = datetime.getHours();
		result = result.replace('a', (hours >= 12) ? 'pm' : 'am');
		var ghours = hours > 12 ? (hours - 12) : hours;
		result = result.replace('g', (ghours < 10) ? ('0'+ ghours) : ghours);
		return result;
	},

	/**
	 * Get date format
	 *
	 * @param string type
	 * @returns string
	 */
	get_date_format: function(type) {
		var format, global_format = array_key_get(numbers, 'flag.global.format') || {};
		if (type == 'time') {
			format = global_format.time ? global_format.time : 'H:i:s';
		} else if (type == 'datetime') {
			format = global_format.datetime ? global_format.datetime : 'Y-m-d H:i:s';
		} else {
			format = global_format.date ? global_format.date : 'Y-m-d';
		}
		return format;
	},

	/**
	 * Format date
	 *
	 * @param string value
	 * @param array options
	 * @returns string
	 */
	date: function(value, options) {
		return this.date_format(value, 'date', options);
	},

	/**
	 * Format datetime
	 *
	 * @param string value
	 * @param array options
	 * @returns string
	 */
	datetime: function(value, options) {
		return this.date_format(value, 'datetime', options);
	},

	/**
	 * Format time
	 *
	 * @param string value
	 * @param array options
	 * @returns string
	 */
	time: function(value, options) {
		return this.date_format(value, 'time', options);
	}
};