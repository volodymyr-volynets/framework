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
	const INVALID_VALUE = 'Invalid value(s)!';
	const DUPLICATE_VALUE = 'Duplicate value(s)!';
	const OPTIMISTIC_LOCK = 'Someone has updated the record while you were editing, please refresh!';
	const NO_CHANGES = 'Records have not been changed, nothing to save!';
	const SUBMISSION_PROBLEM = 'There was a problem with your submission!';
	const SUBMISSION_WARNING = 'Your submission contains warnings!';
	const STRING_UPPERCASE = 'The string must be uppercase!';
	const STRING_LOWERCASE = 'The string must be lowercase!';
	const STRING_FUNCTION = 'The string did not pass validation function!';
	const ERROR_500 = 'Internal Server Error!';
	// warnings
	const AMOUNT_RECALCULATED = 'The amount has been recalculated!';
	const AMOUNT_ROUNDED = 'The amount has been rounded!';
	// good messages
	const RECORD_DELETED = 'Record has been successfully deleted!';
	const RECORD_INSERTED = 'Record has been successfully created!';
	const RECORT_UPDATED = 'Record has been successfully updated!';
	const RECORD_POSTED = 'Record has been successfully posted!';
	const RECORD_READY_TO_POST = 'Record has been marked as ready to post!';
	const RECORD_MARK_DELETED = 'Record has been marked as deleted!';
	const RECORD_OPENED = 'Record has been successfully opened!';
	// confirmation
	const CONFIRM_DELETE = 'Are you sure you want to delete this record?';
	const CONFIRM_RESET = 'Are you sure you want to reset?';
	const CONFIRM_BLANK = 'The changes you made would be lost, proceed?';
	// information
	const INFO_CLOSED = '[Closed]';
	const INFO_INACTIVE = '[Inactive]';
	const INFO_FUNCTIONAL_CURRENCY = '[FC]';
}