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

use Object\Table\Options;

class SQLObject extends Options
{
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
     * Path
     *
     * @var string
     */
    public $path;

    /**
     * SQL
     *
     * @var string
     */
    public $sql;

    /**
     * Constructing object
     *
     * @param array $options
     *		skip_db_object
     * @throws \Exception
     */
    public function __construct($options = [])
    {
        $this->options = $options;
        // we need to handle overrides
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
        // initialize db object
        if (empty($options['skip_db_object'])) {
            $this->db_object = new \Db($this->db_link);
        }
    }

    /**
     * Parse SQL File (Static)
     *
     * @param string $path
     * @param array $options
     * @return string
     */
    public static function parseSQLFileStatic(string $path, array $options = []): string
    {
        unset($options['__is_path_content']);
        return \Template::renderStatic(\Template::SQL, $path, $options);
    }

    /**
     * Parse SQL Statement (Static)
     *
     * @param string $sql
     * @param array $options
     * @return string
     */
    public static function parseSQLStatementStatic(string $sql, array $options = []): string
    {
        $options['__is_path_content'] = true;
        return \Template::renderStatic(\Template::SQL, $sql, $options);
    }

    /**
     * Query
     *
     * @param string $path_or_sql
     * @param string|array|null $key
     * @param array $options
     * @return array
     */
    public function query(string $path_or_sql, string|array|null $key = null, array $options = []): array
    {
        if (str_ends_with($path_or_sql, '.object.sql') || str_ends_with($path_or_sql, '.template.sql')) {
            $result['sql'] = self::parseSQLFileStatic($path_or_sql, $options);
        } else {
            $result['sql'] = self::parseSQLStatementStatic($path_or_sql, $options);
        }
        /** @var \Db */
        return $this->db_object->query($result['sql'], $key, $options);
    }

    /**
     * Query (static)
     *
     * @param string|null $db_link
     * @param string $path_or_sql
     * @param string|array|null $key
     * @param array $options
     */
    public static function queryStatic(string|null $db_link, string $path_or_sql, string|array|null $key = null, array $options = []): array
    {
        $object = new self([
            'db_link' => $db_link,
        ]);
        return $object->query($path_or_sql, $key, $options);
    }

    /**
     * Execute
     *
     * @param string|array|null $key
     * @param array $options
     * @return array
     */
    public function execute(string|array|null $key = null, array $options = []): array
    {
        $sql = $this->sql ?? $this->path;
        if (empty($sql)) {
            throw new \Exception('SQLObject: you must set path or SQL!');
        }
        return $this->query($sql, $key, $options);
    }

    /**
     * Validate
     *
     * @param array $rules
     * @param array $options
     * @throws \Exception
     * @return array
     */
    public static function validate(array $rules, array $options): array
    {
        $validator = \Validator::validateInputStatic($options, $rules);
        if ($validator->hasErrors()) {
            $temp = $validator->errors('result');
            throw new \Exception(implode("\n", $temp['error']));
        }
        return $validator->values();
    }
}
