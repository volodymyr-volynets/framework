<?php

class sendmail {
	
	/**
	 * Send an email
	 * 
	 * Usage example:
	 * 
	 * 	$m = sendmail::send(array(
	 * 		'to' => 'test@localhost',
	 *		'subject' => 'test subject',
	 * 		'message' => 'test message',
	 * 		'attachments' => array(
	 *	 		array('filepath'=>'path to file', 'filename'=>'test.txt'),
	 * 			array('filedata'=>'!!!data!!!', 'filename'=>'test.txt')
	 * 		)
	 * 	));
	 * 
	 * @param array $options
	 * @throws Exception
	 * @return array
	 */
	public static function send($options) {
		$result = array(
			'success' => false,
			'error' => array()
		);
		
		// create a new output buffer, this function echoes on the screen
		ob_start();

		$mail = new PHPMailer();

		// todo: set mailer to use SMTP
		/*
		$smtp = application::get(array('mail', 'smtp'));
 		if (!empty($smtp)) {
 			$mail->IsSMTP();
 			$mail->Host = $smtp['host'];
 			$mail->Port = $smtp['port'];
 		}
 		*/

		// sender fields from session
		$sender = session::get(array('mail', 'sender'));

		// if not set in sessions we grab it from ini file
		if (empty($sender)) {
			$sender = application::get(array('mail', 'sender'));
		}

		// override from field
		if (!empty($options['from']['email'])) {
			$sender = array(
				'email' => $options['from']['email'],
				'name' => @$options['from']['name']
			);
		}

		// if we have sender we set it
		if (!empty($sender)) {
			$mail->SetFrom($sender['email'], $sender['name']);
		}

		// to field
		if (empty($options['to'])) {
			$result['error'][] = 'To field?';
			return $result;
		}
		$options['to'] = is_array($options['to']) ? $options['to'] : array($options['to']);

		// cc field
		if (!empty($options['cc'])) {
			$options['cc'] = is_array($options['cc']) ? $options['cc'] : array($options['cc']);
		} else {
			$options['cc'] = array();
		}

		// bcc field
		if (!empty($options['bcc'])) {
			$options['bcc'] = is_array($options['bcc']) ? $options['bcc'] : array($options['bcc']);
		} else {
			$options['bcc'] = array();
		}

		// if we are sending to email from ini file
		$mail_to = application::get(array('mail', 'to'));
		if (application::get('environment') == 'production' || empty($mail_to['email'])) {
			foreach ($options['to'] as $v) $mail->AddAddress($v);
			foreach ($options['cc'] as $v) $mail->AddCC($v);
			foreach ($options['bcc'] as $v) $mail->AddBCC($v);
		} else {
			// we need to assemble header information
			$temp = array();
			$temp[]= 'Subject: ' . @$options['subject'];
			foreach ($options['to'] as $v) $temp[] = 'To: ' . $v;
			foreach ($options['cc'] as $v) $temp[] = 'CC: ' . $v;
			foreach ($options['bcc'] as $v) $temp[] = 'BCC: ' . $v;

			// setting proper "to" and subject line
			$mail->AddAddress($mail_to['email']);
			if (isset($mail_to['prepend_subject'])) {
				$options['subject'] = $mail_to['prepend_subject'] . ' ' . $options['subject'];
			}

			// prepending assembled data to body
			if (empty($options['plain'])) {
				$options['message'] = implode("<br/>", $temp) . "<br/><hr/><br/>" . @$options['message'];
			} else {
				$options['message'] = implode("\n", $temp) . "\n---------------------------------------\n\n" . @$options['message'];
			}
		}

		// set word wrap to 50 characters
		$mail->WordWrap = 50;

		// process attachements
		if (!empty($options['attachments'])) {
			foreach ($options['attachments'] as $k=>$v) {
				if (isset($v['filepath'])) {
					$mail->AddAttachment($v['filepath'], @$v['filename']);
				} else if (isset($v['filedata']) && !empty($v['filename'])) {
					$mail->AddStringAttachment($v['filedata'], $v['filename']);
				} else {
					Throw new Exception('Mail - unknown attachement type!');
				}
			}
		}

		// set email format to HTML
		$mail->IsHTML(empty($options['plain']));

		// set attributes
		$mail->Subject = @$options['subject'];
		$mail->Body = @$options['message'];
		$mail->AltBody = @$options['text_message'] ? $options['text_message'] : strip_tags($options['message']);

		// send mail
		$flag = $mail->Send();
		$buffer = ob_get_clean();

		if(!$flag) {
			$result['error'][] = 'Message could not be send. ' . $mail->ErrorInfo;
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
	public static function send_simple($to, $subject, $message) {
		return self::send(array('to'=>$to, 'subject'=>$subject, 'message'=>$message));
	}
}