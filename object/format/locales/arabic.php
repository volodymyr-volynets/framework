<?php

class object_format_locales_arabic {

	public static function amount($amount) {
		return strtr($amount . '', ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩']);
	}

	public static function read_floatval($amount) {
		return strtr($amount . '', array_flip(['٠','١','٢','٣','٤','٥','٦','٧','٨','٩']));
	}

	public static function js() {
		$js = <<<TTT
			/* custom locale methods */
			numbers.format.__custom = {
				amount: function(amount, options) {
					return amount.toString().replace(/[0123456789]/g, function(number) { return '٠١٢٣٤٥٦٧٨٩'[parseInt(number)]; });
				},
				read_floatval: function(amount, options) {
					return amount.replace(/[٠١٢٣٤٥٦٧٨٩]/g, function(char) { return char.charCodeAt(0) - 1632; }).replace(/[۰۱۲۳۴۵۶۷۸۹]/g, function(char) { return char.charCodeAt(0) - 1776; });
				}
			};
TTT;
		layout::onload($js);
	}
}