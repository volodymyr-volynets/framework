<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object;

use Object\Data\Common;
use Object\Data\Types;
use Object\Table\Options;

class Data extends Options
{
    /**
     * Primary key columns, used to convert data
     * Note: we must use full column names with column prefix
     *
     * @var array
     */
    public $pk = [];

    /**
     * Key in data is this column
     *
     * @var string
     */
    public $column_key;

    /**
     * Column prefix or table alias
     *
     * @var string
     */
    public $column_prefix;

    /**
     * A list of available columns
     *
     * @var array
     */
    public $columns = [];

    /**
     * Data would be here
     *
     * @var array
     */
    public $data = [];

    /**
     * List of columns to sort by
     * Format:
     *		column 1 => asc or SORT_ASC
     *		column 2 => desc or SORT_DESC
     * @var array
     */
    public $orderby = [];

    /**
     * Mapping for options() method
     * Note if you need to map the same field to multiple array keys we could prepend one or more "*" (asterisks)
     *
     * @var array
     */
    public $options_map = [
        //'[data column]' => '[key in array]',
    ];

    /**
     * Condition for options_active() method
     *
     * @var type
     */
    public $options_active = [
        //'[data column]' => [value],
    ];

    /**
     * Mapping for optgroups() method
     *
     * @var array
     */
    public $optgroups_map = [
        //'column' => '[data column]',
        //'model' => '[model name]',
    ];

    /**
     * Mapping for optmultis() method
     *
     * @var array
     */
    public $optmultis_map = [
        //'column' => ['alias' => '[alias name]', 'model' => '[model name]'],
        //'column' => ['alias' => '[alias name]', 'column' => '[column name]'],
    ];

    /**
     * Initiator class
     *
     * @var string
     */
    public $initiator_class = '\Object\Data';

    /**
     * Tenant
     *
     * @var boolean
     */
    public $tenant;

    /**
     * Tenant column
     *
     * @var string
     */
    public $tenant_column;

    /**
     * Constructor
     */
    public function __construct()
    {
        // we need to handle overrrides
        parent::overrideHandle($this);
        // we must have columns
        if (empty($this->columns)) {
            throw new \Exception('\Object\Data ' . get_called_class() . ' children must have columns!');
        }
        // process domain in columns, we skip domain model
        $class = get_called_class();
        if ($class != 'Object\Data\Domains') {
            if ($class == 'Object\Data\Types') {
                $this->columns = Common::processDomainsAndTypes($this->columns, $this->data);
            } else {
                $this->columns = Common::processDomainsAndTypes($this->columns);
            }
        }
    }

    /**
     * Get data
     *
     * @param array $options
     *		where - array of conditions
     *		pk - primary key to be used by query
     *		orderby - array of columns to sort by
     * @return array
     */
    public function get($options = [])
    {
        // get available data types
        if (get_called_class() == 'Object\Data\Types') {
            $types = $this->data;
        } else {
            $types = Types::getStatic();
        }
        // transform data
        $result = [];
        foreach ($this->data as $k => $v) {
            foreach ($this->columns as $k2 => $v2) {
                if ($this->column_key == $k2) {
                    $result[$k][$k2] = $k;
                } elseif (!array_key_exists($k2, $v)) {
                    $result[$k][$k2] = $v2['default'] ?? $types[$v2['type']]['no_data_type_default'] ?? null;
                } else {
                    $result[$k][$k2] = $v[$k2];
                }
            }
        }
        // filtering
        if (!empty($options['where'])) {
            foreach ($result as $k => $v) {
                $found = true;
                foreach ($options['where'] as $k2 => $v2) {
                    // todo: add options ad in query
                    if (array_key_exists($k2, $v) && $v[$k2] != $v2) {
                        $found = false;
                        break;
                    }
                }
                if (!$found) {
                    unset($result[$k]);
                }
            }
        }
        // sorting, if none specified we sort by name if its in columns
        $orderby = null;
        if (isset($options['orderby'])) {
            $orderby = $options['orderby'];
        } elseif (isset($this->orderby)) {
            $orderby = $this->orderby;
        } elseif (isset($this->columns[$this->column_prefix . 'name'])) {
            $orderby = [$this->column_prefix . 'name' => SORT_ASC];
        }
        if (!empty($orderby)) {
            $method = [];
            foreach ($orderby as $k => $v) {
                $type = $types[$this->columns[$k]['type']]['php_type'];
                if ($type == 'integer' || $type == 'float') {
                    $method[$k] = SORT_NUMERIC;
                }
            }
            array_key_sort($result, $orderby, $method);
        }
        // if we have primary key
        $pk = $options['pk'] ?? $this->pk;
        if (!empty($pk)) {
            pk($pk, $result);
        }
        // single row
        if (!empty($options['single_row'])) {
            return current($result);
        }
        return $result;
    }

    /**
     * Get setting
     *
     * @param string $setting
     * @param string $property
     * @return mixed
     */
    public static function getSetting($setting = null, $property = null)
    {
        $data = self::getStatic();
        $keys = [];
        if (isset($setting)) {
            $keys[] = $setting;
            if (isset($property)) {
                $keys[] = $property;
            }
        }
        return array_key_get($data, $keys);
    }

    /**
     * @see $this->get()
     */
    public static function getStatic($options = [])
    {
        $class = get_called_class();
        $object = new $class();
        return $object->get($options);
    }

    /**
     * Options (static)
     *
     * @see $this::get()
     */
    public static function optionsStatic(array $options = [])
    {
        $class = get_called_class();
        $object = new $class();
        return $object->options($options);
    }

    /**
     * @see $this->get()
     * @return boolean
     */
    public function exists($options = [])
    {
        $data = $this->get($options);
        return !empty($data);
    }

    /**
     * @see $this->get()
     * @return boolean
     */
    public static function existsStatic($options = [])
    {
        $class = get_called_class();
        $object = new $class();
        return $object->exists($options);
    }

    /**
     * @see $this->get()
     */
    public static function getOneKeyStatic($key, array $options = [])
    {
        $class = get_called_class();
        $object = new $class();
        $data = $object->options($options);
        return $data[$key]['name'];
    }
}
