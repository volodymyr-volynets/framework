<?php

if (empty($params['dir']) || empty($params['domain']) || empty($params['version'])) {
	echo " - you must specify \"dir\", \"domain\" and \"version\" parameters\n";
	exit;
}

// we need to check if installation directory exists
$install_dir = rtrim(trim($params['dir']), '/');
if (file_exists($install_dir)) {
	if (empty($params['clean'])) {
		echo " - directory already exists $install_dir, use clean=1 parameter to override\n";
		exit;
	} else { // cleaning
		shell_exec("rm -r $install_dir");
		shell_exec("rm -r $install_dir.c");
		echo " - cleaning installation directory $install_dir\n";
	}
}

// create temporary directory
$temp_dir = rtrim(sys_get_temp_dir(), '/') . '/numbers_installer_' . rand(100000, 999999);
mkdir($temp_dir, 0777);
echo " - created temporary directory $temp_dir\n";

// extract skeleton to it
$phar = new Phar(rtrim(getcwd(), '/') . '/numbers.phar');
$phar->extractTo($temp_dir);
echo " - extracted all files from numbers.phar\n";

// now we need to compare
mkdir($install_dir, 0777);
echo " - created installation directory $install_dir\n";

// copy files
shell_exec("cp -r $temp_dir/skeleton/application/. $install_dir");
echo " - copied files to $install_dir\n";

// making replaces
$params['domain'] = trim($params['domain']);
$params['version'] = trim($params['version']);
$file = file_get_contents($install_dir . '/libraries/composer.json');
$file = str_replace('[version]', $params['version'], $file);
file_put_contents($install_dir . '/libraries/composer.json', $file);

// create a folder with config files
mkdir($install_dir . '.c', 0777);
mkdir($install_dir . '.c/production', 0777);
echo " - created conf directory $install_dir.c/production\n";

// copy hosts
$file = file_get_contents($temp_dir . '/conf/hosts');
$file = str_replace('[domain]', $params['domain'], $file);
file_put_contents($install_dir . '.c/production/hosts', $file);

// copy common apache settings
file_put_contents($install_dir . '.c/production/vhosts.000.general.conf', file_get_contents($temp_dir . '/conf/vhosts.000.general.conf'));

// copy domain specific conf file
if (empty($params['wildcard'])) {
	$file = file_get_contents($temp_dir . '/conf/vhosts.xxx.domain.conf');
	$last_dir = basename($install_dir);
	$file = str_replace(array('[domain]', '[dir]', '[last_dir]'), array($params['domain'], $install_dir, $last_dir), $file);
	file_put_contents($install_dir . '.c/production/vhosts.' . rand(100, 999) .'.' . $last_dir . '.conf', $file);
} else {
	$file = file_get_contents($temp_dir . '/conf/vhosts.xxx.wildcard.conf');
	$last_dir = basename($install_dir);
	$temp = explode('.', $params['domain']);
	unset($temp[0]);
	$main_domain = implode('.', $temp);
	$file = str_replace(array('[domain]', '[dir]', '[last_dir]', '[main_domain]'), array($params['domain'], $install_dir, $last_dir, $main_domain), $file);
	file_put_contents($install_dir . '.c/production/vhosts.' . rand(100, 999) .'.' . $last_dir . '.conf', $file);
}

echo " - copied configuration files\n";

// remove temporary directory
shell_exec("rm -r $temp_dir");
echo " - removed temporary directory $temp_dir\n";
