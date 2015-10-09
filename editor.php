<?php

class editor {

	private $model2 = null;

	public function initialize() {
		// if we have a model
		if (!empty($this->model)) {
			$model_class = $this->model;
			$model = new $model_class();
			$this->model2 = $model;

			if (!empty($model->save_columns)) {
				if (empty($this->list_columns)) $this->list_columns = $model->save_columns;
				if (empty($this->filter_columns)) $this->filter_columns = $model->save_columns;
				if (empty($this->edit_columns)) $this->edit_columns = $model->save_columns;
			}

			if (empty($this->list_table)) $this->list_table = $model->table;
			if (empty($this->link)) $this->link = $model->link;
			if (empty($this->list_pk)) $this->list_pk = $model->pk;
			if (!empty($model->left_join) && empty($this->left_join)) $this->left_join = $model->left_join;
		}

		// checking for missing peices of data
		if (empty($this->model)) Throw new Exception('model?');
		if (empty($this->list_orderby)) Throw new Exception('list_orderby?');
		if (empty($this->list_table)) Throw new Exception('list_table?');
		if (empty($this->list_columns)) Throw new Exception('list_columns?');
		if (empty($this->list_pk)) Throw new Exception('list_pk?');

		// populating other values
		if (empty($this->filter_columns)) $this->filter_columns = $this->list_columns;

		// converting to array
		if (!is_array($this->list_pk)) $this->list_pk = array($this->list_pk);

		// action items
		if (@$this->edit_can_create) {
			$url = application::get(array('mvc', 'controller')) . '/~edit';
			$new = array('value'=>'New', 'href'=>$url, 'icon'=>icon::render('new16.png'));
			layout::add_action('new', $new);
		}

		// css & js
		layout::add_css('/css/editor.css');
		layout::add_js('/js/editor.js');
	}

	/**
	 * Index action
	 */
	public function action_index() {
		$this->initialize();

		$input = request::input();

		// import file
		if (!empty($input['flag_editor_import_submit'])) {
			set_time_limit(0);
			$result_import = io::import_from_file_id('editor_import_file', $this->model);
			if ($result_import['success']) {
				layout::add_message('File uploaded successfully!', 'good');
			}
			if (!empty($result_import['error'])) {
				layout::add_message($result_import['error'], 'error');
			}
		}
		unset($input['flag_editor_import_submit']);

		$settings = array();
		$settings['limit'] = (@$input['limit'] ? @intval($input['limit']) : 30);
		$settings['offset'] = @intval(@$input['offset']); // show starting from this row
		$settings['orderby'] = (isset($input['orderby']) ? $input['orderby'] : $this->list_orderby); // order by column
		$settings['orderdesc'] = (isset($input['orderdesc']) ? $input['orderdesc'] : @$this->list_orderdesc); // order direction

		// building sql for select
		$from = ' FROM ' . $this->list_table . @$this->left_join . ' WHERE 1=1';

		// filtering
		$filter_columns = array();
		$filter_found = false;
		foreach ($this->filter_columns as $k=>$v) {
			if (!@$v['filter']) continue;
			if (@$v['filter_range']) {
				$filter_columns[] = array('field'=>$k, 'operator'=>'>=', 'value'=>@$input[$k . '_1']);
				$filter_columns[] = array('field'=>$k, 'operator'=>'<=', 'value'=>@$input[$k . '_2']);
				if (@$input[$k . '_1'].''!='' || @$input[$k . '_1'].''!='') $filter_found = true;
			} else {
				$filter_columns[] = array('field'=>$k, 'operator'=>'=', 'value'=>@$input[$k]);
				if (@$input[$k].''!='') $filter_found = true;
			}
		}
		if ($filter_found) {
			$structure = db::table_structures($this->list_table);
			foreach ($filter_columns as $k=>$v) {
				// start with array
				if (is_array($v['value']) && !empty($v['value'])) {
					$v['value'] = array_fix($v['value']);
					$flag_is_string = false;
					foreach ($v['value'] as $v0) {
						if (!is_numeric($v0)) {
							$flag_is_string = true;
							break;
						}
					}
					if ($structure[$v['field']]['type'][0]=='_') {
						if ($flag_is_string) {
							$from.= " AND {$v['field']} && ARRAY['" . implode("','", $v['value']) . "']";
						} else {
							$from.= " AND {$v['field']} && ARRAY[" . implode(",", $v['value']) . "]";
						}
					} else {
						if ($flag_is_string) {
							$from.= " AND {$v['field']} IN ('" . implode("','", $v['value']) . "')";
						} else {
							$from.= " AND {$v['field']} IN (" . implode(",", $v['value']) . ")";
						}
					}
				} else if (in_array($structure[$v['field']]['type'], array('int2', 'int4', 'int8'))) {
					if ($v['value'].''!='') $from.= " AND {$v['field']} {$v['operator']} " . format::read_intval($v['value']);
				} else if ($structure[$v['field']]['type'] == 'numeric') {
					if ($v['value'].''!='') $from.= " AND {$v['field']} {$v['operator']} " . format::read_floatval($v['value']);
				} else {
					if (!is_null(@$v['value']) && @$v['value']!='') {
						$from.= " AND {$v['field']} {$v['operator']} '" . db::escape($v['value']) . "'";
					}
				}
			}
		}

		// full text search
		$full_text_search = array();
		$gist_columns = array();
		foreach ($this->filter_columns as $k=>$v) if (@$v['filter_gist']) $gist_columns[] = $k;
		if (!empty($input['full_text_search']) && !empty($gist_columns)) {
			$full_text_search = db::tsquery($gist_columns, $input['full_text_search'], '|');
			$from.= $full_text_search['where'];
		}

		// getting number of records
		if (@$this->list_count_rows) {
			$sql = 'SELECT COUNT(*) as rows_count ' . $from;
			$result = db::query($sql, null, null, $this->model2->link);
			if (@$result['error']) {
				layout::add_message($result['error'], 'error');
			}
			// use this variable to get number of rows, isset for verification
			$settings['count_rows'] = @$result['rows'][0]['rows_count'] ? $result['rows'][0]['rows_count'] : 0;
		} else {
			// EXPERIMENTAL: increase number of rows fetched by 1 to check whether next row exists
			$settings['limit']++;
		}

		// exporting all rows
		if (@$input['flag_editor_export_submit']) {
			$settings['limit'] = 0;
			$settings['offset'] = 0;
		}

		// quering
		$sql = 'SELECT * ' . $from;
		$sql.= ' ORDER BY ' . (@$full_text_search['orderby'] ? (@$full_text_search['orderby'] . ", ") : "") . $settings['orderby'] . ($settings['orderdesc'] ? ' DESC' : '');
		$sql.= $settings['limit'] ? (' LIMIT ' . $settings['limit']) : '';
		$sql.= $settings['offset'] ? (' OFFSET ' . $settings['offset']) : '';

		$result = db::query($sql, null, null, $this->model2->link);

		// exporting data
		if (@$input['flag_editor_export_submit']) {
			io::export($sql, $this->model, array('format'=>@$input['flag_editor_export_format'], 'name'=>@$input['flag_editor_export_name'], 'header'=>@$input['flag_editor_export_header']));
		}

		// processing count
		if (!@$this->list_count_rows) {
			if (isset($result['rows'][$settings['limit']-1])) $settings['flag_next_row_exists'] = true;
			unset($result['rows'][$settings['limit']-1]);
			$settings['limit']--;
		}
		$settings['num_rows'] = count($result['rows']);
		$ms = '';

		// we need to unset imvisible columns
		$list_columns = array();
		foreach ($this->list_columns as $k=>$v) if (empty($v['invisible'])) $list_columns[$k] = $v;

		// number of columns in this section
		$number_of_columns = sizeof($this->list_columns) + (@$this->list_numerate_rows ? 1 : 0) + (@$this->edit_enabled ? 1 : 0);

		// Hidden elements
		$ms.= h::hidden(array('name'=>'orderby', 'id'=>'orderby', 'value'=>$settings['orderby']));
		$ms.= h::hidden(array('name'=>'orderdesc', 'id'=>'orderdesc', 'value'=>$settings['orderdesc']));
		$ms.= h::hidden(array('name'=>'offset', 'id'=>'offset', 'value'=>$settings['offset']));
		$ms.= h::hidden(array('name'=>'limit', 'id'=>'limit', 'value'=>$settings['limit']));

		// export
		if (@$this->export_enabled) {
			$ms.= h::dialog('editor_export', 'hidden', 'Export', $this->export(), array('clickable'=>true, 'style'=>'display: none;', 'position'=>'center', 'form'=>'editor'));
			$export = array('value'=>'Export', 'href'=>'javascript:void(0);', 'onclick'=>'$(\'#h_hoverbox_id_editor_export_hover\').click();', 'icon'=>icon::render('export16.png'));
			layout::add_action('export', $export);
		}

		// import
		if (@$this->import_enabled) {
			$ms.= h::dialog('editor_import', 'hidden', 'Import', $this->import(), array('clickable'=>true, 'style'=>'display: none;', 'position'=>'center', 'form'=>'editor'));
			$import = array('value'=>'Import', 'href'=>'javascript:void(0);', 'onclick'=>'$(\'#h_hoverbox_id_editor_import_hover\').click();', 'icon'=>icon::render('import16.png'));
			layout::add_action('import', $import);
		}

		// filter
		if (@$this->filter_enabled) {
			$ms.= h::dialog('editor_filter', 'hidden', 'Filter', $this->filter(), array('clickable'=>true, 'width'=>'auto', 'style'=>'display: none;', 'position'=>'center', 'form'=>'editor'));
			$filter = array('value'=>'Filter', 'href'=>'javascript:void(0);', 'sort'=>32000, 'onclick'=>'$(\'#h_hoverbox_id_editor_filter_hover\').click();', 'icon'=>icon::render('find16.png'));
			layout::add_action('filter', $filter);
		}

		// if we have no rows
		if (empty($result['rows'])) {
			$ms.= 'No records found!';
		} else {
			// main container
			$ms.= '<table cellpadding="0" cellspacing="0" class="editor table">';

	 			//filter
	 			$ms.= '<tr>';
		 			$ms.= '<td colspan="' . $number_of_columns . '">';
		 			if (!empty($result['rows'])) {
		 				$ms_footer = $this->header($settings, $this->list_columns);
		 				$ms.= $ms_footer;
		 			}
		 			$ms.= '</td>';
	 			$ms.= '</tr>';

				// header if we have rows
				if (!empty($result['rows'])) {
					$ms.= '<tr class="editor columns">';
					if (@$this->list_numerate_rows) {
						$ms .= '<th class="editor columns numeration">&nbsp;</th>';
					}
	 				if (@$this->edit_enabled) {
	 					$ms .= '<th class="editor columns edit">&nbsp;</th>';
	 				}
					foreach ($list_columns as $k=>$v) {
						$ms .= '<th class="editor columns cell" nowrap>' . $v['name'] . '</th>';
					}
					$ms .= '</tr>';
				}

				// preloading models
				foreach ($this->list_columns as $k2=>$v2) {
					if (!empty($v2['options_model'])) {
						$options_model_class =  $v2['options_model'];
						$options_model = new $options_model_class();
						$this->list_columns[$k2]['options'] = call_user_func_array(array($options_model, 'options'), @$v2['options_paremeters'] ? $v2['options_paremeters'] : array());
						$list_columns[$k2]['options'] = $this->list_columns[$k2]['options'];
					}
				}

				// rows
				$row_counter = 1;
				foreach ($result['rows'] as $k=>$v) {
					$ms.= '<tr class="editor row ' . ($row_counter%2 ? 'odd' : 'even') . '" id="editor_row_id_' . $row_counter . '">';

					// generating keys structure
					//extract_keys($this->list_pk, $v);

					if (@$this->list_numerate_rows) {
						$ms .= '<td class="editor cell numeration">' . $row_counter . '</td>';
					}

	 				if (@$this->edit_enabled) {
	 					$save = extract_keys($this->list_pk, $v);
	 					$filter = $input;
	 					unset($filter['save']);
	 					$url = application::get(array('mvc', 'controller')) . '/~edit?' . http_build_query2(array('save'=>$save)) . '&' . http_build_query2($filter);
	 					$ms.= '<td class="editor cell edit" valign="middle" align="center">' . h::a(array('href'=>$url, 'title'=>'Edit', 'value'=>'<span class="ui-icon ui-icon-document"></span>')) . '</td>';
	 				}

	 				// columns
					foreach ($list_columns as $k2=>$v2) {
						$pk_column = in_array($k2, $this->list_pk) ? ' pk' : '';
						$length = @$v2['maxlength'] ? (' style="width:' . $v2['maxlength'] . 'em;" ') : '';
						$ms.= '<td class="editor cell regular ' . $pk_column . '" align="' . (@$v2['align'] ? $v2['align'] : 'left') . '" ' . $length . 'nowrap>';
						 	if (!empty($v2['options_model'])) {
						 		if (is_array($v[$k2])) {
						 			$temp = array();
						 			foreach ($v[$k2] as $k9=>$v9) {
						 				if (isset($v2['options'][$v9]['name'])) $temp[] = $v2['options'][$v9]['name'];
						 			}
						 			$ms.= implode(', ', $temp);
						 		} else {
						 			$ms.= @$v2['options'][$v[$k2]]['name'];
						 		}
						 	} else if (!empty($v2['format'])) {
						 		$format_parameters = array($v[$k2]);
						 		if (!empty($v2['format_parameters'])) array_merge3($format_parameters, $v2['format_parameters']);
						 		$ms.= call_user_func_array(array('format', $v2['format']), $format_parameters);
							} else {
								if (is_array($v[$k2])) {
						 			$ms.= implode(',', $v[$k2]);
								} else {
									$ms.= $v[$k2];
								}
						 	}
						$ms.= '</td>';
					}
					$ms.= '</tr>';
					$row_counter++;
				}

				//footer
				if (!empty($result['rows'])) {
					$ms.= '<tr>';
						$ms.= '<td colspan="' . $number_of_columns . '">';
							$ms.= str_replace('editor footer top', 'editor footer bottom', $ms_footer);
						$ms.= '</td>';
					$ms.= '</tr>';
				}
			$ms.= '</table>';
			$ms.= '<br class="clearfloat">';
		}

		// adding other actions
		if (@$this->other_actions) {
			foreach ($this->other_actions as $k0=>$v0) {
				if (isset($this->other_actions[$k0]['icon'])) $this->other_actions[$k0]['icon'] = icon::render($this->other_actions[$k0]['icon']);
				layout::add_action($k0, $this->other_actions[$k0]);
			}
		}

		echo h::frame(h::form(array('name'=>'editor', 'id'=>'editor', 'value'=>$ms, 'action'=>application::get(array('mvc', 'full')))), 'simple');
	}

	/**
	 * Edit action
	 */
	public function action_edit() {
		$this->initialize();

		// adding action
		$url = application::get(array('mvc', 'controller'));
		$back = array('value'=>'Back', 'href'=>$url, 'icon'=>icon::render('back16.png'));
		layout::add_action('back', $back);

		if (!@$this->edit_enabled) {
			Throw new Exception('Editing not allowed!');
		}

		// populating other columns
		if (empty($this->edit_columns)) $this->edit_columns = $this->list_columns;

		$input = request::input();

		$flag_save = !empty($input['save']['submit_save']) || !empty($input['save']['submit_save_and_close']);
		$flag_error = false;
		$flag_close = !empty($input['save']['submit_save_and_close']);
		$flag_delete = !empty($input['save']['submit_delete']);

		// initializing model
		$model_class = $this->model;
		$model = new $model_class();

		// saving record
		if ($flag_save) {
			$save_result = $model->save($input['save']);
			if (!$save_result['success']) {
				$flag_error = true;
				layout::add_message($save_result['error'], 'error');
			} else if ($flag_close) {
				$filter = $input;
				unset($filter['save']);
				$url = application::get(array('mvc', 'controller')) . '?' . http_build_query2($filter);
				header('Location: ' . $url);
				exit;
			} else {
				// we need to get proper key from database
				$input['save'] = $save_result['data'];
			}
		}

		// deleting record
		$flag_deleted = false;
		if ($flag_delete) {
			$delete_result = $model->remove($input['save']);
			if (!$delete_result['success']) {
				$flag_error = true;
				layout::add_message($delete_result['error'], 'error');
			} else {
				$flag_deleted = true;
			}
		}

		// analizing primary key
		$pk = extract_keys($this->list_pk, $model->process_fields(@$input['save']));
		$flag_not_empty = false;
		foreach ($pk as $k=>$v) if (!empty($v)) $flag_not_empty = true;

		// loading row
		if ($flag_not_empty && !$flag_error && !$flag_delete) {
			$input['save'] = $model->row($pk);
		}

		$ms = '';

		// we need to save input data in hidden fields
		$filter = $input;
		unset($filter['save']);
		foreach ($filter as $k=>$v) {
			if (!is_array($v)) {
				$ms.= h::hidden(array('name'=>$k, 'value'=>$v));
			}
		}

		// screen header
		$ms.= '<b><span class="ui-icon ui-icon-document"></span>Edit</b><br /><br />';

		// main container
		$ms.= '<table cellpadding="0" cellspacing="0" class="editor edit columns">';
			foreach ($this->edit_columns as $k2=>$v2) {
				$ms.= '<tr>';
					$pk_column = in_array($k2, $this->list_pk) ? ' pk' : '';
					$empty = empty($v2['empty']) ? (' ' . h::mandatory()) : '';
					$ms.= '<td class="editor edit label ' . $pk_column . '" nowrap>' . $v2['name'] . $empty . ':</td>';
					$ms.= '<td class="editor edit input ' . $pk_column . '" nowrap>';
						$parameters = $v2;
						$parameters['name'] = 'save[' . $k2 . ']';
						$parameters['id'] = str_replace(array('['), '_', $parameters['name']);
						$parameters['id'] = str_replace(array(']'), '', $parameters['id']);
						// we we need to format the value						
						if (!empty($v2['format'])) {
							$format_parameters = array(@$input['save'][$k2]);
							if (!empty($v2['format_parameters'])) array_merge3($format_parameters, $v2['format_parameters']);
							$parameters['value'] = call_user_func_array(array('format', $v2['format']), $format_parameters);
						} else {
							$parameters['value'] = @$input['save'][$k2];
						}
						$ms.= h::element($parameters);
					$ms.= '</td>';
				$ms.= '</tr>';
			}
		$ms.= '</table>';
		$ms.= '<br />';
		$ms.= '<table cellpadding="0" cellspacing="0" class="editor edit buttons" width="100%">';
			$ms.= '<tr>';
				$ms.= '<td>';
					$ms.= h::submit(array('name'=>'save[submit_save]', 'class'=>'button yellow', 'value'=>'Save'));
					$ms.= ' ';
					$ms.= h::submit(array('name'=>'save[submit_save_and_close]', 'value'=>'Save & Close'));
					$ms.= ' ';
					$filter = $input;
					unset($filter['save']);
					$url = application::get(array('mvc', 'controller')) . '?' . http_build_query2($filter);
					$ms.= h::a(array('value'=>'Close', 'href'=>$url, 'class'=>'button'));
				$ms.= '</td>';
				// delete button
				if (!empty($this->edit_can_delete) && !$flag_deleted) {
					$ms.= '<td align="right">';
						$ms.= h::submit(array('name'=>'save[submit_delete]', 'value'=>'Delete', 'onclick'=>'return confirmbox();'));
					$ms.= '</td>';
				}
			$ms.= '</tr>';
		$ms.= '</table>';

		echo h::frame(h::form(array('name'=>'editor', 'value'=>$ms, 'action'=>application::get(array('mvc', 'full')))), 'simple');
	}

	/**
	 * This function will generate header/footer for editors
	 * 
	 * @param array $options
	 * @param array $localization
	 * @return string
	 */
	private function header($options, $localization) {
		$result = '';
		$result.= '<table cellpadding="2" cellspacing="0" width="100%" class="editor footer top">';
			$result.= '<tr>';
				$result.= '<td><span>Displaying:</span> ';
					$result.= h::select(array('options' => array(20 => array('name' => 20), 30 => array('name' => 30), 50 => array('name' => 50), 100 => array('name' => 100), 200 => array('name' => 200), 500 => array('name' => 500)), 'value' => $options['limit'], 'no_choose' => true, 'onchange' => 'document.getElementById(\'offset\').value = 0; document.getElementById(\'limit\').value = this.value; this.form.submit();'));
				$result.= '</td>';
				// separator
				$result.= '<td class="editor separator">|</td>';

				$result.= '<td>';
					$result.= ' <span>Fetched: ' . $options['num_rows'] . (@$options['count_rows'] ? (' of ' . $options['count_rows']) : '') . '</span>';
				$result.= '</td>';

				// sorting if enabled
				if (true) { //!@$options['flag_disable_sort']
					$sort_array = array();
					foreach ($localization as $k => $v) if (@$v['sort']) $sort_array[$k] = array('name'=>$v['name']);
					if (!empty($sort_array)) {
						// separator
						$result.= '<td class="editor separator">|</td>';

						// sorting
						$result.= '<td nowrap>';
							$result.= '<table>';
								$result.= '<tr>';
									$result.= '<td>Sort:</td> ';
									// preparing for sorting
									$result.= '<td>' . h::select(array('options' => $sort_array, 'value' => $options['orderby'], 'no_choose' => true, 'onchange' => '$(\'#orderby\').val(this.value); this.form.submit();')) . '</td>';
									$result.= '<td>';
									if (empty($options['orderdesc'])) {
										$result.= h::a(array('href'=>'javascript:void(0);', 'class'=>'button button_short', 'title'=>'Order Descending', 'onclick'=>'$(\'#orderdesc\').val(1); document.getElementById(\'orderdesc\').form.submit();', 'value'=>'<span class="ui-icon ui-icon-arrowthick-1-s"></span>'));
									} else {
										$result.= h::a(array('href'=>'javascript:void(0);', 'class'=>'button button_short', 'title'=>'Order Ascending', 'onclick'=>'$(\'#orderdesc\').val(0); document.getElementById(\'orderdesc\').form.submit();', 'value'=>'<span class="ui-icon ui-icon-arrowthick-1-n"></span>'));
									}
									$result.= '</td>';
								$result.= '</tr>';
							$result.= '</table>';
						$result.= '</td>';
					}
				}

				// separator
				$result.= '<td class="editor separator">|</td>';

				$result.= '<td align="right" style="text-align:right;">';
				// building pages
				$result_pages = array();
				$current_page = intval($options['offset'] / $options['limit']);
				if ($current_page >= 2) {
					$result_pages[] = h::button2(array('value'=>'<span class="ui-icon ui-icon-seek-first"></span>', 'class'=>'button button_short', 'onclick'=>('document.getElementById(\'offset\').value = 0;this.form.submit();')));
				}
				if ($current_page >= 1) {
					$result_pages[] = h::button2(array('value'=>'<span class="ui-icon ui-icon-seek-prev"></span>', 'class'=>'button button_short', 'onclick'=>('document.getElementById(\'offset\').value = ' . (($current_page - 1) * $options['limit']) . ';this.form.submit();')));
				}
				// select with number of pages
				if (isset($options['count_rows'])) {
					$pages = ceil($options['count_rows'] / $options['limit']);
					$temp = array();
					for ($i = 0; $i < $pages; $i++) {
						$temp[($i * $options['limit'])] = array('name' => $i + 1);
					}
					$result_pages[] = '<span>Page:</span> ' . h::select(array('options' => $temp, 'value' => $options['offset'], 'no_choose' => true, 'onchange' => 'document.getElementById(\'offset\').value = this.value; this.form.submit();'));
					// checking for next and last pages
					$options['flag_next_row_exists'] = ($pages - $current_page - 2 > 0) ? true : false;
					$options['flag_last_row_exists'] = ($pages - $current_page - 1 > 0) ? true : false;
				} else {
					// showing hidden element
					$result_pages[] = 'Page: ' . ($current_page + 1);
				}
				if (@$options['flag_next_row_exists']) {
					$result_pages[] = h::button2(array('value'=>'<span class="ui-icon ui-icon-seek-next"></span>', 'class'=>'button button_short', 'onclick'=>('$(\'#offset\').val(' . (($current_page + 1) * $options['limit']) . '); this.form.submit();')));
				}
				if (@$options['flag_last_row_exists']) {
					$result_pages[] = h::button2(array('value'=>'<span class="ui-icon ui-icon-seek-end"></span>', 'class'=>'button button_short', 'onclick'=>('$(\'#offset\').val(' . (($pages - 1) * $options['limit']) . '); this.form.submit();')));
				}
				$result.= implode('&nbsp;&nbsp;|&nbsp;&nbsp;', $result_pages);
				$result.= '</td>';
			$result.= '</tr>';
		$result.= '</table>';
		return $result;
	}

	/**
	 * Export bar
	 * 
	 * @return string
	 */
	public function export() {
		$ms = '';
		$ms.= '<div id="editor_export_content">';
			$ms.= '<label><b>File Format:</b></label>';
			$ms.= '<br />';
			$ms.= h::select(array('name' => 'flag_editor_export_format', 'options' => io::export_format_options(), 'no_choose' => true, 'class' => 'select'));
			$ms.= '<br />';
			$ms.= '<br />';
			$ms.= '<label><b>Name (Optional):</b></label>';
			$ms.= '<br />';
			$ms.= h::input(array('name' => 'flag_editor_export_name', 'maxlength' => 20, 'size' => 20, 'class' => 'input'));
			$ms.= '<br />';
			$ms.= '<br />';
			$ms.= h::submit(array('value' => 'Download', 'name' => 'flag_editor_export_submit', 'class' => 'button'));
		$ms.= '</div>';
		return $ms;
	}

	/**
	 * Import bar
	 * 
	 * @return string
	 */
	public function import() {
		$ms = '';
		$ms.= '<div id="editor import">';
			$ms.= '<label><b>File location:</b></label>';
			$ms.= '<br />';
			$ms.= h::file(array('id' => 'editor_import_file', 'name'=>'editor_import_file'));
			$ms.= '<br />';
			$ms.= '<br />';
			$ms.= '<label><b>File types:</b></label>';
			$ms.= '<br />';
			foreach (io::import_format_options() as $k=>$v) {
				$ms.= $v['name'] . '<br/ >';
			}
			$ms.= '<br />';
			$ms.= h::submit(array('value' => 'Upload', 'class' => 'button', 'name' => 'flag_editor_import_submit'));
		$ms.= '</div>';
		return $ms;
	}

	/**
	 * Filter
	 * 
	 * @return string
	 */
	public function filter() {
		$input = request::input();
		$ms = '';
		$ms.= '<div id="editor filter">';
			$ms.= '<table>';
				// regular columns
				foreach ($this->filter_columns as $k=>$v) {
					unset($v['no_choose']);
					if (!@$v['filter']) continue;
					$ms.= '<tr>';
						$ms.= '<td>' . $v['name'] . ':</td>';
						$ms.= '<td>';
							if (@$v['filter_range']) {
								$parameters = $v;
								$parameters['name'] = $parameters['id'] = $k . '_1';
								$parameters['value'] = @$input[$parameters['name']];
								$ms.= h::element($parameters);
								$ms.= ' &mdash; ';
								$parameters = $v;
								$parameters['name'] = $parameters['id'] = $k . '_2';
								$parameters['value'] = @$input[$parameters['name']];
								$ms.= h::element($parameters);
							} else {
								$parameters = $v;
								$parameters['name'] = $parameters['id'] = $k;
								$parameters['value'] = @$input[$k];
								$ms.= h::element($parameters);
							}
						$ms.= '</td>';
					$ms.= '<tr>';
				}

				// gist search
				$gist_columns = array();
				foreach ($this->filter_columns as $k=>$v) if (@$v['filter_gist']) $gist_columns[] = $v['name'];
				if (!empty($gist_columns)) {
					$ms.= '<tr>';
						$ms.= '<td><b>Text Search:</b></td>';
						$ms.= '<td>';
							$ms.= h::input(array('name'=>'full_text_search', 'value'=>@$input['full_text_search'], 'size'=>10));
							$ms.= ' ' . implode(', ', $gist_columns);
						$ms.= '</td>';
					$ms.= '</tr>';
				}
			$ms.= '</table>';
			$ms.= h::submit(array('value' => 'Filter', 'class' => 'button', 'name' => 'editor_filter_submit'));
		$ms.= '</div>';
		return $ms;
	}
}