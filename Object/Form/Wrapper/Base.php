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
        // we need to handle overrrides
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
        $this->form_object->initiator_class = $options['initiator_class'] ?? 'form';
        $this->form_object->form_parent = & $this;
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
        // add collection
        $this->form_object->collection = $this->collection;
        $this->form_object->preloadCollectionObject(); // must initialize it before calls to container/row/element
        $this->form_object->column_prefix = $this->column_prefix ?? $this->form_object->collection_object->primary_model->column_prefix ?? null;
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
        foreach (['refresh', 'validate', 'save', 'post', 'success', 'finalize',
            'owners', 'overrideFieldValue', 'overrideDetailValue', 'overrideTabs', 'processDefaultValue',
            'processOptionsModels', 'processAllValues', 'listQuery', 'buildReport', 'loadOriginalValues', 'loadValues'] as $v) {
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
        }
        if (isset($this->form_object->form_parent->options['segment']['footer']['loc'])) {
            $this->loc[$this->form_object->form_parent->options['segment']['footer']['loc']] = $this->form_object->form_parent->options['segment']['footer']['title'];
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
            // footer we add if cofigured
            if ($this->form_object->initiator_class == 'email' && $k == self::PANEL_FOOTER) {
                $custom_renderer = \Application::get('brand.email.footer.method');
                if (empty($v['custom_renderer']) && $custom_renderer) {
                    $v['custom_renderer'] = $custom_renderer;
                } elseif (empty($v['custom_renderer'])) {
                    $v['custom_renderer'] = '\Numbers\Users\Users\Helper\Brand\Footer::renderBottomFooter';
                }
            }
            $this->form_object->container($k, $v);
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
            foreach ($v as $k2 => $v2) {
                if ($v2 === null) {
                    continue;
                }
                $this->form_object->row($k, $k2, $v2);
                // loc
                if (isset($v2['loc'])) {
                    $this->loc[$v2['loc']] = $v2['label_name'] ?? '';
                }
            }
        }
        // step 4: create all elements
        foreach ($this->elements as $k => $v) {
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
                    $this->form_object->element($k, $k2_copy, $k3, $v3);
                    // loc
                    if (isset($v3['loc'])) {
                        $this->loc[$v3['loc']] = $v3['label_name'] ?? $v3['value'] ?? '';
                    }
                }
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
        // subforms
        if (!empty($this->subforms)) {
            foreach ($this->subforms as $k => $v) {
                // if we can create a record
                if (!empty($v['actions']['new'])) {
                    if ((!empty($this->form_object->options['acl_subresource_edit']) && \Application::$controller->canSubresourceMultiple($this->form_object->options['acl_subresource_edit'], 'Record_New')) || empty($this->form_object->options['acl_subresource_edit'])) {
                        $temp_collection_link = $this->form_object->options['collection_link'] ?? '';
                        $temp_collection_screen_link = $this->form_object->options['collection_screen_link'] ?? '';
                        // bypass variables
                        $temp_bypass_hidden_input = [];
                        if (!empty($this->form_object->options['bypass_hidden_from_input'])) {
                            foreach ($this->form_object->options['bypass_hidden_from_input'] as $v2) {
                                $temp_bypass_hidden_input[$v2] = $this->form_object->options['input'][$v2] ?? '';
                            }
                        }
                        $temp_bypass_hidden_input = json_encode($temp_bypass_hidden_input);
                        $this->form_object->actions['form_new'] = [
                            'href' => 'javascript:void(0);',
                            'onclick' => "Numbers.Form.openSubformWindow('{$temp_collection_link}', '{$temp_collection_screen_link}', '{$this->form_link}', '{$k}', {$temp_bypass_hidden_input}, {__submit_blank: true});",
                            'value' => $v['actions']['new']['name'],
                            'sort' => -31000,
                            'icon' => 'far fa-file'
                        ];
                    }
                }
                // edit
                if (!empty($v['actions']['edit']['url_edit'])) {
                    if ((!empty($this->form_object->options['acl_subresource_edit']) && \Application::$controller->canSubresourceMultiple($this->form_object->options['acl_subresource_edit'], 'Record_Edit')) || empty($this->form_object->options['acl_subresource_edit'])) {
                        $this->form_object->misc_settings['subforms']['url_edit'] = [
                            'subform_link' => $k
                        ];
                    } else {
                        $this->form_object->misc_settings['subforms']['url_edit'] = false;
                    }
                }
                // delete
                if (!empty($v['actions']['delete']['url_delete'])) {
                    if ((!empty($this->form_object->options['acl_subresource_edit']) && \Application::$controller->canSubresourceMultiple($this->form_object->options['acl_subresource_edit'], 'Record_Delete')) || empty($this->form_object->options['acl_subresource_edit'])) {
                        $this->form_object->misc_settings['subforms']['url_delete'] = [
                            'subform_link' => $k
                        ];
                    } else {
                        $this->form_object->misc_settings['subforms']['url_delete'] = false;
                    }
                }
            }
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
