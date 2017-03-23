<?php

class Mail {

	/**
	 * Send an email
	 *
	 * Usage example:
	 *
	 * 	$result = mail::send([
	 * 		'to' => 'test@localhost',
	 *		'cc' => 'cc@localhost',
	 *		'bcc' => 'bcc@localhost',
	 *		'subject' => 'test subject',
	 * 		'message' => 'test message',
	 * 		'attachments' => [
	 *	 		['path' => 'path to file', 'name' => 'test.txt'],
	 * 			['data' => '!!!data!!!', 'name' => 'test.txt', 'type' => 'plain/text']
	 * 		]
	 * 	]);
	 *
	 * @param array $options
	 */
	public static function send(array $options) : array {
		$result = [
			'success' => false,
			'error' => []
		];
		// mail delivery first
		$mail_delivery_class = Application::get('flag.global.mail.delivery.submodule', ['class' => 1]);
		if (empty($mail_delivery_class)) {
			Throw new Exception('You need to specify mail delivery submodule');
		}
		$mail_delivery_object = new $mail_delivery_class();
		$temp = $mail_delivery_object->send($options);
		if (!$temp['success']) {
			array_merge3($result['error'], $temp['error']);
		} else {
			$result['success'] = true;
		}
		return $result;
	}

	/**
	 * Send simple
	 *
	 * @param string $to
	 * @param string $subject
	 * @param string $message
	 */
	public static function sendSimple($to, string $subject, string $message) : array {
		return self::send(['to' => $to, 'subject' => $subject, 'message' => $message]);
	}
}