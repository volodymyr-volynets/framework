<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

class Db
{
    /**
     * Db link
     *
     * @var string
     */
    public $db_link;

    /**
     * Database object
     *
     * @var object
     */
    public $object;

    /**
     * Backend
     *
     * @var string
     */
    public $backend;

    /**
     * Options
     *		cache_link
     *		crypt_link
     *
     * @var array
     */
    public $options = [];

    /**
     * Flag database loaded
     *
     * @var boolen
     */
    public static $flag_db_loaded = false;

    /**
     * Constructing database object
     *
     * @param string $db_link
     * @param string $class
     * @param array $options
     */
    public function __construct($db_link = null, $class = null, $options = [])
    {
        // if we need to use default link from application
        if (empty($db_link)) {
            $db_link = Application::get('flag.global.default_db_link');
            if (empty($db_link)) {
                throw new Exception('You must specify database link and/or class!');
            }
        }
        $this->db_link = $db_link;
        // get object from factory
        $temp = Factory::get(['db', $db_link]);
        // if we have class
        if (!empty($class) && !empty($db_link)) {
            // check if backend has been enabled
            if (!Application::get($class, ['submodule_exists' => true])) {
                throw new Exception('You must enable ' . $class . ' first!');
            }
            // if we are replacing database connection with the same link we
            // need to manually close database connection
            if (!empty($temp['object'])) {
                $object = $temp['object'];
                $object->close();
                unset($this->object);
            }
            // creating new class
            $this->object = new $class($db_link, $options);
            // determining ddl class & object
            $ddl_class = str_replace('\\Base\\Abc123', '\\DDL', $class . '\\Abc123');
            $ddl_object = new $ddl_class();
            // backend
            $this->backend = $this->object->backend;
            // putting every thing into factory
            Factory::set(['db', $db_link], [
                'object' => $this->object,
                'class' => $class,
                'backend' => $this->backend,
                'ddl_class' => $ddl_class,
                'ddl_object' => $ddl_object
            ]);
            // set options without credentials
            $this->options = $options;
            self::$flag_db_loaded = true;
        } elseif (!empty($temp['object'])) {
            $this->object = & $temp['object'];
            $this->backend = $temp['backend'];
        } else {
            throw new Exception('You must specify database link and/or class!');
        }
    }

    /**
     * Open database connection
     *
     * @param array $options
     * @return array
     */
    public function connect($options)
    {
        return $this->object->connect($options);
    }

    /**
     * Close database connection
     */
    public function close()
    {
        return $this->object->close();
    }

    /**
     * Query database
     *
     * @param string $sql
     * @param mixed $key
     * @param array $options
     * @return array
     */
    public function query($sql, $key = null, $options = [])
    {
        return $this->object->query($sql, $key, $options);
    }

    /**
     * Begin transaction
     *
     * @return array
     */
    public function begin()
    {
        return $this->object->begin();
    }

    /**
     * Commit transaction
     *
     * @return array
     */
    public function commit()
    {
        return $this->object->commit();
    }

    /**
     * Roll transaction back
     *
     * @return array
     */
    public function rollback()
    {
        return $this->object->rollback();
    }

    /**
     * In transaction
     *
     * @return bool
     */
    public function inTransaction()
    {
        return $this->object->inTransaction();
    }

    /**
     * Escape value
     *
     * @param mixed $value
     * @return string
     */
    public function escape($value)
    {
        return $this->object->escape($value);
    }

    /**
     * Escape array of string
     *
     * @param array $value
     * @return array
     */
    public function escapeArray($value)
    {
        return $this->object->escape_array($value);
    }

    /**
     * Save row
     *
     * @param string $table
     * @param array $data
     * @param mixed $keys
     * @return array
     */
    public function save($table, $data, $keys, $options = [])
    {
        return $this->object->save($table, $data, $keys, $options);
    }

    /**
     * Insert row(s) into table
     *
     * @param string $table
     * @param array $rows
     * @param mixed $keys
     * @return array
     */
    public function insert($table, $rows, $keys = null, $options = [])
    {
        return $this->object->insert($table, $rows, $keys, $options);
    }

    /**
     * Update table rows
     *
     * @param string $table
     * @param array $data
     * @param mixed $keys
     * @param array $options
     * @return array
     */
    public function update($table, $data, $keys, $options = [])
    {
        return $this->object->update($table, $data, $keys, $options);
    }

    /**
     * Delete rows from table
     *
     * @param string $table
     * @param array $data
     * @param mixed $keys
     * @param array $options
     * @return array
     */
    public function delete($table, $data, $keys, $options = [])
    {
        return $this->object->delete($table, $data, $keys, $options);
    }

    /**
     * Generate sequence
     *
     * @param string $sequence_name
     * @param string $type - nextval or currval
     * @param int $tenant
     * @param int $module
     * @return array
     */
    public function sequence($sequence_name, $type = 'nextval', $tenant = null, $module = null)
    {
        return $this->object->sequence($sequence_name, $type, $tenant, $module);
    }

    /**
     * Other methods inherited from base
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->object, $name], $arguments);
    }

    /**
     * Connect to servers
     *
     * @param string $db_link
     * @param array $db_settings
     * @return array
     */
    public static function connectToServers(string $db_link, array $db_settings): array
    {
        $result = [
            'success' => false,
            'error' => []
        ];
        // load application structure
        $application_structure = Application::get('application.structure');
        $db_options = $db_settings;
        unset($db_options['servers']);
        // loop through available servers
        foreach ($db_settings['servers'] as $db_server) {
            $db_object = new Db($db_link, $db_settings['submodule'], $db_settings);
            // application structure provides more data
            if (!empty($application_structure) && isset($application_structure['settings']['db'][$db_link])) {
                foreach ($application_structure['settings']['db'][$db_link] as $k => $v) {
                    if (!empty($db_server[$k]) && $db_server[$k] != $v) {
                        $db_server['__original_' . $k] = $db_server[$k];
                    }
                    $db_server[$k] = $v;
                }
            }
            // fix database issue for multi database configurations
            if (empty($db_server['dbname'])) {
                $db_server['dbname'] = Application::get("db.{$db_link}_schema.dbname");
            }
            // try to connect
            $db_status = $db_object->connect($db_server);
            if ($db_status['success']) {
                $result['success'] = true;
                return $result;
            }
        }
        $result['error'][] = 'Unable to open db connection!';
        return $result;
    }

    /**
     * Transaction
     *
     * @param string $db_link
     * @param callable $callback
     * @return array
     */
    public function transaction(string $db_link, callable $callback): array
    {
        $this->begin();
        $result = $callback($db_link, $this);
        if ($result === false) {
            return $this->rollback();
        }
        return $this->commit();
    }

    /**
     * Transaction static
     *
     * @param string $db_link
     * @param callable $callback
     * @return array
     */
    public function transactionStatic(string $db_link, callable $callback): array
    {
        $db_object = new static($db_link);
        return $db_object->transaction($db_link, $callback);
    }

    /**
     * UUID4
     *
     * @return string
     */
    public static function uuid4(): string
    {
        $bytes = bin2hex(random_bytes(16));
        return sprintf(
            "%s-%s-%s-%s-%s",
            substr($bytes, 0, 8),
            substr($bytes, 8, 4),
            substr($bytes, 12, 4),
            substr($bytes, 16, 4),
            substr($bytes, 20)
        );
    }

    /**
     * UUID for tenant
     *
     * @param int|null $tenant_id
     * 		0 - all tenants
     * 		null - current tenant
     * @param ?string $ip
     * @return string
     */
    public static function uuidTenanted(?int $tenant_id = null, ?string $ip = null): string
    {
        if ($tenant_id === null) {
            $tenant_id = Tenant::id() ?? 0;
        }
        if (!$ip) {
            $ip = Request::ip();
        }
        $microtime = explode(" ", microtime());
        return sprintf(
            '%04x-%08s-%08s-%04s-%04x%04x',
            $tenant_id,
            dechex(ip2long($ip)),
            substr("00000000" . dechex($microtime[1]), -8),
            substr("0000" . dechex(round($microtime[0] * 65536)), -4),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * UUID for tenant (Decode)
     *
     * @param string $uuid
     * @return array
     */
    public static function uuidTenantedDecode(string $uuid): array
    {
        $result = [];
        $parts = explode('-', $uuid);
        if (is_array($parts) && count($parts) == 5) {
            $result = [
                'tenant_id' => (int) $parts[0],
                'ip' => long2ip(hexdec($parts[1])),
                'unixtime' => hexdec($parts[2]),
                'micro' => hexdec($parts[3]) / 65536,
                'random' => ($parts[4]),
            ];
        }
        return $result;
    }
}
