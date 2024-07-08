<?php

namespace Object\Traits;
trait Debugable {

	/**
	 * To debugs
	 *
	 * @return array
	 */
	public function __debugInfo() {
		$result = [];
		// preload aliases
		$alias_object = new \Object\Data\Aliases();
		$alias_data = $alias_object->get();
        foreach ((new \ReflectionClass($this))->getProperties() as $property) {
            $name = $property->getName();
			$value = $property->getValue($this);
			$comment = $property->getDocComment();
			if ($property->isPrivate()) {
				$key = ['private', $name];
            } else if ($property->isProtected()) {
				$key = ['protected', $name];
            } else {
				$key = ['public', $name];
			}
			$key2 = [];
			if (strpos($name, 'object_') !== false) {
				$key = ['system', $name];
			} else if (strpos($comment, '(Generated)') !== false) {
				$key2 = ['generated', $name];
			}
			if ($property->isStatic()) {
				$key[1] = '(static)' . $key[1];
			}
			// key to value
			if ($value && preg_match('/{options_model{(.*?)}}/', $comment, $matches)) {
				$model = \Factory::model($matches[1], true);
				$value.= ' => ' . $model->loadById($value, [
					'column' => $model->column_prefix . 'name',
					'cache_memory' => true,
				]);
			} else if ($value && preg_match('/{domain{(.*?)}}/', $comment, $matches)) {
				if (str_ends_with($matches[1], '_sequence')) {
					$matches[1] = str_replace('_sequence', '', $matches[1]);
				}
				if (isset($alias_data[$matches[1]])) {
					$model = \Factory::model($alias_data[$matches[1]]['no_data_alias_model'], true);
					$value.= ' => ' . $model->loadById($value, [
						'column' => $model->column_prefix . 'name',
						'cache_memory' => true,
					]);
				}
			}
            // actions
			$temp = [];
			foreach (ACTION_ALL as $v) {
				if (strpos($comment, $v) !== false) {
					$temp[] = $v;
				}
			}
			if ($temp) {
				$value.= ' => [' . implode(', ', $temp) . ']';
			}
			// for objects we only display clss name
            if (is_object($value)) {
                $value  = '(object): ' . get_class($value);
            }
			if ($key2) {
				array_key_set($result, $key2, $value);
			}
			if (strpos($comment, '(Non Database)') === false) {
				array_key_set($result, $key, $value);
			}
        }
        return $result;
    }
}