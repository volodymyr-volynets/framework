<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Form;

use Helper\Ob;
use Object\ACL\Resources;
use Object\Collection;
use Object\Content\Messages;
use Object\Data\Common;
use Object\Data\Model\Order;
use Object\Form\DataSource\Navigation;
use Object\Form\Model\Report\Types;
use Object\Table\Columns;
use Object\Table\Options;
use Numbers\Backend\SMS\Common\Renderer;

class Base extends Parent2
{
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
     * Module Code
     *
     * @var string
     */
    public $module_code;

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
    public $initiator_class = 'form';

    /**
     * Form parent
     *
     * @var object
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
     * Original input
     *
     * @var array
     */
    public $original_input = [];

    /**
     * Tracked values
     *
     * @var array
     */
    public $tracked_values = [];

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
     * Values formatted.
     *
     * @var array
     */
    public $formatted_values = [];

    /**
     * API values
     *
     * @var array
     */
    public $api_values = [];

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
    public $process_submit_refresh = false;

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
    public $values_no_changes = false;

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
     * @var \\Object\Query\Builder
     */
    public $query;

    /**
     * List rendered
     *
     * @var boolean
     */
    public $list_rendered;

    /**
     * Buttons model
     *
     * @var object
     */
    public $buttons_model;

    /**
     * Import object
     *
     * @var object
     */
    public $import_object;

    /**
     * Cached options
     *
     * @var array
     */
    public $cached_options = [];

    /**
     * Whether its AJAX reload
     *
     * @var bool
     */
    public $is_ajax_reload = false;

    /**
     * Preserved values
     *
     * @var array
     */
    public $preserved_values = [];

    /**
     * Is API
     *
     * @var bool
     */
    public bool $is_api = false;

    /**
     * Changed field
     *
     * @var string|array|null
     */
    public $changed_field;
    public $changed_detail;
    public $changed_api_fields;

    /**
     * Constructor
     *
     * @param string $form_link
     * @param array $options
     */
    public function __construct($form_link, $options = [])
    {
        $this->form_link = $form_link . '';
        $this->options = $options;
        // overrides from ini files
        $overrides = \Application::get('flag.numbers.frontend.html.form');
        if (!empty($overrides)) {
            $this->options = array_merge_hard($overrides, $this->options);
        }
        $this->errorResetAll();
    }

    /**
     * Trigger method
     *
     * @param string $method
     */
    public function triggerMethod($method)
    {
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
     * @param boolean $for_update
     */
    private function getOriginalValues($input, $for_update)
    {
        // process primary key
        $this->full_pk = false;
        $this->loadPk($input);
        // load values if we have full pk
        if ($this->full_pk) {
            $temp = $this->loadValues($for_update);
            if ($temp !== false) {
                $this->original_values = $temp;
                if (!empty($this->preserved_values)) {
                    $this->original_values = array_merge_hard($this->original_values, $this->preserved_values);
                }
                $this->values_loaded = true;
                // original values override
                $this->triggerMethod('loadOriginalValues');
            }
        }
    }

    /**
     * Sort fields for processing
     *
     * @param array $fields
     * @return int
     */
    public function sortFieldsForProcessing($fields, $options = [])
    {
        if (!empty($this->collection_object)) {
            $collection = array_key_get($this->collection_object->data, $options['details_collection_key'] ?? null);
            foreach ($fields as $k => $v) {
                // skip certain values
                if ($k == $this::SEPARATOR_HORIZONTAL || $k == '__separator__module_id' || $k == $this::SEPARATOR_VERTICAL || !empty($v['options']['process_submit']) || !empty($v['options']['custom_renderer'])) {
                    unset($fields[$k]);
                    continue;
                }
                // sort
                if (isset($fields[$k]['options']['order_for_defaults'])) {
                    $fields[$k]['order_for_defaults'] = $fields[$k]['options']['order_for_defaults'];
                } elseif (in_array($k, $collection['pk'] ?? [])) {
                    $fields[$k]['order_for_defaults'] = -32000;
                } elseif (!empty($v['options']['default']) && strpos($v['options']['default'], 'dependent::') !== false) { // processed last
                    $fields[$k]['order_for_defaults'] = 2147483647 - 32000 + intval(str_replace(['dependent::', 'static::'], '', $v['options']['default']));
                } elseif (!empty($v['options']['default']) && (strpos($v['options']['default'], 'parent::') !== false || strpos($v['options']['default'], 'static::') !== false)) {
                    $column = str_replace(['parent::', 'static::'], '', $v['options']['default']);
                    $fields[$k]['order_for_defaults'] = ($fields[$column]['order_for_defaults'] ?? 0) + 100;
                } elseif (!isset($fields[$k]['order_for_defaults'])) {
                    $fields[$k]['order_for_defaults'] = (($this->data[$fields[$k]['options']['container_link']]['order'] ?? 1000) * 10000) + ($fields[$k]['row_order'] * 100) + $fields[$k]['order'];
                }
            }
            array_key_sort($fields, ['order_for_defaults' => SORT_ASC], ['order_for_defaults' => SORT_NUMERIC]);
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
    public function validateRequiredOneField(& $value, $error_name, $options)
    {
        // if we have type errors we skip required validation
        if ($this->hasErrors($error_name)) {
            return;
        }
        // check lock, no need to validate it second time
        if (!empty($this->misc_settings['validateRequiredOneField'][$error_name])) {
            return;
        }
        // neighbouring values
        $neighbouring_values = [];
        if (!empty($options['options']['values_key'])) {
            $neighbouring_values_key = $options['options']['values_key'];
            array_pop($neighbouring_values_key);
            $neighbouring_values = array_key_get($this->values, $neighbouring_values_key);
        }
        // required if set
        if (!empty($options['options']['required_if_set'])) {
            if (!is_array($options['options']['required_if_set'])) {
                $options['options']['required_if_set'] = [$options['options']['required_if_set']];
            }
            foreach ($options['options']['required_if_set'] as $v) {
                if (!empty($neighbouring_values[$v])) {
                    $options['options']['required'] = true;
                    break;
                }
            }
        }
        // check if its required field
        if (isset($options['options']['required']) && ($options['options']['required'] === true || ($options['options']['required'] . '') === '1')) {
            if ($options['options']['php_type'] == 'integer' || $options['options']['php_type'] == 'float') {
                if (empty($value)) {
                    $this->error('danger', Messages::REQUIRED_FIELD, $error_name);
                }
            } elseif ($options['options']['php_type'] == 'bcnumeric') { // accounting numbers
                if (\Math::compare($value, '0', $options['options']['scale']) == 0) {
                    $this->error('danger', Messages::REQUIRED_FIELD, $error_name);
                }
            } elseif (!empty($options['options']['multiple_column']) || is_array($value)) {
                if (empty($value)) {
                    $this->error('danger', Messages::REQUIRED_FIELD, $error_name);
                }
            } else {
                if ($value . '' == '') {
                    $this->error('danger', Messages::REQUIRED_FIELD, $error_name);
                }
            }
        }
        // validator
        if (!empty($options['options']['validator_method']) && !empty($value) && empty($options['options']['multiple_column']) && (!is_array($value) || ($options['options']['method'] ?? '') == 'file')) {
            $temp = \Object\Validator\Base::method(
                $options['options']['validator_method'],
                $value,
                $options['options']['validator_params'] ?? [],
                $options['options'],
                $neighbouring_values
            );
            if (!$temp['success']) {
                foreach ($temp['error'] as $v10) {
                    $this->error('danger', $v10, $error_name);
                }
            } elseif (!empty($temp['data'])) {
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
    public function parentKeysToErrorName($parent_keys)
    {
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
    public function generateDetailsPrimaryKey(& $holder, $type = 'reset', $values = null, $parent_keys = null, $options = [])
    {
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
                    } elseif (!empty($values[$v])) {
                        $new_pk[] = $values[$v];
                    } else {
                        $new_pk[] = '__new_key_' . $holder['new_pk_counter'];
                        $holder['new_pk_counter']++;
                    }
                }
            } elseif (!empty($options['options']['details_pk'])) {
                foreach ($options['options']['details_pk'] as $v) {
                    if (isset($values[$v])) {
                        $new_pk[] = $values[$v];
                    }
                }
            } else {
                $new_pk[] = current($values);
            }
            $holder['pk'] = implode('::', array_flatten($new_pk));
            if (!empty($holder['new_pk_locks'][$holder['pk']])) {
                // error only if we have pk
                if (!empty($holder['pk'])) {
                    $holder['pk'] = '__duplicate_key_' . $holder['new_pk_counter'];
                    $holder['new_pk_counter']++;
                    $error_pk = !empty($options['options']['details_11']) ? ($parent_keys ?? []) : array_merge($parent_keys ?? [], [$holder['pk']]);
                    $holder['error_name'] = $this->parentKeysToErrorName($error_pk);
                    foreach ($options['options']['details_pk'] as $v) {
                        $this->error('danger', Messages::DUPLICATE_VALUE, "{$holder['error_name']}[{$v}]");
                    }
                }
            } else {
                $error_pk = !empty($options['options']['details_11']) ? ($parent_keys ?? []) : array_merge($parent_keys ?? [], [$holder['pk']]);
                $holder['error_name'] = $this->parentKeysToErrorName($error_pk);
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
    public function generateMultipleColumns($value, $error_name, $values, $parent_keys, $options = [])
    {
        if (!empty($value)) {
            if (!is_array($value)) {
                $value = [$value];
            }
            $result = [];
            $fields_key_holder = [];
            $this->generateDetailsPrimaryKey($fields_key_holder, 'reset', $values, $parent_keys, $options);
            foreach ($value as $k2 => $v2) {
                if (is_array($v2) && isset($v2[$options['options']['multiple_column']])) {
                    $v2 = $v2[$options['options']['multiple_column']];
                }
                $temp = $this->validateDataTypesSingleValue($options['options']['multiple_column'], $options, $v2, $error_name);
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
                $this->generateDetailsPrimaryKey($fields_key_holder, 'pk', $temp_value_new, $parent_keys, $options);
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
    public function getAllValues($input, $options = [])
    {
        // reset values
        $this->values = [];
        // sort fields
        $fields = $this->sortFieldsForProcessing($this->fields);
        // inject tenant #
        if (!empty($this->collection_object->primary_model->tenant)) {
            $this->values[$this->collection_object->primary_model->tenant_column] = \Tenant::id();
        }
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
                $this->changed_field = $changed_field['parent'] = $this->misc_settings['__form_onchange_field_values_key'][0];
            }
        }
        // process json_contains
        foreach ($fields as $k => $v) {
            if (empty($v['options']['json_contains'])) {
                continue;
            }
            $value = array_key_get($input, $v['options']['values_key']);
            if (is_json($value)) {
                $value = json_decode($value, true);
                foreach ($v['options']['json_contains'] as $k2 => $v2) {
                    array_key_set($input, $v2, $value[$k2] ?? null);
                }
            } elseif (empty($value)) {
                foreach ($v['options']['json_contains'] as $k2 => $v2) {
                    array_key_set($input, $v2, null);
                }
            }
        }
        // process fields
        foreach ($fields as $k => $v) {
            // skip certain values
            if (!empty($options['only_columns']) && !in_array($k, $options['only_columns'])) {
                continue;
            }
            if (!empty($allowed) && !in_array($k, $allowed)) {
                continue;
            }
            // default data type
            if (empty($v['options']['type'])) {
                $v['options']['type'] = 'varchar';
            }
            // get value
            $input = array_merge_hard($input, $this->values);
            $value = array_key_get($input, $v['options']['values_key']);
            // wysiwyg
            if (($v['options']['method'] ?? '') == 'wysiwyg' && !$this->is_api) {
                $value = \Request::input($k, true, false, [
                    'skip_xss_on_keys' => [$k],
                    'trim_empty_html_input' => true,
                    'remove_script_tag' => true
                ]);
            }
            $error_name = $v['options']['error_name'];
            // null_if_changed
            if (!empty($v['options']['null_if_changed']) && !empty($changed_field['parent']) && in_array($changed_field['parent'], $v['options']['null_if_changed'])) {
                $value = null;
            }
            // multiple column
            if (!empty($v['options']['multiple_column'])) {
                // todo - validate
                $value = $this->generateMultipleColumns($value, $error_name, array_merge($input, $this->values), null, $v);
            } else {
                $temp = $this->validateDataTypesSingleValue($k, $v, $value, $error_name);
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
                } elseif ($this->misc_settings['persistent']['fields'][$k] === 'if_set' && empty($this->original_values[$k])) {
                    // we allow value change
                } elseif ($value !== $this->original_values[$k]) {
                    $this->error('danger', 'You are trying to change persistent field!', $error_name);
                }
            }
            $v['options']['error_name_no_field'] = $error_name;
            // default
            $default = null;
            if (array_key_exists('default', $v['options'])) {
                array_key_set($this->values, $v['options']['values_key'], $value);
                $default = $this->processDefaultValue($k, $v['options']['default'], null, $this->values, false, $changed_field, $v);
                if (!isset($value) && $this->canProcessDefaultValue($value, $v, $v['options']['default'])) {
                    $value = $default;
                } else {
                    $temp = array_key_get($this->values, $v['options']['values_key']);
                    if ($temp !== $value) {
                        $value = $temp;
                    }
                }
            }
            // json processing after default
            if ($v['options']['type'] === 'json') {
                $value = htmlspecialchars_decode($value ?? '');
                if ($value === 'null' || $value === '') {
                    $value = null;
                } elseif ($value === "''" || $value === '""') {
                    $value = null;
                }
            } elseif (is_string($value) && !empty($value)) {
                $value = htmlspecialchars_decode($value);
            }
            // if we need to reset
            if ($this->initiator_class == 'list' || $this->initiator_class == 'report') {
                if (!empty($v['options']['options_depends'])) {
                    foreach ($v['options']['options_depends'] as $v78) {
                        if (($this->misc_settings['__form_onchange_field_values_key'][0] ?? '') == $v78) {
                            $value = null;
                        }
                    }
                }
            }
            // put into values
            array_key_set($this->values, $v['options']['values_key'], $value);
            // options_model validation
            if (isset($value) && !empty($v['options']['options_model']) && empty($v['options']['options_manual_validation'])) {
                $this->checkOptionsModel($v['options'], $value, $error_name, array_merge($input, $this->values));
            }
            // options validation
            if (isset($value) && !empty($v['options']['options']) && empty($v['options']['options_manual_validation'])) {
                $temp_value = is_array($value) ? $value : [$value];
                foreach ($temp_value as $k54 => $v54) {
                    $key = is_scalar($v54) ? $v54 : $k54;
                    if (empty($v['options']['options'][$key])) {
                        $this->error('danger', Messages::INVALID_VALUES, $error_name);
                    }
                }
            }
            // id we need to refresh master object
            if (isset($this->master_options['refresh_if_set'])) {
                if (!is_array($this->master_options['refresh_if_set'])) {
                    $this->master_options['refresh_if_set'] = [$this->master_options['refresh_if_set']];
                }
                if (in_array($k, $this->master_options['refresh_if_set']) && (!empty($this->master_options['refresh_full_reload']) || !$this->master_object->isDataFound())) {
                    $this->master_object = \Factory::model($this->master_options['model'], true, [$this->values['__module_id'], $this->master_options['ledger'], & $this]);
                }
            }
            // file upload handling
            if (!empty($v['options']['documents_save']) && empty($options['for_load_values_only']) && !$this->hasErrors($k)) {
                // we need to validate
                $this->validateRequiredOneField($this->values[$k], $v['options']['error_name'], $v);
                $this->misc_settings['validateRequiredOneField'][$v['options']['error_name']] = true;
                // if all ok we need to upload files
                if (!empty($value) && !$this->hasErrors($k)) {
                    if (isset($value['name'])) {
                        $value = [$value];
                    }
                    $method = Resources::getStatic('save_documents', 'save_document_mass', 'method');
                    $files = \Factory::callMethod($method, false, [
                        & $this,
                        $v['options']['documents_save']['max_files'],
                        $value,
                        $v['options']['documents_save']['prefix'],
                        $v['options']['validator_params'],
                        '',
                        ['return_files' => true, 'file_upload_field_name' => $k, 'skip_is_uploaded_file' => true]
                    ]);
                    if ($files !== false && !$this->hasErrors($k)) {
                        $this->values = array_merge_hard($this->values, $files);
                    }
                }
            }
        }
        // check optimistic lock
        if ($this->values_loaded && $this->collection_object->primary_model->optimistic_lock && !in_array($this->initiator_class, ['report', 'list', 'import']) && empty($this->options['skip_optimistic_lock'])) {
            if (($this->values[$this->collection_object->primary_model->optimistic_lock_column] ?? '') !== $this->original_values[$this->collection_object->primary_model->optimistic_lock_column]) {
                $this->error('danger', Messages::OPTIMISTIC_LOCK);
            }
        }
        // we do not process details if only_columns is set
        if (!empty($options['only_columns'])) {
            goto processAllValues;
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
                $fields = $this->sortFieldsForProcessing($v['elements'], $v['options']);
                // if we have custom data processor
                if (!empty($v['options']['details_process_widget_data'])) {
                    $widget_model = \Factory::model($k, true);
                    $v['validate_required'] = $options['validate_required'] ?? false;
                    $this->values[$k] = $widget_model->formProcessWidgetData($this, [$k], $details, $this->values, $fields, $v);
                    continue;
                }
                // start processing of keys
                $detail_key_holder = [];
                $this->generateDetailsPrimaryKey($detail_key_holder, 'reset', $this->values, [$k], $v);
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
                            } elseif ($this->misc_settings['__form_onchange_field_values_key'][1] . '' == $k2 . '') {
                                $changed_field_details['detail'] = $this->misc_settings['__form_onchange_field_values_key'][2];
                            }
                        }
                    }
                    if (isset($changed_field_details['detail'])) {
                        $this->changed_field = $changed_field_details['detail'];
                        $this->changed_detail = $this->misc_settings['__form_onchange_field_values_key'];
                    }
                    // change detected
                    $flag_change_detected = false;
                    // put pk into detail
                    $detail = $detail_key_holder['parent_pks'];
                    // process json_contains
                    foreach ($fields as $k3 => $v3) {
                        if (empty($v3['options']['json_contains'])) {
                            continue;
                        }
                        // get value, grab from neighbouring values first
                        $value = $v2[$k3] ?? null;
                        if (is_json($value)) {
                            $value = json_decode($value, true);
                            foreach ($v3['options']['json_contains'] as $k31 => $v31) {
                                $v2[$v31] = $value[$k31] ?? null;
                            }
                        } elseif (empty($value)) {
                            foreach ($v3['options']['json_contains'] as $k31 => $v31) {
                                $detail[$v31] = $v2[$v31] = null;
                            }
                        }
                    }
                    // process pk
                    $this->generateDetailsPrimaryKey($detail_key_holder, 'pk', $v2, [$k], $v);
                    $error_name = $detail_key_holder['error_name'];
                    $k2 = $detail_key_holder['pk'];
                    // process fields
                    foreach ($fields as $k3 => $v3) {
                        // skip buttons and links
                        if (in_array(($v3['options']['method'] ?? ''), ['button', 'button2', 'submit', 'a'])) {
                            continue;
                        }
                        // default data type
                        if (empty($v3['options']['type'])) {
                            $v3['options']['type'] = 'varchar';
                        }
                        // get value, grab from neighbouring values first
                        $value = $detail[$k3] ?? $v2[$k3] ?? null;
                        // validate data type
                        if (!empty($v3['options']['multiple_column'])) {
                            if (!empty($value)) {
                                $value = $this->generateMultipleColumns($value, $error_name, $detail, [$k], $v3);
                            } else {
                                $value = null;
                            }
                        } else {
                            $temp = $this->validateDataTypesSingleValue($k3, $v3, $value, "{$error_name}[{$k3}]");
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
                            } elseif ($value !== $original_values) {
                                $this->error('danger', 'You are trying to change persistent field!', "{$error_name}[{$k3}]");
                            }
                        }
                        $v3['options']['error_name_no_field'] = $error_name;
                        // default
                        $default = null;
                        if (array_key_exists('default', $v3['options'])) {
                            $detail[$k3] = $value;
                            $default = $this->processDefaultValue($k3, $v3['options']['default'], null, $detail, false, $changed_field_details, $v3);
                            if (!isset($value) && $this->canProcessDefaultValue($value, $v3)) {
                                $value = $default;
                            } elseif ($detail[$k3] !== $value) {
                                $value = $detail[$k3];
                            }
                        }
                        // json processing after default
                        if ($v3['options']['type'] === 'json') {
                            $value = htmlspecialchars_decode($value ?? '');
                            if ($value === 'null' || $value === '') {
                                $value = null;
                            } elseif ($value === "''" || $value === '""') {
                                $value = null;
                            }
                        }
                        // see if we changed the value but not autoincrement
                        if (isset($value) && $value !== $default && !isset($autoincrement_details[$k3])) {
                            $flag_change_detected = true;
                        }
                        // options_model validation
                        if (isset($value) && !empty($v3['options']['options_model']) && empty($v3['options']['options_manual_validation'])) {
                            $this->checkOptionsModel($v3['options'], $value, "{$error_name}[{$k3}]", array_merge($v2, $detail));
                        }
                        // options validation
                        if (isset($value) && !empty($v3['options']['options']) && empty($v3['options']['options_manual_validation'])) {
                            $temp_value = is_array($value) ? $value : [$value];
                            foreach ($temp_value as $v54) {
                                if (empty($v3['options']['options'][$v54])) {
                                    $this->error('danger', Messages::INVALID_VALUES, "{$error_name}[{$k3}]");
                                }
                            }
                        }
                        // file upload handling
                        if (!empty($v3['options']['documents_save']) && empty($options['for_load_values_only']) && !$this->hasErrors("{$error_name}[{$k3}]")) {
                            // we need to validate
                            $this->validateRequiredOneField($value, "{$error_name}[{$k3}]", $v3);
                            $this->misc_settings['validateRequiredOneField']["{$error_name}[{$k3}]"] = true;
                            // if all ok we need to upload files
                            if (!empty($value) && !$this->hasErrors("{$error_name}[{$k3}]")) {
                                if (isset($value['name'])) {
                                    $value = [$value];
                                }
                                $method = Resources::getStatic('save_documents', 'save_document_mass', 'method');
                                $files = \Factory::callMethod($method, false, [
                                    & $this,
                                    $v3['options']['documents_save']['max_files'],
                                    $value,
                                    $v3['options']['documents_save']['prefix'],
                                    $v3['options']['validator_params'],
                                    '',
                                    ['return_files' => true, 'file_upload_field_name' => $k3, 'skip_is_uploaded_file' => true]
                                ]);
                                if ($files !== false && !$this->hasErrors("{$error_name}[{$k3}]")) {
                                    $detail = array_merge_hard($detail, $files);
                                }
                            }
                        }
                        // add field to details
                        $detail[$k3] = $value;
                    }
                    // process subdetails, first to detect change
                    if (!empty($v['subdetails'])) {
                        foreach ($v['subdetails'] as $k0 => $v0) {
                            // make empty array
                            $detail[$k0] = [];
                            // sort fields
                            $subdetail_fields = $this->sortFieldsForProcessing($v0['elements']);
                            // if we have custom data processor
                            if (!empty($v0['options']['details_process_widget_data'])) {
                                $widget_model = \Factory::model($k0, true);
                                $v0['validate_required'] = $options['validate_required'] ?? false;
                                $detail[$k0] = $widget_model->formProcessWidgetData($this, [$k, $k2, $k0], $v2[$k0] ?? [], $detail, $subdetail_fields, $v0);
                                // change detected
                                if (!empty($detail[$k0])) {
                                    $flag_change_detected = true;
                                }
                                continue;
                            }
                            // start processing of keys
                            $subdetail_key_holder = [];
                            $this->generateDetailsPrimaryKey($subdetail_key_holder, 'reset', $detail, [$k, $k2, $k0], $v0);
                            // go through data
                            if (!empty($v0['options']['details_11'])) {
                                $subdetail_data = [$v2[$k0] ?? []];
                            } else {
                                $subdetail_data = $v2[$k0] ?? [];
                            }
                            // handle auto increment
                            $autoincrement_subdetails = [];
                            if (!empty($v0['options']['details_autoincrement']) && empty($v0['options']['details_11'])) {
                                foreach ($v0['options']['details_autoincrement'] as $v72) {
                                    $autoincrement_subdetails[$v72] = 0;
                                }
                                // find maximum in new values
                                if (!empty($subdetail_data)) {
                                    foreach ($subdetail_data as $k71 => $v71) {
                                        foreach ($v0['options']['details_autoincrement'] as $v72) {
                                            if (!empty($v71[$v72]) && intval($v71[$v72]) > $autoincrement_subdetails[$v72]) {
                                                $autoincrement_subdetails[$v72] = $v71[$v72];
                                            }
                                        }
                                    }
                                }
                            }
                            if (!empty($subdetail_data)) {
                                foreach ($subdetail_data as $k5 => $v5) {
                                    $flag_subdetail_change_detected = false;
                                    // put pk into detail
                                    $subdetail = $subdetail_key_holder['parent_pks'];
                                    // process json_contains
                                    foreach ($subdetail_fields as $k30 => $v30) {
                                        if (empty($v30['options']['json_contains'])) {
                                            continue;
                                        }
                                        // get value, grab from neighbouring values first
                                        $value = $v5[$k30] ?? null;
                                        if (is_json($value)) {
                                            $value = json_decode($value, true);
                                            foreach ($v30['options']['json_contains'] as $k31 => $v31) {
                                                $v5[$v31] = $value[$k31] ?? null;
                                            }
                                        } elseif (empty($value)) {
                                            foreach ($v30['options']['json_contains'] as $k31 => $v31) {
                                                $subdetail[$v31] = $v5[$v31] = null;
                                            }
                                        }
                                    }
                                    // process pk
                                    $this->generateDetailsPrimaryKey($subdetail_key_holder, 'pk', $v5, [$k, $k2, $k0], $v0);
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
                                        $temp = $this->validateDataTypesSingleValue($k6, $v6, $value, "{$subdetail_error_name}[{$k6}]");
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
                                            } elseif ($value !== $original_values) {
                                                $this->error('danger', 'You are trying to change persistent field!', "{$subdetail_error_name}[{$k3}]");
                                            }
                                        }
                                        // default
                                        $default = null;
                                        if (array_key_exists('default', $v6['options'])) {
                                            $default = $this->processDefaultValue($k6, $v6['options']['default'], $value, $subdetail, false);
                                            if (strpos($v6['options']['default'] . '', 'static::') !== false || is_null($value)) {
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
                                        if (!empty($v0['options']['details_11'])) {
                                            $detail[$k0] = $subdetail;
                                        } else {
                                            // autoincrement
                                            if (!empty($autoincrement_subdetails)) {
                                                foreach ($autoincrement_subdetails as $k71 => $v71) {
                                                    if (empty($subdetail[$k71])) {
                                                        $subdetail[$k71] = $v71 + 1;
                                                        $autoincrement_subdetails[$k71]++;
                                                    }
                                                }
                                            }
                                            $detail[$k0][$k5] = $subdetail;
                                        }
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
            // we need to process not set details
            foreach ($input as $k => $v) {
                if (is_string($k) && $k[0] == '\\' && !in_array($k, array_keys($this->fields)) && !in_array($k, array_keys($this->detail_fields))) {
                    $this->values[$k] = $input[$k];
                }
            }
        }
        processAllValues:
                $this->triggerMethod('processAllValues');
        // debug
        //print_r2($this->values);
    }

    /**
     * Check options model
     *
     * @param array $field
     * @param mixed $value
     * @param string $error_name
     * @param array $neighbouring_values
     */
    private function checkOptionsModel(array $field, $value, string $error_name, array $neighbouring_values)
    {
        // we need to convert value
        if (!empty($field['json_contains'])) {
            $temp = [];
            foreach ($field['json_contains'] as $k31 => $v31) {
                $temp[$k31] = $neighbouring_values[$v31];
            }
            $value = Options::optionJsonFormatKey($temp);
        }
        if (is_string($value) && $value === '') {
            return;
        }
        if (is_array($value) && empty($value)) {
            return;
        }
        if (empty($field['options_params'])) {
            $field['options_params'] = [];
        }
        if (empty($field['options_options'])) {
            $field['options_options'] = [];
        }
        $field['options_options']['i18n'] = false;
        if (empty($field['options_depends'])) {
            $field['options_depends'] = [];
        }
        // options depends & params
        $options = [];
        $this->processParamsAndDepends($field['options_depends'], $neighbouring_values, $options, true);
        $this->processParamsAndDepends($field['options_params'], $neighbouring_values, $options, false);
        $field['options_params'] = array_merge_hard($field['options_params'], $field['options_depends']);
        // call override method
        if (!empty($this->wrapper_methods['processOptionsModels']['main'])) {
            $model = $this->wrapper_methods['processOptionsModels']['main'][0];
            $model->{$this->wrapper_methods['processOptionsModels']['main'][1]}($this, $field['name'], $field['details_key'] ?? null, $field['details_parent_key'] ?? null, $field['options_params'], $neighbouring_values, [/* todo: __detail_values */]);
        }
        // we do not need options for autocomplete
        if (strpos(($field['method'] ?? ''), 'autocomplete') === false) {
            $skip_values = [];
            $existing_values = $value;
            if (!empty($field['multiple_column'])) {
                $existing_values = array_extract_values_by_key($value, $field['multiple_column'], ['type' => $field['type']]);
            }
            $field['options_options']['include_null_filter'] = $field['include_null_filter'] ?? null;
            $field['options'] = Common::processOptions($field['options_model'], $this, $field['options_params'], $existing_values, $skip_values, $field['options_options']);
        } else {
            $field['options'] = [];
        }
        // check if we have values
        if (!is_array($value)) {
            $value = [$value];
        }
        foreach ($value as $k => $v) {
            if (!empty($field['multiple_column'])) {
                if (is_array($v[$field['multiple_column']])) {
                    $temp = current($v[$field['multiple_column']]);
                } else {
                    $temp = $v[$field['multiple_column']];
                }
            } else {
                $temp = $v;
            }
            if (empty($field['options'][$temp])) {
                $this->error('danger', Messages::INVALID_VALUE, $error_name, [
                    'replace' => [
                        '[value]' => $temp
                    ]
                ]);
            }
        }
    }

    /**
     * Validate required fields
     *
     * @param array $options
     */
    private function validateRequiredFields($options = [])
    {
        // sort fields
        $fields = $this->sortFieldsForProcessing($this->fields);
        // process fields
        foreach ($fields as $k => $v) {
            if (!empty($options['only_columns']) && !in_array($k, $options['only_columns'])) {
                continue;
            }
            // validate required
            $this->validateRequiredOneField($this->values[$k], $v['options']['error_name'], $v);
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
                $fields = $this->sortFieldsForProcessing($v['elements'], $v['options']);
                // process details one by one
                foreach ($details as $k2 => $v2) {
                    if (!empty($v['options']['details_11'])) {
                        $values_key = [$k];
                    } else {
                        $values_key = [$k, $k2];
                    }
                    foreach ($fields as $k3 => $v3) {
                        // 1 to 1
                        if (!empty($v['options']['details_11'])) {
                            $v3['options']['values_key'] = [$k, $k3];
                            $value = $v2[$k3] ?? null;
                            $this->validateRequiredOneField($value, "{$k}[{$k3}]", $v3);
                            // put value back into values
                            if ($value !== ($v2[$k3] ?? null)) {
                                $this->values[$k][$k3] = $value;
                            }
                        } else { // 1 to M
                            $v3['options']['values_key'] = [$k, $k2, $k3];
                            $value = $v2[$k3] ?? null;
                            $this->validateRequiredOneField($value, "{$k}[{$k2}][{$k3}]", $v3);
                            // put value back into values
                            if ($value !== ($v2[$k3] ?? null)) {
                                $this->values[$k][$k2][$k3] = $value;
                            }
                        }
                    }
                    // process subdetails
                    if (!empty($v['subdetails'])) {
                        foreach ($v['subdetails'] as $k3 => $v3) {
                            $subdetails = $v2[$k3] ?? [];
                            // sort fields
                            $subfields = $this->sortFieldsForProcessing($v3['elements'], $v3['options']);
                            // 1 to 1
                            if (!empty($v3['options']['details_11'])) {
                                $subdetails = [$subdetails];
                                $error_values_key = array_merge($values_key, [$k3]);
                            } else {
                                $error_values_key = array_merge($values_key, [$k3, 1]);
                            }
                            foreach ($subdetails as $k4 => $v4) {
                                // 1 to 1
                                if (!empty($v3['options']['details_11'])) {
                                    $values_key2 = array_merge($values_key, [$k3]);
                                } else {
                                    $values_key2 = array_merge($values_key, [$k3, $k4]);
                                }
                                foreach ($subfields as $k5 => $v5) {
                                    // 1 to 1
                                    if (!empty($v3['options']['details_11'])) {
                                        $v5['options']['values_key'] = array_merge($values_key2, [$k5]);
                                        $value = $v4[$k5] ?? null;
                                        $this->validateRequiredOneField($value, array_to_field($v5['options']['values_key']), $v5);
                                        // put value back into values
                                        if ($value !== ($v4[$k5] ?? null)) {
                                            array_key_set($this->values, $v3['options']['values_key'], $value);
                                        }
                                    } else { // 1 to M
                                        $v5['options']['values_key'] = array_merge($values_key2, [$k5]);
                                        $value = $v4[$k5] ?? null;
                                        $this->validateRequiredOneField($value, array_to_field($v5['options']['values_key']), $v5);
                                        // put value back into values
                                        if ($value !== ($v4[$k5] ?? null)) {
                                            array_key_set($this->values, $v3['options']['values_key'], $value);
                                        }
                                    }
                                }
                            }
                            // see if subdetail is required, we display
                            if (!empty($v3['options']['required']) && empty($subdetails)) {
                                // add error to pk
                                $counter = 1;
                                foreach ($v3['options']['details_pk'] as $v8) {
                                    if (empty($v3['elements'][$v8]['options']['row_link']) || $v3['elements'][$v8]['options']['row_link'] == $this::HIDDEN) {
                                        continue;
                                    }
                                    $this->error(DANGER, Messages::REQUIRED_FIELD, $this->parentKeysToErrorName(array_merge($error_values_key, [$v8])));
                                    $counter++;
                                }
                                // sometimes pk can be hidden, so we add error to two more
                                if ($counter == 1) {
                                    array_key_sort($v3['elements'], ['row_order' => SORT_ASC, 'order' => SORT_ASC]);
                                    foreach ($v3['elements'] as $k8 => $v8) {
                                        if (($v8['options']['required'] ?? '') . '' == '1' && !in_array($k8, $v3['options']['details_pk']) && $counter == 1) {
                                            $this->error(DANGER, Messages::REQUIRED_FIELD, $this->parentKeysToErrorName(array_merge($error_values_key, [$k8])));
                                            $counter++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                // see if detail is required, we display
                if (!empty($v['options']['required']) && empty($this->values[$k])) {
                    // add error to pk
                    $counter = 1;
                    foreach ($v['options']['details_pk'] as $v8) {
                        if (empty($v['elements'][$v8]['options']['row_link']) || $v['elements'][$v8]['options']['row_link'] == $this::HIDDEN) {
                            continue;
                        }
                        $this->error(DANGER, Messages::REQUIRED_FIELD, "{$k}[1][{$v8}]");
                        $counter++;
                    }
                    // sometimes pk can be hidden, so we add error to two more
                    if ($counter == 1) {
                        array_key_sort($v['elements'], ['row_order' => SORT_ASC, 'order' => SORT_ASC]);
                        foreach ($v['elements'] as $k8 => $v8) {
                            if (($v8['options']['required'] ?? '') . '' == '1' && !in_array($k8, $v['options']['details_pk']) && ($v8['options']['method'] ?? '') != 'hidden' && $counter == 1) {
                                $this->error(DANGER, Messages::REQUIRED_FIELD, "{$k}[1][{$k8}]");
                                $counter++;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Add input
     * @param array $input
     */
    public function addInput(array $input)
    {
        $this->options['input'] = $input;
        // if we are passing all fields that changed via API.
        if (!empty($input['__changed_api_fields'])) {
            $this->changed_api_fields = $input['__changed_api_fields'];
        }
    }

    /**
     * Process
     */
    public function process()
    {
        // reset
        $this->submitted = false;
        $this->refresh = false;
        $this->delete = false;
        $this->blank = false;
        $this->values_loaded = false;
        $this->values_saved = false;
        $this->values_deleted = false;
        $this->values_inserted = false;
        $this->values_updated = false;
        $this->values_no_changes = false;
        $this->transaction = false;
        $this->rollback = false;
        $this->list_rendered = false;
        $this->errorResetAll();
        // original input
        $this->original_input = $this->options['input'] ?? \Request::input();
        // preload collection, must be first
        if ($this->preloadCollectionObject() && !in_array($this->initiator_class, ['report', 'list'])) {
            // if we have relation
            if (!empty($this->collection_object->primary_model->relation['field']) && !in_array($this->collection_object->primary_model->relation['field'], $this->collection_object->primary_model->pk)) {
                $this->element($this::HIDDEN, $this::HIDDEN, $this->collection_object->primary_model->relation['field'], ['label_name' => 'Relation #', 'domain' => 'relation_id_sequence', 'persistent' => true]);
            }
            // optimistic lock
            if (!empty($this->collection_object->primary_model->optimistic_lock)) {
                $this->element($this::HIDDEN, $this::HIDDEN, $this->collection_object->primary_model->optimistic_lock_column, ['label_name' => 'Optimistic Lock', 'type' => 'text', 'null' => true, 'default' => null, 'method' => 'hidden', 'skip_during_export' => true]);
            }
        }
        // special field for
        if (in_array($this->initiator_class, ['report', 'list'])) {
            $this->element($this::HIDDEN, $this::HIDDEN, '__list_report_filter_loaded', ['label_name' => 'Filter Loader', 'type' => 'boolean', 'method' => 'hidden', 'preserved' => true]);
            $this->options['input']['__list_report_filter_loaded'] = 1;
            $this->element($this::HIDDEN, $this::HIDDEN, '__list_report_filter_skip_one_record_redirect', ['label_name' => 'Filter Skip One Record Redirect', 'type' => 'boolean', 'method' => 'hidden']);
        }
        // back link through __form_filter_id
        if (in_array($this->initiator_class, ['form'])) {
            $this->element($this::HIDDEN, $this::HIDDEN, '__form_filter_id', ['label_name' => 'Filter #', 'domain' => 'big_id', 'method' => 'hidden', 'preserved' => true]);
        }
        // module #
        $blank_reset_var = [];
        if ($this->collection_object->primary_model->module ?? false) {
            if (!empty(\Application::$controller) && empty($this->options['skip_acl'])) {
                $available_modules = \Application::$controller->getControllersModules();
                $module_id = \Application::$controller->module_id;
            } else {
                $available_modules = [];
                $module_id = $this->options['input'][$this->collection_object->primary_model->module_column] ?? null;
                if (!empty($module_id)) {
                    $available_modules[$module_id] = ['name' => $module_id];
                }
            }
            // reset of module #
            if (($this->options['input']['__form_onchange_field_values_key'] ?? null) == '__module_id') {
                $ajax_values = extract_keys(['__ajax', '__ajax_form_id'], $this->options['input']);
                $this->options['input'] = [];
                $this->options['input'][$this->collection_object->primary_model->module_column] = $this->options['input']['__module_id'] = $module_id;
                $this->options['input'] = array_merge_hard($this->options['input'], $ajax_values);
            }
            $blank_reset_var[$this->collection_object->primary_model->module_column] = $blank_reset_var['__module_id'] = $this->options['input'][$this->collection_object->primary_model->module_column] = $this->options['input']['__module_id'] = $module_id;
            // add container & elements
            $this->container('__module_container', [
                'default_row_type' => 'grid',
                'order' => -35000
            ]);
            $temp_row_class = "form_{$this->form_link}_form__module_container_row__module_id";
            if (!empty($this->options['hide_module_id'])) {
                $temp_row_class .= ' grid_row_hidden';
            }
            $this->element('__module_container', 'row', '__module_id', [
                'label_name' => 'Module / Ledger',
                'domain' => 'module_id',
                'null' => true,
                //'required' => true,
                //'default' => $module_id,
                'method' => 'select',
                'no_choose' => true,
                'options' => $available_modules,
                'onchange' => 'this.form.submit();',
                'skip_during_export' => true,
                'order' => 0,
                'row_class' => $temp_row_class,
                'order_for_defaults' => PHP_INT_MIN,
            ]);
            $this->element('__module_container', $this::HIDDEN, $this->collection_object->primary_model->module_column, [
                'label_name' => 'Module / Ledger',
                'domain' => 'module_id',
                'required' => true,
                //'default' => $module_id,
                'null' => true,
                'method' => 'hidden',
                'query_builder' => 'a.' . $this->collection_object->primary_model->module_column . ';=',
                'skip_during_export' => true,
                'order' => 0,
                'order_for_defaults' => PHP_INT_MIN + 1,
            ]);
            $this->element('__module_container', 'separator_1', '__separator__module_id', ['row_order' => 400, 'method' => 'separator', 'label_name' => '', 'percent' => 100, 'row_class' => $temp_row_class]);
            // master object
            if (!empty($this->form_parent->master_options['model'])) {
                $this->master_options = $this->form_parent->master_options;
                $module_id = $this->master_options['module_id'] ?? $module_id;
                $this->master_object = \Factory::model($this->master_options['model'], true, [$module_id ?? 0, $this->master_options['ledger'], & $this]);
            }
        }
        // fetch API data
        if (!empty($this->options['input']['__is_api_get']) || $this->initiator_class == 'email' || $this->initiator_class == 'SMS') {
            $this->getOriginalValues($this->options['input'] ?? [], false);
            $this->values = $this->original_values;
            // for email templates we pass additional fields as input
            if ($this->initiator_class == 'email' || $this->initiator_class == 'SMS') {
                $this->values = array_merge_hard($this->options['input'], $this->values);
                $this->triggerMethod('refresh');
            }
            goto convertMultipleColumns;
        }
        // preserve blank and validate through session
        $this->misc_settings['validate_through_session'] = [];
        $refetch_values_on_change = [];
        foreach ($this->fields as $k => $v) {
            if (!empty($v['options']['preserve_blank'])) {
                $blank_reset_var[$k] = $this->options['input'][$k] ?? null;
            }
            if (!empty($v['options']['refetch_values_on_change'])) {
                $refetch_values_on_change[] = $k;
            }
            if (!empty($v['options']['validate_through_session'])) {
                if (!empty($this->options['input'][$k])) {
                    $existing = \Session::get(['numbers', 'locks', 'form', $this->options['collection_link'] ?? $this->form_link, $k]) ?? [];
                    $value = concat_ws('::', $this->options['input'][$this->collection_object->primary_model->module_column] ?? null, $this->options['input'][$k]);
                    if (!in_array($value, $existing)) {
                        $this->error(DANGER, Messages::NO_MODIFICATION_ALLOWED);
                        $this->values = [];
                        $this->options['input'] = [];
                        return;
                    }
                }
                $this->misc_settings['validate_through_session'][$k] = [
                    'form' => $this->options['collection_link'] ?? $this->form_link,
                    'key' => $k,
                    'module_id' => $this->options['input'][$this->collection_object->primary_model->module_column] ?? null,
                    'value' => $this->options['input'][$k] ?? null,
                ];
            }
            if (!empty($v['options']['preserved'])) {
                $this->preserved_values[$k] = $this->options['input'][$k] ?? null;
                if ($v['options']['php_type'] == 'integer' && isset($this->preserved_values[$k])) {
                    $this->preserved_values[$k] = (int) $this->preserved_values[$k];
                }
            }
        }
        // if we have blank overrides
        if (!empty($this->options['__input_override_blanks'])) {
            $blank_reset_var = array_merge($blank_reset_var, $this->options['__input_override_blanks']);
        }
        // hidden buttons to handle form though javascript
        $this->element($this::HIDDEN, $this::HIDDEN, $this::BUTTON_SUBMIT_REFRESH, $this::BUTTON_SUBMIT_REFRESH_DATA);
        if (!isset($this->process_submit_all[$this::BUTTON_SUBMIT_BLANK])) {
            $this->element($this::HIDDEN, $this::HIDDEN, $this::BUTTON_SUBMIT_BLANK, $this::BUTTON_SUBMIT_BLANK_DATA);
        }
        // extra elements for list
        if ($this->initiator_class == 'list') {
            $this->element($this::HIDDEN, $this::HIDDEN, '__limit', ['label_name' => 'Limit', 'type' => 'integer', 'default' => $this->form_parent->list_options['default_limit'] ?? 30, 'method' => 'hidden']);
            $this->element($this::HIDDEN, $this::HIDDEN, '__offset', ['label_name' => 'Offset', 'type' => 'integer', 'default' => 0, 'method' => 'hidden']);
            $this->element($this::HIDDEN, $this::HIDDEN, '__preview', ['label_name' => 'Preview', 'type' => 'integer', 'default' => $this->form_parent->list_options['default_preview'] ?? 0, 'method' => 'hidden']);
            // default sort
            if (empty($this->options['input']['\Object\Form\Model\Dummy\Sort']) && !empty($this->form_parent->list_options['default_sort'])) {
                $this->options['input']['\Object\Form\Model\Dummy\Sort'] = [];
                foreach ($this->form_parent->list_options['default_sort'] as $k => $v) {
                    $this->options['input']['\Object\Form\Model\Dummy\Sort'][] = [
                        '__sort' => $k,
                        '__order' => $v
                    ];
                }
            }
        }
        // if we need to have list fields but not in a form
        if (!empty($this->options['include_list_fields'])) {
            $this->element($this::HIDDEN, $this::HIDDEN, '__limit', ['label_name' => 'Limit', 'type' => 'integer', 'null' => true, 'default' => $this->form_parent->list_options['default_limit'] ?? 30, 'method' => 'hidden']);
            $this->element($this::HIDDEN, $this::HIDDEN, '__offset', ['label_name' => 'Offset', 'type' => 'integer', 'null' => true, 'default' => 0, 'method' => 'hidden']);
            $this->element($this::HIDDEN, $this::HIDDEN, '__preview', ['label_name' => 'Preview', 'type' => 'integer', 'null' => true, 'default' => $this->form_parent->list_options['default_preview'] ?? 0, 'method' => 'hidden']);
            $this->element($this::HIDDEN, $this::HIDDEN, '__sort', ['label_name' => 'Sort', 'type' => 'text', 'null' => true, 'method' => 'hidden']);
        }
        // extra elements for report
        if ($this->initiator_class == 'report') {
            // default sort
            if (empty($this->options['input']['\Object\Form\Model\Dummy\Sort']) && !empty($this->form_parent->report_default_sort)) {
                $this->options['input']['\Object\Form\Model\Dummy\Sort'] = [];
                foreach ($this->form_parent->report_default_sort as $k => $v) {
                    $this->options['input']['\Object\Form\Model\Dummy\Sort'][] = [
                        '__sort' => $k,
                        '__order' => $v
                    ];
                }
            }
        }
        // error messages
        $html_messages = '';
        if (!empty(\Object\Error\Base::$errors)) {
            $html_messages = '<br/><hr/>' . print_r(\Object\Error\Base::$errors, true);
        }
        // ajax requests from other forms are filtered by id
        if (!empty($this->options['input']['__ajax'])) {
            $this->is_ajax_reload = true;
            // if we have a sub form
            if (!empty($this->options['input']['__subform_link']) && !empty($this->form_parent->subforms[$this->options['input']['__subform_link']])) {
                Ob::cleanAll();
                $input = \Request::input();
                unset($input['__ajax'], $input['__ajax_form_id']);
                $form_class = $this->form_parent->subforms[$this->options['input']['__subform_link']]['form'];
                $form_model = new $form_class(array_merge([
                    'input' => $input,
                    'parent_form_link' => $this->form_link,
                    'collection_link' => $input['__collection_link'] ?? '',
                    'collection_screen_link' => $input['__collection_screen_link'] ?? '',
                    'model_table' => $this->options['model_table'] ?? null,
                    'notification' => $this->options['notification'] ?? null,
                    'bypass_hidden_from_input' => $this->options['bypass_hidden_from_input'] ?? [],
                    'acl_subresource_edit' => $this->options['acl_subresource_edit'] ?? $this->options['__parent_options']['options']['acl_subresource_edit'] ?? null,
                    'flag_subform' => true,
                    //'plain_text_note' => $this->options['plain_text_note'] ?? false,
                ], $this->options['custom_tags'] ?? []));
                if (!empty($this->options['input']['__subform_load_window'])) {
                    $modal = \HTML::modal([
                        'id' => 'form_subform_' . $input['__subform_link'] . '_form',
                        'modal_class' => $form_model->options['modal_class'] ?? 'numbers_frontend_form_modal_level_1',
                        'class' => 'numbers_frontend_modal_full_width',
                        'title' => i18n(null, $this->form_parent->subforms[$this->options['input']['__subform_link']]['label_name']),
                        'body' => $form_model->render() . $html_messages,
                    ]);
                } else {
                    $modal = $form_model->render() . $html_messages;
                }
                $result = [
                    'success' => true,
                    'error' => [],
                    'html' => $modal,
                    'js' => \Layout::$onload,
                    'js_first' => \Layout::$onload_first,
                    'media_js' => \Layout::renderJs(['return_list' => true]),
                    'media_css' => \Layout::renderCss(['return_list' => true]),
                ];
                \Layout::renderAs($result, 'application/json');
            } elseif (!empty($this->options['input']['__ajax_custom_calculate_total'])) { // custom form recalculation
                do {
                    $result = [
                        'success' => false,
                        'error' => [],
                        'data' => [],
                        'return' => null
                    ];
                    $func = $this->options['input']['__ajax_custom_calculate_function'] ?? 'customCalculateTotal';
                    if (!method_exists($this->form_parent, $func)) {
                        $result['error'][] = i18n(null, 'Calculation method does not exists!');
                    }
                    $this->getAllValues($this->options['input']);
                    // assemble parameters
                    $params = [& $this];
                    $temp = json_decode($this->options['input']['__ajax_custom_calculate_params'] ?? '[]', true);
                    if (!empty($temp)) {
                        foreach ($temp as $v) {
                            $params[] = $v;
                        }
                    }
                    $result['return'] = call_user_func_array([$this->form_parent, $func], $params);
                    $this->formatAllValues($this->values);
                    $result['data'] = $this->formatted_values;
                    $result['success'] = true;
                } while (0);
                // todos
                \Layout::renderAs($result, 'application/json');
            } elseif (($this->options['input']['__ajax_form_id'] ?? '') == "form_{$this->form_link}_form") { // if its ajax call to this form
                // if its a call to auto complete
                /* todo
                if ($this->attributes && !empty($this->options['input']['__ajax_autocomplete']['rn_attrattr_id'])) {
                    return \Factory::model('numbers_data_relations_model_attribute_form', true)->autocomplete($this, $this->options['input']);
                } else if (!empty($this->options['input']['__ajax_autocomplete']['name'])
                    && !empty($this->fields[$this->options['input']['__ajax_autocomplete']['name']]['options']['method'])
                    && strpos($this->fields[$this->options['input']['__ajax_autocomplete']['name']]['options']['method'], 'autocomplete') !== false
                ) {
                    $options = $this->fields[$this->options['input']['__ajax_autocomplete']['name']]['options'];
                    $options['__ajax'] = true;
                    $options['__ajax_autocomplete'] = $this->options['input']['__ajax_autocomplete'];
                    $temp = explode('::', $this->fields[$this->options['input']['__ajax_autocomplete']['name']]['options']['method']);
                    if (count($temp) == 1) {
                        return \HTML::{$temp[0]}($options);
                    } else {
                        return \Factory::model($temp[0])->{$temp[1]}($options);
                    }
                }
                */
            } else {
                // load pk
                $this->loadPk($this->options['input']);
                // we need to set this flag so ajax calls can go through
                //$this->values_loaded = true;
                $this->flag_another_ajax_call = true;
                return;
            }
        }
        // collection refresh
        if (!empty($this->options['input']['__collection_refresh']) && $this->initiator_class == 'form' && (!empty($this->options['__parent_options']['flag_main_form'])) || !empty($this->options['flag_main_form'])) {
            $this->getOriginalValues($this->options['input'] ?? [], false);
            $this->values = $this->original_values;
            $this->triggerMethod('refresh');
            goto convertMultipleColumns;
        }
        // call from another form
        if (!empty($this->options['input']['__form_link']) && !($this->options['input']['__form_link'] == $this->form_link || $this->options['input']['__subform_link'] == $this->form_link)) {
            $this->refresh = true;
            $this->submitted = false;
            $this->options['skip_optimistic_lock'] = true;
            $this->options['flag_other_form_submitted'] = true;
            goto otherFormSubmitted;
        }
        // navigation
        if (!empty($this->options['input']['navigation'])) {
            $this->processNavigation($this->options['input']['navigation']);
        }
        // onchange fields
        $this->misc_settings['__form_onchange_field_values_key'] = null;
        $this->misc_settings['__form_field_changed'] = null;
        $this->misc_settings['__default_field_changed'] = [];
        if (!empty($this->options['input']['__form_onchange_field_values_key'])) {
            $this->misc_settings['__form_onchange_field_values_key'] = explode('[::]', $this->options['input']['__form_onchange_field_values_key']);
            $this->misc_settings['__form_field_changed'] = $this->misc_settings['__form_onchange_field_values_key'][0] ?? '';
        }
        // track previous values
        if (!empty($this->options['input']['__track_previous_values'])) {
            $this->tracked_values = $this->options['input']['__track_previous_values'];
        }
        // we need to refetch data
        if (!empty($refetch_values_on_change)) {
            foreach ($refetch_values_on_change as $v) {
                if (($this->options['input'][$v] ?? '') != ($this->tracked_values[$v] ?? '')) {
                    goto otherFormSubmitted;
                }
            }
        }
        // we need to see if form has been submitted
        $this->process_submit = [];
        if (isset($this->process_submit_all[$this::BUTTON_SUBMIT_BLANK]) && !empty($this->options['input'][$this::BUTTON_SUBMIT_BLANK])) {
            $this->blank = true;
            $this->process_submit = [
                $this::BUTTON_SUBMIT_BLANK => true
            ];
        } elseif (isset($this->process_submit_all[$this::BUTTON_SUBMIT_REFRESH]) && !empty($this->options['input'][$this::BUTTON_SUBMIT_REFRESH])) {
            $this->refresh = true;
            $this->process_submit = [
                $this::BUTTON_SUBMIT_REFRESH => true
            ];
        } else {
            foreach ($this->process_submit_all as $k => $v) {
                if (!empty($this->options['input'][$k])) {
                    $this->submitted = true;
                    $this->process_submit[$k] = true;
                }
            }
        }
        // find child submits
        foreach (new \RecursiveIteratorIterator(new \RecursiveArrayIterator($this->options['input'] ?? []), \RecursiveIteratorIterator::LEAVES_ONLY) as $k0 => $v0) {
            if ($k0 != $this::BUTTON_SUBMIT_REFRESH && isset($this->process_submit_all[$k0]) && !empty($v0)) {
                $this->submitted = true;
                $this->process_submit[$k0] = true;
            }
        }
        // if we delete
        if (!empty($this->process_submit[self::BUTTON_SUBMIT_DELETE])) {
            $this->delete = true;
        }
        // if we are blanking the form
        if ($this->blank) {
            $this->getAllValues($blank_reset_var);
            $this->triggerMethod('refresh');
            goto convertMultipleColumns;
        }
        otherFormSubmitted:
                // we need to start transaction
                if (!empty($this->collection_object) && empty($this->collection['skip_transaction']) && $this->submitted && !in_array($this->initiator_class, ['import', 'list', 'report'])) {
                    $this->collection_object->primary_model->db_object->begin();
                    $this->transaction = true;
                }
        // load original values
        $this->getOriginalValues($this->options['input'] ?? [], $this->transaction);
        // if we do not submit the form and have no values
        if (!$this->submitted && !$this->refresh) {
            if ($this->values_loaded) {
                goto loadValues;
            } else { // if we have no values its blank
                $this->blank = true;
                $this->getAllValues($this->options['input'] ?? []);
                $this->triggerMethod('refresh');
                goto convertMultipleColumns;
            }
        }
        // get all values
        $this->getAllValues($this->options['input'] ?? [], [
            'validate_required' => $this->submitted, // a must, used for widget data processing
            'validate_for_delete' => $this->delete
        ]);
        // validate submits
        if ($this->submitted) {
            if (!$this->validateSubmitButtons()) {
                goto processErrors;
            }
        }
        // handling form refresh
        $this->triggerMethod('refresh');
        // if we are refresing
        if ($this->process_submit_refresh) {
            goto convertMultipleColumns;
        }
        // validate required fields after refresh
        if ($this->submitted && !$this->delete) {
            $this->validateRequiredFields();
        }
        // other form submitted
        if (!empty($this->options['flag_other_form_submitted'])) {
            goto loadValues;
        }
        // convert columns on refresh
        if ($this->refresh) {
            goto convertMultipleColumns;
        }
        // if form has been submitted
        if ($this->submitted && ($this->initiator_class != 'list' || !empty($this->wrapper_methods['validate']))) {
            // call attached method to the form
            if (!$this->delete || !empty($this->options['validate_when_delete'])) {
                // create a snapshot of values for rollback
                $this->snapshot_values = $this->values;
                // execute validate method
                $this->triggerMethod('validate');
                if (method_exists($this, 'validate')) {
                    $this->validate($this);
                }
            }
            if ($this->initiator_class == 'list') {
                goto processErrors;
            }
            // save for regular forms
            if (!$this->hasErrors() && !empty($this->process_submit[$this::BUTTON_SUBMIT_SAVE])) {
                // if it is a report we would skip save
                if ($this->initiator_class == 'report') {
                    goto convertMultipleColumns;
                }
                // process save
                if (method_exists($this, 'save')) {
                    $this->values_saved = $this->save($this);
                } elseif (!empty($this->wrapper_methods['save'])) {
                    $this->values_saved = $this->triggerMethod('save');
                } elseif (!empty($this->collection_object)) {
                    // native save based on collection
                    if (empty($this->collection['readonly'])) {
                        $this->values_saved = $this->saveValues();
                    } else {
                        $this->values_saved = true;
                    }
                }
                // if we have post or success we need to change values_saved
                if ($this->values_no_changes) {
                    if (!empty($this->wrapper_methods['post']) || !empty($this->wrapper_methods['success'])) {
                        $this->values_saved = true;
                    }
                }
                // if save was successfull we post
                if (!$this->hasErrors()) {
                    // save data in sessions
                    if (!empty($this->misc_settings['validate_through_session'])) {
                        foreach ($this->misc_settings['validate_through_session'] as $k => $v) {
                            \Session::set(['numbers', 'locks', 'form', $v['form'], $v['key']], concat_ws('::', $v['module_id'], $this->values[$k]), ['append' => true, 'append_unique' => true]);
                        }
                    }
                    // trigger post method
                    $temp = $this->triggerMethod('post');
                }
                // rollback changes maid in validate method
                if ($this->hasErrors()) {
                    $this->values = $this->snapshot_values;
                    if (!$this->rollback) {
                        $this->values_saved = false;
                    }
                }
            }
        }
        // adding general error
        processErrors:
                if ($this->errors['flag_error_in_fields'] && empty($this->errors['general']['danger'])) {
                    $this->error('danger', Messages::SUBMISSION_PROBLEM);
                }
        if ($this->errors['flag_warning_in_fields']) {
            $this->error('warning', Messages::SUBMISSION_WARNING);
        }
        // close transaction
        $this->closeTransaction();
        // reindex errors and warnings when pk is a serial type
        if (!empty($this->new_serials) && !empty($this->errors['fields'])) {
            $intersect = array_intersect($this->collection_object->data['pk'], array_keys($this->new_serials));
            if (!empty($intersect) && count($intersect) == 1) {
                $serial_pk = $this->values[current($intersect)];
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
        if ($this->delete && $this->hasErrors()) {
            goto loadValues2;
        }
        loadValues:
                if (!$this->hasErrors()) {
                    if ($this->values_deleted) { // we need to provide default values
                        $this->values_loaded = false;
                        $this->original_values = [];
                        // we need to preserver module #
                        $this->getAllValues(['__module_id' => $this->options['input']['__module_id'] ?? null]);
                    } elseif ($this->values_saved) { // if saved we need to reload from database
                        $this->triggerMethod('success');
                        loadValues2:
                                        // skip readonly
                                        if (empty($this->collection_object->data['readonly'])) {
                                            $this->original_values = $this->values = $this->loadValues();
                                            // values override
                                            $this->triggerMethod('loadOriginalValues');
                                            $this->triggerMethod('loadValues');
                                        }
                        // we need to preserver module #
                        if (isset($this->options['input']['__module_id'])) {
                            $this->values['__module_id'] = (int) $this->options['input']['__module_id'];
                        }
                        // if we have extra variable that are not in database
                        $this->values_loaded = true;
                    } elseif ($this->values_loaded) { // otherwise set loaded values
                        $this->values = $this->original_values;
                        // we need to preserve module #
                        if (isset($this->options['input']['__module_id'])) {
                            $this->values['__module_id'] = $this->options['input']['__module_id'];
                        }
                        // if we are preserving columns during navigation
                        if (!empty($this->misc_settings['navigation']['preserve'])) {
                            $this->values = array_merge_hard($this->values, $this->misc_settings['navigation']['preserve']);
                        }
                        // trigger refresh
                        $this->getAllValues($this->values);
                        $this->values = array_merge_hard($this->values, $this->original_values);
                        $this->triggerMethod('refresh');
                    }
                }
        convertMultipleColumns:
                // close transaction
                $this->closeTransaction();
        // finilize
        $this->triggerMethod('finalize');
        // save and new and/or close
        if (!$this->hasErrors()) {
            // we need to redirect for certain buttons
            $mvc = \Application::get('mvc');
            // save and new
            if (!empty($this->process_submit[self::BUTTON_SUBMIT_SAVE_AND_NEW])) {
                $this->redirect($mvc['full'] . '?__module_id=' . ($this->values['__module_id'] ?? ''));
            }
            // save and close
            if (!empty($this->process_submit[self::BUTTON_SUBMIT_SAVE_AND_CLOSE])) {
                $this->redirect($mvc['controller'] . '/_Index' . '?__module_id=' . ($this->values['__module_id'] ?? ''));
            }
        }
        // convert multiple column to a form renderer can accept
        $this->convertMultipleColumns($this->values);
        // assuming save has been executed without errors we need to process on_success_js
        if (!$this->hasErrors() && !empty($this->options['on_success_js'])) {
            \Layout::onload($this->options['on_success_js']);
        }
        // add success messages
        if (!$this->hasErrors()) {
            if (isset($this->misc_settings['success_message_if_no_errors'])) {
                $this->error(SUCCESS, $this->misc_settings['success_message_if_no_errors']);
            } else {
                if (!empty($this->process_submit[self::BUTTON_SUBMIT_TEMPORARY_POST]) || !empty($this->process_submit[self::BUTTON_SUBMIT_POST])) {
                    if ($this->values_inserted || $this->values_updated) {
                        $this->error(SUCCESS, Messages::RECORD_POSTED);
                    }
                } else {
                    if ($this->values_deleted) {
                        $this->error(SUCCESS, Messages::RECORD_DELETED);
                    }
                    if ($this->values_inserted) {
                        $this->error(SUCCESS, Messages::RECORD_INSERTED);
                    }
                    if ($this->values_updated) {
                        $this->error(SUCCESS, Messages::RECORD_UPDATED);
                    }
                }
            }
        }
        // we need to hide buttons
        $this->validateSubmitButtons(['skip_validation' => true]);
        // query for list
        if ($this->initiator_class == 'list' && !$this->hasErrors() && ($this->submitted || (!$this->refresh && !$this->submitted))) {
            $this->list_rendered = true;
            // create query object
            if (!empty($this->form_parent->query_primary_model)) {
                $where = $this->form_parent->query_primary_parameters ?? [];
                foreach ($where as $k0 => $v0) {
                    if ($v0 == '::current_user_id::') {
                        $where[$k0] = \User::id();
                    }
                }
                $query_primary_model = new $this->form_parent->query_primary_model();
                // if we have periods
                if (isset($query_primary_model->periods) && $query_primary_model->periods['type'] != 'none') {
                    $reflector = new \ReflectionClass($query_primary_model);
                    $reflector->getShortName();
                    $period_year = \Request::input($query_primary_model->column_prefix . 'year') ?? date('Y');
                    $period_month = str_pad(date('m'), 2, '0', STR_PAD_LEFT);
                    $period_short_name = str_replace(['[table]', '[year]', '[month]'], [$reflector->getShortName(), $period_year, $period_month], $query_primary_model->periods['class']);
                    $ar_class = explode("\\", $this->form_parent->query_primary_model);
                    array_pop($ar_class);
                    array_push($ar_class, $period_short_name);
                    $new_class_name = implode('\\', $ar_class);
                    $query_primary_model = new $new_class_name();
                    $where = array_merge_hard($query_primary_model->filter, $where);
                }
                $this->query = $query_primary_model->queryBuilder([
                    'initiator' => 'list',
                    'skip_global_scope' => true,
                ])->select();
                if (!empty($where)) {
                    $this->query->whereMultiple('AND', $where);
                }
            }
            // add filter
            if (!empty($this->query)) {
                $this->processReportQueryFilter($this->query);
            }
            // if we are rendering not text/html we need to reset limit and offset
            if (($this->values['__format'] ?? 'text/html') != 'text/html') {
                $this->values['__limit'] = PHP_INT_MAX;
                $this->values['__offset'] = 0;
            }
            // execute custom query processor
            $result = $this->triggerMethod('listQuery');
            if (is_array($result) && !empty($result['success'])) {
                $this->misc_settings['list']['total'] = $result['total'];
                $this->misc_settings['list']['num_rows'] = count($result['rows']);
                $this->misc_settings['list']['rows'] = $result['rows'];
            } elseif (!empty($this->query)) { // when we need to query
                // query #1 get counter
                $counter_query = clone $this->query;
                $counter_query->columns(['counter' => 'COUNT(*)'], ['empty_existing' => true]);
                $temp = $counter_query->query();
                $this->misc_settings['list']['total'] = $temp['rows'][0]['counter'];
                // query #2 get rows
                $this->processListQueryOrderBy();
                $this->query->offset($this->values['__offset'] ?? 0);
                $this->query->limit($this->values['__limit']);
                $temp = $this->query->query();
                $this->misc_settings['list']['num_rows'] = count($temp['rows']);
                $this->misc_settings['list']['rows'] = & $temp['rows'];
                // fix subquery columns
                if (!empty($this->misc_settings['list']['subquery'])) {
                    foreach ($this->misc_settings['list']['rows'] as $k => $v) {
                        foreach ($this->misc_settings['list']['subquery'] as $k2 => $v2) {
                            if (!empty($v[$k2])) {
                                // todo: maybe add type casting
                                $this->misc_settings['list']['rows'][$k][$k2] = explode($v2['delimiter'], $v[$k2]);
                            }
                        }
                    }
                }
            }
            $this->misc_settings['list']['limit'] = $this->values['__limit'] ?? 0;
            $this->misc_settings['list']['offset'] = $this->values['__offset'] ?? 0;
            $this->misc_settings['list']['preview'] = $this->values['__preview'] ?? $this->form_parent->list_options['default_preview'] ?? 0;
            $this->misc_settings['list']['preview_as_line'] = !empty($this->data[$this::LIST_LINE_CONTAINER]['rows']);
            // line preview has different container
            if ($this->misc_settings['list']['preview'] == 2) {
                $this->misc_settings['list']['columns'] = $this->data[$this::LIST_LINE_CONTAINER]['rows'] ?? [];
            } else {
                $this->misc_settings['list']['columns'] = $this->data[$this::LIST_CONTAINER]['rows'] ?? [];
            }
            $this->misc_settings['list']['full_text_search'] = $this->values['full_text_search'] ?? null;
            $this->misc_settings['list']['full_text_search2'] = $this->values['full_text_search2'] ?? null;
            $this->misc_settings['list']['full_text_search3'] = $this->values['full_text_search3'] ?? null;
            // save filter id
            $this->misc_settings['list']['__form_filter_id'] = $this->triggerMethod('filterChanged');
        }
        // usage for list
        if ($this->initiator_class == 'list') {
            if (!isset($this->options['__parent_options'])) {
                \Application::$controller->addUsageAction('list_opened', [
                    'replace' => [
                        '[list_name]' => $this->title,
                    ],
                    'affected_rows' => $this->misc_settings['list']['num_rows'] ?? 0,
                    'error_rows' => $this->errors['flag_num_errors'],
                    'url' => \Application::get('mvc.full') . '?__form_filter_id=' . ($this->misc_settings['list']['__form_filter_id'] ?? null),
                ]);
            } else {
                \Application::$controller->addUsageAction('list_opened', [
                    'replace' => [
                        '[list_name]' => $this->title,
                    ],
                    'affected_rows' => $this->misc_settings['list']['num_rows'] ?? 0,
                    'error_rows' => $this->errors['flag_num_errors'],
                    'history' => false,
                ]);
            }
            \Log::add([
                'type' => 'List',
                'only_channel' => 'default',
                'message' => 'List opened!',
                'other' => 'Title: ' . $this->title,
                'affected_rows' => $this->misc_settings['list']['num_rows'] ?? 0,
                'error_rows' => $this->errors['flag_num_errors']
            ]);
        }
        // usage for report
        if ($this->initiator_class == 'report') {
            if (empty($this->options['parent_form_link'])) {
                \Application::$controller->addUsageAction('report_opened', [
                    'replace' => [
                        '[report_name]' => $this->title,
                    ],
                    'affected_rows' => $this->misc_settings['report']['num_rows'] ?? 0,
                    'error_rows' => $this->errors['flag_num_errors'],
                    'url' => \Application::get('mvc.full') . '?__form_filter_id=' . $this->triggerMethod('filterChanged'),
                ]);
            } else {
                \Application::$controller->addUsageAction('report_opened', [
                    'replace' => [
                        '[report_name]' => $this->title,
                    ],
                    'affected_rows' => $this->misc_settings['report']['num_rows'] ?? 0,
                    'error_rows' => $this->errors['flag_num_errors'],
                    'history' => false,
                ]);
            }
            \Log::add([
                'type' => 'Report',
                'only_channel' => 'default',
                'message' => 'Report opened!',
                'other' => 'Title: ' . $this->title,
                'affected_rows' => $this->misc_settings['report']['num_rows'] ?? 0,
                'error_rows' => $this->errors['flag_num_errors'],
            ]);
        }
        // report, filter form must be submitted
        if ($this->initiator_class == 'report' && !$this->hasErrors() && $this->submitted) {
            $result = $this->triggerMethod('buildReport');
            if (!is_a($result, 'Object\Form\Builder\Report')) {
                throw new \Exception('buildReport method should return Object\Form\Builder\Report object!');
            }
            // render report
            $format = $this->values['__format'] ?? 'text/html';
            $content_types_model = new Types();
            $content_types = $content_types_model->get();
            if (empty($content_types[$format])) {
                $format = 'text/html';
            }
            $model = new $content_types[$format]['no_report_content_type_model']();
            $report_html = $model->render($result);
            // if report did not exited means we have html
            $this->container('__report_builder_container', [
                'default_row_type' => 'grid',
                'order' => PHP_INT_MAX,
                '__html' => & $report_html
            ]);
        }
        // usage for form
        if ($this->initiator_class == 'form' || !empty($this->options['__parent_options']['flag_main_form'])) {
            $refresh_params = [];
            if ($this->values_loaded) {
                $refresh_params = $this->pk;
                // remove tenant
                if (!empty($this->collection_object->primary_model->tenant)) {
                    unset($refresh_params[$this->collection_object->primary_model->tenant_column]);
                }
            }
            if (!empty($this->values['__form_filter_id'])) {
                $refresh_params['__form_filter_id'] = $this->values['__form_filter_id'];
            }
            \Application::$controller->addUsageAction('form_opened', [
                'replace' => [
                    '[form_name]' => $this->title,
                ],
                'affected_rows' => 1,
                'error_rows' => $this->errors['flag_num_errors'],
                'url' => \Application::get('mvc.full') . '?' . http_build_query($refresh_params),
            ]);
            \Log::add([
                'type' => 'Form',
                'only_channel' => 'default',
                'message' => 'Form opened!',
                'other' => 'Title: ' . $this->title,
                'affected_rows' => 1,
                'error_rows' => $this->errors['flag_num_errors'],
                'trace' => $this->hasErrors() ? \Object\Error\Base::debugBacktraceString(null, ['skip_params' => true]) : null,
                'form_statistics' => $this->apiResult(),
            ]);
        }
        // process all values
        $this->triggerMethod('processAllValues');
        $this->triggerMethod('redirect');
        // debug
        //print_r2($this->errors);
        //print_r2($this->values);
    }

    /**
     * Process list query order by clause
     *
     * @param bool $set_query
     * @return array
     */
    public function processListQueryOrderBy(bool $set_query = true): array
    {
        $result = [];
        if (!empty($this->values['\Object\Form\Model\Dummy\Sort'])) {
            foreach ($this->values['\Object\Form\Model\Dummy\Sort'] as $k => $v) {
                if (!empty($v['__sort'])) {
                    $name = $this->detail_fields['\Object\Form\Model\Dummy\Sort']['elements']['__sort']['options']['options'][$v['__sort']]['name'];
                    $this->misc_settings['list']['sort'][$name] = $v['__order'];
                    if ($set_query) {
                        $this->query->orderby([$v['__sort'] => $v['__order']]);
                    }
                    $result[$v['__sort']] = $v['__order'];
                }
            }
        }
        return $result;
    }

    /**
     * Process list query order by clause
     */
    public function processReportQueryOrderBy(& $query)
    {
        if (!empty($this->values['\Object\Form\Model\Dummy\Sort'])) {
            foreach ($this->values['\Object\Form\Model\Dummy\Sort'] as $k => $v) {
                if (!empty($v['__sort'])) {
                    $name = $this->detail_fields['\Object\Form\Model\Dummy\Sort']['elements']['__sort']['options']['options'][$v['__sort']]['name'];
                    $this->misc_settings['list']['sort'][$name] = $v['__order'];
                    $query->orderby([$v['__sort'] => $v['__order']]);
                }
            }
        }
    }

    /**
     * Process report query filter
     *
     * @param object $query
     */
    public function processReportQueryFilter(& $query, array $options = [])
    {
        $where = [];
        foreach ($this->fields as $k => $v) {
            // filter by value
            if (!empty($v['options']['query_builder']) && isset($this->values[$k])) {
                if (is_array($this->values[$k]) && empty($this->values[$k])) {
                    continue;
                }
                if (strpos($k, '_module_id') !== false && !empty($options['skip_module'])) {
                    continue;
                }
                $where[$v['options']['query_builder']] = $this->values[$k];
            }
            // subquery - fetch data
            if (!empty($v['options']['subquery'])) {
                $on = [];
                foreach ($v['options']['subquery']['on'] as $v2) {
                    $v2[3] = true;
                    $on[] = ['AND', $v2, false];
                }
                $query->join('LEFT', function (& $query) use ($v) {
                    $model = new $v['options']['subquery']['model']();
                    $query = $model->queryBuilder(['skip_acl' => true, 'alias' => $v['options']['subquery']['alias'] . '_inner'])->select();
                    $columns = [];
                    if (is_array($v['options']['subquery']['groupby'])) {
                        $columns = $v['options']['subquery']['groupby'];
                    } else {
                        $columns[] = $v['options']['subquery']['groupby'];
                    }
                    $columns[$v['name']] = $query->db_object->sqlHelper('string_agg', ['expression' => $query->db_object->cast($v['name'], 'varchar'), 'delimiter' => ';;']);
                    $query->columns($columns);
                    $query->groupby(is_array($v['options']['subquery']['groupby']) ? $v['options']['subquery']['groupby'] : [$v['options']['subquery']['groupby']]);
                }, $v['options']['subquery']['alias'], 'ON', $on);
                // special constants to fix
                $this->misc_settings['list']['subquery'][$v['name']] = ['delimiter' => ';;'];
            }
            // subquery - filter
            if (!empty($v['options']['subquery_builder'])) {
                if (is_array($this->values[$k]) && empty($this->values[$k])) {
                    continue;
                }
                $on = [];
                foreach ($v['options']['subquery_builder']['on'] as $v2) {
                    $v2[3] = true;
                    $on[] = $v2;
                }
                $values = $this->values[$k];
                $query->where('AND', function (& $query) use ($values, $on, $v) {
                    $model = new $v['options']['subquery_builder']['model']();
                    $query = $model->queryBuilder(['alias' => $v['options']['subquery_builder']['alias']])->select();
                    $query->columns(1);
                    foreach ($on as $v2) {
                        $query->where('AND', $v2);
                    }
                    $query->where('AND', [$v['options']['subquery_builder']['column'], '=', $values, false]);
                }, 'EXISTS');
            }
        }
        if (isset($this->values['full_text_search'])) {
            $where['full_text_search;FTS'] = [
                'fields' => $this->fields['full_text_search']['options']['full_text_search_columns'],
                'str' => $this->values['full_text_search']
            ];
        }
        if (!empty($where)) {
            $query->whereMultiple('AND', $where);
        }
    }

    /**
     * Close transaction
     */
    public function closeTransaction()
    {
        if ($this->transaction) {
            if (!empty($this->options['commit_on_error'])) { // we commit if error occurs
                $this->collection_object->primary_model->db_object->commit();
            } elseif ($this->values_saved) { // we commit
                $this->collection_object->primary_model->db_object->commit();
            } elseif (!$this->rollback) {
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
    private function processNavigation($navigation)
    {
        do {
            $column = key($navigation);
            if (empty($this->fields[$column]['options']['navigation'])) {
                break;
            }
            $navigation_type = key($navigation[$column]);
            if (empty($navigation_type) || !in_array($navigation_type, ['first', 'previous', 'refresh', 'next', 'last'])) {
                break;
            }
            // we need to process columns
            $navigation_columns = [$column];
            $navigation_depends = [];
            $depends = [];
            if (is_array($this->fields[$column]['options']['navigation'])) {
                if (!empty($this->fields[$column]['options']['navigation']['depends'])) {
                    foreach ($this->fields[$column]['options']['navigation']['depends'] as $k => $v) {
                        if (is_numeric($k)) {
                            $navigation_columns[] = $v;
                            $navigation_depends[] = $v;
                        } else {
                            $depends[$k] = $v;
                        }
                    }
                }
            }
            // get all values
            $this->getAllValues($this->options['input'] ?? [], [
                'only_columns' => $navigation_columns
            ]);
            // if we have errors we need to refresh
            if ($this->hasErrors()) {
                $this->errorResetAll();
                $this->options['input'][$this::BUTTON_SUBMIT_REFRESH] = true;
                break;
            }
            foreach ($navigation_depends as $v) {
                $depends[$v] = $this->values[$v];
            }
            $model = new Navigation();
            $result = $model->get([
                'where' => [
                    'model' => $this->collection['model'],
                    'type' => $navigation_type,
                    'column' => $column,
                    'pk' => $this->collection_object->data['pk'],
                    'value' => $this->values[$column] ?? null,
                    'depends' => $depends,
                    'acl_datasource' => $this->collection_object->data['acl_datasource'] ?? null,
                    'acl_parameters' => $this->collection_object->data['acl_parameters'] ?? null,
                ]
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
                $this->options['input'] = array_merge_hard($this->options['input'], $result[0]);
            } else {
                if ($navigation_type == 'refresh') {
                    $this->error('danger', Messages::RECORD_NOT_FOUND, $column);
                } else {
                    $this->error('danger', Messages::PREV_OR_NEXT_RECORD_NOT_FOUND, $column);
                }
                $this->options['input'][$this::BUTTON_SUBMIT_REFRESH] = true;
            }
        } while (0);
    }

    /**
     * Convert multiple columns
     */
    private function convertMultipleColumns(& $values)
    {
        // regular fields
        foreach ($this->fields as $k => $v) {
            if (!empty($v['options']['multiple_column'])) {
                if (!empty($values[$k])) {
                    $values[$k] = array_extract_values_by_key($values[$k], $v['options']['multiple_column'], ['type' => $v['options']['type']]);
                }
            }
        }
        // details
        foreach ($this->detail_fields as $k => $v) {
            if (empty($values[$k]) || !is_array($values[$k])) {
                continue;
            }
            if (!empty($v['options']['details_convert_multiple_columns'])) {
                $widget_model = \Factory::model($k, true);
                $widget_model->convertMultipleColumns($this, $values[$k]);
            } elseif (!empty($values[$k])) { // convert fields
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
                            $widget_model = \Factory::model($k0, true);
                            $widget_model->convertMultipleColumns($this, $values[$k][$k11][$k0]);
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
    public function validateSubmitButtons($options = [])
    {
        $buttons_found = [];
        $names = [];
        foreach ($this->data as $k => $v) {
            foreach (($v['rows'] ?? []) as $k2 => $v2) {
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
        if (!empty($this->buttons_model)) {
            // make a call to master object
            $result = $this->buttons_model->processButtons($this, [
                'skip_validation' => $options['skip_validation'] ?? false
            ]);
            $not_allowed = $result['not_allowed'];
            $also_set_save = $result['also_set_save'];
            $all_standard_buttons = $result['all_buttons'];
        } else { // standard buttons
            $all_standard_buttons = [
                self::BUTTON_SUBMIT,
                self::BUTTON_SUBMIT_SAVE,
                self::BUTTON_SUBMIT_SAVE_AND_NEW,
                self::BUTTON_SUBMIT_SAVE_AND_CLOSE,
                self::BUTTON_SUBMIT_RESET,
                self::BUTTON_SUBMIT_DELETE,
                self::BUTTON_CONTINUE,
                self::BUTTON_STOP,
                self::BUTTON_SUBMIT_OTHER,
                self::BUTTON_SUBMIT_GENERATE
            ];
            // process
            $not_allowed = [];
            $show_save_buttons = false;
            if (!empty($this->options['acl_subresource_edit'])) {
                // remove delete buttons if we do not have loaded values or do not have permission
                if (!$this->values_loaded || (empty($this->options['skip_acl']) && !$this->tempProcessACLSubresources($this->options['acl_subresource_edit'], 'Record_Delete'))) {
                    $not_allowed[] = self::BUTTON_SUBMIT_DELETE;
                }
                // we need to check permissions
                if (!$this->values_loaded && (empty($this->options['skip_acl']) && $this->tempProcessACLSubresources($this->options['acl_subresource_edit'], 'Record_New'))) {
                    $show_save_buttons = true;
                }
                if ($this->values_loaded && (empty($this->options['skip_acl']) && $this->tempProcessACLSubresources($this->options['acl_subresource_edit'], 'Record_Edit'))) {
                    $show_save_buttons = true;
                }
            } elseif (!empty($this->options['flag_subform'])) {
                // remove delete buttons if we do not have loaded values or do not have permission
                if (!$this->values_loaded || (empty($this->options['skip_acl']) && !\Application::$controller->can('Record_Delete', 'Edit'))) {
                    $not_allowed[] = self::BUTTON_SUBMIT_DELETE;
                }
                // we need to check permissions
                //				if ((empty($this->options['skip_acl']) && \Application::$controller->can('Record_New', 'Edit'))) {
                //					$show_save_buttons = true;
                //				}
                //				if ((empty($this->options['skip_acl']) && \Application::$controller->can('Record_Edit', 'Edit'))) {
                //					$show_save_buttons = true;
                //				}
                $show_save_buttons = true;
            } else {
                // remove delete buttons if we do not have loaded values or do not have permission
                if (!$this->values_loaded || (empty($this->options['skip_acl']) && !\Application::$controller->can('Record_Delete', 'Edit'))) {
                    $not_allowed[] = self::BUTTON_SUBMIT_DELETE;
                }
                // we need to check permissions
                if (!$this->values_loaded && (empty($this->options['skip_acl']) && \Application::$controller->can('Record_New', 'Edit'))) {
                    $show_save_buttons = true;
                }
                if ($this->values_loaded && (empty($this->options['skip_acl']) && \Application::$controller->can('Record_Edit', 'Edit'))) {
                    $show_save_buttons = true;
                }
            }
            if (!$show_save_buttons && empty($this->options['skip_acl'])) {
                $not_allowed[] = self::BUTTON_SUBMIT_SAVE;
                $not_allowed[] = self::BUTTON_SUBMIT_SAVE_AND_NEW;
                $not_allowed[] = self::BUTTON_SUBMIT_SAVE_AND_CLOSE;
                // if we need to make form readonly
                if (!empty($this->options['readonly_if_cannot_edit'])) {
                    $this->readonly();
                }
            }
            // these buttons are considered save
            $also_set_save = [
                self::BUTTON_SUBMIT,
                self::BUTTON_SUBMIT_SAVE_AND_NEW,
                self::BUTTON_SUBMIT_SAVE_AND_CLOSE,
                self::BUTTON_SUBMIT_DELETE,
                self::BUTTON_SUBMIT_OTHER_DELETE,
                self::BUTTON_CONTINUE,
                self::BUTTON_STOP,
                self::BUTTON_SUBMIT_GENERATE
            ];
        }
        // validate if we have that button
        $result = true;
        foreach ($buttons_found as $k => $v) {
            if (empty($this->process_submit[$k])) {
                unset($this->process_submit[$k]);
            } elseif (empty($buttons_found[$k]) || (!in_array($k, $all_standard_buttons) && empty($this->process_submit_other[$k])) || in_array($k, $not_allowed)) {
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
                        if (\Application::get('flag.numbers.frontend.html.form.show_field_settings')) {
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
        if ($this->submitted) {
            $this->misc_settings['__original_submitted'] = true;
        }
        $this->submitted = !empty($this->process_submit);
        // fix for save
        foreach ($also_set_save as $v) {
            if (!empty($this->process_submit[$v])) {
                $this->process_submit[self::BUTTON_SUBMIT_SAVE] = true;
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
    public function errorInTabs($counters)
    {
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
    final public function validateDataTypesSingleValue($k, $v, $in_value, $error_field = null)
    {
        // we set error field as main key
        if (empty($error_field)) {
            $error_field = $k;
        }
        $result = Columns::validateSingleColumn($k, $v['options'], $in_value, ['process_domains' => true]);
        if (!$result['success']) {
            $this->error('danger', $result['error'], $error_field, ['skip_i18n' => true]);
        }
        return $result['data'];
    }

    /**
     * Save values to database
     *
     * @return boolean
     */
    final public function saveValues()
    {
        // double check if we have collection object
        if (empty($this->collection_object)) {
            throw new \Exception('You must provide collection object!');
        }
        $options = [
            'flag_delete_row' => $this->process_submit[self::BUTTON_SUBMIT_DELETE] ?? false,
            'skip_type_validation' => true,
            'skip_optimistic_lock' => $this->options['skip_optimistic_lock'] ?? false,
            'form_class' => $this->form_class,
            'max_records' => $this->misc_settings['max_records'] ?? [],
        ];
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
            // show warnings
            if (!empty($result['warning'])) {
                foreach ($result['warning'] as $v) {
                    $this->error('warning', $v);
                }
            }
            $this->rollback = true;
            return false;
        } else { // if success
            // show warnings
            if (!empty($result['warning']) && empty($this->options['skip_wannings'])) {
                foreach ($result['warning'] as $v) {
                    $this->error('warning', $v);
                }
            }
            // success messages
            if (!empty($result['deleted'])) { // deleted
                $this->values_deleted = true;
            } elseif ($result['inserted']) { // inseted
                $this->values_inserted = true;
                // we must put serial columns back into values
                if (!empty($result['new_serials'])) {
                    $this->new_serials = $result['new_serials'];
                    $this->values = array_merge_hard($this->values, $result['new_serials']);
                    $this->loadPk($this->values);
                } elseif (!empty($result['new_pk'])) {
                    $this->values = array_merge_hard($this->values, $result['new_pk']);
                    $this->loadPk($this->values);
                }
            } elseif (!empty($result['updated'])) { // updated
                $this->values_updated = true;
                // merge updated pk
                $this->pk = array_merge_hard($this->pk, $result['new_pk']);
            } else { // if no update/insert/delete we rollback
                $this->values_no_changes = true;
                return false;
            }
            return true;
        }
    }

    /**
     * Pre load collection object
     *
     * @return boolean
     */
    final public function preloadCollectionObject()
    {
        if (empty($this->collection)) {
            return false;
        }
        if (empty($this->collection_object)) {
            // handling acl_subresource
            if (!empty($this->collection['details'])) {
                $this->tempAclSubresourceUnsetFromCollection($this->collection['details']);
            }
            if (empty($this->options['skip_db_object'])) {
                $this->collection_object = Collection::collectionToModel($this->collection);
                if (empty($this->collection_object)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Unset collection sub-resources based on acl
     *
     * @param array $details
     */
    private function tempAclSubresourceUnsetFromCollection(array & $details)
    {
        foreach ($details as $k0 => $v0) {
            if (!empty($v0['acl_subresource'])) {
                if (!is_array($v0['acl_subresource'])) {
                    $v0['acl_subresource'] = [$v0['acl_subresource']];
                }
                $found = true;
                foreach ($v0['acl_subresource'] as $v) {
                    if (!\Application::$controller->canSubresourceCached($v, 'Record_View')) {
                        $found = false;
                        break;
                    }
                }
                if (!$found) {
                    unset($details[$k0]);
                    continue;
                }
            }
            // if we have more details
            if (!empty($v0['details'])) {
                $this->tempAclSubresourceUnsetFromCollection($details[$k0]['details']);
            }
        }
    }

    /**
     * Update collection object
     */
    final public function updateCollectionObject()
    {
        if (!empty($this->collection_object) && !empty($this->collection)) {
            $this->collection_object->data = array_merge_hard($this->collection_object->data, $this->collection);
        }
    }

    /**
     * Load primary key from values
     */
    final public function loadPk(& $values)
    {
        $this->pk = [];
        $this->full_pk = true;
        if (!empty($this->collection_object)) {
            foreach ($this->collection_object->data['pk'] as $v) {
                // inject tenant
                if (!empty($this->collection_object->primary_model->tenant) && $v == $this->collection_object->primary_model->tenant_column) {
                    if (!isset($values[$this->collection_object->primary_model->tenant_column])) {
                        $values[$this->collection_object->primary_model->tenant_column] = \Tenant::id();
                    }
                }
                if (isset($values[$v])) {
                    $temp = Columns::processSingleColumnType($v, $this->collection_object->primary_model->columns[$v], $values[$v]);
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
    final public function loadValues($for_update = false)
    {
        if ($this->full_pk) {
            // temporary disable for update flag
            // todo check if we have acl
            $for_update = false;
            // get all values
            $this->getAllValues($this->options['input'] ?? [], ['for_load_values_only' => true]);
            // load using collection
            $result = $this->collection_object->get([
                'where' => $this->pk,
                'single_row' => true,
                'for_update' => $for_update,
                'all_values' => $this->values,
            ]);
            if ($result['success']) {
                $this->misc_settings['max_records'] = $result['max_records'];
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
     *		array replace
     *		boolean unique_options_hash
     *		boolean postponed
     */
    public function error($type, $message, $field = null, $options = [])
    {
        // if its an array of message we process them one by one
        if (is_array($message) && !is_loc($message)) {
            foreach ($message as $v) {
                $this->error($type, $v, $field, $options);
            }
            return;
        }
        // generate hash
        if (!empty($options['unique_options_hash'])) {
            $hash = sha1(serialize($message) . serialize($options));
        } else {
            $hash = sha1(serialize($message));
        }
        // i18n
        if (empty($options['skip_i18n'])) {
            if (is_loc($message)) {
                $message = loc($message, '', $options);
            } else {
                $message = i18n(null, $message, $options);
            }
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
                            if ($k2 == 'danger') {
                                $this->errors['flag_error_in_fields'] = true;
                            }
                            if ($k2 == 'warning') {
                                $this->errors['flag_warning_in_fields'] = true;
                            }
                        }
                    }
                }
            } else {
                array_key_set($this->errors, ['fields', $field, $type, $hash], $message);
                // modals
                if (!empty($this->fields[$field])) {
                    $container_link = $this->fields[$field]['options']['container_link'];
                    if (($this->data[$container_link]['type'] ?? '') == 'modal') {
                        $this->misc_settings['errors_in_modal'][$container_link] = $this->data[$container_link]['options']['modal_id'];
                    }
                }
                // set special flag that we have error in fields
                if ($type == 'danger') {
                    $this->errors['flag_error_in_fields'] = true;
                    $this->errors['flag_num_errors']++;
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
            if ($type == 'reset') {
                $this->errors['general'] = [];
                $_SESSION['numbers']['forms'][$this->options['parent_form_link'] ?? $this->form_link]['messages'] = [];
                return;
            }
            // subforms need to display messages on parent form
            if (!empty($this->options['on_success_refresh_parent']) && !empty($this->options['parent_form_link']) && $type != 'danger') {
                $_SESSION['numbers']['forms'][$this->options['parent_form_link']]['messages'][$type][$hash] = $message;
                $this->misc_settings['form_postponed_messages'] = true;
                return;
            }
            // regular postponed messages
            if (!empty($options['postponed'])) {
                $_SESSION['numbers']['forms'][$this->options['parent_form_link'] ?? $this->form_link]['messages'][$type][$hash] = $message;
                $this->misc_settings['form_postponed_messages'] = true;
                return;
            }
            // regular messages
            $this->errors['general'][$type][$hash] = $message;
            if ($type == 'danger') {
                $this->errors['flag_num_errors']++;
            }
        }
    }

    /**
     * Whether form has errors
     *
     * @param mixed $error_names
     * @return boolean
     */
    public function hasErrors($error_names = null)
    {
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
    public function errorResetAll()
    {
        if ($this->is_api) {
            $general = [];
        } else {
            $general = $this->errors['general'] ?? [];
        }
        $this->errors = [
            'flag_error_in_fields' => false,
            'flag_warning_in_fields' => false,
            'flag_num_errors' => 0,
            'general' => $general,
        ];
    }

    /**
     * Process widget
     *
     * @param array $options
     * @return boolean
     */
    private function processWidget($options)
    {
        $property = str_replace('detail_', '', $options['widget']);
        // determine object
        if ($options['type'] == 'tabs' || $options['type'] == 'fields') {
            $object = & $this->collection_object->primary_model;
        } elseif ($options['type'] == 'subdetails') {
            $object = \Factory::model($options['details_parent_key'], true);
        }
        if (!empty($object->{$property})) {
            return \Factory::model($object->{"{$property}_model"}, true)->formProcessWidget($this, $options);
        }
        return false;
    }

    /**
     * Process ACL sub-resources
     *
     * @param mixed $subresources
     * @param mixed $action
     * @return bool
     */
    public function tempProcessACLSubresources($subresources, $action): bool
    {
        if (!is_array($subresources)) {
            $subresources = [$subresources];
        }
        foreach ($subresources as $v) {
            if (empty($this->options['skip_acl']) && !\Application::$controller->canSubresourceCached($v, $action)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Add container to the form
     *
     * @param string $container_link
     * @param array $options
     */
    public function container($container_link, $options = [])
    {
        // handling acl_subresource
        if (!empty($options['acl_subresource_remove']) && !$this->tempProcessACLSubresources($options['acl_subresource_remove'], 'Record_View')) {
            $this->misc_settings['acl_subresource_locks'][$container_link]['remove'] = true;
            return;
        }
        if (!empty($options['acl_subresource_edit'])) {
            if ($this->tempProcessACLSubresources($options['acl_subresource_edit'], 'All_Actions')) {
                // nothing
            } else {
                if (!$this->tempProcessACLSubresources($options['acl_subresource_edit'], 'Record_New')) {
                    $this->misc_settings['acl_subresource_locks'][$container_link]['no_new'] = true;
                }
                if (!$this->tempProcessACLSubresources($options['acl_subresource_edit'], 'Record_Edit')) {
                    $this->misc_settings['acl_subresource_locks'][$container_link]['no_edit'] = true;
                }
                if (!$this->tempProcessACLSubresources($options['acl_subresource_edit'], 'Record_Inactivate')) {
                    $this->misc_settings['acl_subresource_locks'][$container_link]['no_inactivate'] = true;
                }
                if (!$this->tempProcessACLSubresources($options['acl_subresource_edit'], 'Record_Delete')) {
                    $this->misc_settings['acl_subresource_locks'][$container_link]['no_delete'] = true;
                }
            }
        }
        // see if we have container already
        if (!isset($this->data[$container_link])) {
            $options['container_link'] = $container_link;
            $type = $options['type'] = $options['type'] ?? 'fields';
            // make hidden container last
            if ($container_link == $this::HIDDEN) {
                $options['order'] = 35000;
            }
            // see if we adding a widget
            if (!empty($options['widget'])) {
                // we skip if widgets are not enabled
                $widget = str_replace('detail_', '', $options['widget']);
                $temp = Resources::getStatic('widgets', $widget);
                if (empty($temp)) {
                    return;
                }
                // handling widgets
                return $this->processWidget($options);
            }
            // processing details
            if ($type == 'details') {
                if (empty($options['details_key']) || empty($options['details_pk'])) {
                    throw new \Exception('Detail key or pk?');
                }
                $options['details_collection_key'] = $options['details_collection_key'] ?? ['details', $options['details_key']];
                $options['details_rendering_type'] = $options['details_rendering_type'] ?? 'grid_with_label';
                $options['details_new_rows'] = $options['details_new_rows'] ?? 0;
            }
            if ($type == 'trees') {
                if (empty($options['details_key']) || empty($options['details_pk'])) {
                    throw new \Exception('Detail key or pk?');
                }
                if (empty($options['details_tree_parent_key']) || empty($options['details_tree_key'])) {
                    throw new \Exception('Detail tree key or parent key?');
                }
                $options['details_collection_key'] = $options['details_collection_key'] ?? ['details', $options['details_key']];
                $options['details_rendering_type'] = $options['details_rendering_type'] ?? 'name_only';
                $options['details_new_rows'] = 0;
            }
            // processing subdetails
            if ($type == 'subdetails' || $type == 'subtrees') {
                if (empty($options['details_key']) || empty($options['details_pk']) || empty($options['details_parent_key'])) {
                    throw new \Exception('Subdetail key, parent key or pk?');
                }
                $options['flag_child'] = true;
                $options['details_collection_key'] = $options['details_collection_key'] ?? ['details', $options['details_parent_key'], 'details', $options['details_key']];
                $options['details_rendering_type'] = $options['details_rendering_type'] ?? 'table';
                $options['details_new_rows'] = $options['details_new_rows'] ?? 0;
            }
            // modal
            if ($type == 'modal') {
                $options['modal_id'] = "form_{$this->form_link}_modal_{$container_link}_dialog";
            }
            $this->data[$container_link] = [
                'options' => $options,
                'order' => $options['order'] ?? 0,
                'type' => $type,
                'flag_child' => !empty($options['flag_child']),
                'default_row_type' => $options['default_row_type'] ?? 'grid',
                'label_name' => $options['label_name'] ?? null,
                'rows' => [],
            ];
            // special handling for details
            if ($type == 'details') {
                $model = \Factory::model($options['details_key'], true);
                // if we have relation
                if (!empty($model->relation['field']) && !in_array($model->relation['field'], $model->pk)) {
                    $this->element($container_link, $this::HIDDEN, $model->relation['field'], ['label_name' => 'Relation #', 'domain' => 'relation_id_sequence', 'method' => 'input', 'persistent' => true]);
                }
            }
            /*
            if ($type == 'details' || $type == 'subdetails' || $type == 'trees' || $type == 'subtrees') {
                // if we have autoincrement
                if (!empty($options['details_autoincrement'])) {
                    $model = \Factory::model($options['details_key'], true);
                    foreach ($options['details_autoincrement'] as $v) {
                        $this->element($container_link, $this::HIDDEN, $v, array_merge_hard($model->columns[$v], ['default' => 0]));
                    }
                }
            }
            */
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
    public function row($container_link, $row_link, $options = [])
    {
        // if we have a lock we do not add elements
        if (!empty($this->misc_settings['acl_subresource_locks'][$container_link]['remove'])) {
            return;
        }
        // process acl_subresource
        if (!empty($options['acl_subresource_remove']) && !$this->tempProcessACLSubresources($options['acl_subresource_remove'], 'Record_View')) {
            $this->misc_settings['acl_subresource_locks'][$container_link . '::' . $row_link]['remove'] = true;
            return;
        }
        // process acl_subresource_hide
        if (!empty($options['acl_subresource_hide']) && !$this->tempProcessACLSubresources($options['acl_subresource_hide'], 'Record_View')) {
            $options['hidden'] = true;
        }
        $this->container($container_link, array_key_extract_by_prefix($options, 'container_', false));
        if (!isset($this->data[$container_link]['rows'][$row_link])) {
            // hidden rows
            if ($row_link == $this::HIDDEN) {
                $options['order'] = 35000;
            }
            // validating row type
            $types = \Object\HTML\Form\Row\Types::getStatic();
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
            if (($this->data[$container_link]['type'] ?? '') == 'tabs' && !empty($options['widget'])) {
                $options['type'] = 'tabs';
                // we skip if widgets are not enabled
                $temp = Resources::getStatic('widgets', $options['widget']);
                if (empty($temp) || empty($this->collection_object->primary_model->{$options['widget']}) || !$this->processWidget($options)) {
                    unset($this->data[$container_link]['rows'][$row_link]);
                    return;
                }
            }
        } else {
            $this->data[$container_link]['rows'][$row_link]['options'] = array_merge($this->data[$container_link]['rows'][$row_link]['options'], $options);
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
    public function element($container_link, $row_link, $element_link, $options = [])
    {
        // if we explicitly hiding navigation
        if (!empty($options['navigation']) && !empty($this->options['hide_navigation'])) {
            unset($options['navigation']);
            if ($this->options['hide_navigation'] === 'readonly') {
                $options['readonly'] = true;
            }
        }
        // if we have a lock we do not add elements
        if (!empty($this->misc_settings['acl_subresource_locks'][$container_link]['remove']) || !empty($this->misc_settings['acl_subresource_locks'][$container_link . '::' . $row_link]['remove'])) {
            return;
        }
        if (!empty($this->misc_settings['acl_subresource_locks'][$container_link]['no_edit'])) {
            $options['readonly'] = true;
        }
        // presetting options for buttons, making them last
        if (in_array($row_link, [$this::BUTTONS, $this::TRANSACTION_BUTTONS, $this::WIDE_BUTTONS])) {
            $options['row_type'] = 'grid';
            if (!isset($options['row_order'])) {
                $options['row_order'] = PHP_INT_MAX - 500;
            }
        }
        // hidden rows
        if ($row_link == self::HIDDEN && !isset($options['row_order'])) {
            $options['row_order'] = -32000;
        }
        // processing row and container
        $this->container($container_link, array_key_extract_by_prefix($options, 'container_', false));
        $this->row($container_link, $row_link, array_key_extract_by_prefix($options, 'row_', false));
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
                $type = $this->data[$container_link]['type'] ?? '';
                if (in_array($type, ['details', 'subdetails', 'trees', 'subtrees'])) { // details & subdetails
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
                // fix type/domain array
                if (isset($options['type']) && strpos($options['type'], '[]') !== false) {
                    $options['array'] = true;
                    $options['type'] = str_replace('[]', '', $options['type']);
                }
                if (isset($options['domain']) && strpos($options['domain'], '[]') !== false) {
                    $options['array'] = true;
                    $options['domain'] = str_replace('[]', '', $options['domain']);
                }
                // multiple column
                if (!empty($options['multiple_column'])) {
                    $options['details_collection_key'] = array_merge(($options['details_collection_key'] ?? []), ['details', $element_link]);
                }
                // process domain & type
                $temp = Common::processDomainsAndTypes(['options' => $options]);
                $options = $temp['options'];
                $options['row_link'] = $row_link;
                $options['container_link'] = $container_link;
                // default = null for integers
                if ($this->initiator_class == 'list' || $this->initiator_class == 'report') {
                    if ($options['php_type'] == 'integer' && isset($options['default']) && $options['default'] === 0 && !empty($options['null'])) {
                        $options['default'] = null;
                    }
                }
                // fixes for list container
                if ($this->initiator_class == 'list' && ($container_link == self::LIST_CONTAINER || $container_link == self::LIST_LINE_CONTAINER)) {
                    // add manual validation
                    if (!empty($options['options_model'])) {
                        $options['options_manual_validation'] = true;
                    }
                    // add options model for boolean type
                    if (($options['type'] ?? '') == 'boolean') {
                        if (\Application::get('flag.numbers.frontend.html.form.revert_inactive') && ($options['label_name'] ?? '') == 'Inactive') {
                            $options['label_name'] = 'Active';
                            $options['options_model'] = '\Object\Data\Model\Inactive2';
                        } else {
                            $options['options_model'] = '\Object\Data\Model\Inactive';
                        }
                    }
                } elseif (($options['type'] ?? '') == 'boolean' && !isset($options['method'])) { // fix boolean type for forms
                    $options['method'] = 'checkbox';
                    // acl Record_Inactivate
                    if (($options['label_name'] ?? '') == 'Inactive' && !empty($this->misc_settings['acl_subresource_locks'][$container_link]['no_inactivate'])) {
                        $options['readonly'] = true;
                    }
                    // we revert inactive if set
                    if (\Application::get('flag.numbers.frontend.html.form.revert_inactive') && ($options['label_name'] ?? '') == 'Inactive') {
                        $options['label_name'] = 'Active';
                        $options['oposite_checkbox'] = true;
                    }
                    // we need to make inactive column disabled
                    if ($element_link == (($this->collection_object->primary_model->column_prefix ?? '') . 'inactive')) {
                        if (empty($this->options['skip_acl']) && !\Application::$controller->can('Record_Inactivate', 'Edit')) {
                            $options['readonly'] = true;
                        }
                    }
                } elseif (in_array($this->initiator_class, ['list', 'report']) && ($options['type'] ?? '') == 'boolean') {
                    // we revert inactive if set
                    if (\Application::get('flag.numbers.frontend.html.form.revert_inactive') && ($options['label_name'] ?? '') == 'Inactive') {
                        $options['label_name'] = 'Active';
                        $options['options_model'] = '\Object\Data\Model\Inactive2';
                    }
                }
                // validator method for captcha
                if (($options['method'] ?? '') == 'captcha') {
                    $options['validator_method'] = \Application::get('flag.numbers.framework.html.captcha.submodule', ['class' => true]) . '::validate';
                }
                // render document links
                if (!empty($options['documents_render_links']) && empty($options['custom_renderer'])) {
                    $method = Resources::getStatic('save_documents', 'generate_document_links', 'method');
                    if (!empty($method)) {
                        $options['custom_renderer'] = $method;
                    }
                }
                // type for buttons
                if (in_array(($options['method'] ?? ''), ['button', 'button2', 'submit']) && empty($options['type'])) {
                    $options['type'] = $this->options['segment']['type'] ?? 'primary';
                }
                // format in report
                if ($this->initiator_class == 'report' && $options['name'] == '__format') {
                    if (isset($this->form_parent->_format_add_options)) {
                        $__format_options = Types::optionsStatic(['i18n' => 'skip_sorting']);
                        $__format_options = array_merge_hard($__format_options, $this->form_parent->_format_add_options);
                        unset($options['options_model']);
                        $options['options'] = $__format_options;
                    }
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
                if ($type == 'details' || $type == 'trees') {
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
                } elseif ($type == 'subdetails' || $type == 'subtrees') {
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
                // process refresh
                if (!empty($options['process_refresh']) && !empty($this->options['input'][$element_link])) {
                    $this->process_submit_refresh = true;
                    $this->values[$element_link] = true;
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
            $this->data[$container_link]['rows'][$row_link]['elements'][$element_link]['options'] = array_merge_hard($this->data[$container_link]['rows'][$row_link]['elements'][$element_link]['options'], $options);
        }
    }

    /**
     * Render form
     *
     * @param string $format
     * @return mixed
     */
    public function render($format = null)
    {
        // SMS text messages
        if ($this->initiator_class == 'SMS') {
            $model = new Renderer();
            return $model->render($this);
        }
        // list has its own format
        if ($this->initiator_class == 'list') {
            $format = $this->options['input']['__format'] ?? $this->values['__format'] ?? 'text/html';
        }
        if (!isset($format)) {
            $format = $this->options['input']['__content_type'] ?? 'text/html';
        }
        $content_types_model = new Model\Content\Types();
        $content_types = $content_types_model->get();
        if (empty($content_types[$format])) {
            $format = 'text/html';
        }
        $model =  new $content_types[$format]['no_form_content_type_model']();
        // special mode for emails
        if ($this->initiator_class == 'email') {
            \HTML::setMode(true);
            $result = $model->render($this);
            \HTML::setMode(false);
            return $result;
        } else {
            return $model->render($this);
        }
    }

    /**
     * API result
     *
     * @param array $options
     * @return array
     */
    public function apiResult(array $options = []): array
    {
        if (!empty($options['simple'])) {
            $result = [
                'success' => false,
                'error' => [],
                'pk' => $this->pk,
                'values' => $this->values,
            ];
            if ($options['simple'] === 2) {
                unset($result['values'], $result['pk']);
            }
        } else {
            $result = [
                'success' => false,
                'error' => [],
                'pk' => $this->pk,
                'values' => $this->values,
                'values_loaded' => $this->values_loaded,
                'values_saved' => $this->values_saved,
                'values_inserted' => $this->values_inserted,
                'values_updated' => $this->values_updated,
                'values_no_changes' => $this->values_no_changes,
                'new_serials' => $this->new_serials
            ];
        }
        // we need to error out if no data found
        if (($options['method'] ?? '') == 'Get') {
            if (!$this->values_loaded) {
                $this->error(DANGER, Messages::NO_ROWS_FOUND);
            }
        }
        // api values
        if (!empty($this->api_values)) {
            $result = array_merge_hard($result, $this->api_values);
        }
        // process errors
        if ($this->hasErrors()) {
            $message = [];
            // details
            $details = [];
            if (!empty($this->collection['details'])) {
                $this->disassembleCollectionObject($this->collection['details'], $details);
            }
            $ignore_fields = [];
            if ($this->collection_object->primary_model->tenant ?? false) {
                $ignore_fields[$this->collection_object->primary_model->tenant_column] = $this->collection_object->primary_model->tenant_column;
            }
            if ($this->collection_object->primary_model->module ?? false) {
                $ignore_fields[$this->collection_object->primary_model->tenant_column] = $this->collection_object->primary_model->tenant_column;
            }
            foreach ($details as $k3 => $v3) {
                foreach ($ignore_fields as $k4 => $v4) {
                    if (isset($v3['map'][$k4])) {
                        $ignore_fields[$v3['map'][$k4]] = $v3['map'][$k4];
                    }
                }
            }
            $message[] = i18n(null, 'Record') . ':';
            $temp = [];
            foreach ($this->pk as $k => $v) {
                if (!empty($ignore_fields[$k])) {
                    continue;
                }
                $temp[] = $k . ' = ' . $v;
            }
            $message[] = "\t" . i18n(null, 'Primary Key') . ': ' . implode(', ', $temp);
            // general errors
            if (!empty($this->errors['general']['danger'])) {
                $message[] = "\t" . i18n(null, 'General Errors') . ':';
                foreach ($this->errors['general']['danger'] as $v) {
                    $message[] = "\t\t" . $v;
                }
            }
            // fields
            if (!empty($this->errors['flag_error_in_fields'])) {
                $message[] = "\t" . i18n(null, 'Field Errors') . ':';
                foreach ($this->errors['fields'] as $k => $v) {
                    if (empty($v['danger'])) {
                        continue;
                    } // only errors
                    // regular fields
                    if (strpos($k, '[') === false) {
                        $message[] = "\t\t" . $k . ': ';
                        foreach ($v['danger'] as $v2) {
                            $message[] = "\t\t\t" . $v2;
                        }
                    } else { // details
                        $field = str_replace(']', '', $k);
                        $parts = explode('[', $field);
                        $message[] = "\t\t" . $details[$parts[0]]['name'] . ', ' . $parts[2] . ': ';
                        // pk
                        $pk_parts = explode('::', $parts[1]);
                        $pk_details = [];
                        foreach ($details[$parts[0]]['pk'] as $k2 => $v2) {
                            $pk_details[$v2] = $pk_parts[$k2] ?? null;
                        }
                        $temp = [];
                        foreach ($pk_details as $k2 => $v2) {
                            if (!empty($ignore_fields[$k2])) {
                                continue;
                            }
                            $temp[] = $k2 . ' = ' . $v2;
                        }
                        $message[] = "\t\t\t" . i18n(null, 'Primary Key') . ': ' . implode(', ', $temp);
                        foreach ($v['danger'] as $v2) {
                            $message[] = "\t\t\t" . $v2;
                        }
                        // todo handle subdetails
                    }
                }
            }
            $result['error'][] = implode("\n", $message);
        } else {
            $result['success'] = true;
        }
        return $result;
    }

    /**
     * Get field errors
     *
     * @param array $field
     * @return mixed
     */
    public function getFieldErrors($field)
    {
        $existing = $this->errors['fields'][$field['options']['name']] ?? null;
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
                if (empty($v)) {
                    continue;
                }
                foreach ($v as $k2 => $v2) {
                    $result['message'] .= \HTML::text(['tag' => 'div', 'class' => 'numbers_field_error_messages', 'data-field_value_hash' => $k2, 'type' => $k, 'value' => $v2]);
                }
            }
            return $result;
        }
        return null;
    }

    /**
     * Process depends and params
     *
     * @param array $params
     * @param array $neighbouring_values
     * @param array $options
     * @param boolean $flag_params
     */
    public function processParamsAndDepends(& $params, & $neighbouring_values, $options, $flag_params = true)
    {
        foreach ($params as $k => $v) {
            if (is_array($v)) {
                continue;
            }
            if (!isset($v)) {
                continue;
            }
            // if we have master object
            if (strpos($v, 'master_object::') !== false) {
                $field = explode('::', str_replace(['master_object::', 'static::'], '', $v));
                if (isset($this->master_object->{$field[0]}->{$field[1]}->{$field[2]})) {
                    $params[$k] = $this->master_object->{$field[0]}->{$field[1]}->{$field[2]};
                } else {
                    $params[$k] = null;
                }
            } elseif (strpos($v, 'parent::') !== false) { // value from parent
                $field = str_replace(['parent::', 'static::'], '', $v);
                if (!empty($this->errors['fields'][$field]['danger'])) {
                    $params[$k] = null;
                } else {
                    $params[$k] = $this->values[$field] ?? null;
                }
            } elseif (strpos($v, 'detail::') !== false) { // if we need to grab value from detail
                $field = str_replace('detail::', '', $v);
                $params[$k] = $neighbouring_values[$field] ?? $options['options']['__detail_values'][$field] ?? null;
            } elseif ($flag_params) {
                // todo process errors
                // todo process details
                if (!empty($this->fields[$v]['options']['multiple_column']) && is_array(current($neighbouring_values[$v] ?? []))) {
                    $params[$k] = array_extract_values_by_key($neighbouring_values[$v] ?? [], $this->fields[$v]['options']['multiple_column']);
                } else {
                    $params[$k] = $neighbouring_values[$v] ?? null;
                }
                // we need to unset empty parameters
                if (isset($params[$k]) && empty($params[$k])) {
                    unset($params[$k]);
                }
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
    public function processDefaultValue($key, $default, $value, & $neighbouring_values, $set_neightbouring_values = true, $changed_field = [], $options = [])
    {
        if (strpos($default . '', 'dependent::') !== false) {
            // nothing
        } elseif (strpos($default . '', 'master_object::') !== false) {
            $field = explode('::', str_replace(['master_object::', 'static::'], '', $default));
            if (isset($this->master_object->{$field[0]}->{$field[1]}->{$field[2]})) {
                $this->misc_settings['__default_field_changed'][] = $key;
                return $this->master_object->{$field[0]}->{$field[1]}->{$field[2]};
            } else {
                return null;
            }
        } elseif (strpos($default . '', 'parent::') !== false) {
            $field = str_replace(['parent::', 'static::'], '', $default);
            $value = $this->values[$field] ?? null;
        } else {
            if ($default === 'now()') {
                $default = \Format::now('timestamp');
            }
            $value = $default;
        }
        // handling override_field_value method
        if (!empty($this->wrapper_methods['processDefaultValue']['main'])) {
            // fix changed field
            if (empty($changed_field)) {
                $changed_field = [];
            }
            $changed_field['parent'] = $changed_field['parent'] ?? null;
            $changed_field['detail'] = $changed_field['detail'] ?? null;
            $changed_field['subdetail'] = $changed_field['subdetail'] ?? null;
            // call override method
            $model = $this->wrapper_methods['processDefaultValue']['main'][0];
            $model->{$this->wrapper_methods['processDefaultValue']['main'][1]}($this, $key, $default, $value, $neighbouring_values, $changed_field, $options);
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
    public function canProcessDefaultValue($value, $options, $default = null)
    {
        if (strpos($options['options']['default'] . '', 'static::') !== false || strpos($options['options']['default'] . '', 'master_object::') !== false || strpos($options['options']['default'] . '', 'dependent::') !== false || (is_null($value) && empty($options['options']['null']))) {
            return true;
        } elseif (is_string($default) || is_array($default) || is_numeric($default)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Prepare export file variables
     *
     * @return array
     */
    public function prepareExportFileVariables(): array
    {
        $result = [
            'success' => false,
            'error' => [],
            'data' => []
        ];
        // details
        $details = [];
        if (!empty($this->collection['details'])) {
            $this->disassembleCollectionObject($this->collection['details'], $details);
        }
        // step 1: fields
        $result['data'][$this->collection['name']] = [];
        foreach ($this->fields as $k => $v) {
            if (!$this->skipExportField($k, $v)) {
                continue;
            }
            // if we have detail
            if (!empty($details[$k])) {
                $result['data'][$details[$k]['name']] = [];
                foreach ($details[$k]['pk'] as $k2 => $v2) {
                    if ($k2 == 0 && strpos($v2, 'tenant_id') !== false) {
                        continue;
                    }
                    $result['data'][$details[$k]['name']][0][$v2] = $v2;
                }
                continue;
            }
            // regular field
            $result['data'][$this->collection['name']][0][$k] = $k;
        }
        // step 2: detail fields
        foreach ($this->detail_fields as $k => $v) {
            $result['data'][$details[$k]['name']] = [];
            // add pk
            foreach ($details[$k]['pk'] as $k2 => $v2) {
                if ($k2 == 0 && strpos($v2, 'tenant_id') !== false) {
                    continue;
                }
                $result['data'][$details[$k]['name']][0][$v2] = $v2;
            }
            // add fields
            foreach ($v['elements'] as $k2 => $v2) {
                if (!$this->skipExportField($k2, $v2)) {
                    continue;
                }
                $result['data'][$details[$k]['name']][0][$k2] = $k2;
            }
        }
        $result['success'] = true;
        return $result;
    }

    /**
     * Generate form fields
     *
     * @return array
     */
    public function generateFormFields(): array
    {
        $result = [
            'success' => false,
            'error' => [],
            'data' => []
        ];
        // details
        $details = [];
        if (!empty($this->collection['details'])) {
            $this->disassembleCollectionObject($this->collection['details'], $details);
        }
        // step 1: fields
        foreach ($this->fields as $k => $v) {
            if (!$this->skipExportField($k, $v)) {
                continue;
            }
            // if we have detail
            if (!empty($details[$k])) {
                $result['data'][$k . '[' . $v['options']['multiple_column'] . ']'] = [
                    'name' => $v['options']['label_name'],
                    'type' => $this->determineFieldType($v['options']['multiple_column'], $v)
                ];
                continue;
            }
            // regular field
            $result['data'][$k] = [
                'name' => $v['options']['label_name'],
                'type' => $this->determineFieldType($k, $v)
            ];
        }
        // step 2: detail fields
        foreach ($this->detail_fields as $k => $v) {
            foreach ($v['elements'] as $k2 => $v2) {
                if (!$this->skipExportField($k2, $v2)) {
                    continue;
                }
                $result['data'][$k . '[' . $k2 . ']'] = [
                    'name' => (isset($details[$k]['name']) ? ($details[$k]['name'] . ' - ') : '') . $v2['options']['label_name'],
                    'type' => $this->determineFieldType($k2, $v2)
                ];
            }
        }
        $result['success'] = true;
        return $result;
    }

    /**
     * Field type
     *
     * @param string $field_name
     * @param array $field_options
     * @return int
     * @throws \Exception
     */
    private function determineFieldType(string $field_name, array $field_options): int
    {
        // form
        if ($this->initiator_class == 'form') {
            $result = 10;
        } elseif ($this->initiator_class == 'list') { // list
            if ($field_options['options']['container_link'] == self::LIST_CONTAINER || $field_options['options']['container_link'] == self::LIST_LINE_CONTAINER) {
                $result = 30;
            } elseif ($field_options['options']['container_link'] == 'sort') {
                $result = 40;
            } else {
                $result = 20;
            }
        } elseif ($this->initiator_class == 'report') { // report
            if ($field_options['options']['container_link'] == 'filter' || $field_options['options']['container_link'] == '__report_buttons') {
                $result = 50;
            } elseif ($field_options['options']['container_link'] == self::LIST_CONTAINER || $field_options['options']['container_link'] == self::LIST_LINE_CONTAINER) {
                $result = 60;
            } elseif ($field_options['options']['container_link'] == 'sort') {
                $result = 70;
            } elseif ($field_options['options']['container_link'] == '__filter_new') {
                $result = 10;
            } else {
                throw new \Exception('Report field type?');
            }
        } else {
            throw new \Exception('Other field type?');
        }
        return $result;
    }

    /**
     * Skip export field
     *
     * @param string $field_name
     * @param array $field_options
     * @return bool
     */
    private function skipExportField(string $field_name, array $field_options): bool
    {
        if (!empty($field_options['options']['process_submit'])) {
            return false;
        }
        if ($field_name == $this::SEPARATOR_HORIZONTAL || $field_name == $this::SEPARATOR_VERTICAL) {
            return false;
        }
        if (!empty($field_options['options']['skip_during_export'])) {
            return false;
        }
        if (!empty($field_options['options']['custom_renderer'])) {
            return false;
        }
        if ($field_options['options']['container_link'] == self::HIDDEN) {
            return false;
        }
        $label = $field_options['options']['label_name'] ?? '';
        if ($label == '' || $label == ' ') {
            return false;
        }
        if ($field_name == '__format') {
            return false;
        }
        return true;
    }

    /**
     * Disassemble collection details
     *
     * @param array $collection_details
     * @param array $result
     */
    private function disassembleCollectionObject(array $collection_details, & $result, $parent = [])
    {
        foreach ($collection_details as $k => $v) {
            if (!empty($v['readonly'])) {
                continue;
            }
            $result[$k] = $v;
            $result[$k]['model'] = $k;
            $result[$k]['__parent'] = $parent;
            if (!empty($v['details'])) {
                $parent[] = $k;
                $this->disassembleCollectionObject($v['details'], $result, $parent);
            }
            unset($result[$k]['details']);
        }
    }

    /**
     * Process imported sheets
     *
     * @param array $data
     * @param array $globals
     * @return array
     */
    public function processImportedSheets(array $data, array $globals): array
    {
        $result = [
            'success' => false,
            'error' => [],
            'data' => []
        ];
        // process globals
        $globals_final = [];
        $ignore_columns = [];
        if ($this->collection_object->primary_model->tenant) {
            $globals_final[$this->collection_object->primary_model->tenant_column] = $globals[$this->collection_object->primary_model->tenant_column];
            $ignore_columns[$this->collection_object->primary_model->tenant_column] = $this->collection_object->primary_model->tenant_column;
        }
        if ($this->collection_object->primary_model->module) {
            $globals_final[$this->collection_object->primary_model->module_column] = $globals[$this->collection_object->primary_model->module_column];
            $ignore_columns[$this->collection_object->primary_model->module_column] = $this->collection_object->primary_model->module_column;
        }
        // find main sheet
        $main_sheet_data = $data[$this->collection['name']] ?? $data['Main Sheet'] ?? [];
        unset($data[$this->collection['name']], $data['Main Sheet']);
        if (empty($main_sheet_data)) {
            $result['error'][] = Messages::NO_ROWS_FOUND;
            return $result;
        }
        // loop though header rows
        foreach ($main_sheet_data as $k => $v) {
            $v = array_merge_hard($v, $globals_final);
            // details
            if (!empty($this->collection['details'])) {
                foreach ($this->collection['details'] as $k2 => $v2) {
                    $v[$k2] = [];
                    if (empty($data[$v2['name']])) {
                        continue;
                    }
                    // go through data
                    foreach ($data[$v2['name']] as $k3 => $v3) {
                        // map child to parent
                        $found = true;
                        foreach ($v2['map'] as $k4 => $v4) {
                            // important to add new items to ignore columns array
                            if (isset($ignore_columns[$k4])) {
                                $ignore_columns[$v4] = $v4;
                                continue;
                            }
                            // compare
                            if ($v3[$v4] != $v[$k4]) {
                                $found = false;
                            }
                        }
                        // if found
                        if ($found) {
                            // todo process subdetails
                            $v[$k2][] = $v3;
                            unset($data[$v2['name']][$k3]);
                        }
                    }
                }
            }
            $result['data'][$k] = $v;
            unset($main_sheet_data[$k]);
        }
        $result['success'] = true;
        return $result;
    }

    /**
     * Render list one option
     *
     * @param array $options
     * @param mixed $value
     * @return mixed
     */
    public function renderListContainerDefaultOptions(array $options, $value, $neighbouring_values = [])
    {
        if (strpos($options['options_model'], '::') === false) {
            $options['options_model'] .= '::options';
        }
        $params = $options['options_params'] ?? [];
        if (!empty($options['options_depends'])) {
            $this->processParamsAndDepends($options['options_depends'], $neighbouring_values, $options, true);
            $params = array_merge($params, $options['options_depends']);
        }
        $hash = sha1($options['options_model'] . serialize($params));
        if (!isset($this->cached_options[$hash])) {
            $method = \Factory::method($options['options_model'], null, true);
            $this->cached_options[$hash] = call_user_func_array($method, [['where' => $params, 'i18n' => true, 'skip_acl' => true]]); // skip acl is a must
        }
        if (is_array($value)) {
            $temp = [];
            foreach ($value as $v) {
                if (isset($this->cached_options[$hash][$v]['name'])) {
                    $temp[] = $this->cached_options[$hash][$v]['name'];
                }
            }
            return implode(\Format::$symbol_comma . ' ', $temp);
        } else {
            return $this->cached_options[$hash][$value]['name'] ?? null;
        }
    }

    /**
     * Generate filter
     *
     * @return array
     */
    public function generateFilter(): array
    {
        $result = [];
        // filter
        foreach ($this->fields as $k => $v) {
            if ($v['options']['container_link'] != 'filter') {
                continue;
            }
            if (($v['options']['method'] ?? null) == 'hidden') {
                continue;
            }
            $label = i18n(null, $v['options']['label_name']);
            $value = $this->values[$k] ?? null;
            if (!empty($v['options']['options_model'])) {
                $value = $this->renderListContainerDefaultOptions($v['options'], $value, $this->values);
            } elseif (!empty($v['options']['options'])) {
                $value = $v['options']['options'][$value]['name'] ?? null;
            }
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            if (!isset($result[$label])) {
                $result[$label] = $value;
            } else {
                $result[$label] .= ' - ' . $value;
            }
        }
        // sort
        if (!empty($this->values['\Object\Form\Model\Dummy\Sort'])) {
            $sort_options = Order::optionsStatic(['i18n' => true]);
            $temp = [];
            foreach ($this->values['\Object\Form\Model\Dummy\Sort'] as $k => $v) {
                $name = $this->detail_fields['\Object\Form\Model\Dummy\Sort']['elements']['__sort']['options']['options'][$v['__sort']]['name'];
                $temp[] = i18n(null, $name) . ' ' . $sort_options[$v['__order']]['name'];
            }
            $result[i18n(null, 'Sort')] = implode(', ', $temp);
        }
        return $result;
    }

    /**
     * Validate details primary column
     *
     * @param string|array $detail
     * @param string $primary_column
     * @param string $inactive_column
     * @param string $pk_column
     * @param string|null $key
     * @param array $options
     * @return int | null
     */
    public function validateDetailsPrimaryColumn(string|array $detail, string $primary_column, string $inactive_column, string $pk_column, ?string & $key = null, array $options = [])
    {
        $data = array_key_get($this->values, $detail);
        if (empty($data)) {
            return null;
        }
        $primary_found = 0;
        $primary_first_line = null;
        $primary_pk_id = null;
        $detail = $this->parentKeysToErrorName($detail);
        foreach ($data as $k => $v) {
            if (!isset($primary_first_line)) {
                $primary_first_line = "{$detail}[{$k}][{$primary_column}]";
            }
            if (!empty($v[$primary_column])) {
                $primary_pk_id = $v[$pk_column] ?? null;
                $primary_found++;
                if (!empty($v[$inactive_column])) {
                    $message = 'Primary cannot be inactive!';
                    if (!empty($options['replace'])) {
                        $message = str_replace($options['replace'][0], $options['replace'][1], $message);
                    }
                    $this->error(DANGER, $message, "{$detail}[{$k}][{$inactive_column}]");
                }
                if ($primary_found > 1) {
                    $message = 'There can be only one primary!';
                    if (!empty($options['replace'])) {
                        $message = str_replace($options['replace'][0], $options['replace'][1], $message);
                    }
                    $this->error(DANGER, $message, "{$detail}[{$k}][{$primary_column}]");
                }
                // we need to pass first primary back
                if ($primary_found == 1) {
                    $key = $k;
                }
            }
        }
        if ($primary_found == 0) {
            $message = 'You must select primary!';
            if (!empty($options['replace'])) {
                $message = str_replace($options['replace'][0], $options['replace'][1], $message);
            }
            $this->error(DANGER, $message, $primary_first_line);
        }
        return $primary_pk_id;
    }

    /**
     * Validate quick required
     *
     * @param string|array $field
     * @param string $message
     * @return void
     */
    public function validateQuickRequired(string|array $field, string $message = Messages::REQUIRED_FIELD): void
    {
        if (!is_array($field)) {
            $field = [$field];
        }
        $value = array_key_get($this->values, $field);
        if (empty($value)) {
            $this->error(DANGER, $message, $this->parentKeysToErrorName($field));
        }
    }

    /**
     * Redirect
     *
     * @param string $where
     */
    public function redirect(string $where)
    {
        $this->misc_settings['redirect'] = $where;
    }

    /**
     * Refresh
     */
    public function refresh()
    {
        $params = [];
        // add primary key
        if ($this->values_loaded) {
            $params = $this->pk;
            // remove tenant
            if (!empty($this->collection_object->primary_model->tenant)) {
                unset($params[$this->collection_object->primary_model->tenant_column]);
            }
        }
        // we need to pass module #
        if ($this->collection_object->primary_model->module ?? false) {
            $params['__module_id'] = $params[$this->collection_object->primary_model->module_column] = $this->values[$this->collection_object->primary_model->module_column];
        }
        // bypass variables
        if (!empty($this->options['bypass_hidden_from_input'])) {
            foreach ($this->options['bypass_hidden_from_input'] as $v) {
                $params[$v] = $this->options['input'][$v] ?? '';
            }
        }
        if (!empty($this->options['collection_current_tab_id'])) {
            $params[$this->options['collection_current_tab_id']] = $this->form_link;
        }
        if (!empty($this->values['__form_filter_id'])) {
            $params['__form_filter_id'] = $this->values['__form_filter_id'];
        }
        $params['__refresh'] = rand(1000, 9999) . '_' . rand(1000, 9999) . '_' . rand(1000, 9999);
        $this->misc_settings['redirect'] = \Application::get('mvc.full') . '?' . http_build_query2($params) . "#page_top_anchor";
    }

    /**
     * Make form readonly
     */
    public function readonly()
    {
        $this->misc_settings['global']['readonly'] = true;
    }

    /**
     * Redirect on success
     */
    public function redirectOnSuccess()
    {
        $params = [];
        if (!empty($this->options['bypass_hidden_from_input'])) {
            foreach ($this->options['bypass_hidden_from_input'] as $v) {
                $params[$v] = $this->options['input'][$v] ?? '';
            }
        }
        if (!empty($this->options['collection_current_tab_id'])) {
            $params[$this->options['collection_current_tab_id']] = $this->form_link;
        }
        $url = \Application::get('mvc.full') . '?' . http_build_query2($params) . '#' . ($this->options['input']['__anchor'] ?? '');
        $this->redirect($url);
    }

    /**
     * Validate as required
     *
     * @param array $fields
     */
    public function validateAsRequiredFields(array $fields)
    {
        foreach ($fields as $v) {
            $options = $this->fields[$v];
            $options['options']['required'] = true;
            $this->validateRequiredOneField($this->values[$v], $v, $options);
        }
    }

    /**
     * Generate subform link
     *
     * @param string $subform_link
     * @param array $subform_options
     * @param array $params
     * @param array $options
     *	boolean for_menu
     * @return string|boolean
     */
    public function generateSubformLink($subform_link, $subform_options, $params, $options = [])
    {
        // acl
        if (!empty($subform_options['actions']['button']['acl_controller_actions'])) {
            if (!\Application::$controller->canMultiple($subform_options['actions']['button']['acl_controller_actions'])) {
                return false;
            }
        }
        $temp_collection_link = $this->options['collection_link'] ?? '';
        $temp_collection_screen_link = $this->options['collection_screen_link'] ?? '';
        $params_json = json_encode($params);
        $options_json = json_encode($subform_options['actions']['button']['options'] ?? []);
        if (!empty($subform_options['actions']['button']['confirm'])) {
            $onclick = "if (confirm('" . strip_tags(i18n(null, Messages::CONFIRM_CUSTOM, ['replace' => ['[action]' => $subform_options['actions']['button']['label_name']]])) . "')) { Numbers.Form.openSubformWindow('{$temp_collection_link}', '{$temp_collection_screen_link}', '{$this->form_link}', '{$subform_link}', {$params_json}, {$options_json}); }";
        } else {
            $onclick = "Numbers.Form.openSubformWindow('{$temp_collection_link}', '{$temp_collection_screen_link}', '{$this->form_link}', '{$subform_link}', {$params_json}, {$options_json});";
        }
        // name
        $name = '';
        if (isset($options['value'])) {
            $name = $options['value'];
        } else {
            if (!empty($subform_options['actions']['button']['icon'])) {
                $name .= \HTML::icon(['type' => $subform_options['actions']['button']['icon']]);
            }
            if (!empty($subform_options['actions']['button']['label_name'])) {
                $name .= ' ';
                $name .= i18n(null, $subform_options['actions']['button']['label_name']);
            }
        }
        // title
        $title = '';
        if (!empty($subform_options['actions']['button']['title'])) {
            $title .= i18n(null, $subform_options['actions']['button']['title']);
        }
        // when we pass too much data we can create a function to process it instead of adding it to a tag
        if (!empty($options['onclick_as_function'])) {
            $onclick_code = $onclick;
            if (!empty($options['onclick_disable_link'])) {
                $onclick_code .= '$(element).replaceWith("<div style=\"color: red;\">' . i18n(null, 'Please save the form after saving the sub issue.') . '</div>");';
            }
            $onclick = 'subform_onclick_function_' . $temp_collection_link . '_' .$subform_link . '(this);';
            \Layout::onLoad('function ' . 'subform_onclick_function_' . $temp_collection_link . '_' .$subform_link . '(element) {' . $onclick_code . '}');
        }
        // for menu
        if (!empty($options['for_menu'])) {
            return ['id' => $options['id'] ?? $subform_link, 'href' => 'javascript:void(0);', 'onclick' => $onclick, 'value' => $name, 'title' => $title];
        } else {
            return \HTML::a(['id' => $options['id'] ?? $subform_link, 'href' => 'javascript:void(0);', 'onclick' => $onclick . 'return false;', 'value' => $name, 'title' => $title, 'class' => $options['class'] ?? '', 'style' => $options['style'] ?? '']);
        }
    }

    /**
     * Get values for filter used in data sources
     *
     * @param array $values
     * @param array $options
     *	boolean no_unset
     * @return array
     */
    public function getValuesForDataSourceFilter($values = null, array $options = []): array
    {
        if (!isset($values)) {
            $values = $this->values;
        }
        foreach ($values as $k => $v) {
            if ($v === 0 || $v === '' || $v === null || (is_array($v) && empty($v))) {
                unset($values[$k]);
            }
        }
        if (empty($options['no_unset'])) {
            unset($values['__format'], $values['__submit_button'], $values['__list_report_filter_loaded'], $values['\Object\Form\Model\Dummy\Sort']);
        } else {
            unset($values['__submit_button_2'], $values['__list_report_filter_loaded'], $values['__filter_name']);
        }
        return $values;
    }

    /**
     * Set detail values
     *
     * @param array $changed_detail
     * @param array $data
     * @param callable|null $function
     * @return void
     */
    public function setDetailValues(array $changed_detail, array $data = [], ?callable $function = null): void
    {
        $counter = 1;
        foreach ($this->values[$changed_detail[0]] as $k => $v) {
            if ($counter == $changed_detail[1]) {
                if (!empty($function)) {
                    $data = $function($v);
                }
                $this->values[$changed_detail[0]][$k] = array_merge($v, $data);
            }
            $counter++;
        }
    }

    /**
     * Is submitted button.
     *
     * @param string|array $button
     * @return bool
     */
    public function isSubmitted(string|array $button): bool
    {
        if (!is_array($button)) {
            $button = [$button];
        }
        foreach ($button as $v) {
            if (!empty($this->process_submit[$v])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Change tab
     *
     * @param string $tab_container
     * @param string $tab_name
     */
    public function changeTab(string $tab_container, string $tab_name): void
    {
        $_POST['form_tabs_' . $this->form_link . '_' . $tab_container . '_active_hidden'] = $tab_name;
    }

    /**
     * Is field.
     *
     * @param array|string $fields
     * @param array $options
     *	bool not_empty
     *	bool tracked_difference
     * @return bool
     */
    public function isField(array|string $fields, array $options = []): bool
    {
        // if we did not change anything.
        if (empty($this->changed_field) && empty($this->changed_api_fields)) { // && empty($this->misc_settings['__default_field_changed'])
            return false;
        }
        if (!is_array($fields)) {
            $fields = [$fields];
        }
        if (!empty($this->changed_api_fields)) {
            if (!is_array($this->changed_api_fields)) {
                $this->changed_api_fields = [$this->changed_api_fields];
            }
            foreach ($this->changed_api_fields as $v) {
                if (in_array($v, $fields)) {
                    return true;
                }
            }
        }
        if (!empty($options['not_empty']) && empty($this->values[$this->changed_field])) {
            return false;
        }
        if (!empty($options['tracked_difference']) && in_array($this->changed_field, $fields)) {
            if (!empty($this->values[$this->changed_field]) && ($this->tracked_values[$this->changed_field] ?? '') != $this->values[$this->changed_field]) {
                return true;
            }
        }
        // we might have fields that changed in defauls.
        /*
        if (!empty($this->misc_settings['__default_field_changed'])) {
            foreach ($this->misc_settings['__default_field_changed'] as $v) {
                if (in_array($v, $fields)) {
                    return true;
                }
            }
        }
        */
        return in_array($this->changed_field, $fields);
    }

    /**
     * Format all values.
     *
     * @param array $input
     * @param array $options
     */
    public function formatAllValues($input, $options = [])
    {
        // reset values
        $this->formatted_values = [];
        // sort fields
        $fields = $this->sortFieldsForProcessing($this->fields);
        // process fields
        foreach ($fields as $k => $v) {
            // get value
            $value = array_key_get($input, $v['options']['values_key']);
            if (is_null($value)) {
                continue;
            }
            // format
            if (!empty($v['options']['format']) && empty($v['options']['options_model'])) {
                $method = \Factory::method($v['options']['format'], 'Format');
                if (!empty($v['options']['format_depends'])) {
                    $this->processParamsAndDepends($v['options']['format_depends'], $input, $v['options'], true);
                    $v['options']['format_options'] = array_merge_hard($v['options']['format_options'] ?? [], $v['options']['format_depends']);
                }
                $value = call_user_func_array([$method[0], $method[1]], [$value, $v['options']['format_options'] ?? []]);
                array_key_set($this->formatted_values, $v['options']['values_key'], $value);
            }
        }
        // process details & subdetails
        if (!empty($this->detail_fields)) {
            foreach ($this->detail_fields as $k => $v) {
                $this->formatted_values[$k] = []; // a must
                $details = $input[$k] ?? [];
                // 1 to 1
                if (!empty($v['options']['details_11'])) {
                    $details = [$details];
                }
                // sort fields
                $fields = $this->sortFieldsForProcessing($v['elements'], $v['options']);
                foreach ($details as $k2 => $v2) {
                    $detail = [];
                    // process fields
                    foreach ($fields as $k3 => $v3) {
                        // get value, grab from neighbouring values first
                        $value = $detail[$k3] ?? $v2[$k3] ?? null;
                        if (is_null($value)) {
                            continue;
                        }
                        // format
                        if (!empty($v3['options']['format']) && empty($v3['options']['options_model'])) {
                            $method = \Factory::method($v3['options']['format'], 'Format');
                            if (!empty($v3['options']['format_depends'])) {
                                $this->processParamsAndDepends($v3['options']['format_depends'], $v2, $v3['options'], true);
                                $v3['options']['format_options'] = array_merge_hard($v3['options']['format_options'] ?? [], $v3['options']['format_depends']);
                            }
                            $value = call_user_func_array([$method[0], $method[1]], [$value, $v3['options']['format_options'] ?? []]);
                        }
                        // add field to details
                        $detail[$k3] = $value;
                    }
                    // process subdetails, first to detect change
                    if (!empty($v['subdetails'])) {
                        foreach ($v['subdetails'] as $k0 => $v0) {
                            // make empty array
                            $detail[$k0] = [];
                            // sort fields
                            $subdetail_fields = $this->sortFieldsForProcessing($v0['elements']);
                            // go through data
                            if (!empty($v0['options']['details_11'])) {
                                $subdetail_data = [$v2[$k0] ?? []];
                            } else {
                                $subdetail_data = $v2[$k0] ?? [];
                            }
                            if (!empty($subdetail_data)) {
                                foreach ($subdetail_data as $k5 => $v5) {
                                    foreach ($subdetail_fields as $k6 => $v6) {
                                        $value = $v5[$k6] ?? null;
                                        if (is_null($value)) {
                                            continue;
                                        }
                                        // format
                                        if (!empty($v6['options']['format']) && empty($v6['options']['options_model'])) {
                                            $method = \Factory::method($v6['options']['format'], 'Format');
                                            if (!empty($v6['options']['format_depends'])) {
                                                $this->processParamsAndDepends($v6['options']['format_depends'], $v5, $v6['options'], true);
                                                $v6['options']['format_options'] = array_merge_hard($v6['options']['format_options'] ?? [], $v6['options']['format_depends']);
                                            }
                                            $value = call_user_func_array([$method[0], $method[1]], [$value, $v6['options']['format_options'] ?? []]);
                                        }
                                        $subdetail[$k6] = $value;
                                    }
                                    // set value
                                    if (!empty($v0['options']['details_11'])) {
                                        $detail[$k0] = $subdetail;
                                    } else {
                                        $detail[$k0][$k5] = $subdetail;
                                    }
                                }
                            }
                        }
                    }
                    // 1 to 1
                    if (!empty($v['options']['details_11'])) {
                        $this->formatted_values[$k] = $detail;
                    } else { // 1 to M
                        $this->formatted_values[$k][$k2] = $detail;
                    }
                }
            }
        }
    }
}
