<?php

namespace Object\Content;
class Messages {
	// error messages
	const NO_ROWS_FOUND = 'No rows found!';
	const NO_WHAT_FOUND = 'No [what] found!';
	const RECORD_NOT_FOUND = 'Record not found!';
	const NO_PERMISSIONS_FOUND = 'You do not have permissions to view this content!';
	const PREV_OR_NEXT_RECORD_NOT_FOUND = 'Previous/next record not found!';
	const REQUIRED_FIELD = 'Required field!';
	const LESS_THAN_ZERO = 'Value cannot be less than zero!';
	const OPTIONALLY_REQUIRED_FIELD = 'Optionally required field!';
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
	const SUBMISSION_COUNT_PROBLEM = 'There was [count] problem(s) with your submission!';
	const SUBMISSION_WARNING = 'Your submission contains warnings!';
	const STRING_UPPERCASE = 'The string must be uppercase!';
	const STRING_LOWERCASE = 'The string must be lowercase!';
	const STRING_FUNCTION = 'The string did not pass validation function!';
	const ERROR_500 = 'Internal Server Error!';
	const MODAL_HAS_ERRORS = 'Modal has error messages!';
	const NO_MODIFICATION_ALLOWED = 'Modification of existing record(s) is not allowed!';
	const TOKEN_EXPIRED = 'Your token is not valid or expired!';
	const INVALID_DOMAIN = 'Invalid domain or subdomain!';
	const DATE_IN_THE_PAST = 'Date cannot be in the past!';
	const PERCENT_IN_RANGE = 'Value must be within 0 to 100 percent range!';
	// warnings
	const AMOUNT_RECALCULATED = 'The amount has been recalculated!';
	const AMOUNT_ROUNDED = 'The amount has been rounded!';
	// good messages
	const RECORD_DELETED = 'Record(s) has been successfully deleted!';
	const RECORD_NUMBER_DELETED = '[number] record(s) has been successfully deleted!';
	const RECORD_INSERTED = 'Record(s) has been successfully created!';
	const RECORD_UPDATED = 'Record(s) has been successfully updated!';
	const RECORD_POSTED = 'Record(s) has been successfully posted!';
	const RECORD_READY_TO_POST = 'Record(s) has been marked as ready to post!';
	const RECORD_MARK_DELETED = 'Record(s) has been marked as deleted!';
	const RECORD_OPENED = 'Record(s) has been successfully opened!';
	const OPERATION_EXECUTED = 'Operation has been successfully executed!';
	const FILTER_DELETED = 'Filter has been deleted!';
	// confirmation
	const CONFIRM_DELETE = 'Are you sure you want to delete this record(s)?';
	const CONFIRM_RESET = 'Are you sure you want to reset?';
	const CONFIRM_BLANK = 'The changes you made would be lost, proceed?';
	const CONFIRM_CUSTOM = 'Are you sure you want to [action]?';
	// information
	const INFO_CLOSED = '[Closed]';
	const INFO_INACTIVE = '[Inactive]';
	const INFO_MANDATORY = '[Mandatory]';
	const INFO_FUNCTIONAL_CURRENCY = '[FC]';
	// report
	const REPORT_ROWS_NUMBER = 'Rows: [Number]';
	// configuration messages
	const CONFIG_MODULE_IS_NOT_CONFIGURED = 'You must configure ledger first before you can use it!';
	const EMAIL_RESENT = 'Email re-sent!';
	// place holders
	const PLEASE_CHOOSE = 'Please choose';
	const CLICK_HERE = 'Click here';
	// other
	const NEW = 'New';
	const LOADING = 'Loading...';
	const LOADING_PERCENT = 'Loading [percent].';
	const LOADING_COMPLETED = 'Loading [percent], [completed] of [total].';
	// Route messages
	const ROUTE_INVALID_METHODS = 'Invalid methods [methods] provided!';
	const ROUTE_INVALID_METHOD = 'Invalid method [method] provided!';
	const ROUTE_NAME_EXISTS = 'Provided named route [name] already exists!';
	const ROUTE_UNKNOWN_ACL_PARAMETER = 'Unknown ACL parameter [parameter]!';
	const ROUTE_NAME_NOT_FOUND = 'Provided named route [name] not found!';
	const ROUTE_NOT_FOUND = 'Route not found!';
	const ROUTE_ACL_UNAUTHORIZED = 'Unauthorized!';
	const ROUTE_ACL_NOT_PUBLIC = 'Not public controller!';
	const ROUTE_ACL_NOT_AUTHORIZED = 'You cannnot view this controller when authorized!';
	const ROUTE_PERMISSION_DENIED = 'You do not have permission to access this route!';
	const ROUTE_INVALID_TYPE = 'Invalid type [type] provided!';
	const ROUTE_BEARER_TOKEN_EXPIRED = 'Bearer token is missing or expired!';

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

	/**
	 * Throw message
	 *
	 * @param string $const
	 * @param array|null $replace
	 * @param bool $i18n
	 * @param bool $throw
	 * @return string
	 */
	public static function message(string $const, ?array $replace = null, bool $i18n = true, bool $throw = false, int $code = 0) : string {
		if (strpos($const, '::') === false) {
			$const = '\Object\Content\Messages::'. $const;
		}
		$text = constant($const);
		if ($i18n) {
			$text = i18n(null, $text, ['replace' => $replace]);
		} else {
			if (isset($replace)) {
				foreach ($replace as $k => $v) {
					$text = str_replace($k, $v ?? '', $text . '');
				}
			}
		}
		if ($throw) {
			Throw new \Exception($text, $code);
		}
		return $text;
	}
}