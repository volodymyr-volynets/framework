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

use Object\Content\Messages;
use Object\Override\Data;

class Parent2 extends Data
{
    /**
     * Separators
     */
    public const SEPARATOR_VERTICAL = '__separator_vertical';
    public const SEPARATOR_HORIZONTAL = '__separator_horizontal';

    /**
     * List container
     */
    public const LIST_CONTAINER = '__list_container';
    public const LIST_LINE_CONTAINER = '__list_line_container';

    /**
     * Kanban
     */
    public const KANBAN_CONTAINER = '__kanban_container';

    /**
     * Panel for messages
     */
    public const PANEL_MESSAGE = '__message_panel';

    /**
     * Panel for logo
     */
    public const PANEL_LOGO = '__logo_panel';

    /**
     * Panel for brand name
     */
    public const PANEL_BRAND = '__brand_panel';

    /**
     * Panel for footer
     */
    public const PANEL_FOOTER = '__footer_panel';

    /**
     * Panel for SMS message
     */
    public const SMS_SUBJECT = '__sms_subject';

    /**
     * Panel for SMS message
     */
    public const SMS_MESSAGE = '__sms_message';

    /**
     * List buttons
     */
    public const LIST_BUTTONS = '__list_buttons';
    public const LIST_BUTTONS_DATA = [
        '__format' => [
            '__format' => ['order' => 1, 'container_order' => PHP_INT_MAX - 1000, 'container_class' => 'numbers_form_filter_sort_container', 'label_name' => 'Format', 'percent' => 25, 'required' => true, 'method' => 'select', 'default' => 'text/html', 'no_choose' => true, 'options_model' => '\Object\Form\Model\Content\Types', 'options_options' => ['i18n' => 'skip_sorting']]
        ],
        self::BUTTONS => [
            self::BUTTON_SUBMIT => self::BUTTON_SUBMIT_DATA + ['onclick' => '$(this.form).attr(\'no_ajax\', 1); return true;'],
            self::BUTTON_SUBMIT_BLANK => self::BUTTON_SUBMIT_BLANK_DATA,
            self::BUTTON_SUBMIT_RESET => self::BUTTON_SUBMIT_RESET_DATA
        ]
    ];

    /**
     * Report buttons
     */
    public const REPORT_BUTTONS = '__report_buttons';
    public const REPORT_BUTTONS_DATA = [
        '__format' => [
            '__format' => ['order' => 1, 'container_order' => PHP_INT_MAX - 1000, 'container_class' => 'numbers_form_filter_sort_container', 'label_name' => 'Format', 'percent' => 25, 'required' => true, 'method' => 'select', 'default' => 'text/html', 'no_choose' => true, 'options_model' => '\Object\Form\Model\Report\Types', 'options_options' => ['i18n' => 'skip_sorting']]
        ],
        self::BUTTONS => [
            self::BUTTON_SUBMIT => self::BUTTON_SUBMIT_DATA + ['onclick' => '$(this.form).attr(\'no_ajax\', 1); return true;'],
            self::BUTTON_SUBMIT_BLANK => self::BUTTON_SUBMIT_BLANK_DATA,
            self::BUTTON_SUBMIT_RESET => self::BUTTON_SUBMIT_RESET_DATA
        ]
    ];

    /**
     * List sort container
     */
    public const LIST_SORT_CONTAINER = [
        'type' => 'details',
        'details_rendering_type' => 'table',
        'details_new_rows' => 1,
        'details_key' => '\Object\Form\Model\Dummy\Sort',
        'details_pk' => ['__sort'],
        'order' => 1600
    ];

    /**
     * Filter sort
     */
    public const LIST_FILTER_SORT = [
        'value' => 'Filter/Sort',
        'sort' => 32000,
        'icon' => 'fas fa-filter',
        'onclick' => 'Numbers.Form.listFilterSortToggle(this);'
    ];

    /**
     * Row for buttons
     */
    public const BUTTONS = '__submit_buttons';

    /**
     * Row for batch buttons
     */
    public const TRANSACTION_BUTTONS = '__submit_transaction_buttons';

    /**
     * Row for buttons
     */
    public const WIDE_BUTTONS = '__submit_wide_buttons';

    /**
     * Hidden container/row
     */
    public const HIDDEN = '__hidden_row_or_container';

    /**
     * Submit button
     */
    public const BUTTON_SUBMIT = '__submit_button';
    public const BUTTON_SUBMIT_DATA = ['order' => -100, 'button_group' => 'left', 'value' => 'Submit', 'loc' => 'NF.Form.Submit', 'method' => 'button2', 'icon' => 'fas fa-mouse-pointer', 'accesskey' => 's', 'process_submit' => true];

    /**
     * Other submit button
     */
    public const BUTTON_SUBMIT_OTHER = '__submit_button_2';
    public const BUTTON_SUBMIT_OTHER_DATA = ['order' => -100, 'button_group' => 'left', 'value' => 'Submit', 'loc' => 'NF.Form.Submit', 'method' => 'button2', 'icon' => 'fas fa-mouse-pointer', 'accesskey' => 's', 'process_submit' => 'other'];

    /**
     * Approve button
     */
    public const BUTTON_SUBMIT_APPROVE = '__approve_button';
    public const BUTTON_SUBMIT_APPROVE_DATA = ['order' => -100, 'button_group' => 'left', 'value' => 'Approve', 'loc' => 'NF.Form.Approve', 'method' => 'button2', 'icon' => 'far fa-handshake', 'accesskey' => 's', 'process_submit' => 'other'];

    /**
     * Approve button
     */
    public const BUTTON_SUBMIT_DECLINE = '__decline_button';
    public const BUTTON_SUBMIT_DECLINE_DATA = ['order' => -100, 'button_group' => 'left', 'type' => 'danger', 'value' => 'Decline', 'loc' => 'NF.Form.Decline', 'method' => 'button2', 'icon' => 'fas fa-stop', 'accesskey' => 'x', 'process_submit' => 'other'];

    /**
     * Continue button
     */
    public const BUTTON_CONTINUE = '__continue_button';
    public const BUTTON_CONTINUE_DATA = ['order' => -100, 'button_group' => 'left', 'value' => 'Continue', 'loc' => 'NF.Form.Continue', 'method' => 'button2', 'icon' => 'far fa-arrow-alt-circle-right', 'accesskey' => 's', 'process_submit' => true];

    /**
     * Stop button
     */
    public const BUTTON_STOP = '__stop_button';
    public const BUTTON_STOP_DATA = ['order' => -100, 'button_group' => 'left', 'type' => 'danger', 'value' => 'Stop', 'loc' => 'NF.Form.Stop', 'method' => 'button2', 'icon' => 'fas fa-stop', 'accesskey' => 'x', 'process_submit' => true];

    /**
     * Submit save
     */
    public const BUTTON_SUBMIT_SAVE = '__submit_save';
    public const BUTTON_SUBMIT_SAVE_DATA = ['order' => 100, 'button_group' => 'left', 'value' => 'Save', 'loc' => 'NF.Form.Save', 'method' => 'button2', 'icon' => 'far fa-save', 'accesskey' => 's', 'process_submit' => true];

    /**
     * Submit save and new
     */
    public const BUTTON_SUBMIT_SAVE_AND_NEW = '__submit_save_and_new';
    public const BUTTON_SUBMIT_SAVE_AND_NEW_DATA = ['order' => 200, 'button_group' => 'left', 'value' => 'Save & New', 'loc' => 'NF.Form.SaveAndNew', 'type' => 'success', 'method' => 'button2', 'icon' => 'far fa-save', 'process_submit' => true];

    /**
     * Submit save and close
     */
    public const BUTTON_SUBMIT_SAVE_AND_CLOSE = '__submit_save_and_close';
    public const BUTTON_SUBMIT_SAVE_AND_CLOSE_DATA = ['order' => 300, 'button_group' => 'left', 'value' => 'Save & Close', 'loc' => 'NF.Form.SaveAndClose', 'type' => 'default', 'method' => 'button2', 'icon' => 'far fa-save', 'process_submit' => true];

    /**
     * Delete button, actual delete will be performed in database
     */
    public const BUTTON_SUBMIT_DELETE = '__submit_delete';
    public const BUTTON_SUBMIT_DELETE_DATA = ['order' => 32000, 'button_group' => 'right', 'value' => 'Delete', 'loc' => 'NF.Form.Delete', 'type' => 'danger', 'method' => 'button2', 'icon' => 'far fa-trash-alt', 'accesskey' => 'd', 'process_submit' => true, 'confirm_message' => Messages::CONFIRM_DELETE];
    public const BUTTON_SUBMIT_OTHER_DELETE = '__submit_other_delete';
    public const BUTTON_SUBMIT_OTHER_DELETE_DATA = ['order' => 32000, 'button_group' => 'right', 'value' => 'Delete', 'loc' => 'NF.Form.Delete', 'type' => 'danger', 'method' => 'button2', 'icon' => 'far fa-trash-alt', 'accesskey' => 'd', 'process_submit' => 'other', 'confirm_message' => Messages::CONFIRM_DELETE];

    /**
     * Reset button
     */
    public const BUTTON_SUBMIT_RESET = '__submit_reset';
    public const BUTTON_SUBMIT_RESET_DATA = ['order' => 31000, 'button_group' => 'right', 'value' => 'Reset', 'loc' => 'NF.Form.Reset', 'type' => 'warning', 'input_type' => 'reset', 'icon' => 'fas fa-ban', 'accesskey' => 'q', 'method' => 'button2', 'process_submit' => true, 'confirm_message' => Messages::CONFIRM_RESET];

    /**
     * Blank button
     */
    public const BUTTON_SUBMIT_BLANK = '__submit_blank';
    public const BUTTON_SUBMIT_BLANK_DATA = ['order' => 30000, 'button_group' => 'right', 'value' => 'Blank', 'loc' => 'NF.Form.Blank', 'type' => 'default', 'icon' => 'far fa-file', 'method' => 'button2', 'accesskey' => 'n', 'process_submit' => true, 'confirm_message' => Messages::CONFIRM_BLANK];

    /**
     * Refresh button
     */
    public const BUTTON_SUBMIT_REFRESH = '__submit_refresh';
    public const BUTTON_SUBMIT_REFRESH_DATA = ['order' => -100, 'button_group' => 'left', 'value' => 'Refresh', 'loc' => 'NF.Form.Refresh', 'method' => 'button2', 'icon' => 'fas fa-sync', 'accesskey' => 'r', 'process_submit' => true];

    /**
     * Post button
     */
    public const BUTTON_SUBMIT_POST = '__submit_post';
    public const BUTTON_SUBMIT_POST_DATA = ['order' => 150, 'button_group' => 'left', 'value' => 'Post', 'loc' => 'NF.Form.Post', 'type' => 'warning', 'method' => 'button2', 'icon' => 'fas fa-archive', 'accesskey' => 'p', 'process_submit' => true];

    /**
     * Post provisionally button
     */
    public const BUTTON_SUBMIT_TEMPORARY_POST = '__submit_post_temporary';
    public const BUTTON_SUBMIT_TEMPORARY_POST_DATA = ['order' => 151, 'button_group' => 'left', 'value' => 'Temporary Post', 'loc' => 'NF.Form.TemporaryPost', 'type' => 'success', 'icon' => 'fas fa-archive', 'method' => 'button2', 'process_submit' => true];

    /**
     * Ready to post button
     */
    public const BUTTON_SUBMIT_READY_TO_POST = '__submit_ready_to_post';
    public const BUTTON_SUBMIT_READY_TO_POST_DATA = ['order' => 150, 'button_group' => 'center', 'value' => 'Ready To Post', 'loc' => 'NF.Form.ReadyToPost', 'type' => 'info', 'icon' => 'fas fa-archive', 'method' => 'button2', 'process_submit' => true];

    /**
     * Open button
     */
    public const BUTTON_SUBMIT_OPEN = '__submit_open';
    public const BUTTON_SUBMIT_OPEN_DATA = ['order' => 151, 'button_group' => 'center', 'value' => 'Open', 'loc' => 'NF.Form.Open', 'type' => 'info', 'icon' => 'fas fa-archive', 'method' => 'button2', 'process_submit' => true];

    /**
     * Mark deleted button, used in transactions
     */
    public const BUTTON_SUBMIT_MARK_DELETED = '__submit_mark_deleted';
    public const BUTTON_SUBMIT_MARK_DELETED_DATA = self::BUTTON_SUBMIT_DELETE_DATA;

    /**
     * Print button
     */
    public const BUTTON_PRINT = '__print_button';
    public const BUTTON_PRINT_DATA = ['order' => -100, 'button_group' => 'left', 'value' => 'Print', 'loc' => 'NF.Form.Print', 'type' => 'default', 'icon' => 'fas fa-print', 'method' => 'button2', 'accesskey' => 'p', 'process_submit' => true];

    /**
     * Submit generate
     */
    public const BUTTON_SUBMIT_GENERATE = '__submit_generate';
    public const BUTTON_SUBMIT_GENERATE_DATA = ['order' => 100, 'button_group' => 'left', 'value' => 'Generate', 'loc' => 'NF.Form.Generate', 'type' => 'danger', 'method' => 'button2', 'icon' => 'fas fa-sync-alt', 'accesskey' => 'g', 'process_submit' => true];

    /**
     * Standard buttons
     */
    public const BUTTONS_DATA_GROUP = [
        self::BUTTON_SUBMIT_SAVE => self::BUTTON_SUBMIT_SAVE_DATA,
        self::BUTTON_SUBMIT_SAVE_AND_NEW => self::BUTTON_SUBMIT_SAVE_AND_NEW_DATA,
        //self::BUTTON_SUBMIT_SAVE_AND_CLOSE => self::BUTTON_SUBMIT_SAVE_AND_CLOSE_DATA,
        self::BUTTON_SUBMIT_BLANK => self::BUTTON_SUBMIT_BLANK_DATA,
        self::BUTTON_SUBMIT_RESET => self::BUTTON_SUBMIT_RESET_DATA,
        self::BUTTON_SUBMIT_DELETE => self::BUTTON_SUBMIT_DELETE_DATA
    ];

    /**
     * Standard buttons for batches
     */
    public const TRANSACTION_BUTTONS_DATA_GROUP = [
        self::BUTTON_SUBMIT_SAVE => self::BUTTON_SUBMIT_SAVE_DATA,
        self::BUTTON_SUBMIT_POST => self::BUTTON_SUBMIT_POST_DATA,
        self::BUTTON_SUBMIT_TEMPORARY_POST => self::BUTTON_SUBMIT_TEMPORARY_POST_DATA,
        self::BUTTON_SUBMIT_READY_TO_POST => self::BUTTON_SUBMIT_READY_TO_POST_DATA,
        self::BUTTON_SUBMIT_OPEN => self::BUTTON_SUBMIT_OPEN_DATA,
        self::BUTTON_SUBMIT_RESET => self::BUTTON_SUBMIT_RESET_DATA,
        self::BUTTON_SUBMIT_MARK_DELETED => self::BUTTON_SUBMIT_MARK_DELETED_DATA
    ];

    /**
     * Trimmed buttons for batches
     */
    public const TRIMMED_TRANSACTION_BUTTONS_DATA_GROUP = [
        self::BUTTON_SUBMIT_SAVE => self::BUTTON_SUBMIT_SAVE_DATA,
        self::BUTTON_SUBMIT_POST => self::BUTTON_SUBMIT_POST_DATA,
        self::BUTTON_SUBMIT_READY_TO_POST => self::BUTTON_SUBMIT_READY_TO_POST_DATA,
        self::BUTTON_SUBMIT_OPEN => self::BUTTON_SUBMIT_OPEN_DATA,
        self::BUTTON_SUBMIT_RESET => self::BUTTON_SUBMIT_RESET_DATA,
        self::BUTTON_SUBMIT_MARK_DELETED => self::BUTTON_SUBMIT_MARK_DELETED_DATA
    ];

    /**
     * Report buttons
     */
    public const REPORT_BUTTONS_DATA_GROUP = [
        self::BUTTON_SUBMIT => self::BUTTON_SUBMIT_DATA,
        self::BUTTON_SUBMIT_BLANK => self::BUTTON_SUBMIT_BLANK_DATA,
        self::BUTTON_SUBMIT_RESET => self::BUTTON_SUBMIT_RESET_DATA
    ];

    /**
     * Segment list
     */
    public const SEGMENT_LIST = [
        'type' => 'success',
        'header' => [
            'icon' => ['type' => 'far fa-list-alt'],
            'title' => 'List:'
        ]
    ];

    /**
     * Segment report
     */
    public const SEGMENT_REPORT = [
        'type' => 'default',
        'header' => [
            'icon' => ['type' => 'fas fa-table'],
            'title' => 'Report:'
        ]
    ];

    /**
     * Segment form
     */
    public const SEGMENT_FORM = [
        'type' => 'primary',
        'header' => [
            'icon' => ['type' => 'fas fa-pen-square'],
            'title' => 'View / Edit:',
            'loc' => 'NF.Form.ViewEdit'
        ]
    ];

    /**
     * Segment form
     */
    public const SEGMENT_ACTIVATE = [
        'type' => 'success',
        'header' => [
            'icon' => ['type' => 'fas fa-link'],
            'title' => 'Activate:'
        ]
    ];

    /**
     * Segment task
     */
    public const SEGMENT_TASK = [
        'type' => 'warning',
        'header' => [
            'icon' => ['type' => 'fas fa-play'],
            'title' => 'Execute Task:'
        ]
    ];

    /**
     * Segment import
     */
    public const SEGMENT_IMPORT = [
        'type' => 'info',
        'header' => [
            'icon' => ['type' => 'fas fa-upload'],
            'title' => 'Import:'
        ]
    ];

    /**
     * Segment additional information
     */
    public const SEGMENT_ADDITIONAL_INFORMATION = [
        'type' => 'info',
        'header' => [
            'icon' => ['type' => 'fab fa-envira'],
            'title' => 'Additional Information:'
        ]
    ];

    /**
     * Segment workflows
     */
    public const SEGMENT_WORKFLOWS = [
        'type' => 'success',
        'header' => [
            'icon' => ['type' => ' fab fa-hubspot'],
            'title' => 'Workflows:'
        ]
    ];

    /**
     * Segment workflow next step
     */
    public const SEGMENT_WORKFLOW_NEXT_STEP = [
        'type' => 'warning',
        'header' => [
            'icon' => ['type' => ' fab fa-hubspot'],
            'title' => 'Workflow Next Step:'
        ]
    ];
}
