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
		$settings['limit'] = (@$input['limit'] ? @intval($input['limit']) : 30);
		$settings['offset'] = @intval(@$input['offset']); // show starting from this row
		$settings['orderby'] = (isset($input['orderby']) ? $input['orderby'] : $this->list_orderby); // order by column
		$settings['orderdesc'] = (isset($input['orderdesc']) ? $input['orderdesc'] : $this->list_orderdesc); // order direction
		
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
			$result = db::query($sql);
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
			$ms.= '<table cellpadding="0" cellspacing="0" class="editor table">';
			
			// types
			$types = model_presets::get('he_post_type');
			
			// rows
			$row_counter = 1;
			foreach ($result['rows'] as $k=>$v) {
				$ms.= '<tr class="editor row ' . ($row_counter%2 ? 'odd' : 'even') . '" id="editor_row_id_' . $row_counter . '">';
					$ms .= '<td class="editor cell numeration" valign="top">' . $row_counter . '.&nbsp;</td>';
					$ms.= '<td class="editor cell regular" align="left" nowrap>';
						$g = keywords::summary($v['title'], $types[$v['type']]['name'], $v['body'], $input['full_text_search']);
					
						$url_post = $this->url_post($v['type'], $v['post_id']);
						$url_type = $this->url_type($v['type']);
						// title goes first
						$ms.= h::a(array('href'=>$url_post, 'value'=>$g['title']));
						if (isset($v['ts_rank2'])) {
							$ms.= ' [' . $v['ts_rank2'] . ']';
						}
						// entry type
						$ms.= ' ' . h::a(array('href'=>$url_type, 'value'=>$g['type']));
						$ms.= '<br/>';
						$ms.= h::a(array('href'=>$url_post, 'value'=>$url_post, 'style'=>'color: green;'));
						$ms.= '<br/>';
						$ms.= $g['text'];
						$ms.= '<br/>';
						$ms.= '<br/>';
					$ms.= '</td>';
				$ms.= '</tr>';
				$row_counter++;
			}
		
			$ms.= '</table>';
			$ms.= '<br class="clearfloat">';
			return $ms;
		}
	}
	
	public function url_type($type) {
		switch ($type) {
			case 10:
				return '/help?page=faq';
				break;
			default:
				return '';
		}		
	}
	
	public function url_post($type, $post_id) {
		switch ($type) {
			case 10:
				return request::host() . 'help?page=faq#' . $post_id;
				break;
			default:
				return '';
		}
	}
}