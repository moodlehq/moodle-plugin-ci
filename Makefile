COMPOSER := composer
PHPUNIT  := vendor/bin/phpunit
FIXER    := php build/php-cs-fixer.phar

.PHONY:test
test: test-fixer test-phpunit

.PHONY:test-fixer
test-fixer: build/php-cs-fixer.phar
	$(FIXER) fix -v || true

.PHONY:test-phpunit
test-phpunit: vendor/autoload.php
	$(PHPUNIT)

.PHONY:validate
validate: build/php-cs-fixer.phar vendor/autoload.php
	$(FIXER) fix --dry-run --stop-on-violation
	$(COMPOSER) validate
	phpdbg -qrr $(PHPUNIT) --coverage-text

.PHONY: init
init: vendor/autoload.php

.PHONY: update
update: build/php-cs-fixer.phar
	$(COMPOSER) selfupdate
	$(FIXER) selfupdate
	$(COMPOSER) update

.PHONY: clean
clean:
	rm -f build/*.phar
	rm -f build/*.clover
	rm -rf vendor
	rm -f .php_cs.cache

# Update download URL from https://github.com/FriendsOfPHP/PHP-CS-Fixer/releases
build/php-cs-fixer.phar:
	curl -LSs https://github.com/FriendsOfPHP/PHP-CS-Fixer/releases/download/v2.14.2/php-cs-fixer.phar -o build/php-cs-fixer.phar

build/box.phar:
	@cd build && curl -LSs https://box-project.github.io/box2/installer.php | php

build/moodle-plugin-ci.phar: build/box.phar
	$(COMPOSER) install --no-dev --prefer-dist --classmap-authoritative --quiet
	php -d phar.readonly=false build/box.phar build
	$(COMPOSER) install --prefer-dist --quiet

vendor/autoload.php: composer.lock composer.json
	$(COMPOSER) self-update
	$(COMPOSER) install --no-suggest --no-progress
	touch $@
