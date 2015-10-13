# Installer for Numbers Framework

Installation instruction:
1) generate a phar file from source, navigate to:

	(...)/numbers/framework/system/installer/src/

2) run following command:

	make

	or

	sudo php installer.php --build-phar-file

Usage:
1) get version:

	php numbers.phar version

2) create new application structure:

	php numbers.phar new_application

		available parameters:
			version=1.1.21 - framework version
			dir=/home/domains/software.numbers.playground - installation directory
			domain=playground.numbers.software - web site domain name
			clean=1 - optional if we need to remove installation directory first
			wildcard=1 - optional if we are setting up wildcard domains

	full usage:

		php numbers.phar new_application version=1.0.24 dir=/home/domains/local.playground domain=playground.local clean=1

		or with wildcard

		php numbers.phar new_application version=1.0.24 dir=/home/domains/local.playground domain=playground.local clean=1 wildcard=1

3) clean the code:

	php numbers.phar code_cleaner

		available parameters:
			dir=/temp/numbers - directory to check in
			new=1 - optional whether to create .new files

	full usage:

		php numbers.phar code_cleaner dir=/temp/numbers new=1