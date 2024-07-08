<?php

class HTML {

	/**
	 * White space tags
	 */
	const HTML_WHITE_SPACE_TAGS_ARRAY = ['<br>', '<br/>', '<br />', '<hr>', '<hr/>', '<hr />'];

	/**
	 * Initialize
	 *
	 * @return void
	 */
	public static function init() : void {
		\Factory::delegate('flag.numbers.framework.html', 'init', []);
	}

	/**
	 * H1
	 *
	 * @param array $options
	 * @return string
	 */
	public static function h1(array $options = []) : string {
		$options['tag'] = 'h1';
		return \Factory::delegate('flag.numbers.framework.html', 'tag', [$options]);
	}

	/**
	 * H2
	 *
	 * @param array $options
	 * @return string
	 */
	public static function h2(array $options = []) : string {
		$options['tag'] = 'h2';
		return \Factory::delegate('flag.numbers.framework.html', 'tag', [$options]);
	}

	/**
	 * H3
	 *
	 * @param array $options
	 * @return string
	 */
	public static function h3(array $options = []) : string {
		$options['tag'] = 'h3';
		return \Factory::delegate('flag.numbers.framework.html', 'tag', [$options]);
	}

	/**
	 * H4
	 *
	 * @param array $options
	 * @return string
	 */
	public static function h4(array $options = []) : string {
		$options['tag'] = 'h4';
		return \Factory::delegate('flag.numbers.framework.html', 'tag', [$options]);
	}

	/**
	 * H5
	 *
	 * @param array $options
	 * @return string
	 */
	public static function h5(array $options = []) : string {
		$options['tag'] = 'h5';
		return \Factory::delegate('flag.numbers.framework.html', 'tag', [$options]);
	}

	/**
	 * Generate name
	 *
	 * @param string $name
	 * @param string $icon
	 * @param boolean $skip_i18n
	 */
	public static function name($name, $icon = null, bool $skip_i18n = false) : string {
		if (empty($skip_i18n)) {
			$name = i18n(null, $name);
		}
		if (!empty($icon)) {
			$name = \HTML::icon(['type' => $icon]) . ' ' . $name;
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
	public static function separator(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'separator', [$options]);
	}

	/**
	 * Segment
	 *
	 * @param array $options
	 *		type - one of \Object\HTML\Button
	 *		value - body
	 *		header - top part
	 *		footer - bottom part
	 *		other elements become attribute of main div container
	 *
	 * @return string
	 */
	public static function segment(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'segment', [$options]);
	}

	/**
	 * Fieldset
	 *
	 * @param array $options
	 *		legend
	 *		value
	 * @return string
	 */
	public static function fieldset(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'fieldset', [$options]);
	}

	/**
	 * Link
	 *
	 * @param array $options
	 *		value - link text
	 *		other elements become attribute of main a element
	 * @return string
	 */
	public static function a(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'a', [$options]);
	}

	/**
	 * Hidden element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function hidden(array $options = []) : string {
		return Factory::delegate('flag.numbers.framework.html', 'hidden', [$options]);
	}

	/**
	 * Text area
	 *
	 * @param array $options
	 * @return string
	 */
	public static function textarea(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'textarea', [$options]);
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
	public static function mandatory(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'mandatory', [$options]);
	}

	/**
	 * Div
	 *
	 * @param array $options
	 * @return string
	 */
	public static function div(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'div', [$options]);
	}

	/**
	 * Tag
	 *
	 * @param array $options
	 *		tag - defaulted to div
	 * @return string
	 */
	public static function tag(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'tag', [$options]);
	}

	/**
	 * H1
	 *
	 * @param array $options
	 * @return string
	 */
	public static function h1(array $options = []) : string {
		$options['tag'] = 'h1';
		return Factory::delegate('flag.numbers.framework.html', 'tag', [$options]);
	}

	/**
	 * H2
	 *
	 * @param array $options
	 * @return string
	 */
	public static function h2(array $options = []) : string {
		$options['tag'] = 'h2';
		return Factory::delegate('flag.numbers.framework.html', 'tag', [$options]);
	}

	/**
	 * H3
	 *
	 * @param array $options
	 * @return string
	 */
	public static function h3(array $options = []) : string {
		$options['tag'] = 'h3';
		return Factory::delegate('flag.numbers.framework.html', 'tag', [$options]);
	}

	/**
	 * H4
	 *
	 * @param array $options
	 * @return string
	 */
	public static function h4(array $options = []) : string {
		$options['tag'] = 'h4';
		return Factory::delegate('flag.numbers.framework.html', 'tag', [$options]);
	}

	/**
	 * H5
	 *
	 * @param array $options
	 * @return string
	 */
	public static function h5(array $options = []) : string {
		$options['tag'] = 'h5';
		return Factory::delegate('flag.numbers.framework.html', 'tag', [$options]);
	}

	/**
	 * H6
	 *
	 * @param array $options
	 * @return string
	 */
	public static function h6(array $options = []) : string {
		$options['tag'] = 'h6';
		return Factory::delegate('flag.numbers.framework.html', 'tag', [$options]);
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
	public static function label(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'label', [$options]);
	}

	/**
	 * Accordion
	 *
	 * @param array $options
	 *	options - array of options
	 *		id
	 *		title
	 *		content
	 * @return string
	 */
	public static function accordion(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'accordion', [$options]);
	}
	
	/**
	 * Labels with background
	 *
	 * @param array $options
	 * @return string
	 */
	public static function label2(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'label2', [$options]);
	}

	/**
	 * Badge with background
	 *
	 * @param array $options
	 * @return string
	 */
	public static function badge(array $options = []) : string {
		return Factory::delegate('flag.numbers.framework.html', 'label2', [$options]);
	}

	/**
	 * Input
	 *
	 * @param array $options
	 * @return string
	 */
	public static function input(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'input', [$options]);
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
	public static function inputGroup(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'inputGroup', [$options]);
	}

	/**
	 * Checkbox
	 *
	 * @param array $options
	 * @return string
	 */
	public static function checkbox(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'checkbox', [$options]);
	}

	/**
	 * Radio
	 *
	 * @param array $options
	 * @return string
	 */
	public static function radio(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'radio', [$options]);
	}

	/**
	 * Span
	 *
	 * @param array $options
	 * @return string
	 */
	public static function span(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'span', [$options]);
	}

	/**
	 * Grid
	 *
	 * @param array $options
	 * @return string
	 */
	public static function grid(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'grid', [$options]);
	}

	/**
	 * Form
	 *
	 * @param array $options
	 * @return string
	 */
	public static function form(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'form', [$options]);
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
	public static function table(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'table', [$options]);
	}

	/**
	 * Script
	 *
	 * @param array $options
	 * @return string
	 */
	public static function script(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'script', [$options]);
	}

	/**
	 * Password
	 *
	 * @param array $options
	 * @return string
	 */
	public static function password(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'password', [$options]);
	}

	/**
	 * Submit
	 *
	 * @param array $options
	 * @return string
	 */
	public static function submit(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'submit', [$options]);
	}

	/**
	 * Button
	 *
	 * @param array $options
	 * @return string
	 */
	public static function button(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'button', [$options]);
	}

	/**
	 * Button2
	 *
	 * @param array $options
	 * @return string
	 */
	public static function button2(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'button2', [$options]);
	}

	/**
	 * List
	 *
	 * @param array $options
	 * @return string
	 */
	public static function ul(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'ul', [$options]);
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
	public static function select(array $options = []) : string {
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
		if (!empty($options['search_first'])) {
			$options['search_first'] = 'search_first';
		}
		return \Factory::delegate('flag.numbers.framework.html', 'select', [$options]);
	}

	/**
	 * see \HTML::select() with multiple
	 */
	public static function multiselect(array $options = []) : string {
		$options['multiple'] = true;
		return self::select($options);
	}

	/**
	 * Tree
	 *
	 * @param array $options
	 *		options - array of items
	 * @return string
	 */
	public static function tree(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'tree', [$options]);
	}

	/**
	 * Image
	 *
	 * @param array $options
	 * @return string
	 */
	public static function img(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'img', [$options]);
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
	public static function icon(array $options = []) {
		return \Factory::delegate('flag.numbers.framework.html', 'icon', [$options]);
	}

	/**
	 * Flag
	 *
	 * @param array $options
	 *		string country_code
	 * @return string
	 */
	public static function flag(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'flag', [$options]);
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
	public static function calendar(array $options = []) : string {
		// we need to set to date by default
		$options['calendar_type'] = $options['calendar_type'] ?? $options['type'] ?? 'date';
		return \Factory::delegate('flag.numbers.framework.html', 'calendar', [$options]);
	}

	/**
	 * Signature
	 *
	 * @param array $options
	 * @return string
	 */
	public static function signature(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'signature', [$options]);
	}

	/**
	 * Captcha
	 *
	 * @param array $options
	 * @return string
	 */
	public static function captcha(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'captcha', [$options]);
	}

	/**
	 * Map
	 *
	 * @param array $options
	 * @return string
	 */
	public static function map(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'map', [$options]);
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
	public static function autocomplete(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'autocomplete', [$options]);
	}

	/**
	 * Menu
	 *
	 * @param type $options
	 *	array options - array of menu items
	 */
	public static function menu(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'menu', [$options]);
	}

	/**
	 * Menu (mini)
	 *
	 * @param type $options
	 *	array options - array of menu items
	 *	string id
	 *	string align - right | left
	 */
	public static function menuMini(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'menuMini', [$options]);
	}

	/**
	 * Bread crumbs
	 *
	 * @param array $options
	 * @return string
	 */
	public static function breadcrumbs(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'breadcrumbs', [$options]);
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
	public static function message(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'message', [$options]);
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
	public static function modal(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'modal', [$options]);
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
	public static function text(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'text', [$options]);
	}

	/**
	 * File
	 *
	 * @param array $options
	 * @return string
	 */
	public static function file(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'file', [$options]);
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
	public static function tabs(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'tabs', [$options]);
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
	public static function pills(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'pills', [$options]);
	}

	/**
	 * Wysiwyg
	 *
	 * @param array $options
	 * @return string
	 */
	public static function wysiwyg(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'wysiwyg', [$options]);
	}

	/**
	 * Code editor
	 *
	 * @param array $options
	 * @return string
	 */
	public static function ace(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'ace', [$options]);
	}

	/**
	 * Highlight
	 *
	 * @param array $options
	 * @return string
	 */
	public static function highlight(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'highlight', [$options]);
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
	public static function wizard(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'wizard', [$options]);
	}

	/**
	 * HR
	 *
	 * @param array $options
	 * @return string
	 */
	public static function hr(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'hr', [$options]);
	}

	/**
	 * BR
	 *
	 * @param array $options
	 * @return string
	 */
	public static function br(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'br', [$options]);
	}

	/**
	 * Bold
	 *
	 * @param array $options
	 * @return string
	 */
	public static function b(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'b', [$options]);
	}

	/**
	 * Callout
	 *
	 * @param array $options
	 *	string value
	 *	string type
	 * @return string
	 */
	public static function callout(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'callout', [$options]);
	}

	/**
	 * Audio
	 *
	 * @param array $options
	 *		string src
	 *		string mime
	 * @return string
	 */
	public static function audio(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'audio', [$options]);
	}

	/**
	 * Popover
	 *
	 * @param array $options
	 *		string id
	 *		string value
	 *		string title
	 *		string content
	 * @return string
	 */
	public static function popover(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'popover', [$options]);
	}

	/**
	 * Progress bar
	 *
	 * @param array $options
	 *		string id
	 *		string class
	 *		float|string value
	 *		int progressbar_max
	 *		int progressbar_min
	 *		string progressbar_style
	 *		string label_name
	 *		bool skip_hidden
	 *		string bg_color
	 * @return string
	 */
	public static function progressbar(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'progressbar', [$options]);
	}

	/**
	 * Canvas
	 *
	 * @param array $options
	 *	string value
	 *	string class
	 *	string id
	 * @return string
	 */
	public static function canvas(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'canvas', [$options]);
	}

	/**
	 * Process options
	 *
	 * @param string $model_and_method
	 * @param object $existing_object
	 * @return array
	 */
	public static function processOptions($model_and_method, $existing_object = null) {
		return \Factory::delegate('flag.numbers.framework.html', 'process_options', [$model_and_method, $existing_object]);
	}

	/**
	 * Convert array of percentages to grid columns
	 *
	 * @param mixed $percentage_array
	 * @return array
	 */
	public static function percentageToGridColumns($percentage_array, array $options = []) : array {
		if (!is_array($percentage_array)) {
			$percentage_array = [$percentage_array];
		}
		$options = array_merge_hard(Application::get('flag.numbers.framework.html.options'), $options);
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
			'data' => $arr['final'],
			'percent' => $arr['percent']
		];
	}

	/**
	 * Convert number to word
	 *
	 * @param int $number
	 */
	public static function numberToWord(int $number) : string {
		$words = [
			'zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine',
			'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'
		];
		return $words[$number] ?? ($number . '');
	}

	/**
	 * Align
	 *
	 * @param string $align
	 * @return string
	 */
	public static function align(string $align) : string {
		if (empty($align)) $align = 'left';
		if (\I18n::rtl()) {
			if ($align == 'left') {
				$align = 'right';
			} else if ($align == 'right') {
				$align = 'left';
			}
		}
		return $align;
	}

	/**
	 * Number
	 *
	 * @param mixed $number
	 * @return string
	 */
	public static function number($number) : string {
		return str_replace(',', '.', $number . '');
	}

	/**
	 * Iframe
	 *
	 * @param array $options
	 * @return string
	 */
	public static function iframe(array $options = []) : string {
		return \Factory::delegate('flag.numbers.framework.html', 'iframe', [$options]);
	}

	/**
	 * Set mode
	 *
	 * @param bool $email
	 */
	public static function setMode(bool $email) {
		return \Factory::delegate('flag.numbers.framework.html', 'setMode', [$email]);
	}

	/**
	 * Get mode
	 *
	 * @param bool $email
	 */
	public static function getMode() {
		return \Factory::delegate('flag.numbers.framework.html', 'getMode', []);
	}

	/**
	 * Clear
	 *
	 * @return string
	 */
	public static function clear() : string {
		return \Factory::delegate('flag.numbers.framework.html', 'clear', []);
	}

	/**
	 * Convert color to RGB
	 *
	 * @param string $color
	 * @return array
	 */
	public static function color2rgb($color): array {
		$colors = array(
		    "black" => array("red" => 0x00, "green" => 0x00, "blue" => 0x00),
		    "maroon" => array("red" => 0x80, "green" => 0x00, "blue" => 0x00),
		    "green" => array("red" => 0x00, "green" => 0x80, "blue" => 0x00),
		    "olive" => array("red" => 0x80, "green" => 0x80, "blue" => 0x00),
		    "navy" => array("red" => 0x00, "green" => 0x00, "blue" => 0x80),
		    "purple" => array("red" => 0x80, "green" => 0x00, "blue" => 0x80),
		    "teal" => array("red" => 0x00, "green" => 0x80, "blue" => 0x80),
		    "gray" => array("red" => 0x80, "green" => 0x80, "blue" => 0x80),
		    "silver" => array("red" => 0xC0, "green" => 0xC0, "blue" => 0xC0),
		    "red" => array("red" => 0xFF, "green" => 0x00, "blue" => 0x00),
		    "lime" => array("red" => 0x00, "green" => 0xFF, "blue" => 0x00),
		    "yellow" => array("red" => 0xFF, "green" => 0xFF, "blue" => 0x00),
		    "blue" => array("red" => 0x00, "green" => 0x00, "blue" => 0xFF),
		    "fuchsia" => array("red" => 0xFF, "green" => 0x00, "blue" => 0xFF),
		    "aqua" => array("red" => 0x00, "green" => 0xFF, "blue" => 0xFF),
		    "white" => array("red" => 0xFF, "green" => 0xFF, "blue" => 0xFF),
		    "aliceblue" => array("red" => 0xF0, "green" => 0xF8, "blue" => 0xFF),
		    "antiquewhite" => array("red" => 0xFA, "green" => 0xEB, "blue" => 0xD7),
		    "aquamarine" => array("red" => 0x7F, "green" => 0xFF, "blue" => 0xD4),
		    "azure" => array("red" => 0xF0, "green" => 0xFF, "blue" => 0xFF),
		    "beige" => array("red" => 0xF5, "green" => 0xF5, "blue" => 0xDC),
		    "blueviolet" => array("red" => 0x8A, "green" => 0x2B, "blue" => 0xE2),
		    "brown" => array("red" => 0xA5, "green" => 0x2A, "blue" => 0x2A),
		    "burlywood" => array("red" => 0xDE, "green" => 0xB8, "blue" => 0x87),
		    "cadetblue" => array("red" => 0x5F, "green" => 0x9E, "blue" => 0xA0),
		    "chartreuse" => array("red" => 0x7F, "green" => 0xFF, "blue" => 0x00),
		    "chocolate" => array("red" => 0xD2, "green" => 0x69, "blue" => 0x1E),
		    "coral" => array("red" => 0xFF, "green" => 0x7F, "blue" => 0x50),
		    "cornflowerblue" => array("red" => 0x64, "green" => 0x95, "blue" => 0xED),
		    "cornsilk" => array("red" => 0xFF, "green" => 0xF8, "blue" => 0xDC),
		    "crimson" => array("red" => 0xDC, "green" => 0x14, "blue" => 0x3C),
		    "darkblue" => array("red" => 0x00, "green" => 0x00, "blue" => 0x8B),
		    "darkcyan" => array("red" => 0x00, "green" => 0x8B, "blue" => 0x8B),
		    "darkgoldenrod" => array("red" => 0xB8, "green" => 0x86, "blue" => 0x0B),
		    "darkgray" => array("red" => 0xA9, "green" => 0xA9, "blue" => 0xA9),
		    "darkgreen" => array("red" => 0x00, "green" => 0x64, "blue" => 0x00),
		    "darkkhaki" => array("red" => 0xBD, "green" => 0xB7, "blue" => 0x6B),
		    "darkmagenta" => array("red" => 0x8B, "green" => 0x00, "blue" => 0x8B),
		    "darkolivegreen" => array("red" => 0x55, "green" => 0x6B, "blue" => 0x2F),
		    "darkorange" => array("red" => 0xFF, "green" => 0x8C, "blue" => 0x00),
		    "darkorchid" => array("red" => 0x99, "green" => 0x32, "blue" => 0xCC),
		    "darkred" => array("red" => 0x8B, "green" => 0x00, "blue" => 0x00),
		    "darksalmon" => array("red" => 0xE9, "green" => 0x96, "blue" => 0x7A),
		    "darkseagreen" => array("red" => 0x8F, "green" => 0xBC, "blue" => 0x8F),
		    "darkslateblue" => array("red" => 0x48, "green" => 0x3D, "blue" => 0x8B),
		    "darkslategray" => array("red" => 0x2F, "green" => 0x4F, "blue" => 0x4F),
		    "darkturquoise" => array("red" => 0x00, "green" => 0xCE, "blue" => 0xD1),
		    "darkviolet" => array("red" => 0x94, "green" => 0x00, "blue" => 0xD3),
		    "deeppink" => array("red" => 0xFF, "green" => 0x14, "blue" => 0x93),
		    "deepskyblue" => array("red" => 0x00, "green" => 0xBF, "blue" => 0xFF),
		    "dimgray" => array("red" => 0x69, "green" => 0x69, "blue" => 0x69),
		    "dodgerblue" => array("red" => 0x1E, "green" => 0x90, "blue" => 0xFF),
		    "firebrick" => array("red" => 0xB2, "green" => 0x22, "blue" => 0x22),
		    "floralwhite" => array("red" => 0xFF, "green" => 0xFA, "blue" => 0xF0),
		    "forestgreen" => array("red" => 0x22, "green" => 0x8B, "blue" => 0x22),
		    "gainsboro" => array("red" => 0xDC, "green" => 0xDC, "blue" => 0xDC),
		    "ghostwhite" => array("red" => 0xF8, "green" => 0xF8, "blue" => 0xFF),
		    "gold" => array("red" => 0xFF, "green" => 0xD7, "blue" => 0x00),
		    "goldenrod" => array("red" => 0xDA, "green" => 0xA5, "blue" => 0x20),
		    "greenyellow" => array("red" => 0xAD, "green" => 0xFF, "blue" => 0x2F),
		    "honeydew" => array("red" => 0xF0, "green" => 0xFF, "blue" => 0xF0),
		    "hotpink" => array("red" => 0xFF, "green" => 0x69, "blue" => 0xB4),
		    "indianred" => array("red" => 0xCD, "green" => 0x5C, "blue" => 0x5C),
		    "indigo" => array("red" => 0x4B, "green" => 0x00, "blue" => 0x82),
		    "ivory" => array("red" => 0xFF, "green" => 0xFF, "blue" => 0xF0),
		    "khaki" => array("red" => 0xF0, "green" => 0xE6, "blue" => 0x8C),
		    "lavender" => array("red" => 0xE6, "green" => 0xE6, "blue" => 0xFA),
		    "lavenderblush" => array("red" => 0xFF, "green" => 0xF0, "blue" => 0xF5),
		    "lawngreen" => array("red" => 0x7C, "green" => 0xFC, "blue" => 0x00),
		    "lemonchiffon" => array("red" => 0xFF, "green" => 0xFA, "blue" => 0xCD),
		    "lightblue" => array("red" => 0xAD, "green" => 0xD8, "blue" => 0xE6),
		    "lightcoral" => array("red" => 0xF0, "green" => 0x80, "blue" => 0x80),
		    "lightcyan" => array("red" => 0xE0, "green" => 0xFF, "blue" => 0xFF),
		    "lightgoldenrodyellow" => array("red" => 0xFA, "green" => 0xFA, "blue" => 0xD2),
		    "lightgreen" => array("red" => 0x90, "green" => 0xEE, "blue" => 0x90),
		    "lightgrey" => array("red" => 0xD3, "green" => 0xD3, "blue" => 0xD3),
		    "lightpink" => array("red" => 0xFF, "green" => 0xB6, "blue" => 0xC1),
		    "lightsalmon" => array("red" => 0xFF, "green" => 0xA0, "blue" => 0x7A),
		    "lightseagreen" => array("red" => 0x20, "green" => 0xB2, "blue" => 0xAA),
		    "lightskyblue" => array("red" => 0x87, "green" => 0xCE, "blue" => 0xFA),
		    "lightslategray" => array("red" => 0x77, "green" => 0x88, "blue" => 0x99),
		    "lightsteelblue" => array("red" => 0xB0, "green" => 0xC4, "blue" => 0xDE),
		    "lightyellow" => array("red" => 0xFF, "green" => 0xFF, "blue" => 0xE0),
		    "limegreen" => array("red" => 0x32, "green" => 0xCD, "blue" => 0x32),
		    "linen" => array("red" => 0xFA, "green" => 0xF0, "blue" => 0xE6),
		    "mediumaquamarine" => array("red" => 0x66, "green" => 0xCD, "blue" => 0xAA),
		    "mediumblue" => array("red" => 0x00, "green" => 0x00, "blue" => 0xCD),
		    "mediumorchid" => array("red" => 0xBA, "green" => 0x55, "blue" => 0xD3),
		    "mediumpurple" => array("red" => 0x93, "green" => 0x70, "blue" => 0xD0),
		    "mediumseagreen" => array("red" => 0x3C, "green" => 0xB3, "blue" => 0x71),
		    "mediumslateblue" => array("red" => 0x7B, "green" => 0x68, "blue" => 0xEE),
		    "mediumspringgreen" => array("red" => 0x00, "green" => 0xFA, "blue" => 0x9A),
		    "mediumturquoise" => array("red" => 0x48, "green" => 0xD1, "blue" => 0xCC),
		    "mediumvioletred" => array("red" => 0xC7, "green" => 0x15, "blue" => 0x85),
		    "midnightblue" => array("red" => 0x19, "green" => 0x19, "blue" => 0x70),
		    "mintcream" => array("red" => 0xF5, "green" => 0xFF, "blue" => 0xFA),
		    "mistyrose" => array("red" => 0xFF, "green" => 0xE4, "blue" => 0xE1),
		    "moccasin" => array("red" => 0xFF, "green" => 0xE4, "blue" => 0xB5),
		    "navajowhite" => array("red" => 0xFF, "green" => 0xDE, "blue" => 0xAD),
		    "oldlace" => array("red" => 0xFD, "green" => 0xF5, "blue" => 0xE6),
		    "olivedrab" => array("red" => 0x6B, "green" => 0x8E, "blue" => 0x23),
		    "orange" => array("red" => 0xFF, "green" => 0xA5, "blue" => 0x00),
		    "orangered" => array("red" => 0xFF, "green" => 0x45, "blue" => 0x00),
		    "orchid" => array("red" => 0xDA, "green" => 0x70, "blue" => 0xD6),
		    "palegoldenrod" => array("red" => 0xEE, "green" => 0xE8, "blue" => 0xAA),
		    "palegreen" => array("red" => 0x98, "green" => 0xFB, "blue" => 0x98),
		    "paleturquoise" => array("red" => 0xAF, "green" => 0xEE, "blue" => 0xEE),
		    "palevioletred" => array("red" => 0xDB, "green" => 0x70, "blue" => 0x93),
		    "papayawhip" => array("red" => 0xFF, "green" => 0xEF, "blue" => 0xD5),
		    "peachpuff" => array("red" => 0xFF, "green" => 0xDA, "blue" => 0xB9),
		    "peru" => array("red" => 0xCD, "green" => 0x85, "blue" => 0x3F),
		    "pink" => array("red" => 0xFF, "green" => 0xC0, "blue" => 0xCB),
		    "plum" => array("red" => 0xDD, "green" => 0xA0, "blue" => 0xDD),
		    "powderblue" => array("red" => 0xB0, "green" => 0xE0, "blue" => 0xE6),
		    "rosybrown" => array("red" => 0xBC, "green" => 0x8F, "blue" => 0x8F),
		    "royalblue" => array("red" => 0x41, "green" => 0x69, "blue" => 0xE1),
		    "saddlebrown" => array("red" => 0x8B, "green" => 0x45, "blue" => 0x13),
		    "salmon" => array("red" => 0xFA, "green" => 0x80, "blue" => 0x72),
		    "sandybrown" => array("red" => 0xF4, "green" => 0xA4, "blue" => 0x60),
		    "seagreen" => array("red" => 0x2E, "green" => 0x8B, "blue" => 0x57),
		    "seashell" => array("red" => 0xFF, "green" => 0xF5, "blue" => 0xEE),
		    "sienna" => array("red" => 0xA0, "green" => 0x52, "blue" => 0x2D),
		    "skyblue" => array("red" => 0x87, "green" => 0xCE, "blue" => 0xEB),
		    "slateblue" => array("red" => 0x6A, "green" => 0x5A, "blue" => 0xCD),
		    "slategray" => array("red" => 0x70, "green" => 0x80, "blue" => 0x90),
		    "snow" => array("red" => 0xFF, "green" => 0xFA, "blue" => 0xFA),
		    "springgreen" => array("red" => 0x00, "green" => 0xFF, "blue" => 0x7F),
		    "steelblue" => array("red" => 0x46, "green" => 0x82, "blue" => 0xB4),
		    "tan" => array("red" => 0xD2, "green" => 0xB4, "blue" => 0x8C),
		    "thistle" => array("red" => 0xD8, "green" => 0xBF, "blue" => 0xD8),
		    "tomato" => array("red" => 0xFF, "green" => 0x63, "blue" => 0x47),
		    "turquoise" => array("red" => 0x40, "green" => 0xE0, "blue" => 0xD0),
		    "violet" => array("red" => 0xEE, "green" => 0x82, "blue" => 0xEE),
		    "wheat" => array("red" => 0xF5, "green" => 0xDE, "blue" => 0xB3),
		    "whitesmoke" => array("red" => 0xF5, "green" => 0xF5, "blue" => 0xF5),
		    "yellowgreen" => array("red" => 0x9A, "green" => 0xCD, "blue" => 0x32)
		);
		return array_values($colors[$color]);
	}

}