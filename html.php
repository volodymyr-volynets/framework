<?php

class html {

	/**
	 * Segment
	 *
	 * @param array $options
	 *		type - one of object_type_html_button
	 *		value - body
	 *		header - top part
	 *		footer - bottom part
	 *		other elements become attribute of main div container
	 *
	 * @return string
	 */
	public static function segment($options = []) {
		return factory::delegate('flag.global.html', 'segment', [$options]);
	}

	/**
	 * Link
	 *
	 * @param array $options
	 *		value - link text
	 *		other elements become attribute of main a element
	 * @return string
	 */
	public static function a($options = []) {
		return factory::delegate('flag.global.html', 'a', [$options]);
	}

	/**
	 * Mandatory
	 *
	 * @param string $type
	 * @param string $value
	 * @param string $prepend
	 * @return string
	 */
	public static function mandatory($type = 'mandatory', $value = null, $prepend = null) {
		return factory::delegate('flag.global.html', 'mandatory', [$type, $value, $prepend]);
	}

	/**
	 * Div
	 *
	 * @param array $options
	 * @return string
	 */
	public static function div($options = []) {
		return factory::delegate('flag.global.html', 'div', [$options]);
	}

	/**
	 * Tag
	 *
	 * @param string $tag
	 * @param array $options
	 * @return string
	 */
	public static function tag($tag, $options = []) {
		return factory::delegate('flag.global.html', 'tag', [$tag, $options]);
	}

	/**
	 * Label
	 *
	 * @param array $options
	 * @return string
	 */
	public static function label($options = []) {
		return factory::delegate('flag.global.html', 'label', [$options]);
	}

	/**
	 * Input
	 *
	 * @param array $options
	 * @return string
	 */
	public static function input($options = []) {
		return factory::delegate('flag.global.html', 'input', [$options]);
	}

	/**
	 * Grid
	 *
	 * @param array $options
	 * @return string
	 */
	public static function grid($options = []) {
		return factory::delegate('flag.global.html', 'grid', [$options]);
	}

	/**
	 * Form
	 *
	 * @param array $options
	 * @return string
	 */
	public static function form($options = []) {
		return factory::delegate('flag.global.html', 'form', [$options]);
	}

	/**
	 * Table
	 *
	 * @param array $options
	 * @return string
	 */
	public static function table($options = []) {
		return factory::delegate('flag.global.html', 'table', [$options]);
	}

	/**
	 * Script
	 *
	 * @param array $options
	 * @return string
	 */
	public static function script($options = []) {
		return factory::delegate('flag.global.html', 'script', [$options]);
	}

	/**
	 * Password
	 *
	 * @param array $options
	 * @return string
	 */
	public static function password($options = []) {
		return factory::delegate('flag.global.html', 'password', [$options]);
	}

	/**
	 * Submit
	 *
	 * @param array $options
	 * @return string
	 */
	public static function submit($options = []) {
		return factory::delegate('flag.global.html', 'submit', [$options]);
	}

	/**
	 * List
	 *
	 * @param array $options
	 * @return string
	 */
	public static function ul($options = []) {
		return factory::delegate('flag.global.html', 'ul', [$options]);
	}

	/**
	 * Select
	 *
	 * @param array $options
	 * @return string
	 */
	public static function select($options = []) {
		return factory::delegate('flag.global.html', 'select', [$options]);
	}

	/**
	 * Image
	 *
	 * @param array $options
	 * @return string
	 */
	public static function img($options = []) {
		return factory::delegate('flag.global.html', 'img', [$options]);
	}

	/**
	 * Icon
	 *
	 * @param array $options
	 *		two functionalities:
	 *			1) when we pass file/path we would input image
	 *				file - icon filename
	 *				path - icon path from public_html
	 *			2) when we pass type we generate a specified tag
	 *				tag - which tag to render, default is <i>
	 *				type - becomes a class depends on backend
	 *					for base - icon [type]
	 *					for fontawesome - fa fa-[type]
	 *
	 * @return string
	 */
	public static function icon($options = []) {
		return factory::delegate('flag.global.html', 'icon', [$options]);
	}

	/**
	 * Calendar
	 *
	 * @param array $options
	 * @return string
	 */
	public static function calendar($options = []) {
		return factory::delegate('flag.global.html', 'calendar', [$options]);
	}

	/**
	 * Convert array of percentages to grid columns
	 *
	 * @param mixed $percentage_array
	 * @return array
	 */
	public static function percentage_to_grid_columns($percentage_array, $options = []) {
		if (!is_array($percentage_array)) {
			$percentage_array = [$percentage_array];
		}
		$options = array_merge2(application::get('flag.global.html.options'), $options);
		if (empty($options['grid_columns'])) {
			$options['grid_columns'] = 12;
		}
		$step = 100 / $options['grid_columns'];
		$total = 0;
		$empty = 0;
		$arr = ['percent' => [], 'final' => [], 'temp' => [], 'grouped' => []];
		foreach ($percentage_array as $k => $v) {
			$arr['percent'][$k] = $v ? $v : 0;
			if (!empty($v)) {
				$total+= $v;
			} else {
				$empty+= 1;
			}
		}
		// if we have empty columns and percent is less than 100 we prepopulate
		if ($total < 100 && $empty != 0) {
			$temp = (100 - $total) / $empty;
			foreach ($arr['percent'] as $k => $v) {
				if ($v == 0) {
					$arr['percent'][$k] = $temp;
					$total+= $temp;
				}
			}
		}
		// we need to rescale if percent is more than 100
		if ($total > 100) {
			$scale = 100 / $total;
			foreach ($arr['percent'] as $k => $v) {
				$arr['percent'][$k] = $v * $scale;
			}
		}
		// sort in ascending order
		asort($arr['percent']);
		$cells_left = $options['grid_columns'];
		foreach ($arr['percent'] as $k => $v) {
			if ($v <= $step) {
				$arr['final'][$k] = 1;
				$cells_left--;
			} else {
				$rounded = floor($v / $step);
				$leftover = $v - $rounded * $step;
				$arr['final'][$k] = $rounded;
				$cells_left-= $rounded;
				if ($leftover > 0) {
					$arr['temp'][$k] = $leftover;
				}
			}
		}
		// if we have cells left we distribute
		if ($cells_left > 0) {
			// grouping & sorting for special handling
			/* todo: polish login for left overs
			foreach ($arr['percent'] as $k => $v) {
				$arr['grouped'][$v][$k] = 1;
			}
			krsort($arr['grouped']);
			$temp = array_values($arr['grouped']);
			if ($cells_left == 2 && count($arr['grouped']) > 1 && count($temp[0]) == 1 && count($temp[1]) > 1) {
				$arr['final'][key($temp[0])]+= 2;
				$cells_left = 0;
			}
			*/
			// if we got here we distribute on by one
			if ($cells_left > 0) {
				arsort($arr['temp']);
				foreach ($arr['temp'] as $k => $v) {
					$arr['final'][$k]++;
					$cells_left--;
					unset($arr['temp'][$k]);
					if ($cells_left == 0) {
						break;
					}
				}
			}
		}
		return [
			'success' => true,
			'error' => [],
			'data' => $arr['final']
		];
	}

	/**
	 * Convert number to word
	 *
	 * @param int $number
	 */
	public static function number_to_word($number) {
		$words = [
			'zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine',
			'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'
		];
		return $words[$number] ?? '';
	}
}