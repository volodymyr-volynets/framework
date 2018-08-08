<?php

namespace Object\Form;
class Parent2 extends \Object\Override\Data {

	/**
	 * Separators
	 */
	const SEPARATOR_VERTICAL = '__separator_vertical';
	const SEPARATOR_HORIZONTAL = '__separator_horizontal';

	/**
	 * List container
	 */
	const LIST_CONTAINER = '__list_container';

	/**
	 * List buttons
	 */
	const LIST_BUTTONS = '__list_buttons';
	const LIST_BUTTONS_DATA = [
		'__format' => [
			'__format' => ['order' => 1, 'container_order' => PHP_INT_MAX - 1000, 'container_class' => 'numbers_form_filter_sort_container', 'label_name' => 'Format', 'percent' => 25, 'required' => true, 'method' => 'select', 'default' => 'text/html', 'no_choose' => true, 'options_model' => '\Object\Form\Model\Content\Types', 'options_options' => ['i18n' => 'skip_sorting']]
		],
		self::BUTTONS => [
			self::BUTTON_SUBMIT => self::BUTTON_SUBMIT_DATA,
			self::BUTTON_SUBMIT_BLANK => self::BUTTON_SUBMIT_BLANK_DATA,
			self::BUTTON_SUBMIT_RESET => self::BUTTON_SUBMIT_RESET_DATA
		]
	];

	/**
	 * Report buttons
	 */
	const REPORT_BUTTONS = '__report_buttons';
	const REPORT_BUTTONS_DATA = [
		'__format' => [
			'__format' => ['order' => 1, 'container_order' => PHP_INT_MAX - 1000, 'container_class' => 'numbers_form_filter_sort_container', 'label_name' => 'Format', 'percent' => 25, 'required' => true, 'method' => 'select', 'default' => 'text/html', 'no_choose' => true, 'options_model' => '\Object\Form\Model\Report\Types', 'options_options' => ['i18n' => 'skip_sorting']]
		],
		self::BUTTONS => [
			self::BUTTON_SUBMIT => self::BUTTON_SUBMIT_DATA,
			self::BUTTON_SUBMIT_BLANK => self::BUTTON_SUBMIT_BLANK_DATA,
			self::BUTTON_SUBMIT_RESET => self::BUTTON_SUBMIT_RESET_DATA
		]
	];

	/**
	 * List sort container
	 */
	const LIST_SORT_CONTAINER = [
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
	const LIST_FILTER_SORT = [
		'value' => 'Filter/Sort',
		'sort' => 32000,
		'icon' => 'fas fa-filter',
		'onclick' => 'Numbers.Form.listFilterSortToggle(this);'
	];

	/**
	 * Row for buttons
	 */
	const BUTTONS = '__submit_buttons';

	/**
	 * Row for batch buttons
	 */
	const TRANSACTION_BUTTONS = '__submit_transaction_buttons';

	/**
	 * Hidden container/row
	 */
	const HIDDEN = '__hidden_row_or_container';

	/**
	 * Submit button
	 */
	const BUTTON_SUBMIT = '__submit_button';
	const BUTTON_SUBMIT_DATA = ['order' => -100, 'button_group' => 'left', 'value' => 'Submit', 'method' => 'button2', 'icon' => 'fas fa-mouse-pointer', 'accesskey' => 's', 'process_submit' => true];

	/**
	 * Other submit button
	 */
	const BUTTON_SUBMIT_OTHER = '__submit_button_2';
	const BUTTON_SUBMIT_OTHER_DATA = ['order' => -100, 'button_group' => 'left', 'value' => 'Submit', 'method' => 'button2', 'icon' => 'fas fa-mouse-pointer', 'accesskey' => 's', 'process_submit' => 'other'];

	/**
	 * Continue button
	 */
	const BUTTON_CONTINUE = '__continue_button';
	const BUTTON_CONTINUE_DATA = ['order' => -100, 'button_group' => 'left', 'value' => 'Continue', 'method' => 'button2', 'icon' => 'far fa-arrow-alt-circle-right', 'accesskey' => 's', 'process_submit' => true];

	/**
	 * Stop button
	 */
	const BUTTON_STOP = '__stop_button';
	const BUTTON_STOP_DATA = ['order' => -100, 'button_group' => 'left', 'type' => 'danger', 'value' => 'Stop', 'method' => 'button2', 'accesskey' => 'x', 'process_submit' => true];

	/**
	 * Submit save
	 */
	const BUTTON_SUBMIT_SAVE = '__submit_save';
	const BUTTON_SUBMIT_SAVE_DATA = ['order' => 100, 'button_group' => 'left', 'value' => 'Save', 'method' => 'button2', 'icon' => 'far fa-save', 'accesskey' => 's', 'process_submit' => true];

	/**
	 * Submit save and new
	 */
	const BUTTON_SUBMIT_SAVE_AND_NEW = '__submit_save_and_new';
	const BUTTON_SUBMIT_SAVE_AND_NEW_DATA = ['order' => 200, 'button_group' => 'left', 'value' => 'Save & New', 'type' => 'success', 'method' => 'button2', 'icon' => 'far fa-save', 'process_submit' => true];

	/**
	 * Submit save and close
	 */
	const BUTTON_SUBMIT_SAVE_AND_CLOSE = '__submit_save_and_close';
	const BUTTON_SUBMIT_SAVE_AND_CLOSE_DATA = ['order' => 300, 'button_group' => 'left', 'value' => 'Save & Close', 'type' => 'default', 'method' => 'button2', 'icon' => 'far fa-save', 'process_submit' => true];

	/**
	 * Delete button, actual delete will be performed in database
	 */
	const BUTTON_SUBMIT_DELETE = '__submit_delete';
	const BUTTON_SUBMIT_DELETE_DATA = ['order' => 32000, 'button_group' => 'right', 'value' => 'Delete', 'type' => 'danger', 'method' => 'button2', 'icon' => 'far fa-trash-alt', 'accesskey' => 'd', 'process_submit' => true, 'confirm_message' => \Object\Content\Messages::CONFIRM_DELETE];

	/**
	 * Reset button
	 */
	const BUTTON_SUBMIT_RESET = '__submit_reset';
	const BUTTON_SUBMIT_RESET_DATA = ['order' => 31000, 'button_group' => 'right', 'value' => 'Reset', 'type' => 'warning', 'input_type' => 'reset', 'icon' => 'fas fa-ban', 'accesskey' => 'q', 'method' => 'button2', 'process_submit' => true, 'confirm_message' => \Object\Content\Messages::CONFIRM_RESET];

	/**
	 * Blank button
	 */
	const BUTTON_SUBMIT_BLANK = '__submit_blank';
	const BUTTON_SUBMIT_BLANK_DATA = ['order' => 30000, 'button_group' => 'right', 'value' => 'Blank', 'type' => 'default', 'icon' => 'far fa-file', 'method' => 'button2', 'accesskey' => 'n', 'process_submit' => true, 'confirm_message' => \Object\Content\Messages::CONFIRM_BLANK];

	/**
	 * Refresh button
	 */
	const BUTTON_SUBMIT_REFRESH = '__submit_refresh';
	const BUTTON_SUBMIT_REFRESH_DATA = ['order' => -100, 'button_group' => 'left', 'value' => 'Refresh', 'method' => 'button2', 'icon' => 'fas fa-sync', 'accesskey' => 'r', 'process_submit' => true];

	/**
	 * Post button
	 */
	const BUTTON_SUBMIT_POST = '__submit_post';
	const BUTTON_SUBMIT_POST_DATA = ['order' => 150, 'button_group' => 'left', 'value' => 'Post', 'type' => 'warning', 'method' => 'button2', 'icon' => 'fas fa-archive', 'accesskey' => 'p', 'process_submit' => true];

	/**
	 * Post provisionally button
	 */
	const BUTTON_SUBMIT_TEMPORARY_POST = '__submit_post_temporary';
	const BUTTON_SUBMIT_TEMPORARY_POST_DATA = ['order' => 151, 'button_group' => 'left', 'value' => 'Temporary Post', 'type' => 'success', 'icon' => 'fas fa-archive', 'method' => 'button2', 'process_submit' => true];

	/**
	 * Ready to post button
	 */
	const BUTTON_SUBMIT_READY_TO_POST = '__submit_ready_to_post';
	const BUTTON_SUBMIT_READY_TO_POST_DATA = ['order' => 150, 'button_group' => 'center', 'value' => 'Ready To Post', 'type' => 'info', 'icon' => 'fas fa-archive', 'method' => 'button2', 'process_submit' => true];

	/**
	 * Open button
	 */
	const BUTTON_SUBMIT_OPEN = '__submit_open';
	const BUTTON_SUBMIT_OPEN_DATA = ['order' => 151, 'button_group' => 'center', 'value' => 'Open', 'type' => 'info', 'icon' => 'fas fa-archive', 'method' => 'button2', 'process_submit' => true];

	/**
	 * Mark deleted button, used in transactions
	 */
	const BUTTON_SUBMIT_MARK_DELETED = '__submit_mark_deleted';
	const BUTTON_SUBMIT_MARK_DELETED_DATA = self::BUTTON_SUBMIT_DELETE_DATA;

	/**
	 * Print button
	 */
	const BUTTON_PRINT = '__print_button';
	const BUTTON_PRINT_DATA = ['order' => -100, 'button_group' => 'left', 'value' => 'Print', 'type' => 'default', 'icon' => 'fas fa-print', 'method' => 'button2', 'accesskey' => 'p'];

	/**
	 * Standard buttons
	 */
	const BUTTONS_DATA_GROUP = [
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
	const TRANSACTION_BUTTONS_DATA_GROUP = [
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
	const TRIMMED_TRANSACTION_BUTTONS_DATA_GROUP = [
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
	const REPORT_BUTTONS_DATA_GROUP = [
		self::BUTTON_SUBMIT => self::BUTTON_SUBMIT_DATA,
		self::BUTTON_SUBMIT_BLANK => self::BUTTON_SUBMIT_BLANK_DATA,
		self::BUTTON_SUBMIT_RESET => self::BUTTON_SUBMIT_RESET_DATA
	];

	/**
	 * Segment list
	 */
	const SEGMENT_LIST = [
		'type' => 'success',
		'header' => [
			'icon' => ['type' => 'far fa-list-alt'],
			'title' => 'List:'
		]
	];

	/**
	 * Segment report
	 */
	const SEGMENT_REPORT = [
		'type' => 'default',
		'header' => [
			'icon' => ['type' => 'fas fa-table'],
			'title' => 'Report:'
		]
	];

	/**
	 * Segment form
	 */
	const SEGMENT_FORM = [
		'type' => 'primary',
		'header' => [
			'icon' => ['type' => 'fas fa-pen-square'],
			'title' => 'View / Edit:'
		]
	];

	/**
	 * Segment task
	 */
	const SEGMENT_TASK = [
		'type' => 'warning',
		'header' => [
			'icon' => ['type' => 'fas fa-play'],
			'title' => 'Execute Task:'
		]
	];

	/**
	 * Segment import
	 */
	const SEGMENT_IMPORT = [
		'type' => 'info',
		'header' => [
			'icon' => ['type' => 'fas fa-upload'],
			'title' => 'Import:'
		]
	];

	/**
	 * Segment additional information
	 */
	const SEGMENT_ADDITIONAL_INFORMATION = [
		'type' => 'info',
		'header' => [
			'icon' => ['type' => 'fab fa-envira'],
			'title' => 'Additional Information:'
		]
	];

	/**
	 * Segment workflows
	 */
	const SEGMENT_WORKFLOWS = [
		'type' => 'success',
		'header' => [
			'icon' => ['type' => ' fab fa-hubspot'],
			'title' => 'Workflows:'
		]
	];

	/**
	 * Segment workflow next step
	 */
	const SEGMENT_WORKFLOW_NEXT_STEP = [
		'type' => 'warning',
		'header' => [
			'icon' => ['type' => ' fab fa-hubspot'],
			'title' => 'Workflow Next Step:'
		]
	];
}