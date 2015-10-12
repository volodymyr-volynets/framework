.PHONY: all build copy

all: build copy

build:
	# building numbers.phar file
	@-rm -f ../build/numbers.phar
	@-php installer.php --build-phar-file

copy:
	# copying to /home/scripts/ if directory exists
	@-if test -d "/home/scripts"; \
	then \
		rm -f /home/scripts/numbers.phar ; \
		cp ../build/numbers.phar /home/scripts/numbers.phar ; \
	fi
