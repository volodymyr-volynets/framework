/**
 * Numbers wrappers for element manipulations
 *
 * @type object
 */
numbers.element = {

	/**
	 * Toggle element by id or element itself
	 *
	 * @param string id
	 */
	toggle: function(id, elem) {
		if (elem) {
			$(elem).toggle();
		} else if (id) {
			$('#' + id).toggle();
		}
	},

	/**
	 * Get element by id
	 *
	 * @param string id
	 * @returns elem
	 */
	by_id: function(id) {
		return $('#' + id);
	}
};