<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Form\Wrapper;

use Object\ACL\Resources;
use Object\Form\API;
use Object\Form\Model\Overrides;
use Object\Form\Parent2;
use Object\ActiveRecord;

class Base extends Parent2
{
    /**
     * Form link
     *
     * @var string
     */
    public $form_link;

    /**
     * Form object
     *
     * @var object
     */
    public $form_object;

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
     * Options
     *
     * @var array
     */
    public $options = [];

    /**
     * Workflow steps
     *
     * @var array
     */
    public $workflow_steps = [];

    /**
     * Containers
     *
     * @var array
     */
    public $containers = [];

    /**
     * Rows
     *
     * @var array
     */
    public $rows = [];

    /**
     * Elements
     *
     * @var array
     */
    public $elements = [];

    /**
     * Sub forms
     *
     * @var array
     */
    public $subforms = [];

    /**
     * Collection
     *
     * @var mixed
     */
    public $collection;

    /**
     * Preloads
     *
     * @var array
     */
    public $preload_models = [];

    /**
     * A list of wrapper methods
     *
     * @var array
     */
    public $wrapper_methods = [];

    /**
     * Master options
     *
     * @var array
     */
    public $master_options = [];

    /**
     * Acl
     *
     * @var boolean
     */
    public $acl = true;

    /**
     * Column prefix
     *
     * @var string
     */
    public $column_prefix;

    /**
     * Localization
     *
     * @var array
     */
    public $loc = [];

    /**
     * Helper objects
     *
     * @var array
     */
    public $helper_objects = [];

    /**
     * Constructor
     *
     * @param array $options
     *		input - form input
     *		form - form options
     *		segment - segment options
     *			type
     *			header
     *			footer
     */
    public function __construct($options = [])
    {
        // we need to handle overrides
        parent::overrideHandle($this);
        // step 0: apply data fixes
        if (method_exists($this, 'overrides')) {
            $this->values = $options['input'] ?? [];
            $this->__options = $options;
            $this->overrides($this);
        }
        // we need to merge override input
        if (!empty($this->values)) {
            $options['input'] = array_merge_hard($options['input'] ?? [], $this->values);
        }
        if (isset($options['form_link'])) {
            $this->form_link = $options['form_link'];
        }
        // step 0: create form object
        $this->form_object = new \Object\Form\Base($this->form_link, array_merge_hard($this->options, $options));
        // class
        $this->form_object->form_class = '\\' . get_called_class();
        $options['initiator_class'] = $this->form_object->initiator_class = $options['initiator_class'] ?? 'form';
        $this->form_object->form_parent = & $this;
        // workflow
        if (!empty($this->workflow_steps)) {
            $this->form_object->workflow_activated = !empty($options['input']['__form_workflow_activated']);
            $this->form_object->workflow_step = $options['input']['__form_workflow_step_code'] ?? null;
            if (empty($this->form_object->workflow_step)) {
                $this->form_object->workflow_step = $options['input']['__form_workflow_step_code'] = array_key_first($this->workflow_steps);
            }
        } else {
            $this->form_object->workflow_activated = false;
            $this->form_object->workflow_step = null;
        }
        // buttons model
        if (!empty($this->buttons_model)) {
            $this->form_object->buttons_model = new $this->buttons_model();
        }
        // overrides
        $overrides_model = new Overrides();
        $overrides_data = $overrides_model->getOverrides($this->form_object->form_class);
        $overrides_objects = [];
        if (!empty($overrides_data)) {
            foreach ($overrides_data as $v) {
                $one_override = new $v($this);
                $overrides_objects[$v] = & $one_override;
            }
        }
        // list have preset collection
        if (empty($this->collection) && !empty($this->query_primary_model) && $this->form_object->initiator_class == 'list') {
            $this->collection = [
                'name' => $this->title,
                'model' => $this->query_primary_model,
            ];
        }
        // add collection
        $this->form_object->collection = $this->collection;
        $this->form_object->preloadCollectionObject(); // must initialize it before calls to container/row/element
        $this->form_object->column_prefix = $this->column_prefix ?? $this->form_object->collection_object->primary_model->column_prefix ?? null;
        // preload models
        $this->form_object->preload_models = $this->preload_models;
        // title
        if (!empty($this->title)) {
            $this->form_object->title = $this->title;
        } else {
            throw new \Exception('Title?');
        }
        // module code
        if (!empty($this->module_code)) {
            $this->form_object->module_code = $this->module_code;
        } else {
            throw new \Exception('Module Code?');
        }
        // step 1: methods
        foreach ([
            'refresh', 'validate', 'submit', 'save', 'post', 'success', 'finalize', 'finish',
            'owners', 'overrideFieldValue', 'overrideDetailValue', 'overrideTabs', 'processDefaultValue',
            'processOptionsModels', 'processAllValues', 'listQuery', 'buildReport', 'loadOriginalValues',
            'loadValues', 'preload', 'errored'
        ] as $v) {
            if (method_exists($this, $v)) {
                $this->form_object->wrapper_methods[$v]['main'] = [$this, $v];
            }
            // overrides can also have methods
            if (!empty($overrides_objects)) {
                foreach ($overrides_objects as $k2 => $v2) {
                    if (method_exists($v2, $v)) {
                        $this->form_object->wrapper_methods[$v][$k2] = [$v2, $v];
                    }
                }
            }
        }
        // extensions can have their own verify methods
        if (!empty($this->wrapper_methods)) {
            foreach ($this->wrapper_methods as $k => $v) {
                $index = 1;
                foreach ($v as $k2 => $v2) {
                    $this->form_object->wrapper_methods[$k][$index] = [new $k2(), $v2];
                    $index++;
                }
            }
        }
        // load form overrides
        $overrides_fields = [];
        $overrides_model = Resources::getStatic('form_overrides', 'primary', 'model');
        if (!empty($overrides_model) && empty($options['skip_processing'])) {
            $temp_model = new $overrides_model();
            $overrides_fields = $temp_model->get([
                'where' => [
                    'form_model' => $this->form_object->form_class
                ]
            ]);
        }
        // email overrides
        if ($this->form_object->initiator_class == 'email') {
            if (!empty($this->form_object->form_parent->options['segment']) && !isset($this->form_object->form_parent->options['segment']['class'])) {
                $this->form_object->options['segment']['class'] = 'numbers_frontend_form_email_segment_container';
            }
        }
        // segment title
        if (isset($this->form_object->form_parent->options['segment']['header']['loc'])) {
            $this->loc[$this->form_object->form_parent->options['segment']['header']['loc']] = $this->form_object->form_parent->options['segment']['header']['title'];
        } elseif (isset($this->form_object->options['segment']['header']['title'])) {
            $loc_key = \String2::createStatic($this->form_object->options['segment']['header']['title'])->englishOnly(true)->toString();
            $this->loc['NF.Form.' . $loc_key] = $this->form_object->options['segment']['header']['title'];
            $this->form_object->options['segment']['header']['loc'] = 'NF.Form.' . $loc_key;
        }
        if (isset($this->form_object->form_parent->options['segment']['footer']['loc'])) {
            $this->loc[$this->form_object->form_parent->options['segment']['footer']['loc']] = $this->form_object->form_parent->options['segment']['footer']['title'];
        } elseif (isset($this->form_object->options['segment']['footer']['title'])) {
            $loc_key = \String2::createStatic($this->form_object->options['segment']['footer']['title'])->englishOnly(true)->toString();
            $this->loc['NF.Form.' . $loc_key] = $this->form_object->options['segment']['footer']['title'];
            $this->form_object->options['segment']['footer']['loc'] = 'NF.Form.' . $loc_key;
        }
        // hidden content
        if ($this->form_object->initiator_class == 'form') {
            $this->containers['__hidden_buttons_container'] = ['default_row_type' => 'grid', 'order' => PHP_INT_MAX];
            $this->elements['__hidden_buttons_container'][self::BUTTONS][self::BUTTON_HIDDEN_SUBMIT] = self::BUTTON_HIDDEN_SUBMIT_DATA;
            $this->elements['__hidden_buttons_container'][self::BUTTONS][self::BUTTON_HIDDEN_SUBMIT]['row_class'] = 'grid_row_hidden';
        }
        // workflow
        $workflow_current_containers = [];
        $workflow_hidden_containers = [];
        $workflow_not_visible_containers = [];
        if ($this->form_object->workflow_activated) {
            // we need to unset tabs containers
            foreach ($this->containers as $k => $v) {
                if (($v['type'] ?? '') == 'tabs') { // || $k == 'buttons'
                    $workflow_hidden_containers[] = $k;
                    unset($this->containers[$k]);
                }
            }
            // append workflow containers
            $step_data = $this->workflow_steps[$this->form_object->workflow_step];
            $this->containers[self::WORKFLOW_STEPS_TOP_PANEL] = ['order' => PHP_INT_MIN + 1000, 'custom_renderer' => '\Object\Form\Workflow\Base::renderWorkflow'];
            $this->containers[self::WORKFLOW_VISIBLE_CONTAINER] = ['type' => 'panels', 'order' => PHP_INT_MIN + 2000];
            $this->containers[self::WORKFLOW_HIDDEN_CONTAINER] = ['type' => 'panels', 'order' => PHP_INT_MIN + 3000];
            $this->rows[self::WORKFLOW_VISIBLE_CONTAINER]['middle'] = ['order' => 100, 'label_name' => $step_data['label_name'], 'panel_icon' => isset($step_data['icon']) ? ['type' => $step_data['icon']] : null, 'panel_type' => $step_data['panel_type'] ?? 'secondary', 'percent' => 100];
            // review containers
            if ($this->form_object->workflow_step != self::WORKFLOW_REVIEW_CONTAINER) {
                $step_order = 10000;
                foreach ($step_data['containers'] as $v) {
                    $workflow_current_containers[] = $v;
                    $this->elements[self::WORKFLOW_VISIBLE_CONTAINER]['middle']['__generated_' . $v . '_' . $step_order] = ['container' => $v, 'order' => $step_order];
                    $step_order += 100;
                }
            } else {
                $step_order = 20000;
                foreach ($this->workflow_steps as $k => $v) {
                    if ($k == self::WORKFLOW_REVIEW_CONTAINER) {
                        continue;
                    }
                    if (!empty($v['label_name'])) {
                        $workflow_current_containers[] = self::SEPARATOR_HORIZONTAL . '_separator_' . $k;
                        $this->elements[self::SEPARATOR_HORIZONTAL . '_separator_' . $k]['separator'][self::SEPARATOR_HORIZONTAL] = ['order' => 100, 'row_order' => $step_order, 'label_name' => $v['label_name'], 'icon' => $v['icon'] ?? '', 'percent' => 100];
                        $this->elements[self::WORKFLOW_VISIBLE_CONTAINER]['middle']['__generated_' . $k . '_step_' . $step_order] = ['container' => self::SEPARATOR_HORIZONTAL . '_separator_' . $k, 'order' => $step_order];
                        $step_order += 100;
                    }
                    foreach ($v['containers'] as $v2) {
                        $workflow_current_containers[] = $v2;
                        $this->elements[self::WORKFLOW_VISIBLE_CONTAINER]['middle']['__generated_' . $v2 . '_' . $step_order] = ['container' => $v2, 'order' => $step_order];
                        if (($this->containers[$v2]['type'] ?? '') != 'details') {
                            $this->containers[$v2]['default_row_type'] = 'table';
                            $this->containers[$v2]['column_name_width_percent'] = 15;
                            $this->containers[$v2]['class'] = 'numbers_frontend_form_table_row_container';
                        } else {
                            $this->containers[$v2]['default_row_type'] = 'table';
                            $this->containers[$v2]['default_row_label_name'] = $v['label_name'] . ':';
                            $this->containers[$v2]['column_name_width_percent'] = 15;
                            $this->containers[$v2]['class'] = 'numbers_frontend_form_table_row_container';
                        }
                        $step_order += 100;
                    }
                }
            }
            // add next / prev
            $previous_hidden = [];
            if ($this->form_object->workflow_step == array_key_first($this->workflow_steps)) {
                $previous_hidden = ['hidden' => true];
            }
            $next_hidden = [];
            if ($this->form_object->workflow_step == array_key_last($this->workflow_steps)) {
                $next_hidden = ['hidden' => true];
            }
            $submit_hidden = [];
            if ($this->form_object->workflow_step != array_key_last($this->workflow_steps)) {
                $submit_hidden = ['hidden' => true];
            }
            $this->containers[self::WORKFLOW_VISIBLE_BUTTONS] = ['order' => PHP_INT_MAX - 1000];
            $this->elements[self::WORKFLOW_VISIBLE_BUTTONS][self::BUTTONS][self::BUTTON_WORKFLOW_PREVIOUS_SUBMIT] = self::BUTTON_WORKFLOW_PREVIOUS_SUBMIT_DATA + $previous_hidden;
            $this->elements[self::WORKFLOW_VISIBLE_BUTTONS][self::BUTTONS][self::BUTTON_WORKFLOW_NEXT_SUBMIT] = self::BUTTON_WORKFLOW_NEXT_SUBMIT_DATA + $next_hidden;
            $this->elements[self::WORKFLOW_VISIBLE_BUTTONS][self::BUTTONS][self::BUTTON_WORKFLOW_SUBMIT] = self::BUTTON_WORKFLOW_SUBMIT_DATA + $submit_hidden;
            $this->elements[self::WORKFLOW_VISIBLE_CONTAINER]['middle']['__generated_visible_' . self::WORKFLOW_VISIBLE_CONTAINER . '_' . $step_order] = ['container' => self::WORKFLOW_VISIBLE_BUTTONS, 'order' => $step_order];
            $step_order += 100;
            // hidden container
            $this->rows[self::WORKFLOW_HIDDEN_CONTAINER]['middle'] = ['order' => 100, 'label_name' => 'Hidden Panel', 'panel_type' => 'hidden', 'percent' => 100];
            $step_order = 100;
            foreach ($this->elements as $k => $v) {
                if (in_array($k, $workflow_current_containers)) {
                    continue;
                }
                if (in_array($k, $workflow_hidden_containers)) {
                    continue;
                }
                if (in_array($k, [self::WORKFLOW_VISIBLE_CONTAINER, self::WORKFLOW_VISIBLE_BUTTONS, self::WORKFLOW_HIDDEN_CONTAINER])) {
                    continue;
                }
                $workflow_not_visible_containers[] = $k;
                $this->elements[self::WORKFLOW_HIDDEN_CONTAINER]['middle']['__generated_hidden_' . $k . '_' . $step_order] = ['container' => $k, 'order' => $step_order];
                $step_order += 100;
            }
        }
        // step 2: create all containers
        foreach ($this->containers as $k => $v) {
            if ($v === null) {
                continue;
            }
            // email template
            if ($this->form_object->initiator_class == 'email') {
                if (!array_key_exists('class', $v) && ($v['default_row_type'] ?? '') == 'table') {
                    $v['class'] = 'numbers_frontend_form_table_row_container';
                }
            }
            if (!array_key_exists('default_row_type', $v)) {
                $v['default_row_type'] = 'grid';
            }
            // footer we add if configured
            if ($this->form_object->initiator_class == 'email' && $k == self::PANEL_FOOTER) {
                $custom_renderer = \Application::get('brand.email.footer.method');
                if (empty($v['custom_renderer']) && $custom_renderer) {
                    $v['custom_renderer'] = $custom_renderer;
                } elseif (empty($v['custom_renderer'])) {
                    $v['custom_renderer'] = '\Numbers\Users\Users\Helper\Brand\Footer::renderBottomFooter';
                }
            }
            $this->form_object->container($k, $v + ['skip_processing' => $options['skip_processing'] ?? false]);
            // additional options
            if ($this->form_object->initiator_class == 'email') {
                if ($k == self::PANEL_MESSAGE) {
                    $this->form_object->container(self::PANEL_MESSAGE . '_container', ['default_row_type' => 'grid', 'order' => PHP_INT_MAX]);
                    $this->rows[self::PANEL_MESSAGE]['center'] = ['order' => 100, 'label_name' => $this->loc[$v['loc']] ?? ' ', 'loc' => $v['loc'], 'panel_type' => DANGER, 'percent' => 100];
                    $this->elements[self::PANEL_MESSAGE]['center'][self::PANEL_MESSAGE . '_header'] = ['container' => self::PANEL_MESSAGE . '_container', 'order' => 100];
                    $this->elements[self::PANEL_MESSAGE . '_container'][self::PANEL_MESSAGE . '_container']['__message'] = ['order' => 1, 'row_order' => 100, 'label_name' => '', 'loc_options' => $v['loc_options'], 'domain' => 'message[]', 'percent' => 100, 'method' => 'h4'];
                }
            }
            // SMS
            if ($this->form_object->initiator_class == 'SMS') {
                if ($k == self::SMS_MESSAGE) {
                    $this->elements[self::SMS_MESSAGE][self::SMS_MESSAGE . '_row']['__message'] = ['order' => 1, 'row_order' => 100, 'label_name' => '', 'domain' => 'message[]', 'percent' => 100, 'method' => 'text'];
                }
            }
        }
        // step 3: create all rows
        foreach ($this->rows as $k => $v) {
            // we do not add hidden workflow containers
            if (in_array($k, $workflow_hidden_containers)) {
                continue;
            }
            foreach ($v as $k2 => $v2) {
                if ($v2 === null) {
                    continue;
                }
                $this->form_object->row($k, $k2, $v2);
                // loc
                if (isset($v2['loc'])) {
                    $this->loc[$v2['loc']] = $v2['label_name'] ?? '';
                } elseif (isset($v2['label_name']) && $v2['label_name'] !== '') {
                    $this->loc['NF.Form.' . \String2::createStatic($v2['label_name'])->englishOnly(true)->toString()] = $v2['label_name'];
                }
            }
        }
        // step 4: create all elements
        foreach ($this->elements as $k => $v) {
            // we do not add hidden workflow containers
            if (in_array($k, $workflow_hidden_containers)) {
                continue;
            }
            foreach ($v as $k2 => $v2) {
                foreach ($v2 as $k3 => $v3) {
                    if ($v3 === null) {
                        continue;
                    }
                    // if we have an override
                    // todo - revisit, only allow disabling
                    if (!empty($overrides_fields[$k3])) {
                        if ($overrides_fields[$k3]['action'] == 10) {
                            $v3['readonly'] = true;
                            if (($v3['method'] ?? '') == 'multiselect') {
                                $v3['method'] = 'select';
                            }
                        } elseif ($overrides_fields[$k3]['action'] == 30) {
                            if ($this->form_object->initiator_class == 'form') {
                                continue;
                            } else {
                                if ($k == self::LIST_CONTAINER || $k == self::LIST_LINE_CONTAINER) {
                                    $k3 = $k3 . '_dummy_field';
                                    $v3['label_name'] = '';
                                } else {
                                    continue;
                                }
                            }
                        }
                    }
                    // email template
                    if ($this->form_object->initiator_class == 'email') {
                        $v3['null'] = true;
                        if (isset($this->form_object->form_parent->options['all_static'])) {
                            if (!array_key_exists('static', $v3)) {
                                $v3['static'] = $this->form_object->form_parent->options['all_static'];
                            }
                        }
                    }
                    // flags
                    $k2_copy = $k2;
                    if (!empty($v3['sysflag'])) {
                        // set itself
                        $values = null;
                        if (isset($this->form_object->options['__input_override_blanks'][$k3])) {
                            $values = $this->form_object->options['__input_override_blanks'][$k3];
                        } elseif (array_key_exists($k3, $this->form_object->options['__input_override_blanks'] ?? []) && empty($this->form_object->options['__input_override_blanks'][$k3])) {
                            $values = '';
                        }
                        if (isset($this->form_object->options['input'][$k3])) {
                            $values = $this->form_object->options['input'][$k3];
                        } elseif (array_key_exists($k3, $this->form_object->options['input'] ?? []) && empty($this->form_object->options['input'][$k3])) {
                            $values = '';
                        }
                        if (\Can::userFlagExists($v3['sysflag'], 'Filter_Self')) {
                            $this->form_object->options['__input_override_blanks'][$k3] = $this->form_object->options['input'][$k3] = \User::getUser() ?? \User::id();
                            $v3['readonly'] = true;
                            $v3['method'] = 'select';
                        } elseif (\Can::userFlagExists($v3['sysflag'], 'Filter_PresetSelf') && !isset($values)) {
                            if (empty($options['input']['__list_report_filter_loaded'])) {
                                $this->form_object->options['__input_override_blanks'][$k3] = $this->form_object->options['input'][$k3] = \User::getUser() ?? \User::id();
                            }
                        } elseif (\Can::userFlagExists($v3['sysflag'], 'Filter_Hide')) { // hide
                            $k2_copy = self::HIDDEN;
                        }
                    }
                    // default value we preset input
                    if (array_key_exists('default_value', $v3) && !array_key_exists($k3, $options['input'] ?? [])) {
                        $this->form_object->options['input'][$k3] = $options['input'][$k3] = $v3['default_value'];
                    }
                    // loc
                    if (isset($v3['loc'])) {
                        $this->loc[$v3['loc']] = $v3['label_name'] ?? $v3['value'] ?? '';
                    } else {
                        $found = false;
                        if (isset($v3['label_name']) && $v3['label_name'] !== '') {
                            $this->loc['NF.Form.' . \String2::createStatic($v3['label_name'])->englishOnly(true)->toString()] = $v3['label_name'];
                            $v3['loc'] = 'NF.Form.' . \String2::createStatic($v3['label_name'])->englishOnly(true)->toString();
                            $found = true;
                        }
                        if (isset($v3['value']) && $v3['value'] !== '') {
                            $this->loc['NF.Form.' . \String2::createStatic($v3['value'])->englishOnly(true)->toString()] = $v3['value'];
                            if (!$found) {
                                $v3['loc'] = 'NF.Form.' . \String2::createStatic($v3['value'])->englishOnly(true)->toString();
                            }
                        }
                        if (isset($v3['placeholder']) && $v3['placeholder'] !== '') {
                            $this->loc['NF.Form.' . \String2::createStatic($v3['placeholder'])->englishOnly(true)->toString()] = $v3['placeholder'];
                        }
                    }
                    // add element
                    $this->form_object->element($k, $k2_copy, $k3, $v3);
                }
            }
        }
        // list sort
        if ($options['initiator_class'] == 'list') {
            foreach ($this::LIST_SORT_OPTIONS as $v3) {
                $this->loc['NF.Form.' . \String2::createStatic($v3['name'])->englishOnly(true)->toString()] = $v3['name'];
            }
        }
        // saved filters
        if ($this->form_object->initiator_class === 'list' || $this->form_object->initiator_class === 'report') {
            $class = Resources::getStatic('widgets', 'filters', 'form_builder');
            if (!empty($class)) {
                $model = new $class();
                $model->addFilterToForm($this->form_object, $options);
            }
        }
        // saved batches and lists
        if ($this->form_object->initiator_class === 'list' || $this->form_object->initiator_class === 'report') {
            $class = Resources::getStatic('widgets', 'batches_and_lists', 'form_builder');
            if (!empty($class)) {
                $model = new $class();
                $model->addBatchesAndListsToForm($this->form_object, $options);
            }
        }
        // scoped attributes inside of a form
        if (\Application::get('scoped_attributes.subform.list') && $this->form_object->initiator_class === 'form' && !empty($this->form_object->collection_object->primary_model->scoped_records)) {
            $this->form_object->presetABACScopedAttributesSubform($this->form_object->initiator_class);
        }
        // scoped attributes inside of a list
        if (\Application::get('scoped_attributes.subform.list') && $this->form_object->initiator_class === 'list' && !empty($this->form_object->collection_object->primary_model->scoped_records)) {
            $this->form_object->presetABACScopedAttributesSubform($this->form_object->initiator_class);
        }
        // sub-forms from AI tools
        if (\User::authorized()) {
            $ai_subforms = Resources::getStatic('ai_sdk_form_tools', 'primary');
            if (!empty($ai_subforms)) {
                foreach ($ai_subforms as $subform) {
                    $subform['ai_tool_form_settings_json'] = json_decode($subform['ai_tool_form_settings_json'], true);
                    $this->subforms[$subform['ai_tool_form_settings_json']['link']] = $subform['ai_tool_form_settings_json'];
                }
            }
        }
        // sub-forms
        if (!empty($this->subforms)) {
            $index = 1;
            foreach ($this->subforms as $k => $v) {
                // check if we have subresource
                if (isset($v['acl_subresource_hide'])) {
                    if (\Application::$controller && !\Application::$controller->canSubresourceMultiple($v['acl_subresource_hide'][0], $v['acl_subresource_hide'][1])) {
                        unset($this->subforms[$k]);
                        continue;
                    }
                }
                if (!empty($v['acl_need_authorized'])) {
                    if (!\User::authorized()) {
                        unset($this->subforms[$k]);
                        continue;
                    }
                }
                // bypass variables
                $bypass_fields = array_merge($v['bypass_hidden_from_input_fields'] ?? [], $this->form_object->options['bypass_hidden_from_input'] ?? []);
                $temp_bypass_hidden_input = $v['bypass_hidden_from_input'] ?? [];
                if (!empty($bypass_fields)) {
                    foreach ($bypass_fields as $v2) {
                        $temp_bypass_hidden_input[$v2] = $this->form_object->options['input'][$v2] ?? '';
                    }
                }
                $temp_bypass_hidden_input = json_encode($temp_bypass_hidden_input);
                $temp_prefix = '';
                if (!empty($v['actions']['new']['append'])) {
                    $temp_prefix = '_' . $index;
                }
                $button = $v['button_to_submit'] ?? '__submit_blank';
                $subform_options = json_encode([
                    $button => true,
                    '__subform_setting_form' => $v['form'],
                    '__subform_setting_icon' => $v['icon'] ?? '',
                    '__subform_setting_label_name' => $v['label_name'],
                    '__subform_stripped_version' => $v['actions']['button']['options']['__subform_stripped_version'] ?? '',
                ]);
                // if we can create a record
                if (!empty($v['actions']['new'])) {
                    if ((!empty($this->form_object->options['acl_subresource_edit']) && \Application::$controller->canSubresourceMultiple($this->form_object->options['acl_subresource_edit'], 'Record_New')) || empty($this->form_object->options['acl_subresource_edit'])) {
                        $temp_collection_link = $this->form_object->options['collection_link'] ?? '';
                        $temp_collection_screen_link = $this->form_object->options['collection_screen_link'] ?? '';
                        $this->form_object->actions['form_new' . $temp_prefix] = [
                            'href' => 'javascript:void(0);',
                            'class' => 'numbers_frontend_form_action_links',
                            'onclick' => "Numbers.Form.openSubformWindow('{$temp_collection_link}', '{$temp_collection_screen_link}', '{$this->form_link}', '{$k}', {$temp_bypass_hidden_input}, {$subform_options});",
                            'value' => $v['actions']['new']['name'],
                            'sort' => -31000,
                            'icon' => $v['actions']['new']['icon'] ?? 'fa-regular fa-file',
                            // group all new items
                            'group_by_name' => 'New',
                            'group_by_icon' => 'fa-regular fa-file',
                        ];
                    }
                }
                // edit
                if (!empty($v['actions']['edit']['url_edit'])) {
                    if ((!empty($this->form_object->options['acl_subresource_edit']) && \Application::$controller->canSubresourceMultiple($this->form_object->options['acl_subresource_edit'], 'Record_Edit')) || empty($this->form_object->options['acl_subresource_edit'])) {
                        $subform_options = json_encode([
                            '__subform_setting_form' => $v['form'],
                            '__subform_setting_icon' => $v['icon'] ?? '',
                            '__subform_setting_label_name' => $v['label_name'],
                        ]);
                        $this->form_object->misc_settings['subforms']['url_edit'] = [
                            'subform_link' => $k,
                            'bypass_hidden_input' => $temp_bypass_hidden_input,
                            'subform_options' => $subform_options,
                        ];
                    } else {
                        $this->form_object->misc_settings['subforms']['url_edit'] = false;
                    }
                }
                // delete
                if (!empty($v['actions']['delete']['url_delete'])) {
                    if ((!empty($this->form_object->options['acl_subresource_edit']) && \Application::$controller->canSubresourceMultiple($this->form_object->options['acl_subresource_edit'], 'Record_Delete')) || empty($this->form_object->options['acl_subresource_edit'])) {
                        $subform_options = json_encode([
                            '__subform_setting_form' => $v['form'],
                            '__subform_setting_icon' => $v['icon'] ?? '',
                            '__subform_setting_label_name' => $v['label_name'],
                        ]);
                        $this->form_object->misc_settings['subforms']['url_delete'] = [
                            'subform_link' => $k,
                            'bypass_hidden_input' => $temp_bypass_hidden_input,
                            'subform_options' => $subform_options,
                        ];
                    } else {
                        $this->form_object->misc_settings['subforms']['url_delete'] = false;
                    }
                }
                $index++;
            }
        }
        // loc for actions
        foreach ($this->form_object->actions as $v) {
            if (empty($v['value'])) {
                continue;
            }
            $this->loc['NF.Form.' . \String2::createStatic($v['value'])->englishOnly(true)->toString()] = $v['value'];
            // grouped actions
            if (!empty($v['group_by_name'])) {
                $this->loc['NF.Form.' . \String2::createStatic($v['group_by_name'])->englishOnly(true)->toString()] = $v['group_by_name'];
            }
        }
        // loc for workflows
        foreach ($this->workflow_steps ?? [] as $k => $v) {
            if (empty($v['label_name'])) {
                continue;
            }
            $this->loc['NF.Form.' . \String2::createStatic($v['label_name'])->englishOnly(true)->toString()] = $v['label_name'];
        }
        // readonly forms
        if (!empty($this->form_object->options['readonly'])) {
            $this->form_object->readonly();
        }
        // last step: process form
        if (empty($options['skip_processing'])) {
            $this->form_object->process();
        }
    }

    /**
     * Render form
     *
     * @param string $format
     * @return string
     */
    public function render($format = 'text/html')
    {
        return $this->form_object->render($format);
    }

    /**
     * API result
     *
     * @return array
     */
    public function apiResult(): array
    {
        return $this->form_object->apiResult();
    }

    /**
     * Create API object
     *
     * @return API
     */
    public static function API(): API
    {
        $class = '\\' . get_called_class();
        $model = new $class([
            'skip_processing' => true,
            'skip_optimistic_lock' => true,
            'skip_acl' => true // a must because we might execute it from different controller
        ]);
        $api = new API($model);
        return $api;
    }

    /**
     * Get helper object
     *
     * @param string $type
     * @param \Object\Form\Base $form
     * @return mixed
     */
    public function getHelperObject(string $type, \Object\Form\Base & $form): mixed
    {
        $class = $this->helper_objects[$type]['class'];
        if (empty($class) || empty($form->values[$this->helper_objects[$type]['column']])) {
            return null;
        }
        /** @var ActiveRecord $model */
        $model = new $class();
        $inner_model = $model->getTableObject()->getByColumn(
            $this->helper_objects[$type]['inner_where'],
            $form->values[$this->helper_objects[$type]['column']],
            $this->helper_objects[$type]['inner_column']
        );
        if (!$inner_model) {
            return null;
        }
        return \Factory::model($inner_model, true);
    }
}
