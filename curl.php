<?php 

class curl {

	/**
	 * Post
	 *
	 * @param string $url
	 * @param array $fields
	 * @param array $options
	 *		format - json, default is html
	 * @return type
	 */
	public static function post($url, $fields, $options = []) {
		$result = [
			'success' => false,
			'error' => [],
			'data' => null
		];
		$options['format'] = $options['format'] ?? 'html';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// todo: handle HTTPs requests
		$server_output = curl_exec($ch);
		if ($server_output !== false) {
			if ($options['format'] == 'json') {
				$result['data'] = json_decode($server_output, true);
			} else {
				$result['data'] = $server_output;
			}
			$result['success'] = true;
		} else {
			$result['error'][] = curl_error($ch);
		}
		curl_close($ch);
		return $result;
	}

	// todo: refactor here
	public static function multi_get_urls(& $urls) {

		if(count($urls)<=0) return false;

		$harr = array();//handle array

		foreach ($urls as $k=>$v){
			$h = curl_init();
			curl_setopt($h,CURLOPT_URL, $v['url']);
			curl_setopt($h,CURLOPT_HEADER, 0);
			curl_setopt($h,CURLOPT_RETURNTRANSFER, 1);
			array_push($harr, $h);
		}

		$mh = curl_multi_init();
		foreach ($harr as $k=>$v) curl_multi_add_handle($mh, $v);

		$running = null;
		do {
			curl_multi_exec($mh,$running);
		} while ($running > 0);

		// get the result and save it in the result ARRAY
		foreach($harr as $k => $h) {
			$urls[$k]['data'] = curl_multi_getcontent($h);
		}

		// close all the connections
		foreach($harr as $k => $v){
			curl_multi_remove_handle($mh, $v);
		}
		curl_multi_close($mh);
		return true;
	} 
}