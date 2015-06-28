<?php

class io {
	
	/**
	 * File formats
	 *
	 * @var array
	 */
	public static $formats = array(
		'xlsx' => array('name' => 'Excel 2007 Workbook', 'type'=>3, 'file_extension' => 'xlsx', 'excel_code' => 'Excel2007', 'content_type'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
		//'pdf' => array('name' => 'PDF', 'type'=>2, 'file_extension' => 'pdf', 'excel_code' => 'PDF', 'content_type'=>'application/pdf'),
		'csv' => array('name' => 'CSV (Comma Delimited)', 'type'=>3, 'delimiter' => ',', 'enclosure' => '"', 'file_extension' => 'csv','content_type'=>'application/octet-stream'),
		'txt' => array('name' => 'Text (Tab Delimited)', 'type'=>3, 'delimiter' => "\t", 'enclosure' => '"', 'file_extension' => 'txt','content_type'=>'application/octet-stream'),
	);
	
	/**
	 * Export formats
	 * 
	 * @return array
	 */
	public static function export_format_options() {
		$result = array();
		foreach (self::$formats as $k=>$v) if ($v['type']==2 || $v['type']==3) $result[$k] = $v;
		return $result;
	}
	
	/**
	 * Import formats
	 * 
	 * @return array
	 */
	public static function import_format_options() {
		$result = array();
		foreach (self::$formats as $k=>$v) if ($v['type']==1 || $v['type']==3) $result[$k] = $v;
		return $result;
	}
	
	public static function export($sql, $model_class, $options = array()) {
		$records_per_request = !empty($options['records_per_request']) ? $options['records_per_request'] : 10;
		$flag_load_details = @$options['do_not_load_details'] ? false : true;
		// file format and name
		$file_format = @$options['format'];
		if (empty(self::$formats[$file_format]) || !(self::$formats[$file_format]['type']==2 || self::$formats[$file_format]['type']==3)) {
			$file_format = 'csv';
		}
		$file_name = (@$options['name'] ? @$options['name'] : 'file') . '_' . time() . '.' . self::$formats[$file_format]['file_extension'];
		// model
		$model = new $model_class();
		// sql
		if (empty($sql)) {
			$sql = 'SELECT * FROM ' . $model->table . ' WHERE 1=1 ORDER BY ' . $model->orderby . ($model->orderdesc ? ' DESC' : '');
		}
		// step 1: get all the data into an array
		$offset = 0;
		$data = array($model->table=>array());
		do {
			$flag_more = false;
			$sql2 = $sql . ' LIMIT ' . ($records_per_request + 1) . ' OFFSET ' . $offset;
			$rows = db::query($sql2, null, array(), $model->link);
			
			//print_r($rows);
			
			// get columns first run
			if (!isset($columns)) {
				if (!empty($options['columns'])) {
					$columns = $options['columns'];
				} else {
					//foreach ($rows['rows'][0])
					$columns = $rows['structure'];
				}
				$data[$model->table][] = array_keys($columns);
			}
			// processing rows
			if (sizeof($rows['rows'])==($records_per_request + 1)) {
				$flag_more = true;
				// removing last element
				unset($rows['rows'][$records_per_request]);
				$offset+= $records_per_request;
			}
			// process data
			foreach ($rows['rows'] as $k => $v) {
				foreach ($columns as $k2 => $v2) {
					// transforming php arrays to pg arrays
					if (@$result['structure'][$k2]['type'][0] == '_') {
						$rows['rows'][$k][$k2] = db::prepare_array($v[$k2]);
					}
					$rows['rows'][$k][$k2] = !is_array($v[$k2]) ? $v[$k2] : db::prepare_array($v[$k2]);
				}
				$data[$model->table][] = array_values($rows['rows'][$k]);
			}
			
			// processing details
			if ($flag_load_details && !empty($model->details)) {
				foreach ($model->details as $k=>$v) {
					$model2 = new $k();
					// keys
					$keys = array();
					foreach ($v['key'] as $k2=>$v2) $keys[] = $v2 . '::text';
					$keys = implode(' || \'-\' || ', $keys);
					// values
					$values = array();
					foreach ($rows['rows'] as $k3=>$v3) {
						$value = array();
						foreach ($v['key'] as $k2=>$v2) $value[] = $v3[$k2] . '';
						$values[] = implode('-', $value);
					}
					// building sql
					$sql3 = "SELECT * FROM " . $model2->table . " WHERE " . $keys . " IN ('" . implode("','", $values) . "')";
					$rows_details = db::query($sql3, null, array(), $model2->link);
					
					$columns2 = $rows_details['structure'];
					// first time columns
					if (!isset($data[$model2->table])) {
						$data[$model2->table] = array();
						$data[$model2->table][] = array_keys($columns2);
					}
					// process data
					foreach ($rows_details['rows'] as $k0 => $v0) {
						foreach ($columns2 as $k2 => $v2) {
							// transforming php arrays to pg arrays
							if (@$result['structure'][$k2]['type'][0] == '_') {
								$rows_details['rows'][$k0][$k2] = db::prepare_array($v0[$k2]);
							}
							$rows_details['rows'][$k0][$k2] = !is_array($v0[$k2]) ? $v0[$k2] : db::prepare_array($v0[$k2]);
						}
						$data[$model2->table][] = array_values($rows_details['rows'][$k0]);
					}
					
					// details of details
					if ($flag_load_details && !empty($model2->details)) {
						foreach ($model2->details as $k10=>$v10) {
							$model3 = new $k10();
							// keys
							$keys = array();
							foreach ($v10['key'] as $k4=>$v4) $keys[] = $v4 . '::text';
							$keys = implode(' || \'-\' || ', $keys);
							// values
							$values = array();
							foreach ($rows_details['rows'] as $k3=>$v3) {
								$value = array();
								foreach ($v10['key'] as $k2=>$v2) $value[] = $v3[$k2] . '';
								$values[] = implode('-', $value);
							}
							// building sql
							$sql3 = "SELECT * FROM " . $model3->table . " WHERE " . $keys . " IN ('" . implode("','", $values) . "')";
							$rows_details2 = db::query($sql3, null, array(), $model3->link);
								
							$columns2 = $rows_details2['structure'];
							// first time columns
							if (!isset($data[$model3->table])) {
								$data[$model3->table] = array();
								$data[$model3->table][] = array_keys($columns2);
							}
							// process data
							foreach ($rows_details2['rows'] as $k0 => $v0) {
								foreach ($columns2 as $k2 => $v2) {
									// transforming php arrays to pg arrays
									if (@$result['structure'][$k2]['type'][0] == '_') {
										$rows_details2['rows'][$k0][$k2] = db::prepare_array($v0[$k2]);
									}
									$rows_details2['rows'][$k0][$k2] = !is_array($v0[$k2]) ? $v0[$k2] : db::prepare_array($v0[$k2]);
								}
								$data[$model3->table][] = array_values($rows_details2['rows'][$k0]);
							}
						}
					}
				}
			}
		} while ($flag_more);
		
		// step 2: render
		$screen_string = @ob_get_clean();
		unset($screen_string);
		// headers
		header('Content-Type: ' . self::$formats[$file_format]['content_type']);
		header('Content-Disposition: attachment; filename="' . $file_name . '"');
		header('Cache-Control: max-age=0');
		// content
		switch ($file_format) {
			case 'pdf':
			case 'xlsx':
				echo self::array_to_excel($data, self::$formats[$file_format]['excel_code'], null);
				break;
			default:
				// csv or text
				echo self::array_to_csv($data, self::$formats[$file_format]['delimiter'], self::$formats[$file_format]['enclosure']);
		}
		exit;
	}
	
	// This function will convert array into excel or pdf file
	public static function array_to_excel($data, $excel_code, $filename = null) {
		$result = false;
		$excel = new PHPExcel();
		$sheet = $excel->getActiveSheet();
		$sheet_id = 0;
		foreach ($data as $sheet_name=>$sheet_data) {
			// creating new sheet
			if ($sheet_id > 0) {
				$excel->createSheet(NULL);
				$excel->setActiveSheetIndex($sheet_id);
			}
			$sheet_id++;
			// active sheet
			$sheet = $excel->getActiveSheet();
			$excel->getActiveSheet()->setTitle($sheet_name);
			// writing data
			$row = 0;
			foreach ($sheet_data as $row_data) {
				$col = 0;
				foreach ($row_data as $field => $value) {
					$sheet->getCellByColumnAndRow($col, $row + 1)->setValue($value);
					if (strpos($value, "\n") !== false && $excel_code == 'Excel5') {
						$sheet->getStyleByColumnAndRow($col, $row + 1)->getAlignment()->setWrapText(true);
					}
					$col++;
				}
				$row++;
			}
		}
		switch ($excel_code) {
			case 'PDF':
				$writer = PHPExcel_IOFactory::createWriter($excel, $excel_code);
				break;
			default:
				$writer = PHPExcel_IOFactory::createWriter($excel, $excel_code);
				$writer->setOffice2003Compatibility(true);
		}
		$screen_string = @ob_get_clean();
		if (empty($filename)) {
			$writer->save('php://output');
			return;
		} else {
			$writer->save($filename);
			$result = true;
		}
		return $result;
	}
	
	// This function will convert array into csv file
	public static function array_to_csv($data, $delimiter = ',', $enclosure = '"', $as_array = false) {
		$result = array();
		$outstream = fopen("php://temp", 'r+');
		$sheet_counter = 0;
		foreach ($data as $sheet_name=>$sheet_data) {
			if ($sheet_counter > 0) {
				fputcsv($outstream, array('(Begin)', $sheet_name), $delimiter, $enclosure);
			}
			foreach ($sheet_data as $k => $v) {
				fputcsv($outstream, $v, $delimiter, $enclosure);
			}
			$sheet_counter++;
		}
		rewind($outstream);
		while (!feof($outstream)) {
			$result[] = fgets($outstream);
		}
		fclose($outstream);
		// return
		if (!$as_array) {
			return implode('', $result);
		} else {
			return $result;
		}
	}
	
	/**
	 * Read content from csv file into array
	 *
	 * @param string $filename
	 * @param string $delimiter
	 * @param string $enclosure
	 * @param int $max_records
	 * @return array
	 */
	public static function csv_to_array($filename, $delimiter = ',', $enclosure = '"') {
		$temp = false;
		if (($handle = fopen($filename, 'r'))!==false) {
			while (($data = fgetcsv($handle, 0, $delimiter, $enclosure))!==false) {
				$temp[] = $data;
			}
			fclose($handle);
		}
		$data = array();
		$data_index = 'main';
		if (!empty($temp)) {
			foreach ($temp as $k=>$v) {
				if (stripos($v[0], '(Begin)')!==false) {
					$data_index = $v[1];
					continue;
				}
				$data[$data_index][] = $v;
			}
		}
		return $data;
	}
	
	/**
	 * Read content from Excel file
	 *
	 * @param string $filename
	 * @param string $format
	 * @param int $max_records
	 */
	public static function xlsx_to_array($filename, $format) {
		$result = false;
		$objReader = PHPExcel_IOFactory::createReader($format);
		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load($filename);
		$sheets = $objReader->listWorksheetNames($filename);
		$counter = 0;
		$data = array();
		foreach ($sheets as $sheet_index=>$sheet_name) {
			$data[$sheet_name] = $objPHPExcel->setActiveSheetIndex($sheet_index)->toArray(null, false, false, false);
		}
		return $data;
	}
	
	// Importing file into database + logging
	public static function import_from_file_id($file_id, $model_class, $options = array()) {
		$result = array(
			'success' => false,
			'error' => array()
		);
		do {
			$file_name = time() . '_' . rand(10000, 99999) . '_' . rand(10000, 99999) . '_' . trim($_FILES[$file_id]['name']);
			$directory = application::get(array('directory','temp','dir'));
			$file_result = file::upload($file_id, $file_name, $directory, array_keys(self::$formats));
			if (!$file_result['success']) {
				$result['error'] = array_merge($result['error'], $file_result['error']);
				break;
			}
			
			$import_result = self::import($file_result['file_name_full'], $model_class, $options);
			if ($import_result['error']) {
				array_merge3($result['error'], $import_result['error']);
				break;
			}
			$result['success'] = true;
		} while (0);
		return $result;
	}
	
	public static function import($file_name, $model_class, $options = array()) {
		$result = array(
			'success' => false,
			'error' => array()
		);
		do {
			// model
			$model = new $model_class();
			// format
			$format = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
			if (empty(self::$formats[$format]) || !(self::$formats[$format]['type']==1 || self::$formats[$format]['type']==3)) {
				$result['error'][] = 'Not supported upload format!';
				@unlink($file_name);
				break;
			}
			$file_format = self::$formats[$format];
			// reading file
			switch ($format) {
				case 'xlsx':
					$file_data = self::xlsx_to_array($file_name, $file_format['excel_code']);
					break;
				default:
					$file_data = self::csv_to_array($file_name, $file_format['delimiter'], $file_format['enclosure']);
			}
			// cleaning up
			unlink($file_name);
			// checking
			if ($file_data === false || empty($file_data)) {
				$result['error'][] = 'Error reading or empty file!';
				break;
			}
			
			// transforming data
			$objects = array();
			$temp = array_shift($file_data);
			$keys = $temp[0];
			unset($temp[0]);
			foreach ($temp as $k=>$v) {
				// fix array fields
				foreach ($v as $k2=>$v2) {
					$v2 = $v2 . '';
					if (@$v2[0]=='{' && $v2[strlen($v2)-1]=='}') $v[$k2] = db::pg_parse_array($v2);
				}
				$objects[] = array_combine($keys, $v);
			}
			// processing details
			if (!empty($objects) && !empty($file_data)) {
				foreach ($model->details as $k=>$v) {
					$model2 = new $k();
					if (isset($file_data[$model2->table])) {
						// if we have details of details
						$details_of_details = array();
						$keys_of_details = array();
						$options_of_details = array();
						if (isset($model2->details)) {
							foreach ($model2->details as $k10=>$v10) {
								$model3 = new $k10;
								if (isset($file_data[$model3->table])) {
									$options_of_details[$model3->table] = $v10['key'];
									$details_of_details[$model3->table] = $file_data[$model3->table];
									$keys_of_details[$model3->table] = $details_of_details[$model3->table][0];
									unset($file_data[$model3->table], $details_of_details[$model3->table][0]);
								}
							}
							// setting keys properly
							foreach ($details_of_details as $k10=>$v10) {
								foreach ($v10 as $k11=>$v11) {
									$details_of_details[$k10][$k11] = array_combine($keys_of_details[$k10], $v11);
								}
							}
						}
						
						// details itself
						$details = array();
						$temp = $file_data[$model2->table];
						$keys = $temp[0];
						unset($file_data[$model2->table], $temp[0]);
						foreach ($temp as $k2=>$v2) {
							// fix array fields
							foreach ($v2 as $k9=>$v9) {
								$v9 = $v9 . '';
								if (@$v9[0]=='{' && $v9[strlen($v9)-1]=='}') $v2[$k9] = db::pg_parse_array($v9);
							}
							
							// combining keys and values to make an assocoative array
							$value = array_combine($keys, $v2);
							
							// if we have details of details
							if (!empty($details_of_details)) {
								foreach ($details_of_details as $k10=>$v10) {
									// main keys
									$keys10 = array();
									foreach ($options_of_details[$k10] as $k12=>$v12) $keys10[] = $value[$k12];
									$keys10 = implode('-', $keys10);
									// lopping though all items
									foreach ($v10 as $k11=>$v11) {
										$keys11 = array();
										foreach ($options_of_details[$k10] as $k12=>$v12) $keys11[] = $v11[$v12];
										$keys11 = implode('-', $keys11);
										if ($keys10 == $keys11) {
											$value[$k10][] = $v11;
										}
									}
								}
							}
							
							// putting back into loop
							$details[] = $value;
						}
						
						// if we have rows
						if (!empty($details)) {
							foreach ($objects as $k2=>$v2) {
								// keys
								$keys = array();
								foreach ($v['key'] as $k3=>$v3) $keys[] = $v2[$k3];
								$keys = implode('-', $keys);
								foreach ($details as $k3=>$v3) {
									$keys2 = array();
									foreach ($v['key'] as $k4=>$v4) $keys2[] = $v3[$v4];
									$keys2 = implode('-', $keys2);
									if ($keys2==$keys) {
										$objects[$k2][$model2->table][] = $v3;
									}
								}
							}
						}
					}
				}
			}
			
			// saving
			if (!empty($objects)) {
				if (empty($options['all_objects_as_array'])) {
					foreach ($objects as $k=>$v) {
						// replacing values in some cases
						if (!empty($options['replace_values'])) {
							foreach ($options['replace_values'] as $k2=>$v2) $v[$k2] = $v2;
						}
						$save_result = $model->save($v);
						if (!$save_result['success']) {
							array_merge3($result['error'], $save_result['error']);
						}
					}
				} else {
					foreach ($objects as $k=>$v) {
						// replacing values in some cases
						if (!empty($options['replace_values'])) {
							foreach ($options['replace_values'] as $k2=>$v2) $objects[$k2] = $v2;
						}
					}
					$save_result = $model->save($objects);
					if (!$save_result['success']) {
						array_merge3($result['error'], $save_result['error']);
					}
				}
			}
			if (empty($result['error'])) $result['success'] = true;
		} while (0);
		return $result;
	}
}