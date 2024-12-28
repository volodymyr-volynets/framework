<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Content;

class Messages
{
    // error messages
    public const NO_ROWS_FOUND = 'No rows found!';
    public const NO_WHAT_FOUND = 'No [what] found!';
    public const RECORD_NOT_FOUND = 'Record not found!';
    public const NO_PERMISSIONS_FOUND = 'You do not have permissions to view this content!';
    public const PREV_OR_NEXT_RECORD_NOT_FOUND = 'Previous/next record not found!';
    public const REQUIRED_FIELD = 'Required field!';
    public const LESS_THAN_ZERO = 'Value cannot be less than zero!';
    public const OPTIONALLY_REQUIRED_FIELD = 'Optionally required field!';
    public const MULTIPLE_VALUES_ARE_NOT_ALLOWED = 'Multiple values not allowed!';
    public const UNKNOWN_VALUE = 'Unknown value(s)!';
    public const INVALID_VALUE = 'Invalid value: [value]!';
    public const INVALID_VALUES = 'Invalid value(s)!';
    public const OUTDATED_VALUES = 'Outdated value(s)!';
    public const DUPLICATE_VALUE = 'Duplicate value(s)!';
    public const DUPLICATE_RECORD = 'Duplicate record with selected values already exists!';
    public const OPTIMISTIC_LOCK = 'Someone has updated the record while you were editing, please refresh!';
    public const NO_CHANGES = 'Records have not been changed, nothing to save!';
    public const SUBMISSION_PROBLEM = 'There was a problem with your submission!';
    public const SUBMISSION_COUNT_PROBLEM = 'There was [count] problem(s) with your submission!';
    public const SUBMISSION_WARNING = 'Your submission contains warnings!';
    public const STRING_UPPERCASE = 'The string must be uppercase!';
    public const STRING_LOWERCASE = 'The string must be lowercase!';
    public const STRING_FUNCTION = 'The string did not pass validation function!';
    public const ERROR_500 = 'Internal Server Error!';
    public const MODAL_HAS_ERRORS = 'Modal has error messages!';
    public const NO_MODIFICATION_ALLOWED = 'Modification of existing record(s) is not allowed!';
    public const TOKEN_EXPIRED = 'Your token is not valid or expired!';
    public const INVALID_DOMAIN = 'Invalid domain or subdomain!';
    public const DATE_IN_THE_PAST = 'Date cannot be in the past!';
    public const PERCENT_IN_RANGE = 'Value must be within 0 to 100 percent range!';
    // warnings
    public const AMOUNT_RECALCULATED = 'The amount has been recalculated!';
    public const AMOUNT_ROUNDED = 'The amount has been rounded!';
    // good messages
    public const RECORD_DELETED = 'Record(s) has been successfully deleted!';
    public const RECORD_NUMBER_DELETED = '[number] record(s) has been successfully deleted!';
    public const RECORD_INSERTED = 'Record(s) has been successfully created!';
    public const RECORD_UPDATED = 'Record(s) has been successfully updated!';
    public const RECORD_POSTED = 'Record(s) has been successfully posted!';
    public const RECORD_READY_TO_POST = 'Record(s) has been marked as ready to post!';
    public const RECORD_MARK_DELETED = 'Record(s) has been marked as deleted!';
    public const RECORD_OPENED = 'Record(s) has been successfully opened!';
    public const OPERATION_EXECUTED = 'Operation has been successfully executed!';
    public const FILTER_DELETED = 'Filter has been deleted!';
    // confirmation
    public const CONFIRM_DELETE = 'Are you sure you want to delete this record(s)?';
    public const CONFIRM_RESET = 'Are you sure you want to reset?';
    public const CONFIRM_BLANK = 'The changes you made would be lost, proceed?';
    public const CONFIRM_CUSTOM = 'Are you sure you want to [action]?';
    // information
    public const INFO_CLOSED = '[Closed]';
    public const INFO_INACTIVE = '[Inactive]';
    public const INFO_MANDATORY = '[Mandatory]';
    public const INFO_FUNCTIONAL_CURRENCY = '[FC]';
    // report
    public const REPORT_ROWS_NUMBER = 'Rows: [Number]';
    // configuration messages
    public const CONFIG_MODULE_IS_NOT_CONFIGURED = 'You must configure ledger first before you can use it!';
    public const EMAIL_RESENT = 'Email re-sent!';
    // place holders
    public const PLEASE_CHOOSE = 'Please choose';
    public const CLICK_HERE = 'Click here';
    // other
    public const NEW = 'New';
    public const LOADING = 'Loading...';
    public const LOADING_PERCENT = 'Loading [percent].';
    public const LOADING_COMPLETED = 'Loading [percent], [completed] of [total].';
    // Route messages
    public const ROUTE_INVALID_METHODS = 'Invalid methods [methods] provided!';
    public const ROUTE_INVALID_METHOD = 'Invalid method [method] provided!';
    public const ROUTE_NAME_EXISTS = 'Provided named route [name] already exists!';
    public const ROUTE_UNKNOWN_ACL_PARAMETER = 'Unknown ACL parameter [parameter]!';
    public const ROUTE_NAME_NOT_FOUND = 'Provided named route [name] not found!';
    public const ROUTE_NOT_FOUND = 'Route not found!';
    public const ROUTE_ACL_UNAUTHORIZED = 'Unauthorized!';
    public const ROUTE_ACL_NOT_PUBLIC = 'Not public controller!';
    public const ROUTE_ACL_NOT_AUTHORIZED = 'You cannnot view this controller when authorized!';
    public const ROUTE_PERMISSION_DENIED = 'You do not have permission to access this route!';
    public const ROUTE_MIDDLEWARE_DENIED = 'Middleware denied for this route!';
    public const ROUTE_INVALID_TYPE = 'Invalid type [type] provided!';

    public $loc = [
        'NF.Form.New' => 'New',
        'NF.Message.TokenExpired' => 'Your token is not valid or expired!',
    ];

    /**
     * Active
     *
     * @param mixed $value
     * @return string
     */
    public static function active($value, $flip = false): string
    {
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
    public static function message(string $const, ?array $replace = null, bool $i18n = true, bool $throw = false, int $code = 0): string
    {
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
            throw new \Exception($text, $code);
        }
        return $text;
    }
}
