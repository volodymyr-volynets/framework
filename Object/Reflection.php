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

use Helper\Constant\HTTPConstants;

class Reflection
{
    /**
     * Get method code
     *
     * @param mixed $class
     * @param string $method
     */
    public static function getMethodCode($class, string $method): string
    {
        $method_object = new \ReflectionMethod($class, $method);
        $start_line = $method_object->getStartLine() + 1;
        $end_line = $method_object->getEndLine() - 1;
        $length = $end_line - $start_line;
        $source = file_get_contents($method_object->getFileName());
        $source = preg_split('/' . PHP_EOL . '/', $source);
        return implode(PHP_EOL, array_slice($source, $start_line, $length));
    }

    /**
     * Get properties
     *
     * @param $class
     * @param string|null $property
     * @return mixed
     */
    public static function getProperties($class, ?string $property = null): mixed
    {
        $reflect = new \ReflectionClass($class);
        $properties = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC);
        $object = \Factory::model($class, true, [['skip_constructor_loading' => true]]);
        $result = [];
        foreach ($properties as $v) {
            $result[$v->getName()] = [
                'name' => $v->getName(),
                'value' => $v->getValue($object),
                'default' => $v->getDefaultValue(),
            ];
        }
        if ($property) {
            return $result[$property];
        }
        return $result;
    }

    /**
     * Get methods
     *
     * @param mixed $class
     * @param int $filter
     * @param array $prefixes
     * @return array
     */
    public static function getMethods($class, $filter = \ReflectionMethod::IS_PUBLIC, array $prefixes = []): array
    {
        $method_object = new \ReflectionClass($class);
        $methods = $method_object->getMethods($filter);
        $result = [];
        if (!empty($prefixes)) {
            foreach ($methods as $v) {
                foreach ($prefixes as $v2) {
                    if (str_starts_with($v->name, $v2)) {
                        $name_no_prefix = str_replace($v2, '', $v->name);
                        $name_split = preg_replace("([A-Z])", " $0", $name_no_prefix);
                        $name_split = explode(' ', trim($name_split));
                        $result[$v2][$v->name] = [
                            'name' => $v->name,
                            'name_no_prefix' => $name_no_prefix,
                            'name_split' => $name_split,
                            'name_underscore' => implode('_', $name_split),
                            'name_nice' => implode(' ', $name_split),
                        ];
                    }
                }
            }
        } else {
            foreach ($methods as $v) {
                $name_split = preg_replace("([A-Z])", " $0", $v->name);
                $name_split = explode(' ', trim($name_split));
                $result[$v->name] = [
                    'name' => $v->name,
                    'name_no_prefix' => $v->name,
                    'name_split' => $name_split,
                    'name_underscore' => implode('_', $name_split),
                    'name_nice' => implode(' ', $name_split),
                ];
            }
        }
        return $result;
    }

    /**
     * Get method parameters
     *
     * @param mixed $class
     * @param string $method
     * @return array
     */
    public static function getMethodParameters(mixed $class, string $method): array
    {
        $result = [];
        $reflection = new \ReflectionMethod($class, $method);
        foreach ($reflection->getParameters() as $v) {
            $attributes = array_map(fn ($attribute) => $attribute->getName(), $v->getAttributes());
            $type = $v->getType()->__toString();
            $is_nullable = strpos($type, '?') !== false;
            if ($is_nullable) {
                $type = str_replace('?', '', $type);
            }
            $types = explode('|', $type);
            $is_scalar = in_array($type, ['int', 'float', 'string', 'bool']);
            foreach ($types as $k2 => $v2) {
                if ($v2 == 'null') {
                    $is_nullable = true;
                    unset($types[$k2]);
                }
            }
            $result[$v->getName()] = [
                'name' => $v->getName(),
                'type' => $type,
                'types' => array_values($types),
                'is_typed' => $v->hasType(),
                'is_nullable' => $is_nullable,
                'is_scalar' => $is_scalar,
                'is_optional' => $v->isOptional(),
                'default' => $v->isOptional() ? $v->getDefaultValue() : null,
                'attributes' => $attributes,
            ];
        }
        return $result;
    }

    /**
     * Dependency injection parameters
     *
     * @param mixed $class
     * @param string $method
     * @param array $values - values from Validator object
     * @return array
     */
    public static function dependencyInjectionParameters(mixed $class, string $method, array $values = []): array
    {
        $result = [
            'success' => false,
            'error' => [],
            'http_status_code' => HTTPConstants::Status200OK,
            'data' => []
        ];
        $parameters =  self::getMethodParameters($class, $method);
        foreach ($parameters as $k => $v) {
            if ($k == 'input' && $v['type'] == 'array') {
                $result['data'][] = \Application::$request->get();
            } elseif ($v['is_scalar'] && in_array('Request', $v['attributes'])) {
                // first we load processed values from columns
                if (array_key_exists($k, $values)) {
                    $temp = $values[$k];
                } else {
                    $temp = \Request::input($k);
                }
                settype($temp, $v['type']);
                $result['data'][] = $temp;
            } elseif (!$v['is_scalar']) {
                // for request and response we provide static variables
                if ($v['type'] == \Request::class) {
                    $result['data'][] = \Application::$request;
                } elseif ($v['type'] == \Response::class) {
                    $result['data'][] = \Application::$response;
                } else {
                    // other objects
                    $result['data'][] = new $v['type']();
                }
            } else {
                $result['data'][] = null;
            }
        }
        $result['success'] = true;
        return $result;
    }

    /**
     * Get model name
     *
     * @param string $class
     * @return string
     */
    public static function getModelName(string $class): string
    {
        $result = explode('\\Model\\', $class);
        return trim(str_replace('\\', ' ', $result[1]));
    }

    /**
     * Get class constants
     *
     * @param string $class
     * @return array
     */
    public static function getConstants(string $class): array
    {
        $reflect = new \ReflectionClass($class);
        return $reflect->getConstants();
    }
}
