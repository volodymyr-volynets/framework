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

// updating composer file
$file = file_get_contents($install_dir . '/code/libraries/composer.json');
$file = str_replace('[numbers_framework_version]', $params['version'], $file);
file_put_contents($install_dir . '/code/libraries/composer.json', $file);

// generating application.ini file
$temp = file_get_contents($install_dir . '/code/application/application.ini.default');
$temp = str_replace('[numbers_framework_version]', $params['version'], $temp);
file_put_contents($install_dir . '/code/application/application.ini', $temp);
shell_exec("rm $install_dir/code/application/application.ini.default");

// renaming localhost.ini file
shell_exec("mv $install_dir/code/application/localhost.ini.default $install_dir/code/application/localhost.ini");

// create a folder with config files
$conf_dir = $install_dir . '/conf/production';
mkdir($conf_dir);

// copy hosts
$file = file_get_contents($temp_dir . '/skeleton/conf/hosts');
$file = str_replace('[domain]', $params['domain'], $file);
file_put_contents($conf_dir . '/hosts', $file);

// copy common apache settings
file_put_contents($conf_dir . '/vhosts.000.general.conf', file_get_contents($temp_dir . '/skeleton/conf/vhosts.000.general.conf'));

// copy domain specific conf file
if (empty($params['wildcard'])) {
	$file = file_get_contents($temp_dir . '/skeleton/conf/vhosts.xxx.domain.conf');
	$last_dir = basename($install_dir);
	$file = str_replace(array('[domain]', '[dir]', '[last_dir]'), array($params['domain'], $install_dir, $last_dir), $file);
} else {
	$file = file_get_contents($temp_dir . '/skeleton/conf/vhosts.xxx.wildcard.conf');
	$last_dir = basename($install_dir);
	$temp = explode('.', $params['domain']);
	unset($temp[0]);
	$main_domain = implode('.', $temp);
	$file = str_replace(array('[domain]', '[dir]', '[last_dir]', '[main_domain]'), array($params['domain'], $install_dir, $last_dir, $main_domain), $file);
}
file_put_contents($conf_dir . '/vhosts.' . rand(100, 999) .'.' . $last_dir . '.conf', $file);

echo " - copied configuration files\n";

// we need to set permissions after installation
shell_exec("chmod -R 0777 $install_dir");

// remove temporary directory
shell_exec("rm -r $temp_dir");
echo " - removed temporary directory $temp_dir\n";
