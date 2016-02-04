<?php

if (empty($params['dir'])) {
	echo " - you must specify \"dir\" parameter\n";
	exit;
}

$dir = rtrim($params['dir'], '/');

$files = array();
if (is_dir($dir)) {

	$exclude_dirs = array('.git', 'cache');
	$process_files = array('php', 'phps', 'js', 'json', 'css', 'conf', '.htaccess', 'ini', 'html', 'xml', 'txt', 'md');

	$filter = function ($file, $key, $iterator) use ($exclude_dirs) {
		if ($iterator->hasChildren() && !in_array($file->getFilename(), $exclude_dirs)) {
			return true;
		}
		return $file->isFile();
	};

	$iterator = new RecursiveIteratorIterator(
		new RecursiveCallbackFilterIterator(
			new RecursiveDirectoryIterator($dir),
			$filter
		)
	);

	foreach ($iterator as $filename => $cur) {
		$name = $cur->getFilename();
		$extension = $cur->getExtension();
		if (in_array($extension, $process_files) || $name == '.htaccess' || $name == '.gitkeep') {
			$content_original = $content = file_get_contents($filename);

			// step 1: remove windows new lines, two runs
			$content = str_replace(array("\r\n", "\n\r"), "\n", $content);
			$content = str_replace(array("\r\n", "\n\r"), "\n", $content);

			// step 2.1: replace four spaces with tabs
			for ($i = 20; $i >= 1; $i--) {
				$from = $to = "";
				for ($j = $i; $j >= 1; $j--) {
					$from.= "    ";
					$to.= "\t";
				}
				$content = str_replace("\n" . $from, "\n" . $to, $content);
			}

			// step 2.2: replace 3 spaces with tabs
			for ($i = 20; $i >= 1; $i--) {
				$from = $to = "";
				for ($j = $i; $j >= 1; $j--) {
					$from.= "   ";
					$to.= "\t";
				}
				$content = str_replace("\n" . $from, "\n" . $to, $content);
			}

			// step 3: remove empty lines with tabs/spaces
			for ($i = 1; $i < 5; $i++) {
				$content = preg_replace('/\n[\t| ]+\n/', "\n\n", $content);
			}

			// step 4: remove extra lines
			for ($i = 1; $i < 5; $i++) {
				$content = str_replace("\n\n\n", "\n\n", $content);
			}

			echo " - processed $filename\n";

			// writing file back
			if ($content_original !== $content) {
				if (!empty($params['new'])) {
					file_put_contents($filename . '.new', $content);
				} else {
					unlink($filename);
					file_put_contents($filename, $content);
				}
				chmod($filename, 0777);
				echo " |--> changes found\n";
			}
		}
	}
}