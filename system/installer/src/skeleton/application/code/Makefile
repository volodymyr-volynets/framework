.PHONY: all build dep_test dep_commit permissions

all: deploy

build:
	# building application
	@-$(MAKE) git
	@-$(MAKE) permissions
	@-if test -d "libraries/vendor/numbers/framework/system/managers"; \
	then \
		$(MAKE) dep_commit ; \
	fi
	@-$(MAKE) composer
	@-$(MAKE) permissions
	@-$(MAKE) deploy

git:
	# initializing git repository if not exists
	@-if ! test -d ".git"; \
	then \
		git init; \
	fi

dep_test:
	# testing dependencies in test mode
	@-if test -d "libraries/vendor/numbers/framework/system/managers"; \
	then \
		php libraries/vendor/numbers/framework/system/managers/manager.php dependency test ; \
	fi

dep_commit:
	# testing dependencies in commit mode
	@-if test -d "libraries/vendor/numbers/framework/system/managers"; \
	then \
		php libraries/vendor/numbers/framework/system/managers/manager.php dependency commit ; \
	fi

permissions:
	# granting permissions last
	@-chmod -R 0777 $(shell pwd)
	@-chmod -R 0777 $(shell pwd)/../deployed

composer:
	# running composer and the deleting submodules
	@-cd libraries; \
	rm -r vendor; \
	composer.phar update; \
	find vendor -type d -name ".git" -exec rm -rf {} \;

deploy:
	# deploying code in production mode
	@-if test -d "libraries/vendor/numbers/framework/system/managers"; \
	then \
		php libraries/vendor/numbers/framework/system/managers/manager.php deploy code ; \
	fi

deploy_dev:
	# deploying code in development mode, use this when developing
	@-if test -d "libraries/vendor/numbers/framework/system/managers"; \
	then \
		php libraries/vendor/numbers/framework/system/managers/manager.php deploy code_dev ; \
	fi

dev_symlink:
	# building dev symlinks, so we can commit changes right in proper git repository
	@-if test -d "libraries/vendor/numbers/framework"; \
	then \
		rm -r libraries/vendor/numbers/framework ; \
		ln -s /home/numbers/framework libraries/vendor/numbers/framework ; \
	fi
