<?php

namespace Object\Content;
class Messages {
	// error messages
	const NO_ROWS_FOUND = 'No rows found!';
	const RECORD_NOT_FOUND = 'Record not found!';
	const PREV_OR_NEXT_RECORD_NOT_FOUND = 'Previous/next record not found!';
	const REQUIRED_FIELD = 'Required field!';
	const MULTIPLE_VALUES_ARE_NOT_ALLOWED = 'Multiple values not allowed!';
	const UNKNOWN_VALUE = 'Unknown value(s)!';
	const INVALID_VALUE = 'Invalid value: [value]!';
	const INVALID_VALUES = 'Invalid value(s)!';
	const OUTDATED_VALUES = 'Outdated value(s)!';
	const DUPLICATE_VALUE = 'Duplicate value(s)!';
	const DUPLICATE_RECORD = 'Duplicate record with selected values already exists!';
	const OPTIMISTIC_LOCK = 'Someone has updated the record while you were editing, please refresh!';
	const NO_CHANGES = 'Records have not been changed, nothing to save!';
	const SUBMISSION_PROBLEM = 'There was a problem with your submission!';
	const SUBMISSION_WARNING = 'Your submission contains warnings!';
	const STRING_UPPERCASE = 'The string must be uppercase!';
	const STRING_LOWERCASE = 'The string must be lowercase!';
	const STRING_FUNCTION = 'The string did not pass validation function!';
	const ERROR_500 = 'Internal Server Error!';
	const MODAL_HAS_ERRORS = 'Modal has error messages!';
	// warnings
	const AMOUNT_RECALCULATED = 'The amount has been recalculated!';
	const AMOUNT_ROUNDED = 'The amount has been rounded!';
	// good messages
	const RECORD_DELETED = 'Record(s) has been successfully deleted!';
	const RECORD_INSERTED = 'Record(s) has been successfully created!';
	const RECORD_UPDATED = 'Record(s) has been successfully updated!';
	const RECORD_POSTED = 'Record(s) has been successfully posted!';
	const RECORD_READY_TO_POST = 'Record(s) has been marked as ready to post!';
	const RECORD_MARK_DELETED = 'Record(s) has been marked as deleted!';
	const RECORD_OPENED = 'Record(s) has been successfully opened!';
	const OPERATION_EXECUTED = 'Operation has been successfully executed!';
	// confirmation
	const CONFIRM_DELETE = 'Are you sure you want to delete this record?';
	const CONFIRM_RESET = 'Are you sure you want to reset?';
	const CONFIRM_BLANK = 'The changes you made would be lost, proceed?';
	// information
	const INFO_CLOSED = '[Closed]';
	const INFO_INACTIVE = '[Inactive]';
	const INFO_MANDATORY = '[Mandatory]';
	const INFO_FUNCTIONAL_CURRENCY = '[FC]';
	// report
	const REPORT_ROWS_NUMBER = 'Rows: [Number]';
	// configuration messages
	const CONFIG_MODULE_IS_NOT_CONFIGURED = 'You must configure ledger first before you can use it!';

	/**
	 * Active
	 *
	 * @param mixed $value
	 * @return string
	 */
	public static function active($value, $flip = false) : string {
		if ($flip) {
			$value = $value ? false : true;
		}
		return i18n(null, !empty($value) ? 'Yes' : 'No');
	}
}