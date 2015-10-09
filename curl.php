<?php 

class curl {

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