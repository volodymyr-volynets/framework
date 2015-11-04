<?php

class numbers_framework_controller_error {

	/**
	 * This would draw captcha in png image format
	 */
	public function action_index() {
		$input = request::input();
		if (!empty($input['token'])) {
			$crypt = new crypt();
			$token_data = $crypt->token_validate($input['token'], 1, true);
			if (!($token_data === false || $token_data['id'] !== 'general')) {
				$input['data'] = json_decode($input['data'], true);
				error::error_handler('javascript', $input['data']['message'], $input['data']['file'], $input['data']['line']);
			}
		}
		layout::$non_html_output = true;
		header("Content-Type: image/png");
		echo file_get_contents(__DIR__ . '/error.png');
		exit;
	}
}