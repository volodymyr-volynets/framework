.PHONY: all build

all: build

build:
	# running composer and the deleting submodules
	cd libraries; \
	sudo rm -r vendor; \
	sudo composer.phar update; \
	find vendor -type d -name ".git" -exec rm -rf {} \;

	# we need to grant permissions last
	sudo chmod -R 0777 $(shell pwd)
