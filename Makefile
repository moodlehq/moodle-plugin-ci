COMPOSER := composer
PHPUNIT  := vendor/bin/phpunit
FIXER    := php build/php-cs-fixer.phar

.PHONY:test
test: vendor/autoload.php build/php-cs-fixer.phar
	$(FIXER) fix -v || true
	$(PHPUNIT)

.PHONY:validate
validate: vendor/autoload.php build/php-cs-fixer.phar
	$(COMPOSER) validate
	$(FIXER) fix --dry-run --stop-on-violation
	$(PHPUNIT)

.PHONEY:upload-code-coverage
upload-code-coverage: build/ocular.phar build/coverage.clover
	cd build && php ocular.phar code-coverage:upload --format=php-clover coverage.clover

build/coverage.clover: vendor/autoload.php
	phpdbg -qrr $(PHPUNIT) --coverage-clover build/coverage.clover

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
	curl -LsS https://github.com/FriendsOfPHP/PHP-CS-Fixer/releases/download/v2.3.2/php-cs-fixer.phar -o build/php-cs-fixer.phar

build/box.phar:
	@cd build && curl -LSs https://box-project.github.io/box2/installer.php | php

build/ocular.phar:
	wget https://scrutinizer-ci.com/ocular.phar

build/moodle-plugin-ci.phar: build/box.phar
	$(COMPOSER) install --no-dev --prefer-dist --classmap-authoritative --quiet
	php -d phar.readonly=false build/box.phar build
	$(COMPOSER) install --quiet

composer.lock: composer.json
	$(COMPOSER) install --quiet
	touch $@

vendor/autoload.php: composer.lock
	$(COMPOSER) install --quiet
	touch $@
