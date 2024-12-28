<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

class Json2
{
    /**
     * Data
     *
     * @var mixed
     */
    protected mixed $data = null;

    /**
     * Is valid
     *
     * @var bool
     */
    protected bool $is_valid = false;

    /**
     * Constructor
     *
     * @param mixed $data
     */
    public function __construct(mixed $data)
    {
        // json with errors
        if (is_string($data)) {
            $temp = trim($data, '"');
            $temp = str_replace("\\\"", '"', $temp);
            $temp = str_replace("\\\\", '\\', $temp);
            $temp = str_replace('\\r', "", $temp);
            $temp = str_replace('\n', PHP_EOL, $temp);
            $this->is_valid = json_validate($temp);
            if (!$this->is_valid) {
                $this->data = $temp;
                return;
            }
        }
        if (is_string($data) && is_json($data)) {
            // if its has escaped quotes
            if (strpos($data, "\\\"") !== false) {
                $data = str_replace("\\\"", '"', $data);
            }
            // validate
            $this->is_valid = json_validate($data);
            // and we decode
            $this->data = json_decode($data, true);
        } elseif (is_array($data)) {
            $this->data = $data;
            $this->is_valid = true;
        } elseif (is_scalar($data)) {
            $this->data = $data;
            $this->is_valid = true;
        } else {
            $this->data = null;
            $this->is_valid = true;
        }
    }

    /**
     * Is valid
     */
    public function isValid(): bool
    {
        return $this->is_valid;
    }

    /**
     * To JSON
     *
     * @return string
     */
    public function toJSON(bool $nice = false): string
    {
        $flags = $nice ? (JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : 0;
        $result = json_encode($this->data, $flags);
        $result = trim($result, '"');
        $result = str_replace("\\\"", '"', $result);
        return $result;
    }

    /**
     * To array or scalar
     *
     * @return mixed
     */
    public function toArrayOrScalar(): mixed
    {
        return $this->data;
    }

    /**
     * To array
     *
     * @return mixed
     */
    public function toArray(): array
    {
        if (is_null($this->data) || $this->data === 'null') {
            return [];
        }
        if (is_string($this->data) && is_json($this->data)) {
            return json_decode($this->data, true);
        }
        return $this->data;
    }

    /**
     * Sort by key
     *
     * @return Json2
     */
    public function sortByKey(): Json2
    {
        if (is_array($this->data)) {
            ksort($this->data);
        }
        return $this;
    }
}
