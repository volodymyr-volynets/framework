<?php

class system {
	
	// self check
	public static function check($check = array()) {
		$result = array(
			'success' => false,
			'error' => array()
		);
		if (empty($check)) {
			$check = application::get(array('syscheck'));
		}
		do {
			if (empty($check['enabled'])) break;
			// php version
			if (!empty($check['php']['version'])) {
				if (version_compare(PHP_VERSION, $check['php']['version']) < 0) $result['error'][] = 'PHP Version required ' . $check['php']['version'] . ' Have ' . PHP_VERSION;
			}
			// php extensions check
			if (!empty($check['php']['extensions'])) {
				$ext_must = array_map('strtolower', explode(' ', $check['php']['extensions']));
				$ext_have = array_map('strtolower', get_loaded_extensions());
				$ext_missing = array();
				foreach ($ext_must as $ext) if (!in_array($ext, $ext_have)) $ext_missing[] = $ext;
				if (!empty($ext_missing)) {
					$result['error'][] = 'PHP Extensions are missing: "' . implode(', ', $ext_missing) . '" Must be "' . implode(', ', $ext_must) . '" Have "' . implode(', ', $ext_have) . '"';
				}
			}
			// apache version
			if (!empty($check['apache']['version'])) {
				$version = apache_get_version();
				$version_compare = explode('/', $version);
				$version_compare = $version_compare[1];
				$version_compare = explode(' ', $version_compare);
				$version_compare = $version_compare[0];
				if (version_compare($version_compare, $check['apache']['version']) < 0) $result['error'][] = 'Apache Version required ' . $check['apache']['version'] . ' Have ' . $version;
			}
			// apache modules check
			if (!empty($check['apache']['modules'])) {
				$ext_must = array_map('strtolower', explode(' ', $check['apache']['modules']));
				$ext_have = array_map('strtolower', apache_get_modules());
				$ext_missing = array();
				foreach ($ext_must as $ext) if (!in_array($ext, $ext_have)) $ext_missing[] = $ext;
				if (!empty($ext_missing)) {
					$result['error'][] = 'Apache Modules are different: "' . implode(', ', $ext_missing) . '" Must be "' . implode(', ', $ext_must) . '" Have "' . implode(', ', $ext_have) . '"';;
				}
			}
			if (empty($result['error'])) $result['success'] = true;
		} while(0);
		return $result;
	}	
}