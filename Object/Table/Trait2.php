<?php

namespace Object\Table;
trait Trait2 {

	/**
	 * Query builder
	 *
	 * @param array $options
	 *		string alias, default a
	 *		boolean skip_tenant
	 *		boolean skip_acl
	 * @return \Object\Query\Builder
	 */
	public function queryBuilder(array $options = []) : \Object\Query\Builder {
		$model = $this;
		// alias
		$alias = $options['alias'] ?? 'a';
		unset($options['alias']);
		// set tenant parameter
		if ($model->tenant && empty($options['skip_tenant'])) {
			$options['tenant'] = true;
		}
		// we must set pk
		$options['primary_key'] = $model->pk ?? null;
		$object = new \Object\Query\Builder($model->db_link, $options);
		$object->from($model, $alias);
		// inject tenant into the query
		if ($model->tenant && empty($options['skip_tenant'])) {
			$object->where('AND', [$alias . '.' . $model->column_prefix . 'tenant_id', '=', \Tenant::id()]);
			$object->where('AND', [$model->column_prefix . 'tenant_id', '=', \Tenant::id()], false, ['for_delete' => true]);
		}
		// registered ALC
		if (empty($options['skip_acl'])) {
			\Object\ACL\Registered::process('\\' . get_called_class(), $object, [
				'initiator' => $options['initiator'] ?? null,
				'existing_values' => $options['existing_values'] ?? null
			]);
		}
		return $object;
	}

	/**
	 * Query builder (static)
	 *
	 * @param array $options
	 *		string alias, default a
	 *		boolean skip_tenant
	 *		boolean skip_acl
	 * @return \Object\Query\Builder
	 */
	public static function queryBuilderStatic(array $options = []) : \Object\Query\Builder {
		$class = get_called_class();
		$model = new $class();
		return $model->queryBuilder($options);
	}
}