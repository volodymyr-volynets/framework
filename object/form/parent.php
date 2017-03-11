<?php

class object_form_parent extends object_override_data {

	/**
	 * Separators
	 */
	const separator_vertical = '__separator_vertical';
	const separator_horisontal = '__separator_horizontal';

	/**
	 * List container
	 */
	const list_container = '__list_container';

	/**
	 * List buttons
	 */
	const list_buttons = '__list_buttons';
	const list_buttons_data = [
		'__content_type' => [
			'__content_type' => ['order' => 1, 'container_order' => PHP_INT_MAX - 1000, 'container_class' => 'numbers_form_filter_sort_container', 'label_name' => 'Format', 'percent' => 25, 'required' => true, 'method' => 'select', 'default' => 'text/html', 'no_choose' => true, 'options_model' => 'numbers_framework_object_form_model_content_types']
		],
		self::buttons => [
			self::button_submit => self::button_submit_data
		]
	];

	/**
	 * Row for buttons
	 */
	const buttons = '__submit_buttons';

	/**
	 * Row for batch buttons
	 */
	const transaction_buttons = '__submit_transaction_buttons';

	/**
	 * Hidden container/row
	 */
	const hidden = '__hidden_row_or_container';

	/**
	 * Submit button
	 */
	const button_submit = '__submit_button';
	const button_submit_data = ['order' => -100, 'button_group' => 'left', 'value' => 'Submit', 'method' => 'button2', 'accesskey' => 's', 'process_submit' => true]; // , 'type' => 'primary'

	/**
	 * Submit save
	 */
	const button_submit_save = '__submit_save';
	const button_submit_save_data = ['order' => 100, 'button_group' => 'left', 'value' => 'Save', 'method' => 'button2', 'icon' => 'floppy-o', 'accesskey' => 's', 'process_submit' => true]; // , 'type' => 'primary'

	/**
	 * Submit save and new
	 */
	const button_submit_save_and_new = '__submit_save_and_new';
	const button_submit_save_and_new_data = ['order' => 200, 'button_group' => 'left', 'value' => 'Save & New', 'type' => 'success', 'method' => 'button2', 'icon' => 'floppy-o', 'process_submit' => true];

	/**
	 * Submit save and close
	 */
	const button_submit_save_and_close = '__submit_save_and_close';
	const button_submit_save_and_close_data = ['order' => 300, 'button_group' => 'left', 'value' => 'Save & Close', 'type' => 'default', 'method' => 'button2', 'icon' => 'floppy-o', 'process_submit' => true];

	/**
	 * Delete button, actual delete will be performed in database
	 */
	const button_submit_delete = '__submit_delete';
	const button_submit_delete_data = ['order' => 32000, 'button_group' => 'right', 'value' => 'Delete', 'type' => 'danger', 'method' => 'button2', 'icon' => 'trash-o', 'accesskey' => 'd', 'process_submit' => true, 'confirm_message' => object_content_messages::confirm_delete];

	/**
	 * Reset button
	 */
	const button_submit_reset = '__submit_reset';
	const button_submit_reset_data = ['order' => 31000, 'button_group' => 'right', 'value' => 'Reset', 'type' => 'warning', 'input_type' => 'reset', 'icon' => 'ban', 'accesskey' => 'q', 'method' => 'button2', 'process_submit' => true, 'confirm_message' => object_content_messages::confirm_reset];

	/**
	 * Blank button
	 */
	const button_submit_blank = '__submit_blank';
	const button_submit_blank_data = ['order' => 30000, 'button_group' => 'right', 'value' => 'Blank', 'icon' => 'file-o', 'method' => 'button2', 'accesskey' => 'n', 'process_submit' => true, 'confirm_message' => object_content_messages::confirm_blank];

	/**
	 * Refresh button
	 */
	const button_submit_refresh = '__submit_refresh';
	const button_submit_refresh_data = ['order' => -100, 'button_group' => 'left', 'value' => 'Refresh', 'method' => 'button2', 'icon' => 'refresh', 'accesskey' => 'r', 'process_submit' => true];

	/**
	 * Post button
	 */
	const button_submit_post = '__submit_post';
	const button_submit_post_data = ['order' => 150, 'button_group' => 'left', 'value' => 'Post', 'type' => 'warning', 'method' => 'button2', 'accesskey' => 'p', 'process_submit' => true];

	/**
	 * Post provisionally button
	 */
	const button_submit_temporary_post = '__submit_post_temporary';
	const button_submit_temporary_post_data = ['order' => 151, 'button_group' => 'left', 'value' => 'Temporary Post', 'type' => 'success', 'method' => 'button2', 'process_submit' => true];

	/**
	 * Ready to post button
	 */
	const button_submit_ready_to_post = '__submit_ready_to_post';
	const button_submit_ready_to_post_data = ['order' => 150, 'button_group' => 'center', 'value' => 'Ready To Post', 'type' => 'info', 'method' => 'button2', 'process_submit' => true];

	/**
	 * Open button
	 */
	const button_submit_open = '__submit_open';
	const button_submit_open_data = ['order' => 151, 'button_group' => 'center', 'value' => 'Open', 'type' => 'info', 'method' => 'button2', 'process_submit' => true];

	/**
	 * Mark deleted button, used in transactions
	 */
	const button_submit_mark_deleted = '__submit_mark_deleted';
	const button_submit_mark_deleted_data = self::button_submit_delete_data;

	/**
	 * Print button
	 */
	const button_print = '__print_button';
	const button_print_data = ['order' => -100, 'button_group' => 'left', 'value' => 'Print', 'type' => 'default', 'icon' => 'print', 'method' => 'button2', 'accesskey' => 'p'];

	/**
	 * Standard buttons
	 */
	const buttons_data_group = [
		self::button_submit_save => self::button_submit_save_data,
		self::button_submit_save_and_new => self::button_submit_save_and_new_data,
		self::button_submit_save_and_close => self::button_submit_save_and_close_data,
		self::button_submit_blank => self::button_submit_blank_data,
		self::button_submit_reset => self::button_submit_reset_data,
		self::button_submit_delete => self::button_submit_delete_data
	];

	/**
	 * Standard buttons for batches
	 */
	const transaction_buttons_data_group = [
		self::button_submit_save => self::button_submit_save_data,
		self::button_submit_post => self::button_submit_post_data,
		self::button_submit_temporary_post => self::button_submit_temporary_post_data,
		self::button_submit_ready_to_post => self::button_submit_ready_to_post_data,
		self::button_submit_open => self::button_submit_open_data,
		self::button_submit_reset => self::button_submit_reset_data,
		self::button_submit_mark_deleted => self::button_submit_mark_deleted_data
	];

	/**
	 * Report buttons
	 */
	const report_buttons_data_group = [
		self::button_submit => self::button_submit_data,
		self::button_submit_blank => self::button_submit_blank_data,
		self::button_submit_reset => self::button_submit_reset_data
	];
}