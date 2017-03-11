<?php

class object_form_base extends object_form_parent {

	/**
	 * Form link
	 *
	 * @var string
	 */
	public $form_link;

	/**
	 * Title
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Form class
	 *
	 * @var string
	 */
	public $form_class;

	/**
	 * Initiator class
	 *
	 * @var string
	 */
	public $initiator_class;

	/**
	 * Form parent
	 *
	 * @var string
	 */
	public $form_parent;

	/**
	 * Options
	 *
	 * @var array
	 */
	public $options = [];

	/**
	 * Data
	 *
	 * @var array
	 */
	public $data = [];

	/**
	 * Fields
	 *
	 * @var array
	 */
	public $fields = [];

	/**
	 * Fields for details
	 *
	 * @var array
	 */
	public $detail_fields = [];

	/**
	 * Values
	 *
	 * @var array
	 */
	public $values = [];

	/**
	 * Original values
	 *
	 * @var array
	 */
	public $original_values = [];

	/**
	 * Snapshot values
	 *
	 * @var array
	 */
	public $snapshot_values = [];

	/**
	 * Escaped values
	 *
	 * @var array
	 */
	public $escaped_values = [];

	/**
	 * Collection, model or array
	 *
	 * @var mixed
	 */
	public $collection;

	/**
	 * Collection object
	 *
	 * @var object
	 */
	public $collection_object;

	/**
	 * Column prefix
	 *
	 * @var string
	 */
	public $column_prefix;

	/**
	 * Error messages
	 *
	 * @var array
	 */
	public $errors = [];

	/**
	 * Wrapper methods
	 *
	 * @var array
	 */
	public $wrapper_methods = [];

	/**
	 * Which elements submit the form
	 *
	 * @var array
	 */
	public $process_submit = [];
	public $process_submit_all = [];
	public $process_submit_other = [];

	/**
	 * Submitted
	 *
	 * @var boolean
	 */
	public $submitted = false;

	/**
	 * Refresh
	 *
	 * @var boolean
	 */
	public $refresh = false;

	/**
	 * Delete
	 *
	 * @var boolean
	 */
	public $delete = false;

	/**
	 * Reset
	 *
	 * @var boolean
	 */
	public $blank = false;

	/**
	 * Actions
	 *
	 * @var array
	 */
	public $actions = [];

	/**
	 * Indicator that values has been loaded
	 *
	 * @var boolean
	 */
	public $values_loaded = false;

	/**
	 * Indicator that the record has been inserted/updated in database
	 *
	 * @var boolean
	 */
	public $values_saved = false;

	/**
	 * Indicators that record has been deleted, inserted or updated
	 *
	 * @var boolean
	 */
	public $values_deleted = false;
	public $values_inserted = false;
	public $values_updated = false;

	/**
	 * Indicator whether transaction has been started
	 *
	 * @var boolean
	 */
	public $transaction = false;

	/**
	 * Indicator that transaction has been rolled back
	 *
	 * @var boolean
	 */
	public $rollback = false;

	/**
	 * Primary key
	 *
	 * @var array
	 */
	public $pk;

	/**
	 * If full pk was provided
	 *
	 * @var boolean
	 */
	public $full_pk = false;

	/**
	 * Set when we insert serial data types
	 *
	 * @var array
	 */
	public $new_serials;

	/**
	 * Current tab
	 *
	 * @var string
	 */
	public $current_tab = [];

	/**
	 * If we are making an ajax call to another form
	 *
	 * @var boolean
	 */
	public $flag_another_ajax_call = false;

	/**
	 * Misc. Settings
	 *
	 * @var array
	 */
	public $misc_settings = [];

	/**
	 * Acl
	 *
	 * @var boolean
	 */
	public $acl = true;

	/**
	 * Master object, used for validations
	 *
	 * @var object
	 */
	public $master_object;

	/**
	 * Master options
	 *
	 * @var array
	 */
	public $master_options = [];

	/**
	 * Report object
	 *
	 * @var object
	 */
	public $report_object;

	/**
	 * Tab index
	 *
	 * @var int
	 */
	public $tabindex = 1;

	/**
	 * Query
	 *
	 * Used in lists
	 *
	 * @var \object_query_builder
	 */
	public $query;

	/**
	 * Constructor
	 *
	 * @param string $form_link
	 * @param array $options
	 */
	public function __construct($form_link, $options = []) {
		$this->form_link = $form_link . '';
		$this->options = $options;
		// overrides from ini files
		$overrides = application::get('flag.numbers.frontend.html.form');
		if (!empty($overrides)) {
			$this->options = array_merge_hard($this->options, $overrides);
		}
		$this->error_reset_all();
	}

	/**
	 * Trigger method
	 *
	 * @param string $method
	 */
	public function trigger_method($method) {
		$result = null;
		// handling actual method
		if (!empty($this->wrapper_methods[$method])) {
			foreach ($this->wrapper_methods[$method] as $k => $v) {
				$result = call_user_func_array($v, [& $this]);
			}
		}
		return $result;
	}

	/**
	 * Get original values
	 *
	 * @param array $input
	 */
	private function get_original_values($input, $for_update) {
		// process primary key
		$this->full_pk = false;
		$this->load_pk($input);
		// load values if we have full pk
		if ($this->full_pk) {
			$temp = $this->load_values($for_update);
			if ($temp !== false) {
				$this->original_values = $temp;
				$this->values_loaded = true;
			}
		}
	}

	/**
	 * Sort fields for processing
	 *
	 * @param array $fields
	 * @return int
	 */
	public function sort_fields_for_processing($fields, $options = []) {
		if (!empty($this->collection_object)) {
			$collection = array_key_get($this->collection_object->data, $options['details_collection_key'] ?? null);
			foreach ($fields as $k => $v) {
				// skip certain values
				if ($k == $this::separator_horisontal || $k == $this::separator_vertical || !empty($v['options']['process_submit'])) {
					unset($fields[$k]);
					continue;
				}
				// sort
				if (in_array($k, $collection['pk'] ?? [])) {
					$fields[$k]['order_for_defaults'] = -32000;
				} else if (!empty($v['options']['default']) && strpos($v['options']['default'], 'dependent::') !== false) { // processed last
					$fields[$k]['order_for_defaults'] = 32000 + intval(str_replace(['dependent::', 'static::'], '', $v['options']['default']));
				} else if (!empty($v['options']['default']) && (strpos($v['options']['default'], 'parent::') !== false || strpos($v['options']['default'], 'static::') !== false)) {
					$column = str_replace(['parent::', 'static::'], '', $v['options']['default']);
					$fields[$k]['order_for_defaults'] = ($fields[$column]['order_for_defaults'] ?? 0) + 100;
				} else if (!isset($fields[$k]['order_for_defaults'])) {
					$fields[$k]['order_for_defaults'] = 0;
				}
			}
			array_key_sort($fields, ['order_for_defaults' => SORT_ASC]);
		}
		return $fields;
	}

	/**
	 * Validate required one field
	 *
	 * @param mixed $value
	 * @param string $error_name
	 * @param array $options
	 */
	public function validate_required_one_field(& $value, $error_name, $options) {
		// if we have type errors we skip required validation
		if ($this->has_errors($error_name)) return;
		// check if its required field
		if (isset($options['options']['required']) && ($options['options']['required'] === true || ($options['options']['required'] . '') === '1')) {
			if ($options['options']['php_type'] == 'integer' || $options['options']['php_type'] == 'float') {
				if (empty($value)) {
					$this->error('danger', object_content_messages::required_field, $error_name);
				}
			} else if ($options['options']['php_type'] == 'bcnumeric') { // accounting numbers
				if (math::compare($value, '0', $options['options']['scale']) == 0) {
					$this->error('danger', object_content_messages::required_field, $error_name);
				}
			} else if (!empty($options['options']['multiple_column'])) {
				if (empty($value)) {
					$this->error('danger', object_content_messages::required_field, $error_name);
				}
			} else {
				if ($value . '' == '') {
					$this->error('danger', object_content_messages::required_field, $error_name);
				}
			}
		}
		// validator
		if (!empty($options['options']['validator_method']) && !empty($value) && empty($options['options']['multiple_column'])) {
			$neighbouring_values_key = $options['options']['values_key'];
			array_pop($neighbouring_values_key);
			$temp = object_validator_base::method(
				$options['options']['validator_method'],
				$value,
				$options['options']['validator_params'] ?? [],
				$options['options'],
				array_key_get($this->values, $neighbouring_values_key)
			);
			if (!$temp['success']) {
				foreach ($temp['error'] as $v10) {
					$this->error('danger', $v10, $error_name);
				}
			} else if (!empty($temp['data'])) {
				$value = $temp['data'];
			}
		}
	}

	/**
	 * Convert parent keys to error name
	 *
	 * @param mixed $parent_keys
	 * @return string
	 */
	public function parent_keys_to_error_name($parent_keys) {
		$result = [];
		if (!is_array($parent_keys)) {
			$parent_keys = [$parent_keys];
		}
		foreach ($parent_keys as $v) {
			if (empty($result)) {
				$result[] = $v;
			} else {
				$result[] = '[' . $v . ']';
			}
		}
		return implode('', $result);
	}

	/**
	 * Helper to handle detail key generation
	 *
	 * @param array $holder
	 * @param string $type
	 * @param array $values
	 * @param array $parent_keys
	 * @param array $options
	 */
	public function generate_details_primary_key(& $holder, $type = 'reset', $values = null, $parent_keys = null, $options = []) {
		// generate holder
		if ($type == 'reset') {
			$collection = [];
			if (!empty($this->collection_object)) {
				$collection = array_key_get($this->collection_object->data, $options['options']['details_collection_key']);
			}
			$holder = [
				'collection' => $collection,
				'parent_pks' => [],
				'new_pk_counter' => 1,
				'new_pk_locks' => [],
				'error_name' => null,
				'pk' => null
			];
			// populate parent pks
			if (!empty($holder['collection'])) {
				foreach ($holder['collection']['map'] as $k12 => $v12) {
					if (isset($values[$k12])) {
						$holder['parent_pks'][$v12] = $values[$k12];
					}
				}
			}
		}
		// generate new pk
		if ($type == 'pk') {
			// if we have no collection we accept values as they are
			$new_pk = [];
			if (!empty($holder['collection'])) {
				foreach ($holder['collection']['pk'] as $v) {
					if (isset($holder['parent_pks'][$v])) {
						$new_pk[] = $holder['parent_pks'][$v];
					} else if (!empty($values[$v])) {
						$new_pk[] = $values[$v];
					} else {
						$new_pk[] = '__new_key_' . $holder['new_pk_counter'];
						$holder['new_pk_counter']++;
					}
				}
			} else {
				$new_pk[] = current($values);
			}
			$holder['pk'] = implode('::', $new_pk);
			if (!empty($holder['new_pk_locks'][$holder['pk']])) {
				// error only if we have pk
				if (!empty($holder['pk'])) {
					$holder['pk'] = '__duplicate_key_' . $holder['new_pk_counter'];
					$holder['new_pk_counter']++;
					$error_pk = !empty($options['options']['details_11']) ? ($parent_keys ?? []) : array_merge($parent_keys ?? [], [$holder['pk']]);
					$holder['error_name'] = $this->parent_keys_to_error_name($error_pk);
					foreach ($options['options']['details_pk'] as $v) {
						$this->error('danger', object_content_messages::duplicate_value, "{$holder['error_name']}[{$v}]");
					}
				}
			} else {
				$error_pk = !empty($options['options']['details_11']) ? ($parent_keys ?? []) : array_merge($parent_keys ?? [], [$holder['pk']]);
				$holder['error_name'] = $this->parent_keys_to_error_name($error_pk);
				$holder['new_pk_locks'][$holder['pk']] = true;
			}
		}
	}

	/**
	 * Generate values for multiple columns
	 *
	 * @param mixed $value
	 * @param string $error_name
	 * @param array $values
	 * @param array $parent_keys
	 * @param array $options
	 * @return array
	 */
	public function generate_multiple_columns($value, $error_name, $values, $parent_keys, $options = []) {
		if (!empty($value)) {
			if (!is_array($value)) {
				$value = [$value];
			}
			$result = [];
			$fields_key_holder = [];
			$this->generate_details_primary_key($fields_key_holder, 'reset', $values, $parent_keys, $options);
			foreach ($value as $k2 => $v2) {
				$temp = $this->validate_data_types_single_value($options['options']['multiple_column'], $options, $v2, $error_name);
				if (empty($temp['flag_error'])) {
					$temp_value_new = [
						$options['options']['multiple_column'] => $temp[$options['options']['multiple_column']]
					];
				} else {
					$temp_value_new = [
						$options['options']['multiple_column'] => $v2
					];
				}
				// process pk
				$this->generate_details_primary_key($fields_key_holder, 'pk', $temp_value_new, $parent_keys, $options);
				$k2 = $fields_key_holder['pk'];
				$result[$k2] = array_merge_hard($fields_key_holder['parent_pks'], $temp_value_new);
			}
			return $result;
		} else {
			return [];
		}
	}

	/**
	 * Get all values
	 *
	 * @param array $input
	 * @param array $options
	 *		validate_required
	 * @return array
	 */
	private function get_all_values($input, $options = []) {
		// reset values
		$this->misc_settings['options_model'] = [];
		$this->values = [];
		// sort fields
		$fields = $this->sort_fields_for_processing($this->fields);
		// if we delete we only allow pk and optimistic lock
		$allowed = [];
		if (!empty($options['validate_for_delete'])) {
			$allowed = $this->collection_object->data['pk'];
			if ($this->collection_object->primary_model->optimistic_lock) {
				$allowed[] = $this->collection_object->primary_model->optimistic_lock_column;
			}
		}
		// see if we have a change in entry fields
		$changed_field = [
			'parent' => null,
			'detail' => null,
			'subdetail' => null
		];
		if (!empty($this->misc_settings['__form_onchange_field_values_key']) && count($this->misc_settings['__form_onchange_field_values_key']) == 1) {
			if (isset($fields[$this->misc_settings['__form_onchange_field_values_key'][0]])) {
				$changed_field['parent'] = $this->misc_settings['__form_onchange_field_values_key'][0];
			}
		}
		// process fields
		foreach ($fields as $k => $v) {
			// skip certain values
			if (!empty($options['only_columns']) && !in_array($k, $options['only_columns'])) continue;
			if (!empty($allowed) && !in_array($k, $allowed)) continue;
			// default data type
			if (empty($v['options']['type'])) {
				$v['options']['type'] = 'varchar';
			}
			// get value
			$value = array_key_get($input, $v['options']['values_key']);
			$error_name = $v['options']['error_name'];
			// multiple column
			if (!empty($v['options']['multiple_column'])) {
				// todo - validate
				$value = $this->generate_multiple_columns($value, $error_name, $this->values, null, $v);
			} else {
				$temp = $this->validate_data_types_single_value($k, $v, $value, $error_name);
				if (empty($temp['flag_error'])) {
					if (empty($temp[$k]) && !empty($temp[$k . '_is_serial'])) {
						// we do not create empty serial keys
						continue;
					} else {
						$value = $temp[$k];
					}
				}
			}
			// persistent
			if ($this->values_loaded && !empty($this->misc_settings['persistent']['fields'][$k])) {
				if (is_null($value)) {
					$value = $this->original_values[$k];
				} else if ($this->misc_settings['persistent']['fields'][$k] === 'if_set' && empty($this->original_values[$k])) {
					// we allow value change
				} else if ($value !== $this->original_values[$k]) {
					$this->error('danger', 'You are trying to change persistent field!', $error_name);
				}
			}
			$v['options']['error_name_no_field'] = $error_name;
			// default
			if (array_key_exists('default', $v['options'])) {
				if ($this->can_process_default_value($value, $v)) {
					$value = $this->process_default_value($k, $v['options']['default'], $value, $this->values, false, $changed_field, $v);
				}
			}
			// put into values
			array_key_set($this->values, $v['options']['values_key'], $value);
			// options_model
			if (!empty($v['options']['options_model']) && empty($v['options']['options_manual_validation'])) {
				// options depends & params
				$v['options']['options_depends'] = $v['options']['options_depends'] ?? [];
				$v['options']['options_params'] = $v['options']['options_params'] ?? [];
				$this->process_params_and_depends($v['options']['options_depends'], $this->values, [], true);
				$this->process_params_and_depends($v['options']['options_params'], $this->values, [], false);
				$v['options']['options_params'] = array_merge_hard($v['options']['options_params'], $v['options']['options_depends']);
				// todo - validate options_model
				// CHECK CURRENT/EXISTING values
				$this->misc_settings['options_model'][$k] = [
					'options_model' => $v['options']['options_model'],
					'options_params' => $v['options']['options_params'],
					'key' => $v['options']['values_key']
				];
			}
			// options
			// todo: add options validation to details
			if (isset($value) && !empty($v['options']['options']) && empty($v['options']['options_manual_validation'])) {
				if (empty($v['options']['options'][$value])) {
					$this->error('danger', object_content_messages::invalid_value, $error_name);
				}
			}
		}
		// check optimistic lock
		if ($this->values_loaded && $this->collection_object->primary_model->optimistic_lock && $this->initiator_class != 'numbers_frontend_html_form_wrapper_report') {
			if (($this->values[$this->collection_object->primary_model->optimistic_lock_column] ?? '') !== $this->original_values[$this->collection_object->primary_model->optimistic_lock_column]) {
				$this->error('danger', object_content_messages::optimistic_lock);
			}
		}
		// process details & subdetails
		if (empty($options['validate_for_delete']) && !empty($this->detail_fields)) {
			foreach ($this->detail_fields as $k => $v) {
				$this->values[$k] = []; // a must
				$details = $input[$k] ?? [];
				// 1 to 1
				if (!empty($v['options']['details_11'])) {
					$details = [$details];
				}
				// sort fields
				$fields = $this->sort_fields_for_processing($v['elements'], $v['options']);
				// if we have custom data processor
				if (!empty($v['options']['details_process_widget_data'])) {
					$widget_model = factory::model($k, true);
					$v['validate_required'] = $options['validate_required'] ?? false;
					$this->values[$k] = $widget_model->process_widget_data($this, [$k], $details, $this->values, $fields, $v);
					continue;
				}
				// start processing of keys
				$detail_key_holder = [];
				$this->generate_details_primary_key($detail_key_holder, 'reset', $this->values, [$k], $v);
				// autoincrement
				$autoincrement_details = [];
				if (!empty($v['options']['details_autoincrement']) && empty($v['options']['details_11'])) {
					// set it to 0
					foreach ($v['options']['details_autoincrement'] as $v72) {
						$autoincrement_details[$v72] = 0;
					}
					// find maximums in original values
					if (!empty($this->original_values[$k])) {
						foreach ($this->original_values[$k] as $k71 => $v71) {
							foreach ($v['options']['details_autoincrement'] as $v72) {
								if ($v71[$v72] > $autoincrement_details[$v72]) {
									$autoincrement_details[$v72] = $v71[$v72];
								}
							}
						}
					}
					// find maximum in new values
					if (!empty($details)) {
						foreach ($details as $k71 => $v71) {
							foreach ($v['options']['details_autoincrement'] as $v72) {
								if (!empty($v71[$v72]) && intval($v71[$v72]) > $autoincrement_details[$v72]) {
									$autoincrement_details[$v72] = $v71[$v72];
								}
							}
						}
					}
				}
				// process details one by one
				foreach ($details as $k2 => $v2) {
					// see if we have a change in a detail
					$changed_field_details = $changed_field;
					if (!empty($this->misc_settings['__form_onchange_field_values_key'])) {
						$temp_count = count($this->misc_settings['__form_onchange_field_values_key']);
						if ($this->misc_settings['__form_onchange_field_values_key'][0] == $k && ($temp_count == 2 || $temp_count == 3)) {
							if (!empty($v['options']['details_11'])) {
								$changed_field_details['detail'] = $this->misc_settings['__form_onchange_field_values_key'][1];
							} else if ($this->misc_settings['__form_onchange_field_values_key'][1] . '' == $k2 . '') {
								$changed_field_details['detail'] = $this->misc_settings['__form_onchange_field_values_key'][2];
							}
						}
					}
					// change detected
					$flag_change_detected = false;
					// put pk into detail
					$detail = $detail_key_holder['parent_pks'];
					// process pk
					$this->generate_details_primary_key($detail_key_holder, 'pk', $v2, [$k], $v);
					$error_name = $detail_key_holder['error_name'];
					$k2 = $detail_key_holder['pk'];
					// process fields
					foreach ($fields as $k3 => $v3) {
						// default data type
						if (empty($v3['options']['type'])) {
							$v3['options']['type'] = 'varchar';
						}
						// get value, grab from neighbouring values first
						$value = $detail[$k3] ?? $v2[$k3] ?? null;
						// validate data type
						if (!empty($v3['options']['multiple_column'])) {
							$value = $this->generate_multiple_columns($value, $error_name, $detail, [$k], $v3);
						} else {
							$temp = $this->validate_data_types_single_value($k3, $v3, $value, "{$error_name}[{$k3}]");
							if (empty($temp['flag_error'])) {
								if (empty($temp[$k3]) && !empty($temp[$k3 . '_is_serial'])) {
									// we do not create empty serial keys
									continue;
								} else {
									$value = $temp[$k3];
								}
							}
						}
						// persistent
						if (!empty($v['options']['details_11'])) {
							$detail_access_key = [$k];
						} else {
							$detail_access_key = [$k, $k2];
						}
						$original_values = array_key_get($this->original_values, array_merge($detail_access_key, [$k3]));
						if ($this->values_loaded && !empty($this->misc_settings['persistent']['details'][$k][$k3]) && isset($original_values)) {
							// todo: handle if_set
							if (is_null($value)) {
								$value = $original_values;
							} else if ($value !== $original_values) {
								$this->error('danger', 'You are trying to change persistent field!', "{$error_name}[{$k3}]");
							}
						}
						$v3['options']['error_name_no_field'] = $error_name;
						// default
						$default = null;
						if (array_key_exists('default', $v3['options'])) {
							$default = $this->process_default_value($k3, $v3['options']['default'], $value, $detail, false, $changed_field_details, $v3);
							if ($this->can_process_default_value($value, $v3)) {
								$value = $default;
							}
						}
						// see if we changed the value
						if (!is_null($value) && $value !== $default) {
							$flag_change_detected = true;
						}
						$detail[$k3] = $value;
					}
					// process subdetails, first to detect change
					if (!empty($v['subdetails'])) {
						foreach ($v['subdetails'] as $k0 => $v0) {
							// make empty array
							$detail[$k0] = [];
							// sort fields
							$subdetail_fields = $this->sort_fields_for_processing($v0['elements']);
							// if we have custom data processor
							if (!empty($v0['options']['details_process_widget_data'])) {
								$widget_model = factory::model($k0, true);
								$v0['validate_required'] = $options['validate_required'] ?? false;
								$detail[$k0] = $widget_model->process_widget_data($this, [$k, $k2, $k0], $v2[$k0] ?? [], $detail, $subdetail_fields, $v0);
								// change detected
								if (!empty($detail[$k0])) {
									$flag_change_detected = true;
								}
								continue;
							}
							// start processing of keys
							$subdetail_key_holder = [];
							$this->generate_details_primary_key($subdetail_key_holder, 'reset', $detail, [$k, $k2, $k0], $v0);
							// go through data
							$subdetail_data = $v2[$k0] ?? [];
							if (!empty($subdetail_data)) {
								foreach ($subdetail_data as $k5 => $v5) {
									$flag_subdetail_change_detected = false;
									// put pk into detail
									$subdetail = $subdetail_key_holder['parent_pks'];
									// process pk
									$this->generate_details_primary_key($subdetail_key_holder, 'pk', $v5, [$k, $k2, $k0], $v0);
									$subdetail_error_name = $subdetail_key_holder['error_name'];
									$k5 = $subdetail_key_holder['pk'];
									// process fields
									foreach ($subdetail_fields as $k6 => $v6) {
										// default data type
										if (empty($v6['options']['type'])) {
											$v6['options']['type'] = 'varchar';
										}
										// get value
										$value = $v5[$k6] ?? null;
										// validate data type
										$temp = $this->validate_data_types_single_value($k6, $v6, $value, "{$subdetail_error_name}[{$k6}]");
										if (empty($temp['flag_error'])) {
											if (empty($temp[$k6]) && !empty($temp[$k6 . '_is_serial'])) {
												// we do not create empty serial keys
												continue;
											} else {
												$value = $temp[$k6];
											}
										}
										// persistent
										if (!empty($v0['options']['details_11'])) {
											$subdetail_access_key = array_merge($detail_access_key, [$k0]);
										} else {
											$subdetail_access_key = array_merge($detail_access_key, [$k0, $k5]);
										}
										$original_values = array_key_get($this->original_values, array_merge($subdetail_access_key, [$k6]));
										if ($this->values_loaded && !empty($this->misc_settings['persistent']['subdetails'][$k][$k0][$k6]) && isset($original_values)) {
											// todo: handle if_set
											if (is_null($value)) {
												$value = $original_values;
											} else if ($value !== $original_values) {
												$this->error('danger', 'You are trying to change persistent field!', "{$subdetail_error_name}[{$k3}]");
											}
										}
										// default
										$default = null;
										if (array_key_exists('default', $v6['options'])) {
											$default = $this->process_default_value($k6, $v6['options']['default'], $value, $subdetail, false);
											if (strpos($v6['options']['default'], 'static::') !== false || is_null($value)) {
												$value = $default;
											}
										}
										// see if we changed the value
										if (!is_null($value) && $value !== $default) {
											$flag_subdetail_change_detected = true;
										}
										$subdetail[$k6] = $value;
									}
									// if we have a change
									if ($flag_subdetail_change_detected) {
										$flag_change_detected = true;
										$detail[$k0][$k5] = $subdetail;
									}
								}
							}
						}
					}
					// if we have changes we puth them into values
					if ($flag_change_detected) {
						// 1 to 1
						if (!empty($v['options']['details_11'])) {
							$this->values[$k] = $detail;
						} else { // 1 to M
							// autoincrement
							if (!empty($autoincrement_details)) {
								foreach ($autoincrement_details as $k71 => $v71) {
									if (empty($detail[$k71])) {
										$detail[$k71] = $v71 + 1;
										$autoincrement_details[$k71]++;
									}
								}
							}
							$this->values[$k][$k2] = $detail;
						}
					}
				}
			}
		}
	}

	/**
	 * Validate required fields
	 *
	 * @param array $options
	 */
	private function validate_required_fields($options = []) {
		// sort fields
		$fields = $this->sort_fields_for_processing($this->fields);
		// process fields
		foreach ($fields as $k => $v) {
			if (!empty($options['only_columns']) && !in_array($k, $options['only_columns'])) continue;
			// validate required
			$this->validate_required_one_field($this->values[$k], $v['options']['error_name'], $v);
		}
		// process details
		if (!empty($this->detail_fields)) {
			foreach ($this->detail_fields as $k => $v) {
				$details = $this->values[$k] ?? [];
				// 1 to 1
				if (!empty($v['options']['details_11'])) {
					$details = [$details];
				}
				// sort fields
				$fields = $this->sort_fields_for_processing($v['elements'], $v['options']);
				// process details one by one
				foreach ($details as $k2 => $v2) {
					foreach ($fields as $k3 => $v3) {
						// 1 to 1
						if (!empty($v['options']['details_11'])) {
							$error_name = "{$k}";
							$v3['options']['values_key'] = [$k, $k3];
							$this->validate_required_one_field($v2[$k3], "{$k}[{$k3}]", $v3);
						} else { // 1 to M
							$v3['options']['values_key'] = [$k, $k2, $k3];
							$this->validate_required_one_field($v2[$k3], "{$k}[{$k2}][{$k3}]", $v3);
						}
					}
					// process subdetails
					// todo     
				}
				// see if detail is required, we display
				if (!empty($v['options']['required']) && empty($this->values[$k])) {
					// add error to pk
					$counter = 1;
					foreach ($v['options']['details_pk'] as $v8) {
						if (empty($v['elements'][$v8]['options']['row_link']) || $v['elements'][$v8]['options']['row_link'] == $this::hidden) continue;
						$this->error('danger', object_content_messages::required_field, "{$k}[1][{$v8}]");
						$counter++;
					}
					// sometimes pk can be hidden, so we add error to two more
					if ($counter == 1) {
						array_key_sort($v['elements'], ['row_order' => SORT_ASC, 'order' => SORT_ASC]);
						foreach ($v['elements'] as $k8 => $v8) {
							if (($v8['options']['required'] ?? '') . '' == '1' && !in_array($k8, $v['options']['details_pk']) && $counter == 1) {
								$this->error('danger', object_content_messages::required_field, "{$k}[1][{$k8}]");
								$counter++;
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Process
	 */
	public function process() {
		// reset
		$this->submitted = false;
		$this->refresh = false;
		$this->delete = false;
		$this->blank = false;
		$this->values_loaded = false;
		$this->values_saved = false;
		$this->values_deleted = $this->values_inserted = $this->values_updated = false;
		$this->transaction = $this->rollback = false;
		// preload collection, must be first
		// fix here
		if ($this->preload_collection_object() && $this->initiator_class != 'numbers_frontend_html_form_wrapper_report') {
			// if we have relation
			if (!empty($this->collection_object->primary_model->relation['field']) && !in_array($this->collection_object->primary_model->relation['field'], $this->collection_object->primary_model->pk)) {
				$this->element($this::hidden, $this::hidden, $this->collection_object->primary_model->relation['field'], ['label_name' => 'Relation #', 'domain' => 'relation_id_sequence', 'persistent' => true]);
			}
			// optimistic lock
			if (!empty($this->collection_object->primary_model->optimistic_lock)) {
				$this->element($this::hidden, $this::hidden, $this->collection_object->primary_model->optimistic_lock_column, ['label_name' => 'Optimistic Lock', 'type' => 'text', 'null' => true, 'default' => null, 'method'=> 'hidden']);
			}
		}
		// hidden buttons to handle form though javascript
		$this->element($this::hidden, $this::hidden, $this::button_submit_refresh, $this::button_submit_refresh_data);
		if (!isset($this->process_submit_all[$this::button_submit_blank])) {
			$this->element($this::hidden, $this::hidden, $this::button_submit_blank, $this::button_submit_blank_data);
		}
		// extra elements for list
		if ($this->initiator_class == 'list') {
			$this->element($this::hidden, $this::hidden, '__limit', ['label_name' => 'Limit', 'type' => 'integer', 'default' => $this->form_parent->list_options['default_limit'] ?? 30, 'method'=> 'hidden']);
			$this->element($this::hidden, $this::hidden, '__offset', ['label_name' => 'Offset', 'type' => 'integer', 'default' => 0, 'method'=> 'hidden']);
			$this->element($this::hidden, $this::hidden, '__preview', ['label_name' => 'Preview', 'type' => 'integer', 'default' => 0, 'method'=> 'hidden']);
		}
		// custome renderers for reports
		if ($this->initiator_class == 'numbers_frontend_html_form_wrapper_report') {
			// format
			$this->element('default', 'format', 'format', [
				'label_name' => 'Format',
				'order' => -32000,
				'row_order' => PHP_INT_MAX - 3000,
				'default' => 'html',
				'required' => true,
				'percent' => 25,
				'method' => 'select',
				'no_choose' => true,
				'options_model' => 'numbers_frontend_html_form_model_formats',
				'options_options' => ['i18n' => 'skip_sorting'],
				'onchange' => 'numbers.form.report.on_format_changed(this);'
			]);
			// add buttons
			$this->container('buttons', [
				'default_row_type' => 'grid',
				'order' => PHP_INT_MAX - 2000
			]);
			foreach (self::report_buttons_data_group as $k => $v) {
				$this->element('buttons', self::buttons, $k, $v);
			}
			// add report container
			$this->container('__report_container', [
				'default_row_type' => 'grid',
				'order' => PHP_INT_MAX - 1000,
				'custom_renderer' => $this->form_class . '::build_report',
				'report_renderer' => true
			]);
		}
		// ajax requests from other forms are filtered by id
		if (!empty($this->options['input']['__ajax'])) {
			// if its ajax call to this form
			if (($this->options['input']['__ajax_form_id'] ?? '') == "form_{$this->form_link}_form") {
				// it its a call to auto complete
				if ($this->attributes && !empty($this->options['input']['__ajax_autocomplete']['rn_attrattr_id'])) {
					return factory::model('numbers_data_relations_model_attribute_form', true)->autocomplete($this, $this->options['input']);
				} else if (!empty($this->options['input']['__ajax_autocomplete']['name'])
					&& !empty($this->fields[$this->options['input']['__ajax_autocomplete']['name']]['options']['method'])
					&& strpos($this->fields[$this->options['input']['__ajax_autocomplete']['name']]['options']['method'], 'autocomplete') !== false
				) {
					$options = $this->fields[$this->options['input']['__ajax_autocomplete']['name']]['options'];
					$options['__ajax'] = true;
					$options['__ajax_autocomplete'] = $this->options['input']['__ajax_autocomplete'];
					$temp = explode('::', $this->fields[$this->options['input']['__ajax_autocomplete']['name']]['options']['method']);
					if (count($temp) == 1) {
						return html::{$temp[0]}($options);
					} else {
						return factory::model($temp[0])->{$temp[1]}($options);
					}
				}
			} else {
				// load pk
				$this->load_pk($this->options['input']);
				// we need to set this flag so ajax calls can go through
				//$this->values_loaded = true;
				$this->flag_another_ajax_call = true;
				return;
			}
		} else if (!empty($this->options['input']['__form_link']) && $this->options['input']['__form_link'] != $this->form_link) { // it its a call from another form
			$this->trigger_method('refresh');
			goto load_values;
		}
		// navigation
		if (!empty($this->options['input']['navigation'])) {
			$this->process_navigation($this->options['input']['navigation']);
		}
		// onchange fields
		$this->misc_settings['__form_onchange_field_values_key'] = null;
		if (!empty($this->options['input']['__form_onchange_field_values_key'])) {
			$this->misc_settings['__form_onchange_field_values_key'] = explode('[::]', $this->options['input']['__form_onchange_field_values_key']);
		}
		// we need to see if form has been submitted
		$this->process_submit = [];
		if (isset($this->process_submit_all[$this::button_submit_blank]) && !empty($this->options['input'][$this::button_submit_blank])) {
			$this->blank = true;
			$this->process_submit = [
				$this::button_submit_blank => true
			];
		} else if (isset($this->process_submit_all[$this::button_submit_refresh]) && !empty($this->options['input'][$this::button_submit_refresh])) {
			$this->refresh = true;
			$this->process_submit = [
				$this::button_submit_refresh => true
			];
		} else {
			foreach ($this->process_submit_all as $k => $v) {
				if (!empty($this->options['input'][$k])) {
					$this->submitted = true;
					$this->process_submit[$k] = true;
				}
			}
		}
		// if we delete
		if (!empty($this->process_submit[self::button_submit_delete])) {
			$this->delete = true;
		}
		// if we are blanking the form
		if ($this->blank) {
			$this->get_all_values([]);
			$this->trigger_method('refresh');
			goto convert_multiple_columns;
		}
		// we need to start transaction
		if (!empty($this->collection_object) && $this->submitted && $this->initiator_class != 'numbers_frontend_html_form_wrapper_report') {
			$this->collection_object->primary_model->db_object->begin();
			$this->transaction = true;
		}
		// load original values
		$this->get_original_values($this->options['input'] ?? [], $this->transaction);
		// if we do not submit the form and have no values
		if (!$this->submitted && !$this->refresh) {
			if ($this->values_loaded) {
				goto load_values;
			} else { // if we have no values its blank
				$this->blank = true;
				$this->get_all_values([]);
				$this->trigger_method('refresh');
				goto convert_multiple_columns;
			}
		}
		// get all values
		$this->get_all_values($this->options['input'] ?? [], [
			'validate_required' => $this->submitted, // a must, used for widget data processing
			'validate_for_delete' => $this->delete
		]);
		//print_r2($this->values);
		// validate submits
		if ($this->submitted) {
			if (!$this->validate_submit_buttons()) {
				goto process_errors;
			}
		}
		// handling form refresh
		$this->trigger_method('refresh');
		// validate required fields after refresh
		if ($this->submitted && !$this->delete) {
			$this->validate_required_fields();
		}
		// convert columns on refresh
		if ($this->refresh) {
			goto convert_multiple_columns;
		}
		// if form has been submitted
		if ($this->submitted) {
			// call attached method to the form
			if (!$this->delete) {
				// create a snapshot of values for rollback
				$this->snapshot_values = $this->values;
				// execute validate method
				if (method_exists($this, 'validate')) {
					$this->validate($this);
				} else if (!empty($this->wrapper_methods['validate'])) {
					$this->trigger_method('validate');
				}
			}
			// save for regular forms
			if (!$this->has_errors() && !empty($this->process_submit[$this::button_submit_save])) {
				// if it is a report we would skip save
				if ($this->initiator_class == 'numbers_frontend_html_form_wrapper_report') {
					goto convert_multiple_columns;
				}
				// process save
				if (method_exists($this, 'save')) {
					$this->values_saved = $this->save($this);
				} else if (!empty($this->wrapper_methods['save'])) {
					$this->values_saved = $this->trigger_method('save');
				} else if (!empty($this->collection_object)) {
					// native save based on collection
					$this->values_saved = $this->save_values();
					/*
					 * todo
					if ($this->save_values() || empty($this->errors['general']['danger'])) {
						// we need to redirect for certain buttons
						$mvc = application::get('mvc');
						// save and new
						if (!empty($this->process_submit[self::button_submit_save_and_new])) {
							request::redirect($mvc['full']);
						}
						// save and close
						if (!empty($this->process_submit[self::button_submit_save_and_close])) {
							request::redirect($mvc['controller'] . '/_index');
						}
						// we reload form values
						goto load_values;
					} else {
						goto convert_multiple_columns;
					}
					*/
				}
				// if save was successfull we post
				if (!$this->has_errors()) {
					$temp = $this->trigger_method('post');
				}
				// rollback changes maid in validate method
				if ($this->has_errors()) {
					$this->values = $this->snapshot_values;
					if (!$this->rollback) {
						$this->values_saved = false;
					}
				}
			}
		}
		// adding general error
process_errors:
		if ($this->errors['flag_error_in_fields'] && empty($this->errors['general']['danger'])) {
			$this->error('danger', object_content_messages::submission_problem);
		}
		if ($this->errors['flag_warning_in_fields']) {
			$this->error('warning', object_content_messages::submission_warning);
		}
		// close transaction
		$this->close_transaction();
		// reindex errors and warnings when pk is a serial type
		if (!empty($this->new_serials) && !empty($this->errors['fields'])) {
			$intersect = array_intersect($this->collection_object->data['pk'], array_keys($this->new_serials));
			if (!empty($intersect) && count($intersect) == 1) {
				$serial_pk = $this->values[$intersect[0]];
				foreach ($this->detail_fields as $k => $v) {
					foreach ($this->errors['fields'] as $k2 => $v2) {
						if (strpos($k2, $k . '[0::') !== false) {
							unset($this->errors['fields'][$k2]);
							$temp = str_replace($k . '[0::', $k . '[' . $serial_pk . '::', $k2);
							$this->errors['fields'][$temp] = $v2;
						}
					}
				}
			}
		}
		// if we are deleting and have an error we need to pull the data
		if ($this->delete && $this->has_errors()) goto load_values2;
load_values:
		if (!$this->has_errors()) {
			if ($this->values_deleted) { // we need to provide default values
				$this->values_loaded = false;
				$this->original_values = [];
				$this->get_all_values([]);
			} else if ($this->values_saved) { // if saved we need to reload from database
				$this->trigger_method('success');
load_values2:
				$this->original_values = $this->values = $this->load_values();
				$this->values_loaded = true;
			} else if ($this->values_loaded) { // otherwise set loaded values
				$this->values = $this->original_values;
				// if we are preserving columns during navigation
				if (!empty($this->misc_settings['navigation']['preserve'])) {
					$this->values = array_merge_hard($this->values, $this->misc_settings['navigation']['preserve']);
				}
			}
		}
convert_multiple_columns:
		// close transaction
		$this->close_transaction();
		// convert multiple column to a form renderer can accept
		$this->convert_multiple_columns($this->values);
		// assuming save has been executed without errors we need to process on_success_js
		if (!$this->has_errors() && !empty($this->options['on_success_js'])) {
			layout::onload($this->options['on_success_js']);
		}
		// we need to hide buttons
		$this->validate_submit_buttons(['skip_validation' => true]);
		// add success messages
		if (!$this->has_errors()) {
			if (isset($this->misc_settings['success_message_if_no_errors'])) {
				$this->error('success', $this->misc_settings['success_message_if_no_errors']);
			} else {
				if ($this->values_deleted) $this->error('success', object_content_messages::record_deleted);
				if ($this->values_inserted) $this->error('success', object_content_messages::record_inserted);
				if ($this->values_updated) $this->error('success', object_content_messages::recort_updated);
			}
		}
		// query for list
		if ($this->initiator_class == 'list' && !$this->has_errors() && ($this->submitted || (!$this->refresh && !$this->submitted))) {
			$this->misc_settings['list']['enabled'] = true;
			// create query object
			if (!empty($this->form_parent->query_primary_model)) {
				$this->query = call_user_func_array([$this->form_parent->query_primary_model, 'query_builder_static'], [])->select();
			}
			// add filter
			$where = [];
			foreach ($this->fields as $k => $v) {
				if (!empty($v['options']['query_builder']) && isset($this->values[$k])) {
					if (is_array($this->values[$k]) && empty($this->values[$k])) continue;
					$where[$v['options']['query_builder']] = $this->values[$k];
				}
			}
			if (isset($this->values['full_text_search'])) {
				$where['full_text_search;FTS'] = [
					'fields' => $this->fields['full_text_search']['options']['full_text_search_columns'],
					'str' => $this->values['full_text_search']
				];
			}
			if (!empty($where)) {
				$this->query->where_multiple('AND', $where);
			}
			// execute custom query processor
			$result = $this->trigger_method('list_query');
			if (is_array($result) && !empty($result['success'])) {
				$this->misc_settings['list']['total'] = $result['total'];
				$this->misc_settings['list']['num_rows'] = count($result['rows']);
				$this->misc_settings['list']['rows'] = $result['rows'];
			} else if (!empty($this->query)) { // when we need to query
				// query 1 get counter
				$counter_query = clone $this->query;
				$counter_query->columns(['counter' => 'COUNT(*)'], ['empty_existing' => true]);
				$temp = $counter_query->query();
				$this->misc_settings['list']['total'] = $temp['rows'][0]['counter'];
				// query 2 actual rows
				if (!empty($this->values['numbers_framework_object_form_model_dummy_sort'])) {
					foreach ($this->values['numbers_framework_object_form_model_dummy_sort'] as $k => $v) {
						if (!empty($v['__sort'])) {
							$name = $this->detail_fields['numbers_framework_object_form_model_dummy_sort']['elements']['__sort']['options']['options'][$v['__sort']]['name'];
							$this->misc_settings['list']['sort'][$name] = $v['__order'];
							$this->query->orderby([$v['__sort'] => $v['__order']]);
						}
					}
				}
				$this->query->offset($this->values['__offset'] ?? 0);
				$this->query->limit($this->values['__limit']);
				$temp = $this->query->query();
				$this->misc_settings['list']['num_rows'] = count($temp['rows']);
				$this->misc_settings['list']['rows'] = $temp['rows'];
			}
			$this->misc_settings['list']['limit'] = $this->values['__limit'];
			$this->misc_settings['list']['offset'] = $this->values['__offset'];
			$this->misc_settings['list']['preview'] = $this->values['__preview'];
			$this->misc_settings['list']['columns'] = $this->data[$this::list_container]['rows'];
		}
	}

	/**
	 * Close transaction
	 */
	public function close_transaction() {
		if ($this->transaction) {
			if ($this->values_saved) { // we commit
				$this->collection_object->primary_model->db_object->commit();
			} else if (!$this->rollback) {
				$this->collection_object->primary_model->db_object->rollback();
				$this->rollback = true;
			}
			$this->transaction = false;
		}
	}

	/**
	 * Process navigation
	 *
	 * @param array $navigation
	 */
	private function process_navigation($navigation) {
		do {
			$column = key($navigation);
			if (empty($this->fields[$column]['options']['navigation'])) break;
			$navigation_type = key($navigation[$column]);
			if (empty($navigation_type) || !in_array($navigation_type, ['first', 'previous', 'refresh', 'next', 'last'])) break;
			// we need to process columns
			$navigation_columns = [$column];
			$navigation_depends = [];
			if (is_array($this->fields[$column]['options']['navigation'])) {
				if (!empty($this->fields[$column]['options']['navigation']['depends'])) {
					foreach ($this->fields[$column]['options']['navigation']['depends'] as $v) {
						$navigation_columns[] = $v;
						$navigation_depends[] = $v;
					}
				}
			}
			// get all values
			$this->get_all_values($this->options['input'] ?? [], [
				'only_columns' => $navigation_columns
			]);
			// if we have errors we need to refresh
			if ($this->has_errors()) {
				$this->error_reset_all();
				$this->options['input'][$this::button_submit_refresh] = true;
				break;
			}
			$params = [
				'column_name' => $column,
				'column_value' => $this->values[$column],
				'depends' => []
			];
			foreach ($navigation_depends as $v) {
				$params['depends'][$v] = $this->values[$v];
			}
			$model = new numbers_frontend_html_form_model_datasource_navigation();
			$result = $model->get([
				'model' => $this->collection['model'],
				'type' => $navigation_type,
				'column' => $column,
				'pk' => $this->collection_object->data['pk'],
				'where' => $params
			]);
			// if we have data we override
			if (!empty($result[0])) {
				// preserve columns
				$this->misc_settings['navigation']['preserve'] = [];
				if (!empty($this->fields[$column]['options']['navigation']['preserve'])) {
					foreach ($this->fields[$column]['options']['navigation']['preserve'] as $v) {
						$this->misc_settings['navigation']['preserve'][$v] = $this->options['input'][$v] ?? null;
					}
				}
				$this->options['input'] = $result[0];
			} else {
				if ($navigation_type == 'refresh') {
					$this->error('danger', object_content_messages::record_not_found, $column);
				} else {
					$this->error('danger', object_content_messages::prev_or_next_record_not_found, $column);
				}
				$this->options['input'][$this::button_submit_refresh] = true;
			}
		} while(0);
	}

	/**
	 * Convert multiple columns
	 */
	private function convert_multiple_columns(& $values) {
		// regular fields
		foreach ($this->fields as $k => $v) {
			if (!empty($v['options']['multiple_column'])) {
				if (!empty($values[$k])) {
					pk($v['options']['multiple_column'], $values[$k]);
					$values[$k] = array_keys($values[$k]);
				}
			}
		}
		// details
		foreach ($this->detail_fields as $k => $v) {
			if (empty($values[$k]) || !is_array($values[$k])) continue;
			if (!empty($v['options']['details_convert_multiple_columns'])) {
				$widget_model = factory::model($k, true);
				$widget_model->convert_multiple_columns($this, $values[$k]);
			} else if (!empty($values[$k])) { // convert fields
				// 1 to 1
				if (!empty($v['options']['details_11'])) {
					$details = [$values[$k]];
				} else { // 1 to M
					$details = $values[$k];
				}
				foreach ($details as $k5 => $v5) {
					if (!empty($v['options']['details_11'])) {
						$values_key = [$k];
					} else {
						$values_key = [$k, $k5];
					}
					foreach ($v['elements'] as $k2 => $v2) {
						if (!empty($v2['options']['multiple_column'])) {
							if (!empty($v5[$k2])) {
								$temp = $v5[$k2];
								pk($v2['options']['multiple_column'], $temp);
								array_key_set($values, array_merge($values_key, [$k2]), array_keys($temp));
							}
						}
					}
				}
			}
			// subdetails
			if (!empty($v['subdetails'])) {
				foreach ($values[$k] as $k11 => $v11) {
					foreach ($v['subdetails'] as $k0 => $v0) {
						if (!empty($v0['options']['details_convert_multiple_columns'])) {
							$widget_model = factory::model($k0, true);
							$widget_model->convert_multiple_columns($this, $values[$k][$k11][$k0]);
						}
					}
				}
			}
		}
	}

	/**
	 * Validate submit buttons
	 *
	 * @param array $options
	 */
	public function validate_submit_buttons($options = []) {
		$buttons_found = [];
		$names = [];
		$have_transaction_buttons = false;
		foreach ($this->data as $k => $v) {
			foreach ($v['rows'] as $k2 => $v2) {
				if ($k2 == $this::transaction_buttons) {
					$have_transaction_buttons = true;
				}
				// find all process submit buttons
				foreach ($v2['elements'] as $k3 => $v3) {
					if (!empty($v3['options']['process_submit'])) {
						if (!isset($buttons_found[$k3])) {
							$buttons_found[$k3] = [];
						}
						$buttons_found[$k3][] = [
							'name' => $v3['options']['value'],
							'key' => [$k, 'rows', $k2, 'elements', $k3]
						];
						$names[$k3] = $v3['options']['value'];
					}
				}
			}
		}
		// validations
		if ($have_transaction_buttons) {
			// make a call to master object
			$result = $this->master_object->__process_buttons($this, [
				'skip_validation' => $options['skip_validation'] ?? false
			]);
			$not_allowed = $result['not_allowed'];
			$also_set_save = $result['also_set_save'];
			$all_standard_buttons = $result['all_buttons'];
		} else { // standard buttons
			$all_standard_buttons = [
				self::button_submit,
				self::button_submit_save,
				self::button_submit_save_and_new,
				self::button_submit_save_and_close,
				self::button_submit_reset,
				self::button_submit_delete
			];
			// process
			$not_allowed = [];
			// remove delete buttons if we do not have loaded values or do not have permission
			if (!$this->values_loaded || !object_controller::can('record_delete')) {
				$not_allowed[] = self::button_submit_delete;
			}
			// we need to check permissions
			$show_save_buttons = false;
			if (object_controller::can('record_new') && !$this->values_loaded) {
				$show_save_buttons = true;
			}
			if (object_controller::can('record_edit') && $this->values_loaded) {
				$show_save_buttons = true;
			}
			if (!$show_save_buttons) {
				$not_allowed[] = self::button_submit_save;
				$not_allowed[] = self::button_submit_save_and_new;
				$not_allowed[] = self::button_submit_save_and_close;
			}
			// these buttons are considered save
			$also_set_save = [
				self::button_submit,
				self::button_submit_save_and_new,
				self::button_submit_save_and_close,
				self::button_submit_delete
			];
		}
		// validate if we have that button
		$result = true;
		foreach ($buttons_found as $k => $v) {
			if (empty($this->process_submit[$k])) {
				unset($this->process_submit[$k]);
			} else if (empty($buttons_found[$k]) || (!in_array($k, $all_standard_buttons) && empty($this->process_submit_other[$k])) || in_array($k, $not_allowed)) {
				// if we have validation
				if (empty($options['skip_validation'])) {
					$this->error('danger', 'Form action [action] is not allowed!', null, ['replace' => ['[action]' => i18n(null, $names[$k])]]);
					$result = false;
				}
				unset($this->process_submit[$k]);
			}
			// hide it
			if (!empty($options['skip_validation'])) {
				if (!empty($buttons_found[$k]) && in_array($k, $not_allowed)) {
					foreach ($buttons_found[$k] as $v2) {
						// we disable buttons in test mode
						if (application::get('flag.numbers.frontend.html.form.show_field_settings')) {
							$temp = array_key_get($this->data, $v2['key']);
							$temp['options']['class'] = ($temp['options']['class'] ?? '') . ' disabled';
							array_key_set($this->data, $v2['key'], $temp);
						} else { // remove in regular mode
							array_key_get($this->data, $v2['key'], ['unset' => true]);
						}
					}
				}
			}
		}
		$this->submitted = !empty($this->process_submit);
		// fix for save
		foreach ($also_set_save as $v) {
			if (!empty($this->process_submit[$v])) {
				$this->process_submit[self::button_submit_save] = true;
			}
		}
		return $result;
	}

	/**
	 * Add error to tabs
	 *
	 * @param array $counters
	 *		type => number
	 */
	public function error_in_tabs($counters) {
		if (empty($this->current_tab) || empty($counters)) {
			return;
		}
		if (!isset($this->errors['tabs'])) {
			$this->errors['tabs'] = [];
		}
		// we need to process errors in a special way
		foreach ($counters as $type => $counter) {
			$current_tab = $this->current_tab;
			do {
				$key = implode('__', $current_tab) . '__' . $type;
				$current_value = array_key_get($this->errors['tabs'], $key);
				if (is_null($current_value)) {
					$current_value = 0;
				}
				array_key_set($this->errors['tabs'], $key, $current_value + $counter);
				array_pop($current_tab);
			} while (count($current_tab) > 0 && $type != 'records');
		}
	}

	/**
	 * Validate data type for single value
	 *
	 * @param string $k
	 * @param array $v
	 * @param mixed $in_value
	 * @param string $error_field
	 */
	final public function validate_data_types_single_value($k, $v, $in_value, $error_field = null) {
		// we set error field as main key
		if (empty($error_field)) {
			$error_field = $k;
		}
		// perform validation
		$data = object_table_columns::process_single_column_type($k, $v['options'], $in_value, ['process_datetime' => true]);
		if (array_key_exists($k, $data)) {
			// validations
			$error = false;
			$value = $in_value;
			// perform validation
			if ($v['options']['type'] == 'boolean') {
				if (!empty($value) && ($value . '' != $data[$k] . '')) {
					$this->error('danger', 'Wrong boolean value!', $error_field);
					$error = true;
				}
			} else if (in_array($v['options']['type'], ['date', 'time', 'datetime', 'timestamp'])) { // dates first
				if (!empty($value) && empty($data[$k . '_strtotime_value'])) {
					$this->error('danger', 'Invalid date, time or datetime!', $error_field);
					$error = true;
				}
			} else if ($v['options']['php_type'] == 'integer') {
				if ($value . '' !== '' && !format::read_intval($value, ['valid_check' => 1])) {
					$this->error('danger', 'Wrong integer value!', $error_field);
					$error = true;
				}
				// null processing
				if (!$error) {
					if (empty($data[$k]) && !empty($v['options']['null'])) {
						$data[$k] = null;
					}
				}
			} else if ($v['options']['php_type'] == 'bcnumeric') { // accounting numbers
				if ($value . '' !== '' && !format::read_bcnumeric($value, ['valid_check' => 1])) {
					$this->error('danger', 'Wrong numeric value!', $error_field);
					$error = true;
				}
				// precision & scale validations
				if (!$error) {
					// validate scale
					$digits = explode('.', $data[$k] . '');
					if (!empty($v['options']['scale'])) {
						if (!empty($digits[1]) && strlen($digits[1]) > $v['options']['scale']) {
							$this->error('danger', 'Only [digits] fraction digits allowed!', $error_field, ['replace' => ['[digits]' => i18n(null, $v['options']['scale'])]]);
							$error = true;
						}
					}
					// validate precision
					if (!empty($v['options']['precision'])) {
						$precision = $v['options']['precision'] - $v['options']['scale'] ?? 0;
						if (strlen($digits[0]) > $precision) {
							$this->error('danger', 'Only [digits] digits allowed!', $error_field, ['replace' => ['[digits]' => i18n(null, $precision)]]);
							$error = true;
						}
					}
				}
			} else if ($v['options']['php_type'] == 'float') { // regular floats
				if ($value . '' !== '' && !format::read_floatval($value, ['valid_check' => 1])) {
					$this->error('danger', 'Wrong float value!', $error_field);
					$error = true;
				}
				// null processing
				if (!$error) {
					if (empty($data[$k]) && !empty($v['options']['null'])) {
						$data[$k] = null;
					}
				}
			} else if ($v['options']['php_type'] == 'string') {
				// we need to convert empty string to null
				if ($data[$k] . '' === '' && !empty($v['options']['null'])) {
					$data[$k] = null;
				}
				// validate string length
				if ($data[$k] . '' !== '') {
					// validate length
					if (!empty($v['options']['type']) && $v['options']['type'] == 'char' && strlen($data[$k]) != $v['options']['length']) {  // char
						$this->error('danger', 'The length must be [length] characters!', $error_field, ['replace' => ['[length]' => i18n(null, $v['options']['length'])]]);
						$error = true;
					} else if (!empty($v['options']['length']) && strlen($data[$k]) > $v['options']['length']) { // varchar
						$this->error('danger', 'String is too long, should be no longer than [length]!', $error_field, ['replace' => ['[length]' => i18n(null, $v['options']['length'])]]);
						$error = true;
					}
				}
			}
			$data['flag_error'] = $error;
		} else if (!empty($data[$k . '_is_serial'])) {
			if ($in_value . '' !== '' && !empty($data[$k . '_is_serial_error'])) {
				$this->error('danger', 'Wrong sequence value!', $error_field);
				$data['flag_error'] = true;
			}
		} else {
			$this->error('danger', object_content_messages::unknown_value, $error_field);
			$data['flag_error'] = true;
		}
		return $data;
	}

	/**
	 * Save values to database
	 *
	 * @return boolean
	 */
	final public function save_values() {
		// double check if we have collection object
		if (empty($this->collection_object)) {
			Throw new Exception('You must provide collection object!');
		}
		$options = ['flag_delete_row' => $this->process_submit[self::button_submit_delete] ?? false];
		// we do not need to reload values from database because we locked them
		if ($this->values_loaded) {
			$options['original'] = $this->original_values;
		}
		$result = $this->collection_object->merge($this->values, $options);
		if (!$result['success']) {
			if (!empty($result['error'])) {
				foreach ($result['error'] as $v) {
					$this->error('danger', $v);
				}
			}
			if (!empty($result['warning'])) {
				foreach ($result['warning'] as $v) {
					$this->error('warning', $v);
				}
			}
			if (!empty($result['options_model'])) {
				foreach ($result['options_model'] as $k => $v) {
					$this->error('danger', object_content_messages::unknown_value, $k);
				}
				$this->error('danger', object_content_messages::submission_problem);
			}
			$this->rollback = true;
			return false;
		} else { // if success
			if (!empty($result['deleted'])) {
				$this->values_deleted = true;
			} else if ($result['inserted']) {
				$this->values_inserted = true;
				// we must put serial columns back into values
				if (!empty($result['new_serials'])) {
					$this->new_serials = $result['new_serials'];
					$this->values = array_merge_hard($this->values, $result['new_serials']);
					$this->load_pk($this->values);
				}
			} else {
				$this->values_updated = true;
			}
			return true;
		}
	}

	/**
	 * Pre load collection object
	 *
	 * @return boolean
	 */
	final public function preload_collection_object() {
		if (empty($this->collection)) return false;
		if (empty($this->collection_object)) {
			$this->collection_object = object_collection::collection_to_model($this->collection);
			if (empty($this->collection_object)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Update collection object
	 */
	final public function update_collection_object() {
		if (!empty($this->collection_object) && !empty($this->collection)) {
			$this->collection_object->data = array_merge_hard($this->collection_object->data, $this->collection);
		}
	}

	/**
	 * Load primary key from values
	 */
	final public function load_pk(& $values) {
		$this->pk = [];
		$this->full_pk = true;
		if (!empty($this->collection_object)) {
			foreach ($this->collection_object->data['pk'] as $v) {
				if (isset($values[$v])) {
					$temp = object_table_columns::process_single_column_type($v, $this->collection_object->primary_model->columns[$v], $values[$v]);
					if (!empty($temp[$v])) { // pk can not be empty
						$this->pk[$v] = $temp[$v];
					} else {
						$this->full_pk = false;
					}
				} else {
					$this->full_pk = false;
				}
			}
		} else {
			$this->full_pk = false;
		}
	}

	/**
	 * Load values from database
	 *
	 * @return mixed
	 */
	final public function load_values($for_update = false) {
		if ($this->full_pk) {
			$result = $this->collection_object->get(['where' => $this->pk, 'single_row' => true, 'for_update' => $for_update]);
			if ($result['success']) {
				return $result['data'];
			}
		}
		return false;
	}

	/**
	 * Add error to the form
	 *
	 * @param string $type
	 *		muted
	 *		primary
	 *		success
	 *		info
	 *		warning
	 *		danger
	 * @param mixed $message
	 * @param mixed $field
	 * @param array $options - same parameters as in i18n
	 */
	public function error($type, $message, $field = null, $options = []) {
		// if its an array of message we process them one by one
		if (is_array($message)) {
			foreach ($message as $v) {
				$this->error($type, $v, $field, $options);
			}
			return;
		}
		// generate hash
		$hash = sha1($message);
		// i18n
		if (empty($options['skip_i18n'])) {
			$message = i18n(null, $message, $options);
		}
		// set field error
		if (!empty($field)) {
			if ($type == 'reset') {
				unset($this->errors['fields'][$field]);
				// recalculate
				$this->errors['flag_error_in_fields'] = false;
				$this->errors['flag_warning_in_fields'] = false;
				if (!empty($this->errors['fields'])) {
					foreach ($this->errors['fields'] as $k => $v) {
						foreach ($v as $k2 => $v2) {
							if ($k2 == 'danger') $this->errors['flag_error_in_fields'] = true;
							if ($k2 == 'warning') $this->errors['flag_warning_in_fields'] = true;
						}
					}
				}
			} else {
				array_key_set($this->errors, ['fields', $field, $type, $hash], $message);
				// set special flag that we have error in fields
				if ($type == 'danger') {
					$this->errors['flag_error_in_fields'] = true;
				}
				if ($type == 'warning') {
					$this->errors['flag_warning_in_fields'] = true;
				}
				// format
				if (!empty($options['format'])) {
					array_key_set($this->errors, ['formats', $field, $type], 1);
				}
			}
		} else {
			$this->errors['general'][$type][$hash] = $message;
		}
	}

	/**
	 * Whether form has errors
	 *
	 * @param mixed $error_names
	 * @return boolean
	 */
	public function has_errors($error_names = null) {
		if (empty($error_names)) {
			return !empty($this->errors['flag_error_in_fields']) || !empty($this->errors['general']['danger']);
		} else {
			if (!is_array($error_names)) {
				$error_names = [$error_names];
			}
			foreach ($error_names as $v) {
				if (!empty($this->errors['fields'][$v]['danger'])) {
					return true;
				}
			}
			return false;
		}
	}

	/**
	 * Reset all error messages
	 */
	public function error_reset_all() {
		$this->errors = [
			'flag_error_in_fields' => false,
			'flag_warning_in_fields' => false,
		];
	}

	/**
	 * Process widget
	 *
	 * @param array $options
	 * @return boolean
	 */
	private function process_widget($options) {
		$property = str_replace('detail_', '', $options['widget']);
		// determine object
		if ($options['type'] == 'tabs' || $options['type'] == 'fields') {
			$object = & $this->collection_object->primary_model;
		} else if ($options['type'] == 'subdetails') {
			$object = factory::model($options['details_parent_key'], true);
		}
		if (!empty($object->{$property})) {
			return factory::model($object->{"{$property}_model"}, true)->process_widget($this, $options);
		}
		return false;
	}

	/**
	 * Add container to the form
	 *
	 * @param string $container_link
	 * @param array $options
	 */
	public function container($container_link, $options = []) {
		if (!isset($this->data[$container_link])) {
			$options['container_link'] = $container_link;
			$type = $options['type'] = $options['type'] ?? 'fields';
			// make hidden container last
			if ($container_link == $this::hidden) {
				$options['order'] = PHP_INT_MAX - 1000;
			}
			// see if we adding a widget
			if (!empty($options['widget'])) {
				// we skip if widgets are not enabled
				if (!object_widgets::enabled(str_replace('detail_', '', $options['widget']))) return;
				// process default widget options
				$widget = constant('object_widgets::'. $options['widget']);
				$widget_data = constant('object_widgets::'. $options['widget'] . '_data');
				$options = array_merge_hard($widget_data, $options);
				if (isset($widget_data['type'])) {
					$options['type'] = $widget_data['type'];
				}
				// handling widgets
				return $this->process_widget($options);
			}
			// processing details
			if ($type == 'details') {
				if (empty($options['details_key']) || empty($options['details_pk'])) {
					Throw new Exception('Detail key or pk?');
				}
				$options['details_collection_key'] = $options['details_collection_key'] ?? ['details', $options['details_key']];
				$options['details_rendering_type'] = $options['details_rendering_type'] ?? 'grid_with_label';
				$options['details_new_rows'] = $options['details_new_rows'] ?? 0;
			}
			// processing subdetails
			if ($type == 'subdetails') {
				if (empty($options['details_key']) || empty($options['details_pk']) || empty($options['details_parent_key'])) {
					Throw new Exception('Subdetail key, parent key or pk?');
				}
				$options['flag_child'] = true;
				$options['details_collection_key'] = $options['details_collection_key'] ?? ['details', $options['details_parent_key'], 'details', $options['details_key']];
				$options['details_rendering_type'] = $options['details_rendering_type'] ?? 'table';
				$options['details_new_rows'] = $options['details_new_rows'] ?? 0;
			}
			$this->data[$container_link] = [
				'options' => $options,
				'order' => $options['order'] ?? 0,
				'type' => $type,
				'flag_child' => !empty($options['flag_child']),
				'default_row_type' => $options['default_row_type'] ?? 'grid',
				'rows' => [],
			];
			// special handling for details
			if ($type == 'details') {
				$model = factory::model($options['details_key'], true);
				// if we have relation
				if (!empty($model->relation['field']) && !in_array($model->relation['field'], $model->pk)) {
					$this->element($container_link, $this::hidden, $model->relation['field'], ['label_name' => 'Relation #', 'domain' => 'relation_id_sequence', 'method'=> 'input', 'persistent' => true]);
				}
			}
			if ($type == 'details' || $type == 'subdetails') {
				// if we have autoincrement
				if (!empty($options['details_autoincrement'])) {
					$model = factory::model($options['details_key'], true);
					foreach ($options['details_autoincrement'] as $v) {
						$this->element($container_link, $this::hidden, $v, $model->columns[$v]);
					}
				}
			}
		} else {
			$this->data[$container_link]['options'] = array_merge_hard($this->data[$container_link]['options'] ?? [], $options);
			if (isset($options['order'])) {
				$this->data[$container_link]['order'] = $options['order'];
			}
		}
	}

	/**
	 * Add row to the container
	 *
	 * @param string $container_link
	 * @param string $row_link
	 * @param array $options
	 */
	public function row($container_link, $row_link, $options = []) {
		$this->container($container_link, array_key_extract_by_prefix($options, 'container_'));
		if (!isset($this->data[$container_link]['rows'][$row_link])) {
			// hidden rows
			if ($row_link == $this::hidden) {
				$options['order'] = PHP_INT_MAX - 1000;
			}
			// validating row type
			$types = object_html_form_row_types::get_static();
			if (!isset($options['type']) || !isset($types[$options['type']])) {
				$options['type'] = $this->data[$container_link]['default_row_type'] ?? 'grid';
			}
			$options['container_link'] = $container_link;
			$options['row_link'] = $row_link;
			// setting values
			$this->data[$container_link]['rows'][$row_link] = [
				'type' => $options['type'],
				'elements' => [],
				'options' => $options,
				'order' => $options['order'] ?? 0
			];
			// handling widgets
			if ($this->data[$container_link]['type'] == 'tabs' && !empty($options['widget'])) {
				$options['type'] = 'tabs';
				// we skip if widgets are not enabled
				if (!object_widgets::enabled($options['widget']) || !$this->process_widget($options)) {
					unset($this->data[$container_link]['rows'][$row_link]);
					return;
				}
			}
		} else {
			$this->data[$container_link]['rows'][$row_link]['options'] = array_merge_hard($this->data[$container_link]['rows'][$row_link]['options'], $options);
			if (isset($options['order'])) {
				$this->data[$container_link]['rows'][$row_link]['order'] = $options['order'];
			}
		}
	}

	/**
	 * Add element to the row
	 *
	 * @param string $container_link
	 * @param string $row_link
	 * @param string $element_link
	 * @param array $options
	 */
	public function element($container_link, $row_link, $element_link, $options = []) {
		// presetting options for buttons, making them last
		if (in_array($row_link, [$this::buttons, $this::transaction_buttons])) {
			$options['row_type'] = 'grid';
			if (!isset($options['row_order'])) {
				$options['row_order'] = PHP_INT_MAX - 500;
			}
		}
		// processing row and container
		$this->container($container_link, array_key_extract_by_prefix($options, 'container_'));
		$this->row($container_link, $row_link, array_key_extract_by_prefix($options, 'row_'));
		// setting value
		if (!isset($this->data[$container_link]['rows'][$row_link]['elements'][$element_link])) {
			if (!empty($options['container'])) {
				$this->data[$options['container']]['flag_child'] = true;
				$type = 'tab';
				$container = $options['container'];
				// need to add a container to the tabs
				$this->misc_settings['tabs'][$container] = $this->data[$container_link]['rows'][$row_link]['options']['label_name'];
			} else {
				// name & id
				if ($this->data[$container_link]['type'] == 'details' || $this->data[$container_link]['type'] == 'subdetails') { // details & subdetails
					$options['values_key'] = $options['error_name'] = $options['name'] = null;
					$options['id'] = null;
					$options['details_key'] = $this->data[$container_link]['options']['details_key'];
					$options['details_parent_key'] = $this->data[$container_link]['options']['details_parent_key'] ?? null;
					$options['details_field_name'] = $element_link;
					$options['details_collection_key'] = $this->data[$container_link]['options']['details_collection_key'];
				} else { // regular fields
					$options['error_name'] = $options['name'] = $element_link;
					$options['values_key'] = [$element_link];
					$options['id'] = "form_{$this->form_link}_element_{$element_link}";
					$options['details_collection_key'] = null;
					// we do not validate preset fields
					if (!empty($options['preset'])) {
						$options['options_manual_validation'] = true;
						$options['tree'] = true;
						$options['searchable'] = true;
					}
				}
				// multiple column
				if (!empty($options['multiple_column'])) {
					$options['details_collection_key'] = array_merge(($options['details_collection_key'] ?? []), ['details', $element_link]);
				}
				// process domain & type
				$temp = object_data_common::process_domains_and_types(['options' => $options]);
				$options = $temp['options'];
				$options['row_link'] = $row_link;
				$options['container_link'] = $container_link;
				// fix boolean type
				if (($options['type'] ?? '') == 'boolean' && !isset($options['method'])) {
					$options['method'] = 'select';
					$options['no_choose'] = true;
					$options['options_model'] = 'object_data_model_inactive';
					$options['searchable'] = false;
				}
				// validator method for captcha
				if (($options['method'] ?? '') == 'captcha') {
					$options['validator_method'] = application::get('flag.numbers.framework.html.captcha.submodule', ['class' => true]) . '::validate';
				}
				// type for buttons
				if (in_array(($options['method'] ?? ''), ['button', 'button2', 'submit']) && empty($options['type'])) {
					$options['type'] = $this->options['segment']['type'] ?? 'primary';
				}
				// put data into fields array
				$field = [
					'id' => $options['id'],
					'name' => $options['name'],
					'options' => $options,
					'order' => $options['order'] ?? 0,
					'row_order' => $this->data[$container_link]['rows'][$row_link]['order'] // a must used in validations
				];
				// we need to put values into fields and details
				$persistent_key = [];
				if ($this->data[$container_link]['type'] == 'details') {
					array_key_set($this->detail_fields, [$this->data[$container_link]['options']['details_key'], 'elements', $element_link], $field);
					array_key_set($this->detail_fields, [$this->data[$container_link]['options']['details_key'], 'options'], $this->data[$container_link]['options']);
					// details_unique_select
					if (!empty($field['options']['details_unique_select'])) {
						$this->misc_settings['details_unique_select'][$this->data[$container_link]['options']['details_key']][$element_link] = [];
					}
					// persistent
					$persistent_key[] = 'details';
					$persistent_key[] = $this->data[$container_link]['options']['details_key'];
					$persistent_key[] = $element_link;
				} else if ($this->data[$container_link]['type'] == 'subdetails') {
					$this->data[$container_link]['options']['container_link'] = $container_link;
					array_key_set($this->detail_fields, [$this->data[$container_link]['options']['details_parent_key'], 'subdetails', $this->data[$container_link]['options']['details_key'], 'elements', $element_link], $field);
					array_key_set($this->detail_fields, [$this->data[$container_link]['options']['details_parent_key'], 'subdetails', $this->data[$container_link]['options']['details_key'], 'options'], $this->data[$container_link]['options']);
					// details_unique_select
					if (!empty($field['options']['details_unique_select'])) {
						$this->misc_settings['details_unique_select'][$this->data[$container_link]['options']['details_parent_key'] . '::' . $this->data[$container_link]['options']['details_key']][$element_link] = [];
					}
					// persistent
					$persistent_key[] = 'subdetails';
					$persistent_key[] = $this->data[$container_link]['options']['details_parent_key'];
					$persistent_key[] = $this->data[$container_link]['options']['details_key'];
					$persistent_key[] = $element_link;
				} else {
					// persistent
					array_key_set($this->fields, $element_link, $field);
					$persistent_key[] = 'fields';
					$persistent_key[] = $element_link;
				}
				// persistent
				if (!empty($field['options']['persistent']) && !empty($persistent_key)) {
					array_key_set($this->misc_settings['persistent'], $persistent_key, $field['options']['persistent']);
				}
				// type is field by default
				$type = 'field';
				$container = null;
				// process submit elements
				if (!empty($options['process_submit'])) {
					$this->process_submit_all[$element_link] = false;
					// if its other buttons
					if ($options['process_submit'] === 'other') {
						$this->process_submit_other[$element_link] = true;
					}
				}
			}
			// setting data
			$this->data[$container_link]['rows'][$row_link]['elements'][$element_link] = [
				'type' => $type,
				'container' => $container,
				'options' => $options,
				'order' => $options['order'] ?? 0
			];
			// we need to set few misc options
			if (!empty($options['options_model'])) {
				$temp = explode('::', $options['options_model']);
				$name = [];
				if (isset($this->misc_settings['tabs'][$container_link])) {
					$name[] = $this->misc_settings['tabs'][$container_link];
				}
				$name[] = $options['label_name'];
				$this->misc_settings['option_models'][$element_link] = [
					'model' => $temp[0],
					'field_code' => $element_link,
					'field_name' => implode(': ', $name)
				];
			}
		} else {
			$this->data[$container_link]['rows'][$row_link]['elements'][$element_link]['options'] = array_merge_hard($this->data[$container_link]['rows'][$row_link]['elements'][$element_link], $options);
		}
	}

	/**
	 * Render form
	 *
	 * @param string $format
	 * @return mixed
	 */
	public function render($format = 'text/html') {
		switch ($format) {
			case 'text/html':
			default:
				$renderer = new numbers_frontend_html_form_renderers_html_base();
				return $renderer->render($this);
		}
	}

	/**
	 * Get field errors
	 *
	 * @param array $field
	 * @return mixed
	 */
	public function get_field_errors($field) {
		$existing = array_key_get($this->errors['fields'], $field['options']['name']);
		if (!empty($existing)) {
			$result = [
				'type' => null,
				'message' => '',
				'counters' => []
			];
			$sorted = [
				'danger' => [],
				'warning' => [],
				'success' => [],
				'info' => []
			];
			$types = array_keys($existing);
			if (in_array('danger', $types)) {
				$result['type'] = 'danger';
			} else {
				$temp = current($types);
				$result['type'] = $temp;
			}
			// generating text messages
			foreach ($existing as $k => $v) {
				foreach ($v as $k2 => $v2) {
					$result['counters'][$k] = ($result[$k] ?? 0) + 1;
					$sorted[$k][$k2] = $v2;
				}
			}
			foreach ($sorted as $k => $v) {
				if (empty($v)) continue;
				foreach ($v as $k2 => $v2) {
					$result['message'].= html::text(['tag' => 'div', 'class' => 'numbers_field_error_messages', 'field_value_hash' => $k2, 'type' => $k, 'value' => $v2]);
				}
			}
			return $result;
		}
		return null;
	}

	/**
	 * Render table rows
	 *
	 * @param array $rows
	 * @return type
	 */
	public function render_row_table($rows) {

		// todo
		Throw new Exception('todo: make the same as render_row_grid!');

		/*
		$data = [
			'header' => [],
			'options' => [],
			'skip_header' => true
		];
		foreach ($rows as $k => $v) {
			$index = 0;
			array_key_sort($v['value']['elements'], ['order' => SORT_ASC]);
			// group by
			$groupped = [];
			foreach ($v['value']['elements'] as $k2 => $v2) {
				$groupped[$v2['options']['label_name'] ?? ''][$k2] = $v2;
			}
			foreach ($groupped as $k2 => $v2) {
				$first = current($v2);
				if (!empty($first['options']['element_vertical_separator'])) {
					$data['options'][$k][0] = [
						// todo: add custom html and icon
						'value' => '&nbsp;',
						'colspan' => count($data['header'])
					];
				} else {
					$elements = [];
					foreach ($v2 as $k3 => $v3) {
						$v3['options']['error_name'] = $k3;
						$elements[] = $this->render_element_value($v3, $this->get_field_value($v3));
					}
					$first['prepend_to_field'] = ':';
					$data['options'][$k][$index] = [
						'value' => $this->render_element_name($first),
						'width' => '1%',
						'nowrap' => 'nowrap'
					];
					$data['header'][$index] = $index;
					$index++;
					$data['options'][$k][$index] = implode(' ', $elements);
					$data['header'][$index] = $index;
					$index++;
				}
			}
		}
		return html::table($data);
		*/
	}

	/**
	 * Process depends and params
	 *
	 * @param array $params
	 * @param array $neighbouring_values
	 * @param array $options
	 * @param boolean $flag_params
	 */
	public function process_params_and_depends(& $params, & $neighbouring_values, $options, $flag_params = true) {
		foreach ($params as $k => $v) {
			// if we have a parent
			if (strpos($v, 'parent::') !== false) {
				$field = str_replace(['parent::', 'static::'], '', $v);
				if (!empty($this->errors['fields'][$field]['danger'])) {
					$params[$k] = null;
				} else {
					$params[$k] = $this->values[$field] ?? null;
				}
			} else if ($flag_params) {
				// todo process errors   
				$params[$k] = $neighbouring_values[$v] ?? null;
			}
		}
	}

	/**
	 * Process default value
	 *
	 * @param string $key
	 * @param mixed $default
	 * @param array $neighbouring_values
	 * @param boolean $set_neightbouring_values
	 * @param array $changed_field
	 * @param array $options
	 * @return mixed
	 */
	public function process_default_value($key, $default, $value, & $neighbouring_values, $set_neightbouring_values = true, $changed_field = [], $options = []) {
		if (strpos($default, 'dependent::') !== false) {
			// nothing
		} else if (strpos($default, 'master_object::') !== false) {
			$field = explode('::', str_replace(['master_object::', 'static::'], '', $default));
			$value = $this->master_object->{$field[0]}->{$field[1]}->{$field[2]};
		} else if (strpos($default, 'parent::') !== false) {
			$field = str_replace(['parent::', 'static::'], '', $default);
			$value = $this->values[$field] ?? null;
		} else {
			if ($default === 'now()') $default = format::now('timestamp');
			$value = $default;
		}
		// handling override_field_value method
		if (!empty($this->wrapper_methods['process_default_value']['main'])) {
			// fix changed field
			if (empty($changed_field)) $changed_field = [];
			$changed_field['parent'] = $changed_field['parent'] ?? null;
			$changed_field['detail'] = $changed_field['detail'] ?? null;
			$changed_field['subdetail'] = $changed_field['subdetail'] ?? null;
			// call override method
			$model = $this->wrapper_methods['process_default_value']['main'][0];
			$model->{$this->wrapper_methods['process_default_value']['main'][1]}($this, $key, $default, $value, $neighbouring_values, $changed_field, $options);
		}
		// if we need to set neightbouring values
		if ($set_neightbouring_values) {
			$neighbouring_values[$key] = $value;
		}
		return $value;
	}

	/**
	 * Can default value be processed
	 *
	 * @param mixed $value
	 * @param array $options
	 * @return boolean
	 */
	private function can_process_default_value($value, $options) {
		if (strpos($options['options']['default'], 'static::') !== false || strpos($options['options']['default'], 'dependent::') !== false || (is_null($value) && empty($options['options']['null']))) {
			return true;
		} else {
			return false;
		}
	}
}