<?php

/**
 * h class is designed to help generate HTML 5 code
 */
class h {
	
	/**
	 * Autocomplete with ajax
	 * 
	 * @param array $options
	 * @return string
	 */
	public static function autocomplete($options) {
		if (empty($options['id'])) {
			Throw new Exception('id?');
		}
		$id = $options['id'];
		$options['size'] = @$options['size'] ? $options['size'] : 10;
		$options['controller'] = @$options['controller'] ? $options['controller'] : '/ajax/closed';
		$options['map'] = @$options['map'] ? $options['map'] : null;
		$options['onkeyup'] = 'autocomplete_ajax(this, \'' . $options['controller'] . '\', \'' . $options['model'] . '\', ' . str_replace('"', "'", json_encode($options['map'])) . ', \'' . @$options['name_element'] . '\', \'' . @$options['class'] . '\', ' . str_replace('"', "'", json_encode(@$options['where'])) . ', \'' . @$options['function'] . '\', event);';
		$options['onblur'] = 'autocomplete_hide(this, \'' . @$options['function'] . '\');';
		$options['class'] = 'autocomplete_input ' . @$options['class'];
		$options['autocomplete'] = 'off';
		unset($options['map'], $options['where'], $options['controller'], $options['name_element']);
		$result = h::input($options);
		return $result;
	}
	
	/**
	 * Building tabs
	 * 
	 * @param array $options
	 * @return string
	 */
	public static function tabs($options) {
		$input = request::input();
		$ms = '';
		$options['id'] = 'h_tabs_' . $options['id'];
		$ms.= '<div id="' . $options['id'] . '">';
			$ms.= '<ul class="tabs">';
				foreach ($options['value'] as $k=>$v) {
					$v['href'] = @$v['href'] ? $v['href'] : 'javascript:void(0);';
					$class = '';
					if (empty($input[$options['id'] . '_current_tab_element'])) {
						$class = ' selected';
						$input[$options['id'] . '_current_tab_element'] = $options['id'] . '_' . $k;
					}
					$ms.= '<li>';
						$style = @$v['style'] ? (' style="' . $v['style'] . '" ') : '';
						$ms.= '<a href="' . $v['href'] . '" id="' . $options['id'] . '_' . $k . '" class="' . ($k==1 ? 'first' : '') . $class . '"' . $style . '>' . $v['name'] . '</a>';
					$ms.= '</li>';
				}
			$ms.= '</ul>';
			foreach ($options['value'] as $k=>$v) {
				$style = ($k==1) ? '' : 'style="display: none;"';
				$ms.= '<div id="' . $options['id'] . '_' . $k . '_b" ' . $style . ' class="' . $options['id'] . '_class tabs_content">' . $v['body'] . '</div>';
			}
			$ms.= h::hidden(array('name'=>$options['id'] . '_current_tab_element', 'id'=>$options['id'] . '_current_tab_element', 'value'=>@$input[$options['id'] . '_current_tab_element']));
		$ms.= '</div>';
		// onload
		layout::onload('			
					$(document).ready(function(){
						$("#' . $options['id'] . ' ul a").click(function(){
							$("#' . $options['id'] . ' ul a").removeClass("selected");
							$(this).addClass("selected");
							$(".' . $options['id'] . '_class").hide();
							$("#" + $(this).attr("id") + "_b").show();
							$("#' . $options['id'] . '_current_tab_element").val($(this).attr("id"));
						});
						$("#' .$input[$options['id'] . '_current_tab_element'] .  '").click();
					});
				
				');
		return $ms;
	}
	
	/**
	 * Build a calendar
	 * 
	 * @param array $options
	 * @return string
	 */
	public static function calendar($options) {
		$o = $options;
		$o['maxlength'] = $o['size'] = 10;
		$input = self::input($o);
		$format = format::use_locale() ? format::date_format('jquery') : 'yy-mm-dd';
		$onchange = !empty($options['onchange']) ? (', onSelect: function() {' . $options['onchange'] . '}') : '';
		if (empty($options['disabled'])) {
			$script = '$("#' . $options['id'] . '").datepicker({ showOn: "button", buttonImage: "/img/icons/calendar16.png", buttonImageOnly: true, changeMonth: true, changeYear: true, dateFormat: "' . $format . '"' . $onchange . ' });';
			Layout::onload($script);
		}
		// loading files
		layout::add_js('/jquery/js/jquery-ui.min.js', -30000);
		layout::add_css('/jquery/css/jquery-ui.css');
		return $input;
	}

	/**
	 * This function will create a (link) element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function a($options = array()) {
		$result = '';
		$value = @$options['value'];
		unset($options['value']);
		$result.= '<a';
		foreach($options as $k=>$v) {
			if (is_array($v)) {
				$v = implode(' ', $v);
			}
			$result.= ' ' . $k . '="' . $v . '"';
		}
		$result.= '>' . $value . '</a>';
		return $result;
	}
	
	/**
	 * This function will generate img element
	 * 
	 * @param array $options
	 * @return string
	 */
	public static function img($options = array()) {
		$options['border'] = @$options['border'] ? $options['border'] : 0;
		$result = '<img';
		foreach($options as $k=>$v) {
			$result.= ' ' . $k . '="' . $v . '"';
		}
		$result.= '/>';
		return $result;
	}

	/**
	 * Script element
	 * 
	 * @param array $options
	 * @return string
	 */
	public static function script($options = array()) {
		$value = @$options['value'];
		unset($options['value']);
		$options['type'] = @$options['type'] ? $options['type'] : 'text/javascript';
		$result = '';
		$result.= '<script';
		foreach($options as $k=>$v) {
			if (is_array($v)) $v = implode(' ', $v);
			$result.= ' ' . $k . '="' . $v . '"';
		}
		$result.= '>' . $value . '</script>';
		return $result;
	}

	/**
	 * Style element
	 * 
	 * @param array $options
	 * @return string
	 */
	public static function style($options = array()) {
		$value = @$options['value'];
		unset($options['value']);
		$options['type'] = @$options['type'] ? $options['type'] : 'text/css';
		$result = '';
		$result.= '<style';
		foreach($options as $k=>$v) {
			if (is_array($v)) $v = implode(' ', $v);
			$result.= ' ' . $k . '="' . $v . '"';
		}
		$result.= '>' . $value . '</style>';
		return $result;
	}

	/**
	 * Radio options
	 *  
	 * @param array $options
	 * @return string
	 */
	public static function radio($options = array()) {
		if (@$options['checked']) {
			$options['checked'] = 'checked';
		} else {
			unset($options['checked']);
		}
		if (!empty($options['readonly'])) $options['disabled'] = 'disabled';
		unset($options['options'], $options['readonly']);
		$options['type'] = 'radio';
		return self::input($options);
	}

	/**
	 * Input element
	 * 
	 * @param array $options
	 * @return string
	 */
	public static function input($options = array()) {
		$options['type'] = @$options['type'] ? $options['type'] : 'text';
		if (!empty($options['checked'])) $options['checked'] = 'checked'; else unset($options['checked']);
		if (!empty($options['readonly'])) $options['readonly'] = 'readonly'; else unset($options['readonly']);
		if (!empty($options['disabled'])) $options['disabled'] = 'disabled'; else unset($options['disabled']);
		if (!isset($options['autocomplete'])) $options['autocomplete'] = 'off';
		$result = '';
		$result.= '<input';
		foreach($options as $k=>$v) {
			if ($k=='value') $v = htmlspecialchars($v);
			$result.= ' ' . $k . '="' . $v . '"';
		}
		$result.= '/>';
		return $result;
	}

	/**
	 * This function will create checkbox (input) element
	 * 
	 * @param array $options
	 * @return string
	 */
	public static function checkbox($options = array()) {
		if (empty($options['value'])) $options['value'] = 1;
		// we will check within an array automatically
		if (!empty($options['checked'])) {
			if (is_array($options['checked'])) {
				if (in_array($options['value'], $options['checked'])) {
					$options['checked'] = true;
				} else {
					unset($options['checked']);
				}
			} else {
				$options['checked'] = true;
			}
		} else {
			unset($options['checked']);
		}
		if (!empty($options['readonly'])) {
			$options['disabled'] = 'disabled';
		}
		unset($options['options'], $options['readonly']);
		$options['type'] = 'checkbox';
		return self::input($options);
	}

	/**
	 * Button element
	 * 
	 * @param array $options
	 * @return string
	 */
	public static function button($options = array()) {
		$options['type'] = 'button';
		$options['value'] = isset($options['value']) ? $options['value'] : 'Submit';
		$options['class'] = isset($options['class']) ? $options['class'] : 'button';
		return self::input($options);
	}
	
	/**
	 * Button element 2nd edition
	 *
	 * @param array $options
	 * @return string
	 */
	public static function button2($options = array()) {
		$options['type'] = isset($options['type']) ? $options['type'] : 'submit';
		$value = isset($options['value']) ? $options['value'] : 'Submit';
		$options['class'] = isset($options['class']) ? $options['class'] : 'button';
		$options['value'] = @$options['name'] ? $options['name'] : 'value';
		$result = '';
		$result.= '<button';
		foreach($options as $k=>$v) {
			if ($k=='value') $v = htmlspecialchars($v);
			$result.= ' ' . $k . '="' . $v . '"';
		}
		$result.= '>' . $value . '</button>';
		return $result;
	}

	/**
	 * Password element
	 * 
	 * @param array $options
	 * @return string
	 */
	public static function password($options = array()) {
		$options['type'] = 'password';
		return self::input($options);
	}

	/**
	 * File element
	 * 
	 * @param array $options
	 * @return string
	 */
	public static function file($options = array()) {
		$options['type'] = 'file';
		return self::input($options);
	}

	/**
	 * Submit element
	 * 
	 * @param array $options
	 * @return string
	 */
	public static function submit($options = array()) {
		$options['type'] = 'submit';
		$options['value'] = isset($options['value']) ? $options['value'] : 'Submit';
		$options['class'] = isset($options['class']) ? $options['class'] : 'button';
		return self::input($options);
	}

	/**
	 * This function will create hidden element
	 * 
	 * @param array $options
	 * @return string
	 */
	public static function hidden($options = array()) {
		$options['type'] = 'hidden';
		return self::input($options);
	}

	/**
	 * Form element
	 * 
	 * @param array $options
	 * @return string
	 */
	public static function form($options = array()) {
		$options['method'] = @$options['method'] ? $options['method'] : 'post';
		$options['action'] = @$options['action'] ? $options['action'] : '';
		$options['accept-charset'] = @$options['accept-charset'] ? $options['accept-charset'] : 'utf-8';
		$options['enctype'] = @$options['enctype'] ? $options['enctype'] : 'multipart/form-data';

		// fragment
		if (@$options['fragment']) {
			$options['action'].= '#' . $options['fragment'];
		}

		// assembling form
		$value = $options['value'];
		unset($options['value']);
		$result = '';
		$result.= '<form';
		foreach($options as $k=>$v) {
			$result.= ' ' . $k . '="' . @$v . '"';
		}
		$result.= '>' . $value . '</form>';
		return $result;
	}

	/**
	 * Textarea element
	 * 
	 * @param array $options
	 * @return string
	 */
	public static function textarea($options = array()) {
		$options['wrap'] = @$options['wrap'] | 'off';
		$value = @$options['value'];
		unset($options['value'], $options['maxlength']);
		if (empty($options['readonly'])) {
			unset($options['readonly']);
		} else {
			$options['readonly'] = 'readonly';
		}
		$result = '';
		$result.= '<textarea';
		foreach($options as $k=>$v) {
			$result.= ' ' . $k . '="' . @$v . '"';
		}
		$result.= '>' . $value. '</textarea>';
		return $result;
	}

	/**
	 * Select element
	 * 
	 * @param array $options
	 * @return string
	 */
	public static function select($options = array()) {
		$multiselect = null;
		if (isset($options['multiselect'])) {
			$multiselect = @$options['multiselect'];
			$multiselect['flag_present'] = true;
		}
		unset($options['multiselect']);
		// multiple
		$no_choose = false;
		if (!empty($options['multiple']) || !empty($multiselect)) {
			$options['name'] = !empty($options['name']) ? $options['name'] . '[]' : '';
			$options['multiple'] = 'multiple';
			$no_choose = true;
		}
		if (!empty($options['no_choose'])) $no_choose = true;
		if (!empty($options['readonly']) || !empty($options['disabled'])) {
			$options['disabled'] = 'disabled'; 
		} else {
			unset($options['disabled'], $options['readonly']);
		}
		
		$optgroups = @$options['optgroups'];
		$options_array = @$options['options'];
		$value = @$options['value'];
		unset($options['options'], $options['optgroups'], $options['value'], $options['no_choose']);
		
		$result = '';
		$result.= '<select';
		foreach($options as $k=>$v) {
			if (!is_array($v)) $result.= ' ' . $k . '="' . @$v . '"';
		}
		$result.= '>';
		if (!$no_choose) $result.= '<option value=""></option>';
		// options first, optgroups after
		if ($options_array) {
			foreach($options_array as $k=>$v) {
				$k = (string) $k;
				//$result.= '<option value="' . $k . '"'.($value==$k ? ' selected="selected" ' : '') . (!empty($v['title']) ? ' title="' . $v['title'] . '" ' : '') . '>' . $v['name'] . '</option>';
				//$text = @$v['loc_id'] ? Core::loc($v['loc_id']) : $v['name'];
				$text = $v['name'];
				// selected
				$selected = '';
				if (is_array($value) && in_array($k, $value)) {
					$selected = ' selected="selected" ';
				} else if (!is_array($value) && ($value.'')===$k) {
					$selected = ' selected="selected" ';
				}
				$temp = '';
				if (empty($v['disabled'])) unset($v['disabled']); else $v['disabled'] = 'disabled';
				if (empty($v['readonly'])) unset($v['readonly']); else $v['disabled'] = 'disabled';
				foreach($v as $k2=>$v2) if (!is_array($v2) && $k2!='name') $temp.= ' ' . $k2 . '="' . @$v2 . '"';
				$result.= '<option value="' . $k . '"'. $selected . $temp . '>' . $text . '</option>';
			}
		}

		if ($optgroups) {
			foreach ($optgroups as $k2=>$v2) {
				$result.= '<optgroup label="' . $v2['name'] . '" id="' . $k2 . '">';
				foreach ($v2['options'] as $k=>$v) {
					$k = (string) $k;
					$text = $v['name'];
					// selected
					$selected = '';
					if (is_array($value) && in_array($k, $value)) {
						$selected = ' selected="selected" ';
					} else if (!is_array($value) && ($value.'')===$k) {
						$selected = ' selected="selected" ';
					}
					$temp = '';
					if (empty($v['disabled'])) unset($v['disabled']); else $v['disabled'] = 'disabled';
					if (empty($v['readonly'])) unset($v['readonly']); else $v['disabled'] = 'disabled';
					foreach($v as $k3=>$v3) if (!is_array($v3) && $k3!='name') $temp.= ' ' . $k3 . '="' . @$v3 . '"';
					$result.= '<option value="' . $k . '"'. $selected . $temp . '>' . $text . '</option>';
				}
				$result.= '</optgroup>';
			}
		}
		$result.= '</select>';

		// multiselect
		if (!empty($multiselect)) {
			$id = @$options['id'];
			if (empty($id)) Throw new Exception('Multiselect must have id!');
			$text = @$multiselect['text'] ? $multiselect['text'] : 'Please choose';
			$list = @$multiselect['list'] ? $multiselect['list'] : 5;
			$filter = @$multiselect['filter'] ? true : false;
			$min_width = @$multiselect['min_width'] ? $multiselect['min_width'] : 180;
			$height = @$multiselect['height'] ? $multiselect['height'] : 175;
			$header = (@$multiselect['header'] || $filter) ? 'true' : 'false';
			$js = "$('#$id').multiselect({ noneSelectedText: '$text', selectedList: $list, minWidth: $min_width, header: $header, height: '$height', checkAllText: 'All', uncheckAllText: 'None' })" . ($filter ? ".multiselectfilter()" : "" ) . ";";
			Layout::onload($js);
			Layout::add_js('/jquery/js/jquery-ui.min.js', -30000);
			Layout::add_js('/jquery/js/jquery.multiselect.js');
			Layout::add_js('/jquery/js/jquery.multiselect.filter.js');
			Layout::add_css('/jquery/css/jquery-ui.css');
			Layout::add_css('/jquery/css/jquery.multiselect.css');
			Layout::add_css('/jquery/css/jquery.multiselect.filter.css');
		}
		return $result;
	}
	
	/**
	 * An alias for multi select
	 * 
	 * @param unknown_type $options
	 * @return string
	 */
	public static function multiselect($options = array()) {
		if (!isset($options['multiselect'])) $options['multiselect'] = array();
		return self::select($options);
	}
	
	/**
	 * Message box
	 * 
	 * @param mixed $msg
	 * @param string $type
	 * @return string
	 */
	public static function message($msg, $type = 'error') {
		if (!in_array($type, array('error', 'notice', 'good', 'important'))) $type = 'other';
		$result = '';
		$result.= '<div class="messages ' . $type . '">';
		if (is_array($msg)) {
			$result.= '<div>' . implode('</div><div>', $msg) . '</div>';
		} else {
			$result.= '<div>' . $msg . '</div>';
		}
		//$result.= '<br class="clearfloat" />';
		$result.= '</div>';
		return $result;
	}

	/**
	 * Fieldset element
	 * 
	 * @param array $options
	 * @return string
	 */
	public static function fieldset($options = array()) {
		$value = $options['value'];
		$legend = $options['legend'];
		unset($options['value'], $options['legend']);
		$result = '<fieldset';
			foreach($options as $k=>$v) $result.= ' ' . $k . '="' . $v . '"';
			$result.= '>';
			$result.= '<legend>' . $legend . '</legend>';
			$result.= $value;
		$result.= '</fieldset>';
		return $result;
	}
	
	/**
	 * Render a frame
	 * 
	 * @param string $body
	 * @param string $type
	 */
	public static function frame($body, $type = 'simple') {
		$result = '';
		switch ($type) {
			case 'wire':
				$result = '<div class="frame wire">' . $body . '</div>';
				break;
			default:
				$result = '<div class="frame simple">' . $body . '</div>';
		}
		return $result;
	}
	
	/**
	 * Mandatory tag
	 * 
	 * @return string
	 */
	public static function mandatory() {
		return '<span class="mandatory" title="Mandatory">*</span>';
	}
	
	/**
	 * Crean an element
	 * 
	 * @param array $options
	 * @return string
	 */
	public static function element($options) {
		$element = @$options['element'] ? $options['element'] : 'input';
		if (in_array($element, array('select', 'multiselect')) && @$options['options_model']) {
			$options_model_class =  $options['options_model'];
			$options_model = new $options_model_class();
			$options['options'] = call_user_func_array(array($options_model, 'options'), @$options['options_paremeters'] ? $options['options_paremeters'] : array());
		}
		if ($element=='input' && !empty($options['maxlength'])) {
			$options['size'] = $options['maxlength'];
		}
		if ($element=='input' && !empty($options['align'])) {
			@$options['style'].= 'text-align: ' . $options['align'] . ';';
			unset($options['align']);
		}
		if ($element=='checkbox') {
			$options['checked'] = empty($options['value']) ? false : true;
			unset($options['value']);
		}
		// unsettings arrays
		unset($options['options_paremeters'], $options['format_paremeters'], $options['sequence']);
		return call_user_func(array('h', $element), $options);
	}
	
	// tooltip widjet
	public static function tooltip($name, $text, $header = '', $class = 'classic', $url = '', $style = '') {
		$img = '';
		if ($class != 'classic') {
			$img = '<img src="/images/icons/tooltip/' . $class . '.png" height="24" width="24" border="0" />';
			$class = 'custom ' . $class;
		}
		if ($header) {
			$img.= '<em>' . $header . '</em>';
		}
		$url = $url ? $url : 'javascript:void(0);';
		$temp = $name . '<div class="' . $class . '" ' . $style . '>' . $img . $text . '</div>';
		return self::a(array('href'=>$url, 'value'=>$temp, 'class'=>'tooltip'));
	}

    // dialog box mostly for popup comments
    public static function dialog($id, $value, $title, $body, $options = array()) {
		// loading files
    	layout::add_js('/jquery/js/jquery.min.js', -32000);
    	layout::add_js('/jquery/js/jquery-ui.min.js', -30000);
		layout::add_css('/jquery/css/jquery-ui.css');
		
		// options
		$options['height'] = @$options['height'] ? $options['height'] : 'auto';
		$options['width'] = @$options['width'] ? '"'. $options['width'] .'"' : '"350"'; // sreen width: ($(window).width()) * 0.9
		$options['style'] = @$options['style'] ? (' style="' . $options['style'] . '" ') : '';
		$options['class'] = @$options['class'] ? (' class="' . $options['class'] . '" ') : '';
		
		$hover_id = 'h_hoverbox_id_' . $id;
        
        if (@$options['clickable']) {
            $result = '<a href="" id="' . $hover_id . '_hover"' . $options['style'] . $options['class'] . '>'. $value .'</a><div id="' . $hover_id . '_dialog" style="display:none;">' . $body . '</div>'; 
        } else if (@$options['hidden']) {
           $result = '<div id="' . $hover_id . '_dialog" style="display:none;">' . $body . '</div>';
        } else {
           $result = '<span id="' . $hover_id . '_hover"' . $options['style'] . '><span>' . $value . '</span><div id="' . $hover_id . '_dialog" style="display:none;">' . $body . '</div></span>'; 
        }
        
		$js = '$("#' . $hover_id . '_dialog").dialog({
		    autoOpen: false,
		    modal: false,
		    width: ' . $options['width'] . ',
		    height: "' . $options['height'] . '",
		    dialogopened: false,
		    title: "' . $title . '",';
			if (isset($options['close_off_hover'])) {
            	$js.= 'dialogClass: "no-close",';
            }
            // centering dialog
            if (isset($options['position'])) {
            	$js.= 'position: "' . $options['position'] . '",';
            }
			if (!empty($options['onopen'])) {
			    $js.= 'open: function (event, ui) {
				if (!$("#' . $hover_id . '_dialog").dialog("option", "dialogopened")) {
				    $("#' . $hover_id . '_dialog").dialog("option", "dialogopened", true);
				    ' . str_replace('[dialog_id]', $hover_id . '_dialog', $options['onopen']) . '
				}
			    },';
			}
                
	        $js.= 'close: function(event, ui) {}});';
               
		if (@$options['clickable']) {
            $js.= '$("#' . $hover_id . '_hover").click(function() { if ($("#' . $hover_id . '_dialog").dialog("isOpen") != true) { $("#' . $hover_id . '_dialog").dialog("open");';
            if (empty($options['position'])) {
            	$js.= '$("#' . $hover_id . '_dialog").dialog("option", "position", { my: "left top", at: "left bottom", of: $("#' . $hover_id . '_hover") });';
            }
			$js.= '} return false; });';
		} else if (@$options['hidden']) {
			// nothing for now
        } else {
            $js.= '$("#' . $hover_id . '_hover").mouseover(function() {
				if ($("#' . $hover_id . '_dialog").dialog("isOpen") != true) {
					$("#' . $hover_id . '_dialog").dialog("open");
					$("#' . $hover_id . '_dialog").dialog("option", "position", { my: "left top", at: "left bottom", of: $("#' . $hover_id . '_hover") });
				}
	    	});';
            
            if (isset($options['close_off_hover'])) {
                $js.= '

                $("#' . $hover_id . '_hover").mouseout(function() {
                    if ($("#' . $hover_id . '_dialog").dialog("isOpen") == true) {
                        $("#' . $hover_id . '_dialog").dialog("close");
                    }
                });';
            }
        }
        
        if (!empty($options['form'])) {
        	$js.= '$("#' . $hover_id . '_dialog").parent().appendTo($("form#' . $options['form'] . '"));';
        }
        
		layout::onload($js);
		return $result;
    }
    
    /**
     * Catcomplete
     * 
     * @param array $options
     * @return string
     */
    public static function catcomplete($options) {
    	$result = h::input($options);
    	
    	// including files
    	layout::add_js('/jquery/js/jquery.catcomplete.js');
    	layout::add_css('/jquery/css/jquery.catcomplete.css');
    	
    	$keyword = '';
    	if (!empty($options['keyword'])) {
    		$keyword = <<<TTT
    			, select: function(event, ui) {
					$.ajax({
						url: "{$options['keyword']}",
						dataType: "json",
						data: {
							q: ui.item.value
						},
						success: function(data) {
							response(data);
						}
					});
				}
TTT;
    	}
    	
    	// building widget
    	$js = <<<TTT
    		$('#{$options['id']}').catcomplete({
    			source: function(request, response) {
					$.ajax({
						url: "{$options['ajax']}",
						dataType: "json",
						data: {
							q: request.term
						},
						success: function(data) {
							response(data);
						}
					});
				},
				minLength: 1
				{$keyword}
			});
TTT;
    	layout::onload($js);
    	return $result;
    }
}