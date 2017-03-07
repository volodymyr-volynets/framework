<?php

class html {

	/**
	 * Generate name
	 *
	 * @param string $name
	 * @param string $icon
	 * @param boolean $no_i18n
	 */
	public static function name($name, $icon = null, $no_i18n = false) {
		if (!$no_i18n) {
			$name = i18n(null, $name);
		}
		if (!empty($icon)) {
			$name = html::icon(['type' => $icon]) . ' ' . $name;
		}
		return $name;
	}

	/**
	 * Separator
	 *
	 * @param array $options
	 *		value
	 *		icon
	 */
	public static function separator($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'separator', [$options]);
	}

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
	 * Fieldset
	 *
	 * @param array $options
	 *		legend
	 *		value
	 * @return string
	 */
	public static function fieldset($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'fieldset', [$options]);
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
	 * Hidden element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function hidden($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'hidden', [$options]);
	}

	/**
	 * Text area
	 *
	 * @param array $options
	 * @return string
	 */
	public static function textarea($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'textarea', [$options]);
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
	 *		type - one of
	 *			default
	 *			primary
	 *			success
	 *			info
	 *			warning
	 *			danger
	 * @return string
	 */
	public static function label($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'label', [$options]);
	}

	/**
	 * Labels with background
	 *
	 * @param array $options
	 * @return string
	 */
	public static function label2($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'label2', [$options]);
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
	 *		header
	 *		options
	 *		skip_header
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
	 * Button
	 *
	 * @param array $options
	 * @return string
	 */
	public static function button($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'button', [$options]);
	}

	/**
	 * Button2
	 *
	 * @param array $options
	 * @return string
	 */
	public static function button2($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'button2', [$options]);
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
		if (!empty($options['preset'])) {
			$options['preset'] = 'preset';
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
	 *		calendar_icon - left, right
	 * @return string
	 */
	public static function calendar($options = []) {
		// we need to set to date by default
		$options['calendar_type'] = $options['calendar_type'] ?? $options['type'] ?? 'date';
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
	 * Autocomplete
	 *
	 * @param array $options
	 *		id
	 *		name
	 *		multiple
	 *		options_model
	 *		options_params
	 *		__ajax
	 *		__ajax_autocomplete->name
	 *		__ajax_autocomplete->text
	 *		options_autocomplete_fields
	 *		options_autocomplete_pk
	 * @return string
	 */
	public static function autocomplete($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'autocomplete', [$options]);
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
	 * Bread crumbs
	 *
	 * @param array $options
	 * @return string
	 */
	public static function breadcrumbs($options) {
		return factory::delegate('flag.numbers.framework.html', 'breadcrumbs', [$options]);
	}

	/**
	 * Message
	 *
	 * @param array $options
	 *		type one of:
	 *			danger
	 *			warning
	 *			success
	 *			info
	 *			other
	 *		options - a list of messages
	 * @return string
	 */
	public static function message($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'message', [$options]);
	}

	/**
	 * Modal
	 *
	 * @param array $options
	 *		id - id of the modal
	 *		class - class of the modal
	 *		title - title of the modal
	 *		body - body of the modal
	 *		footer - buttons
	 */
	public static function modal($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'modal', [$options]);
	}

	/**
	 * Text
	 *
	 * @param array $options
	 *		type one of:
	 *			muted
	 *			primary
	 *			success
	 *			info
	 *			warning
	 *			danger
	 *		value
	 *		tag - default is <p>
	 * @return string
	 */
	public static function text($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'text', [$options]);
	}

	/**
	 * File
	 *
	 * @param array $options
	 * @return string
	 */
	public static function file($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'file', [$options]);
	}

	/**
	 * Tabs
	 *
	 * @param type $options
	 *		header
	 *		options
	 *		id
	 *		active_tab
	 *		class
	 *		tab_options
	 * @return string
	 */
	public static function tabs($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'tabs', [$options]);
	}

	/**
	 * Pills
	 *
	 * @param array $options
	 *		array options
	 *		string id
	 *		string active_pill
	 * @return string
	 */
	public static function pills($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'pills', [$options]);
	}

	/**
	 * Wysiwyg
	 *
	 * @param array $options
	 * @return string
	 */
	public static function wysiwyg($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'wysiwyg', [$options]);
	}

	/**
	 * Wizard
	 *
	 * @param array $options
	 *		type - one of
	 *			default
	 *			primary
	 *			success
	 *			info
	 *			warning
	 *			danger
	 *		step - current step
	 *		options - a list of steps in a wizard
	 * @return string
	 */
	public static function wizard($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'wizard', [$options]);
	}

	/**
	 * HR
	 *
	 * @param array $options
	 * @return string
	 */
	public static function hr($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'hr', [$options]);
	}

	/**
	 * BR
	 *
	 * @param array $options
	 * @return string
	 */
	public static function br($options = []) {
		return factory::delegate('flag.numbers.framework.html', 'br', [$options]);
	}

	/**
	 * Process options
	 *
	 * @param string $model_and_method
	 * @param object $existing_object
	 * @return array
	 */
	public static function process_options($model_and_method, $existing_object = null) {
		return factory::delegate('flag.numbers.framework.html', 'process_options', [$model_and_method, $existing_object]);
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

	/**
	 * Align
	 *
	 * @param string $align
	 * @return string
	 */
	public static function align($align) {
		if (empty($align)) $align = 'left';
		if (i18n::rtl()) {
			if ($align == 'left') {
				$align = 'right';
			} else if ($align == 'right') {
				$align = 'left';
			}
		}
		return $align;
	}
}