<?php

class controller_error {

	public $title = 'Application Error';
	public $acl = array(
		'login' => false,
		'tokens' => array()
	);

	public function action_error() {
		$ms = '';
		if (count(error::$errors) > 0) {
			$ms.= '<table>';
				foreach (error::$errors as $k => $v) {
					$ms.= '<tr>';
						$ms.= '<td><b>' . error::$error_codes[$v['errno']] . ' (' . $v['errno'] . ') - ' . implode('<br/>', $v['error']) . '</b></td>';
					$ms.= '</tr>';
					$ms.= '<tr>';
						$ms.= '<td>File: ' . $v['file'] . ', Line: ' . $v['line'] . '</td>';
					$ms.= '</tr>';
					// showing code only when we debug
					if (debug::$debug) {
						$ms.= '<tr>';
							$ms.= '<td><pre>' . $v['code'] . '</pre></td>';
						$ms.= '</tr>';
					}
					$ms.= '<tr>';
						$ms.= '<td><hr/></td>';
					$ms.= '</tr>';
				}
			$ms.= '</table>';
		}
		echo $ms;
	}
}