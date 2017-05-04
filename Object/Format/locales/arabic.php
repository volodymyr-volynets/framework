<?php

namespace Object\Format\Locales;
class Arabic {

	public static function amount($amount) {
		return strtr($amount . '', ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩']);
	}

	public static function readFloatval($amount) {
		return strtr($amount . '', array_flip(['٠','١','٢','٣','٤','٥','٦','٧','٨','٩']));
	}

	public static function js() {
		$js = <<<TTT
			/* custom locale methods */
			Numbers.Format.__custom = {
				amount: function(amount, options) {
					return amount.toString().replace(/[0123456789]/g, function(number) { return '٠١٢٣٤٥٦٧٨٩'[parseInt(number)]; });
				},
				read_floatval: function(amount, options) {
					return amount.replace(/[٠١٢٣٤٥٦٧٨٩]/g, function(char) { return char.charCodeAt(0) - 1632; }).replace(/[۰۱۲۳۴۵۶۷۸۹]/g, function(char) { return char.charCodeAt(0) - 1776; });
				}
			};
TTT;
		Layout::onload($js);
	}
}