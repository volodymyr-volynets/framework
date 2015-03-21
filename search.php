<?php

class search {
	
	public function init($options) {
		foreach ($options as $k=>$v) {
			$this->{$k} = $v;
		}
	}
	
	public function render() {
		$input = request::input();
		
		$settings = array();
		$settings['limit'] = (@$input['limit'] ? @intval($input['limit']) : 20);
		$settings['offset'] = @intval(@$input['offset']); // show starting from this row
		$settings['orderby'] = (isset($input['orderby']) ? $input['orderby'] : $this->list_orderby); // order by column
		$settings['orderdesc'] = (isset($input['orderdesc']) ? $input['orderdesc'] : $this->list_orderdesc); // order direction
		$settings['took'] = microtime(true);
		
		// building sql for select
		$from = ' FROM ' . $this->list_table . @$this->left_join . ' WHERE 1=1';
		
		// full text search
		$full_text_search = array();
		$gist_columns = array();
		if (!empty($input['full_text_search']) && !empty($this->full_text_search_column)) {
			$full_text_search = db::tsquery($this->full_text_search_column, $input['full_text_search'], '|', true, array(), $this->link);
			$from.= $full_text_search['where'];
		}
		
		// other where
		if (!empty($this->where)) {
			$from.= $this->where;
		}
		
		// getting number of records
		if (@$this->list_count_rows) {
			$sql = 'SELECT COUNT(*) as rows_count ' . $from;
			$result = db::query($sql, '', array(), $this->link);
			if (@$result['error']) {
				layout::add_message($result['error'], 'error');
			}
			// use this variable to get number of rows, isset for verification
			$settings['count_rows'] = @$result['rows'][0]['rows_count'] ? $result['rows'][0]['rows_count'] : 0;
		} else {
			// EXPERIMENTAL: increase number of rows fetched by 1 to check whether next row exists
			$settings['limit']++;
		}
		
		// quering
		$sql = 'SELECT ' . $this->select . (!empty($full_text_search['rank']) ? (', ' . $full_text_search['rank'] . ' ts_rank2') : '') . $from;
		$sql.= ' ORDER BY ' . (@$full_text_search['orderby'] ? (@$full_text_search['orderby'] . ", ") : "") . $settings['orderby'] . ($settings['orderdesc'] ? ' DESC' : '');
		$sql.= $settings['limit'] ? (' LIMIT ' . $settings['limit']) : '';
		$sql.= $settings['offset'] ? (' OFFSET ' . $settings['offset']) : '';
		
		$result = db::query($sql, '', array(), $this->link);
		
		$settings['took'] = round((microtime(true) - $settings['took']), 2);
		
		// processing count
		if (!@$this->list_count_rows) {
			if (isset($result['rows'][$settings['limit']-1])) $settings['flag_next_row_exists'] = true;
			unset($result['rows'][$settings['limit']-1]);
			$settings['limit']--;
		}
		$settings['num_rows'] = count($result['rows']);
		
		// rendering list
		$ms = '';
		
		// Hidden elements
		$ms.= h::hidden(array('name'=>'orderby', 'id'=>'orderby', 'value'=>$settings['orderby']));
		$ms.= h::hidden(array('name'=>'orderdesc', 'id'=>'orderdesc', 'value'=>$settings['orderdesc']));
		$ms.= h::hidden(array('name'=>'offset', 'id'=>'offset', 'value'=>$settings['offset']));
		$ms.= h::hidden(array('name'=>'limit', 'id'=>'limit', 'value'=>$settings['limit']));
		
		// if we have no rows
		if (empty($result['rows'])) {
			$ms.= 'No records found!';
		} else {
			// main container
			$header = $this->header($settings);
			$ms.= $header;
			$ms.= '<br/>';
			$ms.= '<table cellpadding="0" cellspacing="0" class="editor table" width="100%">';
			
			// types
			$types = model_presets::get('he_post_type');
			
			// rows
			$row_counter = 1;
			foreach ($result['rows'] as $k=>$v) {
				$ms.= '<tr>';
					$ms .= '<td class="editor cell numeration" valign="top">' . $row_counter . '.&nbsp;</td>';
					$ms.= '<td class="editor cell regular" align="left">';
						
						// generating urls
						$url_post = call_user_func_array($this->url_posts, array($v['type'], $v['post_id'], $v['uri']));
						$url_type = call_user_func_array($this->url_type, array($v['type']));

						// title with icon goes first
						$value = $v['title'];
						if ($v['type']==20) {
							$v['icon'] = 'help16.png';
						} else if ($v['type']==10) {
							$v['icon'] = 'faq16.png';
						}
						if (!empty($v['icon'])) {
							$value = icon::render($v['icon']) . ' ' . $value;
						}
						$ms.= h::a(array('href'=>$url_post, 'value'=>$value));
						if (isset($v['ts_rank2'])) {
							$ms.= ' [' . $v['ts_rank2'] . ']';
						}
						// entry type
						$ms.= ' ' . h::a(array('href'=>$url_type, 'value'=>$types[$v['type']]['name'], 'style'=>'color:black;'));
						$ms.= '<br/>';
						$ms.= h::a(array('href'=>$url_post, 'value'=>$url_post, 'style'=>'color: green;'));
						$ms.= '<br/>';
						if (!empty($input['full_text_search'])) {
							$temp = keywords::highlight($v['body'], $input['full_text_search'], array('<b style="color:red;">', '</b>'));
						} else {
							$temp = $v['body'];
						}
						$ms.= $temp;
						$ms.= '<br/>';
					$ms.= '</td>';
				$ms.= '</tr>';
				$row_counter++;
			}
		
			$ms.= '</table>';
			$ms.= $header;
			$ms.= '<br class="clearfloat">';
		}
		return $ms;
	}
	
	/**
	 * Generate header
	 * 
	 * @param array $options
	 * @return string
	 */
	private function header($options) {
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
				
				// separator
				$result.= '<td class="editor separator">|</td>';
				
				// time
				$result.= '<td><span>Query took:</span> ' . $options['took'] . ' seconds</td>';
				
				// separator
				$result.= '<td class="editor separator">|</td>';
				
				// building pages
				$result.= '<td align="right">';
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
}