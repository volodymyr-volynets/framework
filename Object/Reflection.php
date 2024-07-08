<?php

namespace Object;
class Reflection {

    /**
     * Get method code
     *
     * @param mixed $class
     * @param string $method
     */
    public static function getMethodCode($class, string $method) : string {
        $method_object = new \ReflectionMethod($class, $method);
        $start_line = $method_object->getStartLine();
        $end_line = $method_object->getEndLine() - 1;
        $length = $end_line - $start_line;
        $source = file_get_contents($method_object->getFileName());
        $source = preg_split('/' . PHP_EOL . '/', $source);
        return implode(PHP_EOL, array_slice($source, $start_line, $length));
    }

    /**
     * Get methods
     *
     * @param mixed $class
     * @param int $filter
     * @param array $prefixes
     * @return array
     */
    public static function getMethods($class, $filter = \ReflectionMethod::IS_PUBLIC, array $prefixes = []) : array {
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
}