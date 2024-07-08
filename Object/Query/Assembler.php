<?php

/**
 * Query builder (wrapper)
 */
namespace Object\Query;
class Assembler {

    /**
     * @var \Object\Query\Builder
     */
    protected \Object\Query\Builder $query;

    /**
     * @var array
     */
    protected array $data;

    /**
     * @var array
     */
    protected array $result;

    /**
     * @var array
     */
    protected array $options = [];

    /**
     * Constructor
     *
     * @param \Object\Table $model
     * @param array $options
     */
    public function __construct(\Object\Table $model, array & $data, array $options = []) {
        $options['alias']??= 'relation_a';
        $this->query = $model->queryBuilder($options);
        $this->query->columns($options['alias'] . '.*');
        $this->data = & $data;
        $this->options = $options;
        // see if we have other child relations
        if (!empty($options['relation_children'])) {
			$this->query->withRelation([$options['relation_children'] => $options['relation_children']]);
		}
    }

    /**
     * Join
     *
     * @param array $method
     * @param array $options
     * @param array $values
     * @return \Object\Query\Assembler
     */
    public function join(array $method, array $options = [], array $values = []) : \Object\Query\Assembler {
        call_user_func_array($method, [& $this->query, $options, $values]);
        return $this;
    }

    /**
     * Pivot
     *
     * @param array $method
     * @param string $name
     * @param array $columns
     * @param array $options
     * @param array $values
     * @return \Object\Query\Assembler
     */
    public function pivot(array $method, string $name, array|null $columns = null, array $options = [], array $values = []) : \Object\Query\Assembler {
        $options['pivot'] = true;
        $table = $method[0] ?? null;
        if (empty($columns) && is_object($table) && is_a($table, 'Object\Table')) {
            $columns = array_keys($method[0]->columns);
        }
        if (is_numeric_key_array($columns)) {
            $columns = array_combine($columns, $columns);
        }
        call_user_func_array($method, [& $this->query, $name, $columns, $options, $values]);
        return $this;
    }

    /**
     * Query
     *
     * @return \Object\Query\Assembler
     */
    public function query() : \Object\Query\Assembler {
        $this->result = $this->query->query()['rows'];
        return $this;
    }

    /**
     * Pk
     *
     * @param array $keys
     * @return \Object\Query\Assembler
     */
    public function pk(array $keys) : \Object\Query\Assembler {
        pk($keys, $this->result);
        return $this;
    }

    /**
     * Assign
     *
     * @param callable|null $callback
     * @return \Object\Query\Assembler
     */
    public function assign(callable|null $callback = null) : \Object\Query\Assembler {
        foreach ($this->result as $k => $v) {
            // pivot we transform
            if ($this->query->data['pivot']) {
                foreach ($v as $k4 => $v4) {
                    foreach ($this->query->data['pivot'] as $k2 => $v2) {
                        foreach ($v2 as $v3) {
                            $v[$k4]['Pivot'][$v3] = $v4[$v3];
                            unset($v[$k4][$v3]);
                        }
                    }
                    // todo: cast to Active Record
                    // $k2 has model class name
                }
            }
            // assign
            if ($callback == null) {
                $this->data[$k][$this->options['relation_key']] = $v;
            } else {
                $callback($v, $k);
            }
        }
        return $this;
    }
}