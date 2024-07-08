<?php

class Validator {
    /**
     * @var string
     */
    public const REQUEST = 'Request';

    /**
     * @var array
     */
    public const RESULT_DANGER = [
        'success' => false,
        'general' => [],
        'error' => [],
        'error_in_fields' => [],
        'warning' => [],
        'warning_in_fields' =>[],
    ];

    /**
     * @var array
     */
    public $values = [];

    /**
     * @var array
     */
    private $errors = [
        'success' => false,
        'general' => [],
        'error' => [],
        'error_extended' => [],
        'error_count' => 0,
        'warning' => [],
        'warning_extended' => [],
        'warning_count' => 0,
    ];

    /**
     * @var bool
     */
    private $validated = false;

    /**
     * Data
     *
     * @retrun array
     */
    public function values() : array {
        return $this->values;
    }

    /**
     * Has errors
     *
     * @return bool
     */
    public function hasErrors() : bool {
        return !empty($this->errors['error']);
    }

    /**
     * Errors
     *
     * @param string|null $key
     *      result - return array as result for future processing
     * @return array
     */
    public function errors(?string $key = null) : array {
        if (!$key) {
            return $this->errors;
        } else if ($key == 'result') {
            return [
                'success' => false,
                'general' => $this->errors['general'],
                'error' => $this->errors['error_extended'],
                'error_in_fields' => $this->errors['error'],
                'warning' => $this->errors['warning_extended'],
                'warning_in_fields' => $this->errors['warning'],
            ];
        } else {
            return $this->errors[$key];
        }
    }

    /**
     * Throw errors
     */
    public function throwErrors() : void {
        if ($this->errors['error_count']) {
            $previous = null;
            $messages = [];
            foreach ($this->errors['error_extended'] as $v) {
                $messages[] = $v;
            }
            Throw new \Exception(implode("\n", $messages), -1);
        }
    }

    /**
     * Validated
     *
     * @return bool
     */
    public function validated() : bool {
        return $this->validated;
    }

    /**
     * Validate input (static)
     *
     * @param array|string $input
     * @param array $rules
     * @return \Validator
     */
    public static function validateInputStatic(array|string $input, array $rules) : \Validator {
        $validator = new self();
        if ($input === self::REQUEST) {
            $input = \Request::input();
        }
        // step 1 parse rules
        $rules = self::parsesRulesStatic($rules);
        // step 2 preset data from rules
        foreach ($rules as $field => $rule) {
            if (strpos($field, '::') === false) {
                // from_application
                $value = $input[$field] ?? null;
                if (isset($rule['from_application']) && !array_key_exists($field, $input)) {
                    $value = \Application::get($rule['from_application']);
                }
                $validator->values[$field] = $value;
            } else {
                $field = explode('::', $field);
                // details 1 to M
                if (count($field) == 3 && $field[1] == '1M') {
                    foreach ($input[$field[0]] ?? [] as $k => $v) {
                        array_key_set($validator->values, [$field[0], $k, $field[2]], $v[$field[2]] ?? null);
                    }
                }
                // details 1 to 1
                if (count($field) == 3 && $field[1] == '11') {
                    array_key_set($validator->values, [$field[0], $field[2]], $input[$field[0]][$field[2]] ?? null);
                }
            }
        }
        // step 3 process rules
        $error_count = 0;
        foreach ($rules as $field => $rule) {
            if (strpos($field, '::') === false) {
                $rule_result = self::processSingleRules($field, $rule, $validator->values[$field], $validator->values);
                if (!$rule_result['success']) {
                    $validator->prependFieldNameToErrors($field, $rule, $rule_result['error']);
                    $error_count+= count($rule_result['error']);
                }
            } else {
                $field = explode('::', $field);
                if (count($field) == 3 && $field[1] == '1M') {
                    foreach ($validator->values[$field[0]] as $k => $v) {
                        $rule_result = self::processSingleRules($field[2], $rule, $validator->values[$field[0]][$k][$field[2]], $validator->values[$field[0]][$k]);
                        if (!$rule_result['success']) {
                            $temp = $field[0] . '::' . $k . '::' . $field[2];
                            $validator->prependFieldNameToErrors($temp, $rule, $rule_result['error']);
                            $error_count+= count($rule_result['error']);
                        }
                    }
                }
                if (count($field) == 3 && $field[1] == '11') {
                    $rule_result = self::processSingleRules($field[2], $rule, $validator->values[$field[0]][$field[2]], $validator->values[$field[0]]);
                    if (!$rule_result['success']) {
                        $temp = $field[0] . '::' . $field[2];
                        $validator->prependFieldNameToErrors($temp, $rule, $rule_result['error']);
                        $error_count+= count($rule_result['error']);
                    }
                }
            }
        }
        if ($error_count) {
            $validator->errors['error_count'] = $error_count;
            $validator->errors['general'][] = i18n(null, \Object\Content\Messages::SUBMISSION_COUNT_PROBLEM, ['replace' => [
                '[count]' => $error_count,
            ]]);
        }
        $validator->validated = true;
        return $validator;
    }

    /**
     * Parse rules (static)
     *
     * @param array $rules
     * @return array
     */
    private static function parsesRulesStatic(array $rules) : array {
        $result = [];
        foreach ($rules as $k => $v) {
            $new = [];
            if (is_string($v)) {
                $v = explode('|', $v);
            }
            foreach ($v as $k2 => $v2) {
                if (is_numeric($k2)) {
                    if (strpos($v2, ':') !== false) {
                        $v2 = explode(':', $v2);
                        if (strpos($v2[1], ',') !== false) {
                            $v2[1] = explode(',', $v2[1]);
                        }
                        $new[$v2[0]] = $v2[1];
                    } else {
                        $new[$v2] = true;
                    }
                } else {
                    if (strpos($v2, ',') !== false) {
                        $new[$k2] = explode(',', $v2);
                    } else {
                        $new[$k2] = $v2;
                    }
                }
            }
            $result[$k] = $new;
        }
        return $result;
    }

    /**
     * Process single rule (static)
     *
     * @param string $field
     * @param array $rule
     * @param array $data
     * @return array
     */
    private static function processSingleRules($field, $rule, & $value, & $neighbouring) : array {
        $result = [
            'success' => false,
            'error' => [],
        ];
        // domain and type
        if (isset($rule['domain']) || isset($rule['type'])) {
            $temp_result = \Object\Data\Common::processDomainsAndTypes(['rule' => $rule]);
            $rule = $temp_result['rule'];
            $temp_result = \Object\Table\Columns::validateSingleColumn($field, $rule, $value, ['process_domains' => true]);
            if (!$temp_result['success']) {
                $result['error'] = array_merge($result['error'], $temp_result['error']);
            }
        }
        // validator
		if (!empty($rule['validator_method']) && !empty($value)) {
			$temp_result = \Object\Validator\Base::method(
				$rule['validator_method'],
				$value,
				$rule['validator_params'] ?? [],
				$rule,
				$neighbouring
			);
			if (!$temp_result['success']) {
				$result['error'] = array_merge($result['error'], $temp_result['error']);
			} else if (!empty($temp_result['data'])) {
				$value = $temp_result['data'];
			}
		}
        // required
        if (!empty($rule['required'])) {
            if ($value . '' === '') {
                $result['error'][] = i18n(null, \Object\Content\Messages::REQUIRED_FIELD);
            }
        }
        // in
        if (!empty($rule['in']) && $value) {
            if (!in_array($value, $rule['in'])) {
                $result['error'][] = i18n(null, \Object\Content\Messages::INVALID_VALUES);
            }
        }
        if (empty($result['error'])) {
            $result['success'] = true;
        }
        return $result;
    }

    /**
     * Prepend field name to errors
     *
     * @param string $field
     * @param array $rule
     * @param array $errors
     */
    private function prependFieldNameToErrors(string $field, array $rule, array $errors) : void {
        $this->errors['error'][$field] = $errors;
        if (strpos($field, '::') !== false) {
            $name = explode('::', $field);
            $name[count($name) - 1] = ucfirst($name[count($name) - 1]);
            $name = implode('-', $name);
        } else {
            $name = ucfirst($field);
        }
        foreach ($errors as $v) {
            $this->errors['error_extended'][] = '[' . i18n(null, $rule['name'] ?? $name) . ']: ' . $v;
        }
    }
}