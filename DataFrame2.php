<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

class DataFrame2
{
    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @var array
     */
    protected array $header = [];

    /**
     * @var int
     */
    protected int $row_counter = 0;

    /**
     * Construct
     *
     * @param array|JsonSerializable|Traversable|string|Array2|DataFrame2 $data
     */
    public function __construct(array|JsonSerializable|Traversable|string|Array2|DataFrame2 $data)
    {
        $temp = $this->dataToArray($data);
        $this->convertArrayToDataFrame2($temp);
    }

    /**
     * Normalize
     *
     * @param array $columns
     * @param bool $erase_original
     * @return DataFrame2
     */
    public function normalize(array $columns, bool $erase_original = false): DataFrame2
    {
        $data = $this->toArray2()->toArray();
        foreach ($this->toArray2()->toArray() as $k => $v) {
            foreach ($columns as $c) {
                if (!isset($v[$c])) {
                    if ($erase_original) {
                        unset($data[$k][$c]);
                    }
                    continue;
                }
                if (is_json($v[$c])) {
                    $temp = json_decode($v[$c], true);
                } elseif (is_array($v[$c])) {
                    $temp = $v[$c];
                } else {
                    continue;
                }
                foreach ($temp as $k2 => $v2) {
                    $data[$k][$c . '.' . $k2] = $v2;
                }
                if ($erase_original) {
                    unset($data[$k][$c]);
                }
            }
        }
        return new static($data);
    }

    /**
     * Locate
     *
     * @param int|string|array|null $rows
     * @param int|string|array|null $columns
     * @return DataFrame2
     */
    public function locate(int|string|array|null $rows, int|string|array|null $columns): DataFrame2
    {
        // process rows
        $flag_all_rows = false;
        $row_values = [];
        $row_start_numeric = true;
        $row_end_numeric = true;
        if (is_int($rows)) {
            $row_start = 0;
            $row_end = $rows;
            $row_step = 1;
        } elseif (is_null($rows)) {
            $row_start = 0;
            $row_end = PHP_INT_MAX;
            $row_step = 1;
            $flag_all_rows = true;
        } elseif (is_string($rows)) {
            $row_temp = explode(':', $rows);
            if (is_numeric($row_temp[0])) {
                $row_start_numeric = true;
                $row_start = intval($row_temp[0]);
            } else {
                $row_start_numeric = false;
                $row_start = $row_temp[0];
            }
            if (is_numeric($row_temp[1])) {
                $row_end_numeric = true;
                $row_end = intval($row_temp[1]);
            } else {
                $row_end_numeric = false;
                $row_end = $row_temp[1];
            }
            // swap
            if ($row_start > $row_end) {
                $row_temp_value = $row_end;
                $row_end = $row_start;
                $row_start = $row_temp_value;
            }
            $row_step = intval($row_temp[2] ?? 1);
        } elseif (is_array($rows)) {
            $row_values = $rows;
        }
        // process columns
        $flag_all_columns = false;
        $column_values = [];
        $column_start_numeric = true;
        $column_end_numeric = true;
        if (is_int($columns)) {
            $column_start = 0;
            $column_end = $columns;
            $column_step = 1;
        } elseif (is_null($columns)) {
            $column_start = 0;
            $column_end = PHP_INT_MAX;
            $column_step = 1;
            $flag_all_columns = true;
        } elseif (is_string($columns)) {
            $row_temp = explode(':', $columns);
            if (is_numeric($row_temp[0])) {
                $column_start_numeric = true;
                $column_start = intval($row_temp[0]);
            } else {
                $column_start_numeric = false;
                $column_start = $row_temp[0];
            }
            if (is_numeric($row_temp[1])) {
                $column_end_numeric = true;
                $column_end = intval($row_temp[1]);
            } else {
                $column_end_numeric = false;
                $column_end = $row_temp[1];
            }
            // swap
            if ($column_start > $column_end) {
                $column_temp_value = $column_end;
                $column_end = $column_start;
                $column_start = $column_temp_value;
            }
            $column_step = intval($row_temp[2] ?? 1);
        } elseif (is_array($columns)) {
            $column_values = $columns;
        }
        // if we cloning
        if ($flag_all_rows && $flag_all_columns) {
            return new static($this);
        }
        // copy data
        $result = [];
        $row_index = 0;
        foreach ($this->data['__index'] as $k => $v) {
            $copy_row = false;
            if ($row_values) {
                foreach ($row_values as $v2) {
                    $row_values_numeric = is_int($v2);
                    if ($row_values_numeric && $v2 == $k) {
                        $copy_row = true;
                        break;
                    } elseif (!$row_values_numeric && $v2 == $v) {
                        $copy_row = true;
                        break;
                    }
                }
            } elseif ($row_start_numeric && $k >= $row_start && $k < $row_end && ($k % $row_step == 0)) {
                $copy_row = true;
            } elseif (!$row_start_numeric && $v >= $row_start && $v <= $row_end && ($k % $row_step == 0)) {
                $copy_row = true;
            }
            // if we are here means we need to copy
            if ($copy_row) {
                $result[$v] = [];
                // copy columns
                $column_index = 0;
                foreach ($this->header as $k2 => $v2) {
                    $column_copy = false;
                    if ($column_values) {
                        foreach ($column_values as $v3) {
                            $column_values_numeric = is_int($v3);
                            if ($column_values_numeric && $v3 == $v2) {
                                $column_copy = true;
                                break;
                            } elseif (!$column_values_numeric && $v3 == $k2) {
                                $column_copy = true;
                                break;
                            }
                        }
                    } elseif ($column_start_numeric && $column_index >= $column_start && $column_index < $column_end && ($column_index % $column_step == 0)) {
                        $column_copy = true;
                    } elseif (!$column_start_numeric && $k2 >= $column_start && $k2 <= $column_end && ($column_index % $column_step == 0)) {
                        $column_copy = true;
                    }
                    if ($column_copy) {
                        $result[$v][$k2] = $this->data[$k2][$row_index];
                    }
                    $column_index++;
                }
            }
            $row_index++;
        }
        return new static($result);
    }

    /**
     * Filter
     *
     * @param callable $callback
     * @return DataFrame2
     */
    public function filter(callable $callback): DataFrame2
    {
        return new static($this->toArray2()->filter($callback));
    }

    /**
     * Each
     *
     * @param callable $callback
     * @return DataFrame2
     */
    public function each(callable $callback): DataFrame2
    {
        $data = $this->toArray2()->toArray();
        foreach ($data as $k => $v) {
            $data[$k] = $callback($v, $k);
        }
        return new static($data);
    }

    /**
     * Head
     *
     * @param int $count
     * @return DataFrame2
     */
    public function head(int $count = 5): DataFrame2
    {
        return $this->locate('0:' . $count, null);
    }

    /**
     * Tail
     *
     * @param int $count
     * @return DataFrame2
     */
    public function tail(int $count = 5): DataFrame2
    {
        $data = $this->reverse();
        return $data->head($count);
    }

    /**
     * Print
     *
     * @param int $start
     * @param int $end
     * @param int $step
     * @param bool $return
     * @return string
     */
    public function print(int $start = 0, int $end = PHP_INT_MAX, int $step = 1, bool $return = false): string
    {
        $data = $this->locate($start . ':' . $end . ':' . $step, null);
        if ($return) {
            // todo replace with table
            return print_r2($data->toArray2()->toArray());
        } else {
            // todo replace with table
            print_r2($data->toArray2()->toArray());
            return '';
        }
    }

    /**
     * Reverse
     *
     * @return DataFrame2
     */
    public function reverse(): DataFrame2
    {
        $data = $this->toArray2()->toArray();
        return new static(array_reverse($data, true));
    }

    /**
     * Count
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->data['__index']);
    }

    /**
     * Serie
     *
     * @param string $col
     * @return mixed|string[]
     */
    public function serie(string $col, bool $assoc = true): array
    {
        if ($this->count() == 0) {
            return [];
        } elseif (!$assoc) {
            return array_values($this->data[$col]);
        } elseif ($assoc) {
            return array_combine(array_values($this->data['__index']), array_values($this->data[$col]));
        }
    }

    /**
     * Data to array
     *
     * @param array|JsonSerializable|Traversable|string|Array2|DataFrame2 $data
     * @throws Exception
     */
    protected function dataToArray(array|JsonSerializable|Traversable|string|Array2|DataFrame2 $data): array
    {
        if ($data instanceof DataFrame2) {
            return $data->toArray();
        } elseif ($data instanceof Array2) {
            return $data->toArray();
        } elseif (is_array($data)) {
            return $data;
        } elseif ($data instanceof JsonSerializable) {
            return (array) $data->jsonSerialize();
        } elseif ($data instanceof Traversable) {
            return iterator_to_array($data);
        } elseif (is_json($data)) {
            return json_decode($data, true);
        } else {
            throw new Exception('Unknown data.');
        }
    }

    /**
     * Convert array to DataFrame2
     *
     * @param array $data
     * @return void
     */
    protected function convertArrayToDataFrame2(array $data): void
    {
        $this->data = [];
        $this->header = [];
        $this->row_counter = 0;
        $index = 0;
        foreach ($data as $k => $v) {
            foreach ($v ?? [] as $k2 => $v2) {
                if (!isset($this->header[$k2])) {
                    $this->header[$k2] = $index;
                    $index++;
                }
            }
        }
        $row_index = 0;
        foreach ($data as $k => $v) {
            $this->data['__index'][$row_index] = (string) $k;
            foreach ($this->header as $k2 => $v2) {
                $this->data[$k2][$row_index] = $v[$k2] ?? null;
            }
            $row_index++;
        }
        $serie = current($this->data);
        $this->row_counter = count($serie ? $serie : []);
    }

    /**
     * To HTML
     *
     * @return string
     */
    public function toHTML(): string
    {
        $data = [];
        foreach ($this->data as $k => $v) {
            foreach ($v as $k2 => $v2) {
                $data[$k2][$k] = $v2;
            }
        }
        return HTML::table([
            'header' => array_combine(array_values($this->header), array_keys($this->header)),
            'options' => $data,
            'show_zero_rows' => $this->row_counter == 0,
            'show_row_number' => true
        ]);
    }

    /**
     * To JSON
     *
     * @param bool $assoc
     * @return string
     */
    public function toJSONString(bool $assoc = true): string
    {
        if ($assoc) {
            return $this->toArray2()->toJson();
        } else {
            return json_encode($this->toArray());
        }
    }

    /**
     * To JSON file
     *
     * @param string $filename
     * @param bool $assoc
     * @return bool
     */
    public function toJSONFile(string $filename, bool $assoc = true): bool
    {
        return file_put_contents($filename, $this->toJSONString($assoc)) !== false;
    }

    /**
     * To array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * To Array2
     *
     * @return Array2
     */
    public function toArray2(): Array2
    {
        $data = [];
        foreach ($this->data as $k => $v) {
            foreach ($v as $k2 => $v2) {
                if (!isset($this->header[$k])) {
                    continue;
                }
                $data[$this->data['__index'][$k2]][$k] = $v2;
            }
        }
        return \Array2($data);
    }

    /**
     * Read CSV file
     *
     * @param string $filename
     * @param array $options
     *      string separator
     *      string enclosure
     *      string escape
     *      bool has_header
     * @return DataFrame2
     */
    public static function readCSVFile(string $filename, array $options = []): DataFrame2
    {
        $options['separator'] ??= ',';
        $options['enclosure'] ??= '"';
        $options['escape'] ??= '\\';
        $options['has_header'] ??= false;
        $row_number = 0;
        $header = [];
        $result = [];
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($data = fgetcsv($handle, 5000, $options['separator'], $options['enclosure'], $options['escape'])) !== false) {
                // read header
                if ($options['has_header'] && $row_number == 0) {
                    $header = $data;
                    $row_number++;
                    continue;
                }
                if ($options['has_header']) {
                    $result[] = array_combine($header, $data);
                } else {
                    $result[] = $data;
                }
                $row_number++;
            }
            fclose($handle);
        }
        return new self($result);
    }

    /**
     * Read CSV string
     *
     * @param string $filename
     * @param array $options
     * @return DataFrame2
     */
    public static function readCSVString(string $csv, array $options = []): DataFrame2
    {
        $temp_name = tempnam(sys_get_temp_dir(), 'readCSVString');
        file_put_contents($temp_name, $csv);
        return self::readCSVFile($temp_name, $options);
    }

    /**
     * Process table arrays
     *
     * @param mixed $data
     * @param mixed $index
     * @return DataFrame2|DataFrame2[]
     */
    protected static function processTableArrays($data, $index): DataFrame2|array
    {
        if ($index == -1) {
            $result = [];
            foreach ($data as $v) {
                $result[] = new static($v);
            }
            return $result;
        } else {
            return new static($data);
        }
    }

    /**
     * Read HTML file
     *
     * @param string $filename
     * @param int $index
     * @param array $options
     * @return DataFrame2|DataFrame2[]
     */
    public static function readHTMLFile(string $filename, int $index, array $options = []): DataFrame2|array
    {
        $table = self::htmlTablesToArray($filename, $index);
        return self::processTableArrays($table, $index);
    }

    /**
     * Read HTML string
     *
     * @param string $html
     * @param int $index
     * @param array $options
     * @return DataFrame2|DataFrame2[]
     */
    public static function readHTMLString(string $html, int $index, array $options = []): DataFrame2|array
    {
        $table = self::htmlTablesToArray($html, $index, ['as_html_string' => true]);
        return self::processTableArrays($table, $index);
    }

    /**
     * Read XML file
     *
     * @param string $filename
     * @param int $index
     * @param array $options
     * @return DataFrame2|DataFrame2[]
     */
    public static function readXMLFile(string $filename, int $index, array $options = []): DataFrame2|array
    {
        $table = self::htmlTablesToArray($filename, $index, ['as_xml_file' => true]);
        return self::processTableArrays($table, $index);
    }

    /**
     * Read XML string
     *
     * @param string $xml
     * @param int $index
     * @param array $options
     * @return DataFrame2|DataFrame2[]
     */
    public static function readXMLString(string $xml, int $index, array $options = []): DataFrame2|array
    {
        $table = self::htmlTablesToArray($xml, $index, ['as_xml_string' => true]);
        print_r2($table);
        return self::processTableArrays($table, $index);
    }

    /**
     * HTML tables to array
     *
     * @param string $filename
     * @param int $index
     * @param array $options
     * @return array
     */
    protected static function htmlTablesToArray(string $filename, int $index = -1, array $options = []): array
    {
        $dom_document = new DOMDocument();
        if (!empty($options['as_html_string'])) {
            @$dom_document->loadHTML($filename);
        } elseif (!empty($options['as_xml_string'])) {
            @$dom_document->loadXML($filename);
        } elseif (!empty($options['as_xml_file'])) {
            @$dom_document->load($filename);
        } else {
            @$dom_document->loadHTMLFile($filename);
        }
        $dom_document->preserveWhiteSpace = false;
        $table_counter = 0;
        $result = [];
        $tables = $dom_document->getElementsByTagName('table');
        foreach ($tables as $table) {
            $result[$table_counter] = [];
            $rows = $table->getElementsByTagName('tr');
            $row_counter = 0;
            foreach ($rows as $row) {
                if (strlen($row->nodeValue) > 0) {
                    $result[$table_counter][$row_counter] = [];
                    if ($row->childNodes) {
                        foreach ($row->childNodes as $child) {
                            // we can have both td and th
                            if (!in_array(strtolower($child->nodeName), ['td', 'th'])) {
                                continue;
                            }
                            $result[$table_counter][$row_counter][] = $child->nodeValue;
                        }
                    }
                    $row_counter++;
                }
            }
            if ($row_counter > 1) {
                $table_counter++;
            }
        }
        if ($index == -1) {
            return $result;
        }
        return $result[$index] ?? [];
    }
}
