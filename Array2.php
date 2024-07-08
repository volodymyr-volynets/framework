<?php

/**
 * Array2
 */
class Array2 {

    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @var string
     */
    protected string $last_operation = '__construct';

    /**
     * Constructor
     *
     * @param array|JsonSerializable|Traversable|string $data
     */
    public function __construct(array|JsonSerializable|Traversable|string $data) {
        $this->data = $this->dataToArray2($data);
    }

    /**
     * Data to \Array2
     *
     * @param array|JsonSerializable|Traversable|string $data
     * @throws \Exception
     */
    protected function dataToArray2(array|JsonSerializable|Traversable|string $data) : array {
        if (is_array($data)) {
            return $data;
        } elseif ($data instanceof JsonSerializable) {
            return (array) $data->jsonSerialize();
        } elseif ($data instanceof Traversable) {
            return iterator_to_array($data);
        } elseif (is_json($data)) {
            return json_decode($data, true);
        } else {
            Throw new \Exception('Unknown data.');
        }
    }

    /**
     * To array
     *
     * @return array
     */
    public function toArray() : array|stdClass {
        if (in_array($this->last_operation, ['__first', '__last'])) {
            return current($this->data);
        } else {
            return $this->data;
        }
    }

    /**
     * To objects
     *
     * @return array|stdClass
     */
    public function toObjects() : array|stdClass {
        $result = [];
        foreach ($this->data as $k => $v) {
            $result[$k] = (object) $v;
        }
        if (in_array($this->last_operation, ['__first', '__last'])) {
            return current($result);
        } else {
            return $result;
        }
    }

    /**
     * To JSON
     *
     * @return string
     */
    public function toJson() : string {
        if (in_array($this->last_operation, ['__first', '__last'])) {
            return json_encode(current($this->data));
        } else {
            return json_encode($this->data);
        }
    }

    /**
     * First
     *
     * @return \Array2
     */
    public function first() : \Array2 {
        $this->last_operation = '__first';
        $key = array_key_first($this->data);
        if ($key !== null) {
            $this->data = [$key => $this->data[$key]];
        }
        return $this;
    }

    /**
     * Last
     *
     * @return \Array2
     */
    public function last() : \Array2 {
        $this->last_operation = '__last';
        $key = array_key_last($this->data);
        if ($key !== null) {
            $this->data = [$key => $this->data[$key]];
        }
        return $this;
    }

    /**
     * Map
     *
     * @param  callable  $callback
     * @return \Array2
     */
    public function map(callable $callback) : \Array2 {
        $keys = array_keys($this->data);
        $values = array_map($callback, $this->data, $keys);
        return new static(array_combine($keys, $values));
    }

    /**
     * Filter
     *
     * @param  callable|null $callback
     * @return \Array2
     */
    public function filter(callable $callback = null) : \Array2 {
        if ($callback) {
            return new static(array_filter($this->data, $callback, ARRAY_FILTER_USE_BOTH));
        }
        return new static(array_filter($this->data));
    }

    /**
     * Reduce to a single value
     *
     * @param  callable  $callback
     * @param  mixed  $initial
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null) {
        return array_reduce($this->data, $callback, $initial);
    }

    /**
     * Get callback
     *
     * @param  callable|string|null  $value
     * @return callable
     */
    protected function getCallbackRowValue($callback) {
        if (!is_string($callback) && is_callable($callback)) {
            return $callback;
        }
        return function ($row) use ($callback) {
            return array_key_get($row, $callback);
        };
    }

    /**
     * Min
     *
     * @param  callable|string|null $callback
     * @return mixed
     */
    public function min($callback = null) {
        $callback = $this->getCallbackRowValue($callback);
        return $this->map(function ($value) use ($callback) {
            return $callback($value);
        })->filter(function ($value) {
            return !is_null($value);
        })->reduce(function ($result, $value) {
            return is_null($result) || $value < $result ? $value : $result;
        });
    }

    /**
     * Max
     *
     * @param  callable|string|null $callback
     * @return mixed
     */
    public function max($callback = null) {
        $callback = $this->getCallbackRowValue($callback);
        return $this->map(function ($value) use ($callback) {
            return $callback($value);
        })->filter(function ($value) {
            return !is_null($value);
        })->reduce(function ($result, $value) {
            return is_null($result) || $value > $result ? $value : $result;
        });
    }

    /**
     * Wrap and convert to \Array2
     *
     * @param mixed $value
     * @return \Array2
     */
    public static function wrap($value) : \Array2 {
        if (is_null($value)) {
            $value = [];
        } else {
            $value = is_array($value) ? $value : [$value];
        }
        return new static($value);
    }

    /**
     * Unwrap and convert to plain array
     *
     * @param mixed $value
     * @return array
     */
    public function unwrap($value) : array {
        if ($value instanceof \Array2) {
            return $value->toArray();
        } else {
            return (array) $value;
        }
    }

    /**
     * Times
     *
     * @param int $number
     * @param callable  $callback
     * @return \Array2
     */
    public static function times(int $number, callable $callback = null) : \Array2 {
        if ($number == 0) {
            return new static([]);
        } else if (is_null($callback)) {
            return new static(range(1, $number));
        } else {
            return (new static(range(1, $number)))->map($callback);
        }
    }

    /**
     * All data as array
     *
     * @return array
     */
    public function all() : array {
        return $this->data;
    }

    /**
     * Average
     *
     * @param  callable|string|null  $callback
     * @return mixed
     */
    public function avg($callback = null) {
        $callback = $this->getCallbackRowValue($callback);
        $items = $this->map(function ($value) use ($callback) {
            return $callback($value);
        })->filter(function ($value) {
            return !is_null($value);
        });
        if ($count = $items->count()) {
            return $items->sum() / $count;
        }
    }

    /**
     * Count
     *
     * @return int
     */
    public function count() : int {
        return count($this->data);
    }

    /**
     * Sum
     *
     * @param callable|string|null $callback
     * @return mixed
     */
    public function sum($callback = null) {
        if (is_null($callback)) {
            $callback = function($value) { return $value; };
        } else {
            $callback = $this->getCallbackRowValue($callback);
        }
        return $this->reduce(function ($result, $item) use ($callback) {
            return $result + $callback($item);
        }, 0);
    }

    /**
     * Push
     *
     * @param mixed $value
     * @return \Array2
     */
    public function push($value) : \Array2 {
        $result = new static($this->data);
        array_push($this->data, $value);
        return $result;
    }

    /**
     * Pop
     *
     * @return mixed
     */
    public function pop() {
        return array_pop($this->data);
    }

    /**
     * Unshift
     *
     * @param mixed $value
     * @return \Array2
     */
    public function unshift($value) : \Array2 {
        $result = new static($this->data);
        array_unshift($this->data, $value);
        return $result;
    }

    /**
     * Shift
     *
     * @return mixed
     */
    public function shift() {
        return array_shift($this->data);
    }

    /**
     * Each
     *
     * @param callable $callback
     * @return \Array2
     */
    public function each(callable $callback) : \Array2 {
        foreach ($this->data as $k => & $v) {
            if ($callback($v, $k) === false) {
                break;
            }
        }
        return $this;
    }

    /**
     * Sort an array by certain keys with certain methods
     *
     * @param array $keys
     * 		['id' => SORT_ASC, 'name' => SORT_DESC]
     * 		['id' => 'asc', 'name' => 'desc']
     * @param array $methods
     * 		['id' => SORT_NUMERIC, 'name' => SORT_NATURAL]
     */
    public function sort($keys, $methods = []) : \Array2 {
        array_key_sort($this->data, $keys, $methods);
        return $this;
    }

    /**
     * Sort keys
     *
     * @param string|int $order
     * @param int $flags
     * @return \Array2
     */
    public function sortKeys(string|int $order = SORT_ASC, int $flags = SORT_REGULAR) : \Array2 {
        if (in_array($order, [SORT_ASC, SORT_DESC])) {
			// we accept those as is
		} else if (strtolower($v) == 'desc') {
			$order = SORT_DESC;
		} else {
			$order = SORT_ASC;
		}
        if ($order == SORT_ASC) {
            ksort($this->data, $flags);
        } else {
            krsort($this->data, $flags);
        }
        return $this;
    }

    /**
     * Collapse multi-dimensional array into one dimension
     *
     * @return \Array2
     */
    public function collapse() : \Array2 {
        $result = [];
        foreach ($this->data as $v) {
            if ($v instanceof \Array2) {
                $v = $v->all();
            } elseif (!is_array($v)) {
                continue;
            }
            $result[] = $v;
        }
        $this->data = array_merge([], ...$result);
        return $this;
    }

    /**
     * Diff
     *
     * @param array|JsonSerializable|Traversable|string $data
     * @param string $type
     *      array_diff - items in the array that are not present in the given data
     *      array_diff_assoc - items in the array whose keys and values are not present in the given data
     *      array_udiff - items in the array that are not present in the given data, using the callback
     *      array_diff_uassoc - items in the array whose keys and values are not present in the given data, using the callback
     *      array_diff_key - items in the array whose keys are not present in the given data
     *      array_diff_ukey - items in the array whose keys are not present in the given items, using the callback
     * @param callable|null $callback
     * @return static
     */
    public function diff(array|JsonSerializable|Traversable|string $data, string $type = 'array_diff', callable|null $callback = null) : \Array2 {
        // for these 3 callback is mandatory
        if (in_array($type, ['array_udiff', 'array_diff_uassoc', 'array_diff_ukey']) && !$callback) {
            Throw new \Exception('Callback?');
        }
        if ($type == 'array_diff') {
            return new static(array_diff($this->data, $this->dataToArray2($data)));
        } else if ($type == 'array_diff_assoc') {
            return new static(array_diff_assoc($this->data, $this->dataToArray2($data)));
        } else if ($type == 'array_udiff') {
            return new static(array_udiff($this->data, $this->dataToArray2($data), $callback));
        } else if ($type == 'array_diff_uassoc') {
            return new static(array_diff_uassoc($this->data, $this->dataToArray2($data), $callback));
        } else if ($type == 'array_diff_key') {
            return new static(array_diff_key($this->data, $this->dataToArray2($data)));
        } else if ($type == 'array_diff_ukey') {
            return new static(array_diff_ukey($this->data, $this->dataToArray2($data), $callback));
        }
    }

    /**
     * Reject
     *
     * @param callable|mixed $callback
     * @return \Array2
     */
    public function reject($callback) : \Array2 {
        return $this->filter(function($value, $key) use ($callback) {
            return !is_string($callback) && is_callable($callback)
                ? !$callback($value, $key)
                : $value != $callback;
        });
    }

    /**
     * Every
     *
     * @param callable|mixed $callback
     * @return \Array2
     */
    public function every($callback) : \Array2 {
        return $this->filter(function($value, $key) use ($callback) {
            return !is_string($callback) && is_callable($callback)
                ? $callback($value, $key)
                : $value == $callback;
        });
    }

    /**
     * Except
     *
     * @param array $keys
     * @return \Array2
     */
    public function except(array $keys) : \Array2 {
        $result = [];
        foreach ($this->data as $k => $v) {
            foreach ($keys as $key) {
                if (array_key_check_if_key_exists($v, $key)) {
                    array_key_get($v, $key, ['unset' => true]);
                }
            }
            $result[$k] = $v;
        }
        return new static($result);
    }

    /**
     * Only
     *
     * @param array $keys
     * @return \Array2
     */
    public function only(array $keys) : \Array2 {
        $result = [];
        foreach ($this->data as $k => $v) {
            $result[$k] = [];
            foreach ($keys as $key) {
                array_key_set($result[$k], $key, array_key_get($v, $key));
            }
        }
        return new static($result);
    }

    /**
     * Unique
     *
     * @param string|callable|null $key
     * @param bool $strict
     * @return \Array2
     */
    public function unique(string|callable|null $key = null, bool $strict = false) : \Array2 {
        if (!is_string($key) && is_callable($key)) {
            $callback = $key;
        } else {
            $callback = function($item) use ($key) {
                return array_key_get($item, $key);
            };
        }
        $existing = [];
        return $this->reject(function($item, $key) use ($callback, $strict, & $existing) {
            if (in_array($id = $callback($item, $key), $existing, $strict)) {
                return true;
            }
            $existing[] = $id;
        });
    }

    /**
     * Position
     *
     * @param mixed|callable $value
     * @param string $type
     *      next_value - next item after value
     *      next_values - all next items after value
     *      prev_value - previous item before value
     *      prev_values - all previous items before value
     *      next_key - next key after value
     *      next_keys - all next keys after value
     *      prev_key - previous keys before value
     *      prev_keys - all previous keys before value
     *      current_value - current value
     *      current_key - current value
     * @param bool $strict
     * @return static
     */
    public function position($value, string $type = 'next_value', bool $strict = false) {
        $callback = null;
        $result = [];
        if (!is_string($value) && is_callable($value)) {
            $callback = $value;
        }
        if ($type == 'next_value' || $type == 'next_values' || $type == 'prev_value' || $type == 'prev_values' || $type == 'current_value') {
            if ($callback) {
                $pairs = array_map(null, array_keys($this->data), array_values($this->data));
                $key = array_reduce($pairs, function ($result, $pair) use ($callback) {
                    [$key, $value] = $pair;
                    if ($result === false) {
                        if ($callback($result, $value, $key)) {
                            $result = $key;
                        }
                    }
                    return $result;
                }, false);
            } else {
                $key = array_search($value, $this->data, $strict);
            }
        } else if ($type == 'next_key' || $type == 'next_keys' || $type == 'prev_key' || $type == 'prev_keys' || $type == 'current_key') {
            if ($callback) {
                $pairs = array_map(null, array_keys($this->data), array_values($this->data));
                $key = array_reduce($pairs, function ($result, $pair) use ($callback) {
                    [$key, $value] = $pair;
                    if ($result === false) {
                        if ($callback($result, $value, $key)) {
                            $result = $key;
                        }
                    }
                    return $result;
                }, false);
            } else {
                $key = false;
                if (array_key_exists($value, $this->data)) {
                    $key = $value;
                }
            }
        }
        if ($key !== false) {
            $position_type = 'prev';
            foreach ($this->data as $k => $v) {
                if ($k === $key) {
                    // if its current value we return that
                    if ($type == 'current_value' || $type == 'current_key') {
                        $result[$k] = $v;
                        break;
                    }
                    $position_type = 'after';
                    continue;
                }
                if ($position_type == 'prev' && ($type == 'prev_value' || $type == 'prev_values' || $type == 'prev_key' || $type == 'prev_keys')) {
                    $result[$k] = $v;
                }
                if ($position_type == 'after' && ($type == 'next_value' || $type == 'next_values' || $type == 'next_key' || $type == 'next_keys')) {
                    $result[$k] = $v;
                    if ($type == 'next_value' || $type == 'next_key') {
                        break;
                    }
                }
            }
            if ($type == 'prev_value' || $type == 'prev_key') {
                $k2 = array_key_last($result);
                $result = [$k2 => $result[$k2]];
            }
        }
        // for key types we only return keys
        if ($type == 'next_key' || $type == 'next_keys' || $type == 'prev_key' || $type == 'prev_keys' || $type == 'current_key') {
            $result = array_keys($result);
        }
        return new static($result);
    }

    /**
     * Print
     *
     * @param string $name
     */
    public function print(string $name = '') {
        print_r2($this->data, $name);
    }

    /**
     * Is empty
     *
     * @return bool
     */
    public function isEmpty() : bool {
        return count($this->data) == 0;
    }

    /**
     * Is not empty
     *
     * @return bool
     */
    public function isNotEmpty() : bool {
        return count($this->data) != 0;
    }

    /**
     * Keys
     *
     * @return \Array2
     */
    public function keys() : \Array2 {
        return new static(array_keys($this->data));
    }

    /**
     * Values
     *
     * @return \Array2
     */
    public function values() : \Array2 {
        return new static(array_values($this->data));
    }

    /**
     * Value from the \Array2, 1 or many
     *
     * @param string|array $key
     * @param int $number
     * @return mixed
     */
    public function value(string|array $key, int $number = 1) {
        $result = [];
        $counter = 0;
        foreach($this->data as $k => $v) {
            $result[] = array_key_get($v, $key);
            $counter++;
            if ($counter == $number) {
                break;
            }
        }
        if ($number == 1) {
            return reset($result);
        } else {
            return $result;
        }
    }

    /**
     * Slice the array data
     *
     * @param int $offset
     * @param int $length
     * @return \Array2
     */
    public function slice(int $offset, int|null $length = null) : \Array2 {
        return new static(array_slice($this->data, $offset, $length, true));
    }

    /**
     * Take the first or last number of elements.
     *
     * @param int $number
     * @return \Array2
     */
    public function take(int $number) : \Array2 {
        if ($number < 0) {
            return $this->slice($number, abs($number));
        } else {
            return $this->slice(0, $number);
        }
    }

    /**
     * Contains
     *
     * @param callable|mixed $callback
     * @return bool
     */
    public function contains($callback) : bool {
        $result = $this->filter(function($value, $key) use ($callback) {
            return !is_string($callback) && is_callable($callback)
                ? $callback($value, $key)
                : $value == $callback;
        });
        return $result->count() != 0;
    }

    /**
     * Some, alias to contains
     *
     * @param callable|mixed $callback
     * @return bool
     */
    public function some($callback) : bool {
        return $this->contains($callback);
    }

    /**
     * Missing
     *
     * @param callable|mixed $callback
     * @return bool
     */
    public function missing($callback) : bool {
        $result = $this->filter(function($value, $key) use ($callback) {
            return !is_string($callback) && is_callable($callback)
                ? $callback($value, $key)
                : $value == $callback;
        });
        return $result->count() == 0;
    }

    /**
     * Pk
     *
     * @param array|string $keys
     * @return \Array2
     */
    public function pk(array|string $keys) : \Array2 {
        $result = pk($keys, $this->data, true);
        return new static($result);
    }

    /**
     * Chunk
     *
     * @param int $size
     * @retrun \Array2
     */
    public function chunk(int $size) : \Array2 {
        $chunks = [];
        foreach (array_chunk($this->data, $size, true) as $chunk) {
            $chunks[] = new static($chunk);
        }
        return new static($chunks);
    }

    /**
     * Split
     *
     * @param int $number
     * @return \Array2
     */
    public function split(int $number) : \Array2 {
        $result = new static([]);
        if ($this->isEmpty()) {
            return $result;
        }
        $size = floor($this->count() / $number);
        $remain = $this->count() % $number;
        $start = 0;
        for ($i = 0; $i < $number; $i++) {
            $counter = $size;
            if ($i < $remain) {
                $counter++;
            }
            if ($counter) {
                $result->push(new static(array_slice($this->data, $start, $counter)));
                $start+= $counter;
            }
        }
        return $result;
    }

    /**
     * Combine
     *
     * @param array|null $keys
     * @param array $values
     * @return \Array2
     */
    public function combine(array|null $keys, array $values) : \Array2 {
        if ($keys == null) {
            $keys = array_values($this->data);
        }
        $arguments = func_get_args();
        unset($arguments);
        if (count($arguments) == 1) {
            $result = array_combine($keys, $values);
        } else {
            $result = [];
            foreach ($arguments as $v) {
                $result[] = array_combine($keys, $v);
            }
        }
        return new static($result);
    }

    /**
     * Shuffle
     *
     * @return \Array2
     */
    public function shuffle() : \Array2 {
        $result = $this->data;
        shuffle($result);
        return new static($result);
    }

    /**
     * Range
     *
     * @param string|int|float $start
     * @param string|int|float $end
     * @param int|float $step
     * @return \Array2
     */
    public function range(string|int|float $start, string|int|float $end, int|float $step = 1) : \Array2 {
        return new static(range($start, $end, $step));
    }

    /**
     * Pluck
     *
     * @param string|array $column_value
     * @param string|array|null $column_key
     * @return \Array
     */
    public function pluck(string|array $column_value, string|array|null $column_key = null) : \Array2 {
        $result = [];
        foreach ($this->data as $k => $v) {
            if ($column_key !== null) {
                $key = array_key_get($v, $column_key);
                $result[$key] = array_key_get($v, $column_value);
            } else {
                $result[] = array_key_get($v, $column_value);
            }
        }
        return new static($result);
    }

    /**
     * Cross join
     *
     * @param array ...$values
     * @return \Array2
     */
    public function crossJoin(array ...$values) : \Array2 {
        $result = [[]];
        if ($this->count()) {
            array_unshift($values, $this->data);
        }
        foreach ($values as $k => $v) {
            $temp = [];
            foreach ($result as $v2) {
                foreach ($v as $v3) {
                    $v2[$k] = $v3;
                    $temp[] = $v2;
                }
            }
            $result = $temp;
        }
        return new static($result);
    }

    /**
     * Random
     *
     * @param int $number
     * @return \Array2
     */
    public function random(int $number = 1) : \Array2 {
        $count = $this->count();
        if ($number > $count) {
            $number = $count;
        }
        $keys = array_rand($this->data, $number);
        $result = [];
        foreach ((array) $keys as $k) {
            $result[] = $this->data[$k];
        }
        return new static($result);
    }

    /**
     * When based on condition
     *
     * @param bool|mixed $condition
     * @param callable $callback
     * @param callable|null $default
     * @return static|mixed
     */
    public function when($condition, callable $callback, callable|null $default = null) {
        if ($condition) {
            return $callback($this, $condition);
        } else if ($default) {
            return $default($this, $condition);
        }
        return $this;
    }

    /**
     * When is not empty
     *
     * @param bool|mixed $condition
     * @param callable $callback
     * @param callable|null $default
     * @return static|mixed
     */
    public function whenIsNotEmpty(callable $callback, callable|null $default = null) {
        return $this->when($this->isNotEmpty(), $callback, $default);
    }

    /**
     * When is empty
     *
     * @param bool|mixed $condition
     * @param callable $callback
     * @param callable|null $default
     * @return static|mixed
     */
    public function whenIsEmpty(callable $callback, callable|null $default = null) {
        return $this->when($this->isEmpty(), $callback, $default);
    }

    /**
     * Unless based on condition
     *
     * @param bool|mixed $condition
     * @param callable $callback
     * @param callable|null $default
     * @return static|mixed
     */
    public function unless($condition, callable $callback, callable|null $default = null) {
        return !$this->when($condition, $callback, $default);
    }

    /**
     * Unless is not empty
     *
     * @param bool|mixed $condition
     * @param callable $callback
     * @param callable|null $default
     * @return static|mixed
     */
    public function unlessIsNotEmpty(callable $callback, callable|null $default = null) {
        return $this->when($this->isEmpty(), $callback, $default);
    }

    /**
     * Unless is empty
     *
     * @param bool|mixed $condition
     * @param callable $callback
     * @param callable|null $default
     * @return static|mixed
     */
    public function unlessIsEmpty(callable $callback, callable|null $default = null) {
        return $this->when($this->isNotEmpty(), $callback, $default);
    }

    /**
     * Type check
     *
     * @param string|array|null $key
     * @param string $type_or_class
     * @return bool
     */
    public function typeCheck(string|array|null $key, string $type_or_class) : bool {
        $is_class = !in_array($type_or_class, ['boolean', 'integer', 'double', 'string', 'array', 'object', 'resource', 'NULL']);
        foreach ($this->data as $k => $v) {
            if ($key == null) {
                $value = $v;
            } else {
                $value = array_key_get($v, $key);
            }
            if ($is_class) {
                if (!is_a($value, $type_or_class)) {
                    return false;
                }
            } else if (gettype($value) !== $type_or_class) {
                return false;
            }
        }
        return true;
    }

    /**
     * Type cast
     *
     * @param string|array|null $key
     * @param string $type_or_class
     * @return \Array2
     */
    public function typeCast(string|array|null $key, string $type_or_class) : \Array2 {
        $is_class = !in_array($type_or_class, ['boolean', 'integer', 'double', 'string', 'array', 'object', 'resource', 'unset']);
        $result = [];
        foreach ($this->data as $k => $v) {
            if ($key == null) {
                $value = $v;
            } else {
                $value = array_key_get($v, $key);
            }
            if ($is_class) {
                if (!is_a($value, $type_or_class)) {
                    $destination = new $type_or_class();
                    object_cast($destination, (object) $value);
                    $value = $destination;
                }
            } else if (gettype($value) !== $type_or_class) {
                settype($value, $type_or_class);
            }
            if ($key == null) {
                $result[$k] = $value;
            } else {
                $value = array_key_set($v, $key, $value);
                $result[$k] = $value;
            }
        }
        return new static($result);
    }
}