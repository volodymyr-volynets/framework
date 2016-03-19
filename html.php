<?php

class html /*implements numbers_frontend_html_interface_base*/ {

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
		return factory::delegate('flag.numbers.framework.html', 'segment', [$options]);
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
		return factory::delegate('flag.numbers.framework.html', 'a', [$options]);
	}

	/**
	 * Mandatory
	 *
	 * @param array $options
	 *		type - mandatory or conditional
	 *		value - string or array
	 *		prepend - what to prepend to value
	 * @return string
	 */
	public static function mandatory($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'mandatory', [$options]);
	}

	/**
	 * Div
	 *
	 * @param array $options
	 * @return string
	 */
	public static function div($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'div', [$options]);
	}

	/**
	 * Tag
	 *
	 * @param array $options
	 *		tag - defaulted to div
	 * @return string
	 */
	public static function tag($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'tag', [$options]);
	}

	/**
	 * Label
	 *
	 * @param array $options
	 * @return string
	 */
	public static function label($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'label', [$options]);
	}

	/**
	 * Input
	 *
	 * @param array $options
	 * @return string
	 */
	public static function input($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'input', [$options]);
	}

	/**
	 * Input group
	 *
	 * @param array $options
	 *		left - left elements
	 *		right - right elements
	 *		value - center element
	 * @return string
	 */
	public static function input_group($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'input_group', [$options]);
	}

	/**
	 * Checkbox
	 *
	 * @param array $options
	 * @return string
	 */
	public static function checkbox($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'checkbox', [$options]);
	}

	/**
	 * Radio
	 *
	 * @param array $options
	 * @return string
	 */
	public static function radio($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'radio', [$options]);
	}

	/**
	 * Span
	 *
	 * @param array $options
	 * @return string
	 */
	public static function span($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'span', [$options]);
	}

	/**
	 * Grid
	 *
	 * @param array $options
	 * @return string
	 */
	public static function grid($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'grid', [$options]);
	}

	/**
	 * Form
	 *
	 * @param array $options
	 * @return string
	 */
	public static function form($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'form', [$options]);
	}

	/**
	 * Table
	 *
	 * @param array $options
	 * @return string
	 */
	public static function table($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'table', [$options]);
	}

	/**
	 * Script
	 *
	 * @param array $options
	 * @return string
	 */
	public static function script($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'script', [$options]);
	}

	/**
	 * Password
	 *
	 * @param array $options
	 * @return string
	 */
	public static function password($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'password', [$options]);
	}

	/**
	 * Submit
	 *
	 * @param array $options
	 * @return string
	 */
	public static function submit($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'submit', [$options]);
	}

	/**
	 * List
	 *
	 * @param array $options
	 * @return string
	 */
	public static function ul($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'ul', [$options]);
	}

	/**
	 * Select
	 *
	 * @param array $options
	 *		searchable - whether search is enabled
	 *		tree - whether we have multi-level tree
	 *		color_picker - whether its a color select
	 * @return string
	 */
	public static function select($options = []) {
		if (!empty($options['searchable'])) {
			$options['searchable'] = 'searchable';
		}
		if (!empty($options['tree'])) {
			$options['tree'] = 'tree';
		}
		if (!empty($options['color_picker'])) {
			$options['color_picker'] = 'color_picker';
		}
		return factory::delegate('flag.numbers.framework.html', 'select', [$options]);
	}

	/**
	 * see html::select() with multiple
	 */
	public static function multiselect($options = []) {
		$options['multiple'] = true;
		return self::select($options);
	}

	/**
	 * Image
	 *
	 * @param array $options
	 * @return string
	 */
	public static function img($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'img', [$options]);
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
	 *				class_only - whether to return class only
	 *
	 * @return string
	 */
	public static function icon($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'icon', [$options]);
	}

	/**
	 * Calendar
	 *
	 * @param array $options
	 *		calendar_type - date, datetime or time
	 *		calendar_format - i.e. Y-m-d
	 *		calendar_date_week_start_day - start day number
	 *		calendar_date_disable_week_days - array of week days to be disabled
	 *		calendar_master_id - id of master calendar
	 *		calendar_slave_id - id of slave element
	 *		calendar_placeholder - whether to display plaseholder based on calendar_type
	 *		calendar_icon - left, right
	 * @return string
	 */
	public static function calendar($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'calendar', [$options]);
	}

	/**
	 * Captcha
	 *
	 * @param array $options
	 * @return string
	 */
	public static function captcha($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'captcha', [$options]);
	}

	/**
	 * Menu
	 *
	 * @param type $options
	 *		options - array of menu items
	 */
	public static function menu($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'menu', [$options]);
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
		$options = array_merge_hard(application::get('flag.numbers.framework.html.options'), $options);
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