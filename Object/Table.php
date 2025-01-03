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

use Object\ACL\Resources;
use Object\Data\Common;
use Object\Table\Columns;
use Object\Table\Options;
use Object\Table\Trait2;
use Object\Table\Widgets;
use Object\Traits\Debugable;
use Object\Traits\ObjectableAndStaticable;
use Object\Traits\Stringable;

class Table extends Options
{
    /**
     * Include common trait
     */
    use Trait2;
    use Debugable;
    use Stringable;
    use ObjectableAndStaticable;

    /**
     * Link to database
     *
     * @var string
     */
    public $db_link;

    /**
     * Override for link to database
     *
     * @var string
     */
    public $db_link_flag;

    /**
     * Db object
     *
     * @var object
     */
    public $db_object;

    /**
     * Schema name
     *
     * @var string
     */
    public $schema = '';

    /**
     * Name
     *
     * @var string
     */
    public $name;

    /**
     * Full table name
     *
     * @var string
     */
    public $full_table_name;

    /**
     * Title
     *
     * @var string
     */
    public $title;

    /**
     * Table primary key in format ['id1'] or ['id1', 'id2', 'id3']
     *
     * @var array
     */
    public $pk;

    /**
     * Table default order as array
     * Format:
     *		column 1 => asc or SORT_ASC
     *		column 2 => desc or SORT_DESC
     *
     * @var array
     */
    public $orderby = [];

    /**
     * Table get limit
     *
     * @var int
     */
    public $limit = 0;

    /**
     * Column prefix
     *
     * @var string
     */
    public $column_prefix;

    /**
     * Table columns
     *
     * @var array
     */
    public $columns = [
        //'id' => array('name' => '#', 'type' => 'bigserial'),
        //'name' => array('name' => 'Name', 'type' => 'varchar', 'length' => 255),
    ];

    /**
     * Column settings
     *
     * @var array
     */
    public $column_settings = [];

    /**
     * Table constraints
     *
     * @var array
     */
    public $constraints = [
        //'name_un' => array('type' => 'unique', 'columns' => ['name']),
    ];

    /**
     * Table indexes
     *
     * @var array
     */
    public $indexes = [
        //'name_idx' => array('type' => 'btree', 'columns' => ['name']),
    ];

    /**
     * Whether its a table with a history, and we do point in time quering
     *
     * @var bool
     */
    public $history = false;

    /**
     * History table name
     *
     * @var string
     */
    public $history_name;

    /**
     * Optimistic lock
     *
     * @var boolean
     */
    public $optimistic_lock = false;

    /**
     * Optimistic lock column
     *
     * @var string
     */
    public $optimistic_lock_column;

    /**
     * Table engine
     *
     * @var array
     */
    public $engine = [];

    /**
     * Mapping for options(),
     * Note if you need to map the same field to multiple array keys we could prepend one or more "*" (asterisks)
     *
     * @var array
     */
    public $options_map = [
        //'[table field]' => '[key in array]',
    ];

    /**
     * Condition for options_active()
     *
     * @var array
     */
    public $options_active = [
        //'[table field]' => [value],
    ];

    /**
     * Skip translations in options
     *
     * @var boolean
     */
    public $options_skip_i18n = false;

    /**
     * Whether we need to cache this table
     *
     * @var bool
     */
    public $cache = false;

    /**
     * These tags will be added to caches and then will be used in cache::gc();
     *
     * @var type
     */
    public $cache_tags = [];

    /**
     * Whether we need to cache in memory
     *
     * @var bool
     */
    public $cache_memory = false;

    /**
     * Attributes
     *
     * @var boolean
     */
    public $attributes;
    public $attributes_model;

    /**
     * Addresses
     *
     * @var boolean
     */
    public $addresses;
    public $addresses_model;

    /**
     * Audit
     *
     * @var bool
     */
    public $audit = false;
    public $audit_model;

    /**
     * Comments
     *
     * @var bool
     */
    public $comments = false;
    public $comments_model;

    /**
     * Documents
     *
     * @var bool
     */
    public $documents = false;
    public $documents_model;

    /**
     * Map with parent table, used in widgets
     *
     * @var array
     */
    public $map = [];

    /**
     * Virtual class name
     *
     * @var string
     */
    public $virtual_class_name;

    /**
     * Initiator class
     *
     * @var string
     */
    public $initiator_class = 'Object\Table';

    /**
     * Who inserted/updated/posted the record
     *
     * @var array
     */
    public $who = [
        //'inserted' => true,
        //'updated' => true,
        //'posted' => true,
        //'inactivated' => true,
    ];

    /**
     * Periods
     *
     * @var array
     */
    public $periods = [
        'type' => 'none',
        'year_start' => null,
        'year_end' => null,
        'month_start' => null,
        'month_end' => null,
        'class' => '[table]GeneratedYear[year]Month[month]',
    ];

    /**
     * Is period table
     *
     * @var bool
     */
    public bool $is_period_table = false;

    /**
     * Filter
     *
     * @var array
     */
    public array $filter = [];

    /**
     * Have overrides
     *
     * @var bool
     */
    public bool $have_overrides = false;

    /**
     * Acl options returned from get, used in options and presets
     *
     * @var array
     */
    public $acl_get_options = [];

    /**
     * Tenant
     *
     * @var boolean
     */
    public $tenant = false;

    /**
     * Tenant column
     *
     * @var string
     */
    public $tenant_column;

    /**
     * Module
     *
     * @var boolean
     */
    public $module = false;

    /**
     * Module column
     *
     * @var string
     */
    public $module_column;

    /**
     * SQL Last query
     *
     * @var string
     */
    public $sql_last_query;

    /**
     * Options
     *
     * @var array
     */
    public $options = [];

    /**
     * Triggers
     *
     * @var array
     */
    public $triggers = [];

    /**
     * Acl
     *
     * @var array
     */
    public $acl = [];

    /**
     * Whether its a temp table
     *
     * @var bool
     */
    public $temporary = false;

    /**
     * Data asset (default)
     *
     * @var array
     */
    public $data_asset = [
        'classification' => 'public',
        'protection' => 0,
        'scope' => 'global'
    ];

    /**
     * All widgets
     *
     * @var array
     */
    public $all_widgets = [];

    /**
     * Tree settings
     *
     * @var array
     */
    public $tree = [
        //'id' => '[id column]',
        //'name' => '[name column]',
        //'parent_id' => '[parent_column]',
    ];

    /**
     * Pre-validate unique constraints
     *
     * @var array
     */
    public $unique = [
        //'[field]' => '[name of unique constraint]',
        // or
        //'[field]' => ['[column 1]', ...]
    ];

    /**
     * Preset constants in aactive record model
     *
     * @var array
     */
    public $active_record_preset_constants = [];

    /**
     * Code model
     *
     * @var string
     */
    public $code_model;

    /**
     * Collections
     *
     * @var array
     */
    public $collections = [];

    /**
     * Constructing object
     *
     * @param array $options
     *		skip_db_object
     * @throws Exception
     */
    public function __construct($options = [])
    {
        $this->options = $options;
        // we need to handle overrrides
        parent::overrideHandle($this);
        // we need to determine db link
        if (isset($options['db_link'])) {
            $this->db_link = $options['db_link'];
        }
        if (empty($this->db_link)) {
            // get from flags first
            if (!empty($this->db_link_flag)) {
                $this->db_link = \Application::get($this->db_link_flag);
            }
            // get default link
            if (empty($this->db_link)) {
                $this->db_link = \Application::get('flag.global.default_db_link');
            }
            // if we could not determine the link we throw exception
            if (empty($this->db_link)) {
                throw new \Exception('Could not determine db link in model!');
            }
        }
        // see if we have special handling
        $db_object = \Factory::get(['db', $this->db_link, 'object']);
        if (!isset($db_object)) {
            goto assembleSchema;
        }
        if (method_exists($db_object, 'handleName')) {
            $this->full_table_name = $db_object->handleName($this->schema, $this->name, ['temporary' => $this->temporary]);
        } else {
            assembleSchema:
                        // process table name and schema
                        if (!empty($this->schema)) {
                            $this->full_table_name = $this->schema . '.' . $this->name;
                        } else {
                            $this->full_table_name = $this->name;
                            $this->schema = '';
                        }
        }
        // tenant column
        if ($this->tenant) {
            $this->tenant_column = $this->column_prefix . 'tenant_id';
        }
        // module column
        if ($this->module) {
            $this->module_column = $this->column_prefix . 'module_id';
        }
        // cache tags
        $this->cache_tags[] = $this->full_table_name;
        if ($this->tenant) {
            $this->cache_tags[] = '+numbers_tenant_' . \Tenant::id();
        }
        // history table name
        $this->history_name = $this->full_table_name . '__history';
        // optimistic lock
        if ($this->optimistic_lock) {
            $this->optimistic_lock_column = $this->column_prefix . 'optimistic_lock';
            $this->columns[$this->optimistic_lock_column] = ['name' => 'Optimistic Lock', 'domain' => 'optimistic_lock'];
        }
        // who
        if (!empty($this->who)) {
            foreach ($this->who as $k => $v) {
                $k = strtolower($k);
                $this->columns[$this->column_prefix . $k . '_timestamp'] = ['name' => ucwords($k) . ' Datetime', 'type' => 'timestamp', 'null' => ($k != 'inserted')];
                $this->columns[$this->column_prefix . $k . '_user_id'] = ['name' => ucwords($k) . ' User #', 'domain' => 'user_id', 'null' => true];
            }
        }
        // periods
        if ($this->periods['type'] != 'none') {
            if ($this->periods['type'] == YEAR || $this->periods['type'] == YEAR_AND_MONTH) {
                if (empty($this->periods['year_start']) || empty($this->periods['year_end'])) {
                    throw new \Exception('You need to provide year start and/or end values in periods.');
                }
                $year = (int) date('Y');
                if ($year < $this->periods['year_start'] || $year > $this->periods['year_end']) {
                    throw new \Exception('There is no table for given year.');
                }
                if (empty($this->columns[$this->column_prefix . 'year'])) {
                    throw new \Exception('Table must have year column.');
                }
            }
            if ($this->periods['type'] == YEAR_AND_MONTH) {
                if (empty($this->periods['month_start']) || empty($this->periods['month_end'])) {
                    throw new \Exception('You need to provide month start and/or end values in periods.');
                }
                $month = (int) date('m');
                if ($month < $this->periods['month_start'] || $month > $this->periods['month_end']) {
                    throw new \Exception('There is no table for given month.');
                }
                if (empty($this->columns[$this->column_prefix . 'month'])) {
                    throw new \Exception('Table must have month column.');
                }
            }
            if (empty($this->periods['class'])) {
                throw new \Exception('Table must have class defined in periods.');
            }
            // preset filter
            if ($this->is_period_table) {
                $this->processPeriodsFilter($this->periods['type'], $this->filter);
            }
        }
        // process domain in columns
        $this->columns = Common::processDomainsAndTypes($this->columns, null, $this);
        // initialize db object
        if (empty($options['skip_db_object'])) {
            $this->db_object = new \Db($this->db_link);
        }
        // unique constraints pre validation
        if (!empty($this->unique)) {
            foreach ($this->unique as $k => $v) {
                if (is_string($v)) {
                    $this->unique[$k] = $this->constraints[$v]['columns'];
                }
            }
        }
        // process widgets
        $widgets = Resources::getStatic('widgets');
        $table_widgets = Widgets::getStatic();
        foreach ($table_widgets as $k => $v) {
            $table_widgets[$k] = false;
        }
        $widgets = array_merge($table_widgets, $widgets ?? []);
        foreach ($widgets as $widget => $widget_data) {
            if (!empty($this->{$widget}) && !empty($widget_data)) {
                $this->all_widgets[$widget] = $this->{$widget . '_model'} = '\\' . get_class($this) . '\0Virtual0\Widgets\\' . ucwords($widget);
            } else {
                $this->{$widget} = false;
            }
        }
    }

    /**
     * Process period filter
     *
     * @param string $type
     * @param array $filter
     */
    public function processPeriodsFilter(string $type, array & $filter)
    {
        if ($type == YEAR || $type == YEAR_AND_MONTH) {
            $filter = [
                $this->column_prefix . 'year' => (int) date('Y')
            ];
        }
        if ($type == YEAR_AND_MONTH) {
            $filter[$this->column_prefix . 'month'] = (int) date('m');
        }
    }

    /**
     * Process who columns
     *
     * @param mixed $types
     * @param array $row
     */
    public function processWhoColumns($types, & $row, $timestamp = null)
    {
        if ($types === 'all') {
            $types = array_keys($this->who);
        }
        if (!is_array($types)) {
            $types = [$types];
        }
        if (empty($timestamp)) {
            $timestamp = \Format::now('timestamp');
        }
        foreach ($types as $type) {
            if (!empty($this->who[$type])) {
                // timestamp
                $row[$this->column_prefix . $type . '_timestamp'] = $timestamp;
                // user #
                $row[$this->column_prefix . $type . '_user_id'] = \User::getUser() ?? \User::id();
            } elseif ($type == 'optimistic_lock') {
                if ($this->optimistic_lock) {
                    $row[$this->optimistic_lock_column] = $timestamp;
                }
            }
        }
    }

    /**
     * Determine model map
     *
     * @param string $class
     * @param string $widget_name
     * @param string $virtual_class_name
     * @param array $options
     * @return boolean
     * @throws Exception
     */
    final public function determineModelMap($class, $widget_name, $virtual_class_name, $options = [])
    {
        $this->virtual_class_name = $virtual_class_name;
        $model = \Factory::model($class, true, [$options]);
        if (empty($model->{$widget_name}) || empty($model->{$widget_name}['map'])) {
            throw new \Exception("You must indicate {$widget_name} for {$class} map!");
        }
        // title & name
        $this->title = $model->title . ' ' . ucwords($widget_name);
        $this->name = $model->name . '__' . $widget_name;
        $this->full_table_name = $model->full_table_name . '__' . $widget_name;
        $this->module_code = $model->module_code;
        $this->data_asset = $model->data_asset;
        $this->tenant = $model->tenant;
        $this->module = $model->module;
        // we need to reset tags
        $this->cache_tags = array_merge($model->cache_tags, $this->cache_tags);
        // determine pk
        $columns = [];
        $this->map = $model->{$widget_name}['map'];
        foreach ($model->{$widget_name}['map'] as $k => $v) {
            $columns[$v] = $model->columns[$k];
            if (isset($columns[$v]['domain'])) {
                $columns[$v]['domain'] = str_replace('_sequence', '', $columns[$v]['domain']);
                unset($columns[$v]['type'], $columns[$v]['sequence']);
            }
        }
        $this->model_map_options = $model->{$widget_name}['options'] ?? [];
        $this->columns = array_merge_hard($columns, $this->columns);
        return true;
    }

    /**
     * Process columns
     *		removes not existing columns
     *		processes column types
     *
     * @param array $data
     * @param array $options
     *		boolean ignore_not_set_fields
     *		boolean skip_type_validation
     * @return array
     */
    public function processColumns(& $data, $options = [])
    {
        $save = [];
        // we need to determine columns that have overrides like ;;bytea
        $data_columns = [];
        foreach ($data as $k => $v) {
            if (strpos($k, ';') !== false) {
                $k2 = explode(';', $k)[0];
                if (!empty($this->columns[$k2])) {
                    $data_columns[$k2] = $k;
                }
            }
        }
        // go through all columns
        foreach ($this->columns as $k => $v) {
            $original_key = $data_columns[$k] ?? $k;
            if (!empty($options['ignore_not_set_fields']) && !array_key_exists($original_key, $data)) {
                continue;
            }
            if (empty($options['skip_type_validation'])) {
                $temp = Columns::processSingleColumnType($k, $v, $data[$original_key] ?? null);
                if (array_key_exists($k, $temp)) {
                    $save[$original_key] = $temp[$k];
                }
            } else {
                $save[$original_key] = $data[$original_key];
            }
        }
        $data = $save;
    }

    /**
     * Generate a sequence
     *
     * @param string $column
     * @param string $type
     * @param int|null $tenant
     * @param int|null $module
     * @return int
     */
    public function sequence(string $column, string $type = 'nextval', $tenant = null, $module = null): int
    {
        // add tenant
        if (empty($tenant) && $this->tenant) {
            $tenant = \Tenant::id();
        }
        $temp = $this->db_object->sequence($this->full_table_name . '_' . $column . '_seq', $type, $tenant, $module);
        return $temp['rows'][0]['counter'] ?? 0;
    }

    /**
     * Synchronize sequence
     *
     * @param string $column
     * @param int|null $tenant
     * @param int|null $module
     * @param int|null $value
     */
    public function synchronizeSequence(string $column, $tenant = null, $module = null, $value = null)
    {
        $sequence = $this->full_table_name . '_' . $column . '_seq';
        if (!empty($value)) {
            $this->db_object->setval($sequence, $value, $tenant, $module);
        } else {
            $result = $this->db_object->query("SELECT max({$column}) max_sequence FROM {$this->full_table_name}");
            if (empty($result['num_rows']) || empty($result['rows'][0]['max_sequence'])) {
                return;
            }
            $this->db_object->setval($sequence, $result['rows'][0]['max_sequence'], $tenant, $module);
        }
    }

    /**
     * Reset caches on exit
     */
    public function resetCache()
    {
        // only reset caches if cache link is present
        if (!empty($this->db_object->object->options['cache_link'])) {
            $tags = array_unique($this->cache_tags);
            sort($tags, SORT_STRING);
            $hash = sha1(serialize($tags));
            \Cache::$reset_caches[$this->db_object->object->options['cache_link']][$hash] = $tags;
        }
    }

    /**
     * Create collection object
     *
     * @param array $options
     * @return Collection
     */
    public function collection(array $options = []): Collection
    {
        if (!empty($this->virtual_class_name)) {
            $options['model'] = $this->virtual_class_name;
            return Collection::collectionToModel($options);
        } else {
            return self::collectionStatic($options);
        }
    }

    /**
     * Create collection object (static)
     *
     * @param array $options
     * @return Collection
     */
    public static function collectionStatic(array $options = []): Collection
    {
        $options['model'] = get_called_class();
        return Collection::collectionToModel($options);
    }

    /**
     * Check if table exists in database
     *
     * @return boolean
     */
    public function dbPresent()
    {
        return $this->db_object->tableExists($this->full_table_name);
    }

    /**
     * Aggregate
     *
     * @param array $options
     *		array columns
     *		array where
     *		array groupby
     *		array pk
     * @return array
     */
    public static function aggregateStatic(array $options): array
    {
        $query = self::queryBuilderStatic()->select();
        if (!empty($options['columns'])) {
            $query->columns($options['columns']);
        }
        if (!empty($options['where'])) {
            $query->whereMultiple('AND', $options['where']);
        }
        if (!empty($options['groupby'])) {
            $query->groupby($options['groupby']);
        }
        $result = $query->query($options['pk'] ?? null);
        return $result['rows'];
    }

    /**
     * @see self::aggregateStatic
     */
    public function aggregate(array $options): array
    {
        return self::aggregateStatic($options);
    }

    /**
     * Soft sequence
     *
     * @param string $column
     * @param array $where
     * @param array $group
     * @return array
     */
    public function softSequence(string $column, array $where, array $group): array
    {
        $result = [
            'success' => true,
            'error' => [],
            'current' => 0,
            'next' => 1
        ];
        $temp = $this->aggregate([
            'columns' => [
                'new_sequence_id' => 'MAX(' . $column . ')'
            ],
            'where' => $where,
            'groupby' => $group,
            'pk' => null
        ]);
        if (!empty($temp[0])) {
            $result['current'] = $temp[0]['new_sequence_id'];
            $result['next'] = $result['current'] + 1;
        }
        return $result;
    }

    /**
     * Check unique constraint
     *
     * @param string $name
     * @param array $pk
     * @param array $values
     * @return bool
     */
    public function checkUniqueConstraint(string $name, array $pk, array $values): bool
    {
        // create a query bulder
        $query = $this->queryBuilder(['alias' => 'a'])->select()->columns($pk);
        foreach ($this->unique[$name] as $v2) {
            if ($v2 == $this->tenant_column) {
                $values[$v2] = \Tenant::id();
                continue;
            }
            $query->whereMultiple('AND', [
                'a.' . $v2 => $values[$v2] ?? null,
            ]);
        }
        $result = $query->query(null, ['cache' => false]);
        if ($result['num_rows'] > 0) {
            foreach ($result['rows'] as $v2) {
                foreach ($pk as $v3) {
                    if (($v2[$v3] ?? '') != ($values[$v3] ?? '')) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Get widget model
     *
     * @param string $type
     * @return Table
     */
    public function getWidgetModel(string $type): Table
    {
        return \Factory::model($this->{$type . '_model'});
    }

    /**
     * Get widget model (static)
     *
     * @param string $type
     * @return Table
     */
    public static function getWidgetModelStatic(string $type): Table
    {
        $class = get_called_class();
        $model = new $class();
        return $model->getWidgetModel($type);
    }

    /**
     * Begin
     *
     * @return void
     */
    public function begin(): void
    {
        $this->db_object->begin();
    }

    /**
     * Rollback
     *
     * @return void
     */
    public function rollback(): void
    {
        $this->db_object->rollback();
    }

    /**
     * Commit
     *
     * @return void
     */
    public function commit(): void
    {
        $this->db_object->commit();
    }
}
