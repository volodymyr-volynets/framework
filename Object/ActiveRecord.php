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

use Object\Query\Builder;
use Object\Traits\Debugable;
use Object\Traits\ObjectableAndStaticable;
use Object\Traits\Stringable;

/**
 * @method $this get($options = []) : array
 * @method $this loadById(int|array $id, array|string $options = []) : \Object\ActiveRecord
 * @method $this find(int|array $id, array|string $options = []) : \Object\ActiveRecord
 */
class ActiveRecord
{
    /**
     * Include common trait
     */
    use Debugable;
    use Stringable;
    use ObjectableAndStaticable;

    /**
     * @var string
     */
    protected string $object_table_class;

    /**
     * @var Table
     */
    protected ?Table $object_table_object = null;

    /**
     * @var array
     */
    protected array $object_table_pk = [];

    /**
     * @var array
     */
    public array $full_pk = [];

    /**
     * @var array
     */
    protected array $object_table_log = [];

    /**
     * @var array
     */
    protected array $details = [];

    /**
     * @var array
     */
    protected array $collection = [];

    /**
     * @var array
     */
    protected array $filled_values = [];

    /**
     * Constructor
     */
    public function __construct(array $options = [])
    {
        if (empty($options['skip_table_object'])) {
            $this->object_table_object = new $this->object_table_class($options);
        }
        // preset collection
        $this->collection['name'] = Reflection::getModelName($this->object_table_class);
        $this->collection['model'] = $this->object_table_class;
        $this->collection['pk'] = $this->object_table_pk;
        $this->collection['details'] = [];
    }

    /**
     * Query builder
     *
     * @param array $options
     *		string alias, default a
     *		boolean skip_tenant
     *		boolean skip_acl
     *		mixed existing_values
     * @return Builder
     */
    public function queryBuilder(array $options = []): Builder
    {
        return $this->object_table_object->queryBuilder($options);
    }

    /**
     * Query builder (static)
     *
     * @param array $options
     *		string alias, default a
     *		boolean skip_tenant
     *		boolean skip_acl
     * @return Builder
     */
    public static function queryBuilderStatic(array $options = []): Builder
    {
        $class = get_called_class();
        $model = new $class();
        return $model->queryBuilder($options);
    }

    /**
     * Get
     *
     * @param array $options
     *		cache - if we cache
     *		no_cache - if we need to skip caching
     *		search - array of search condition
     *		where - array of where conditions
     *		orderby - array of columns to sort by
     *		pk - primary key to be used by query
     *		columns - if we need to get certain columns
     *		limit - set this integer if we need to limit query
     *		skip_tenant - if we need to skip tenant
     * @return array|ActiveRecord
     */
    public function get($options = []): array|ActiveRecord
    {
        $options['cast_to_class'] = get_called_class();
        $result = call_user_func_array([$this->object_table_object, 'get'], [$options]);
        if (!empty($result) && (!empty($options['single_row']) || ($options['limit'] ?? null) === 1)) {
            return current($result);
        }
        return $result;
    }

    /**
     * Get (static)
     *
     * @param array $options
     *		cache - if we cache
     *		no_cache - if we need to skip caching
     *		search - array of search condition
     *		where - array of where conditions
     *		orderby - array of columns to sort by
     *		pk - primary key to be used by query
     *		columns - if we need to get certain columns
     *		limit - set this integer if we need to limit query
     *		skip_tenant - if we need to skip tenant
     * @return array|ActiveRecord
     */
    public static function getStatic($options = []): array|ActiveRecord
    {
        $class = get_called_class();
        $object = new $class();
        $options['cast_to_class'] = $class;
        $result = call_user_func_array([$object->object_table_class, 'get'], [$options]);
        if (!empty($result) && (!empty($options['single_row']) || ($options['limit'] ?? null) === 1)) {
            return current($result);
        }
        return $result;
    }

    /**
     * Load ID by code
     *
     * @param string $code
     * @param string|null $model
     * @param string|null $column_name
     * @return mixed
     */
    public function loadIDByCode(string $code, ?string $model = null, ?string $column_name = null): mixed
    {
        $where = [];
        if ($model) {
            $object = \Factory::model($model, true);
        } elseif ($this->object_table_object->code_model) {
            $object = \Factory::model($this->object_table_object->code_model, true);
        } else {
            $object = $this->object_table_object;
        }
        if ($object->tenant) {
            $where[$object->column_prefix . 'tenant_id'] = \Tenant::id();
        }
        // some tables does not have codes so we search by name
        $code_column = $object->column_prefix . 'code';
        if (!isset($object->columns[$code_column])) {
            $code_column = $object->column_prefix . 'name';
        }
        $where[$code_column] = $code;
        // by default we return id
        if (!$column_name) {
            $column_name = $object->column_prefix . 'id';
        }
        $result = call_user_func_array([$object, 'get'], [[
            'where' => $where,
            'pk' => null,
            'columns' => $column_name == ALL ? [] : [$column_name],
            'limit' => 1,
            'single_row' => 1
        ]]);
        if ($column_name == ALL) {
            return $result;
        } else {
            return $result[$column_name];
        }
    }

    /**
     * Load by id (static)
     *
     * @param int|array|string $id
     * @param array|string $options
     * 		column - column name
     * @return ActiveRecord
     */
    public static function loadById(int|array|string $id, array|string $options = []): ActiveRecord
    {
        $class = get_called_class();
        $object = new $class();
        $options['cast_to_class'] = $class;
        return call_user_func_array([$object->object_table_object, 'loadById'], [$id, $options]);
    }

    /**
     * Find (static)
     *
     * Alias for $this->loadById();
     *
     * @param int|array|string $id
     * @param array|string $options
     * 		column - column name
     * @return ActiveRecord
     */
    public static function find(int|array|string $id, array|string $options = []): ActiveRecord
    {
        return self::loadByIdStatic($id, $options);
    }

    /**
     * Compare
     *
     * @param string $type
     * 		pk - primary keys comparison
     * 		values - values comparison
     * @param ActiveRecord $object
     * @param bool $strict
     * @return array|bool
     */
    public function compare(ActiveRecord $object, string $type = 'pk', bool $strict = false): array|bool
    {
        // pk comparison
        if ($type == 'pk') {
            foreach ($this->object_table_object->pk ?? [] as $v) {
                if ($strict) {
                    if (!empty($this->{$v}) && $this->{$v} !== $object->{$v}) {
                        return false;
                    }
                } else {
                    if (!empty($this->{$v}) && $this->{$v} != $object->{$v}) {
                        return false;
                    }
                }
            }
            return true;
        }
        // values comparison
        if ($type == 'values') {
            $result = [];
            foreach ($this->object_table_object->columns as $k => $v) {
                if ($strict) {
                    if ($this->{$k} != $object->{$k}) {
                        $result[$k] = $object->{$k};
                    }
                } else {
                    if ($this->{$k} !== $object->{$k}) {
                        $result[$k] = $object->{$k};
                    }
                }
            }
            return count($result) > 0 ? $result : true;
        }
    }

    /**
     * Log changes
     *
     * @param array $data
     * @return void
     */
    public function logChanges(array $data)
    {
        foreach ($data as $k => $v) {
            if (!isset($this->object_table_log[$k])) {
                $this->object_table_log[$k] = [];
            }
            $this->object_table_log[$k][] = $v;
        }
    }

    /**
     * Get table object
     *
     * @return Table
     */
    public function getTableObject(): Table
    {
        // initialize if not
        if (!$this->object_table_object) {
            $this->object_table_object = new $this->object_table_class();
        }
        return $this->object_table_object;
    }

    /**
     * Get Db object
     *
     * @return \Db
     */
    public function getDbObject(): \Db
    {
        return $this->getTableObject()->db_object;
    }

    /**
     * Fill
     *
     * @param array $data
     * @return ActiveRecord
     */
    public function fill(array $data): ActiveRecord
    {
        foreach ($data as $k => $v) {
            if (!isset($this->object_table_object->columns[$k])) {
                continue;
            }
            $this->{$k} = $v;
            $this->filled_values[$k] = $v;
        }
        return $this;
    }

    /**
     * Add detail
     *
     * @param ActiveRecord $detail
     * @return ActiveRecord
     */
    public function detail(ActiveRecord $detail): ActiveRecord
    {
        $self_class = $this->object_table_object::class;
        $detail_class = $detail->object_table_object::class;
        if (!isset($this->details[$detail_class])) {
            $this->details[$detail_class] = [];
        }
        $this->details[$detail_class][] = $detail;
        // add to collection
        if (!isset($this->collection['details'][$detail_class])) {
            if (!isset($detail->object_table_object->collections[$self_class])) {
                throw new \Exception('Collection is not defined for this model! Class: ' . $self_class . ' Detail: ' . $detail_class);
            }
            $this->collection['details'][$detail_class] = $detail->object_table_object->collections[$self_class];
        }
        return $this;
    }

    /**
     * Merge
     *
     * @return array
     */
    public function merge(): array
    {
        $collection = Collection::collectionToModel($this->collection);
        $data = $this->getFilledValues();
        foreach ($this->details as $k => $v) {
            $data[$k] = [];
            foreach ($v as $v2) {
                $data[$k][] = $v2->getFilledValues();
            }
        }
        return $collection->merge($data);
    }

    /**
     * Get filled values
     *
     * @return array
     */
    public function getFilledValues(): array
    {
        return $this->filled_values;
    }

    /**
     * Set full pk and filled columns
     *
     * @param string $column
     * @param mixed $value
     * @return void
     */
    protected function setFullPkAndFilledColumn(string $column, mixed $value): void
    {
        if (in_array($column, $this->object_table_pk)) {
            // preset pk with nulls
            if (empty($this->full_pk)) {
                foreach ($this->object_table_pk as $v) {
                    $this->full_pk[$v] = null;
                }
            }
            $this->full_pk[$column] = $value;
        }
        $this->filled_values[$column] = $value;
    }
}
