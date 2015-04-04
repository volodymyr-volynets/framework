<?php

class lock {
	
	/**
	 * Create lock
	 * 
	 * @param string $id
	 * @param string $data
	 * @return boolean
	 */
	public static function create($id, $data = null) {
		if (empty($data)) $data = format::now();
		$temp_dir = application::get(array('directory','temp'));
		if (isset($temp_dir['dir'])) {
			return (file_put_contents($temp_dir['dir'] . '__lock_' . $id, $data)===false ? false : true);
		}
		return false;
	}
	
	/**
	 * Check if lock exists
	 * 
	 * @param string $id
	 * @return boolean
	 */
	public static function exists($id) {
		$temp_dir = application::get(array('directory','temp'));
		if (isset($temp_dir['dir'])) {
			return @file_get_contents($temp_dir['dir'] . '__lock_' . $id);
		}
		return false;
	}
	
	/**
	 * Release the lock
	 * 
	 * @param string $id
	 * @return boolean
	 */
	public static function release($id) {
		$temp_dir = application::get(array('directory','temp'));
		if (isset($temp_dir['dir'])) {
			return unlink($temp_dir['dir'] . '__lock_' . $id);
		}
		return true;
	}
	
	/**
	 * Process the lock
	 * @param string $id
	 * @return boolean
	 */
	public static function process($id) {
		$lock_data = lock::exists($id);
		if ($lock_data!==false) {
			$minutes = round(abs(strtotime(format::now()) - strtotime($lock_data)) / 60, 2);
			if ($minutes > 30) {
				lock::release($id);
				$lock_data = false;
			}
		}
			
		// we are ok to proceed
		if ($lock_data===false) {
			lock::create($id);
			return true;
		} else {
			return false;
		}
	}
}