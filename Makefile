COMPOSER := composer
PHPUNIT  := vendor/bin/phpunit
FIXER    := vendor/bin/php-cs-fixer
PSALM    := php build/psalm.phar
CMDS     = $(wildcard src/Command/*.php)

.PHONY:test
test: check-init test-fixer psalm test-phpunit check-docs

.PHONY:test-fixer
test-fixer: check-init
	$(FIXER) fix -v || true

.PHONY:test-phpunit
test-phpunit: check-init
	$(PHPUNIT) --verbose

.PHONY:validate
validate: check-init validate-version psalm check-docs
	$(FIXER) fix --dry-run --stop-on-violation
	$(COMPOSER) validate
	XDEBUG_MODE=coverage $(PHPUNIT) --verbose --coverage-text

.PHONY:build
build: build/moodle-plugin-ci.phar

.PHONY:validate-version
validate-version:
	bin/validate-version

.PHONY:psalm
psalm: check-init
	$(PSALM)

.PHONY:psalm-update-baseline
psalm-update-baseline: check-init
	$(PSALM) --update-baseline

.PHONY:check-docs
check-docs: docs/CLI.md
	@echo "CHECKING if 'docs/CLI.md' needs to be committed due to changes.  If this fails, simply commit the changes."
	git diff-files docs/CLI.md

# Setup for testing.
.PHONY: init
init: build/php-cs-fixer.phar build/psalm.phar composer.lock composer.json
	$(COMPOSER) selfupdate
	$(COMPOSER) install --no-progress

.PHONY: update
update: check-init build/php-cs-fixer.phar build/psalm.phar
	$(COMPOSER) selfupdate
	$(FIXER) selfupdate
	$(COMPOSER) update

.PHONY: clean
clean:
	rm -f build/*.phar
	rm -f build/*.clover
	rm -rf vendor
	rm -f .php_cs.cache

# Output error if not initialised.
check-init:
ifeq (, $(wildcard vendor))
	$(error Run 'make init' first)
endif

# Update download URL from https://github.com/FriendsOfPHP/PHP-CS-Fixer/releases
build/php-cs-fixer.phar:
	curl -LSs https://github.com/FriendsOfPHP/PHP-CS-Fixer/releases/download/v3.4.0/php-cs-fixer.phar -o build/php-cs-fixer.phar

# Update download URL from https://github.com/vimeo/psalm/releases
build/psalm.phar:
	curl -LSs https://github.com/vimeo/psalm/releases/download/4.23.0/psalm.phar -o build/psalm.phar

build/box.phar:
	@cd build && curl -LSs https://box-project.github.io/box2/installer.php | php

build/moodle-plugin-ci.phar: build/box.phar
	$(COMPOSER) install --no-dev --prefer-dist --classmap-authoritative --quiet
	php -d memory_limit=-1 -d phar.readonly=false build/box.phar build
	$(COMPOSER) install --prefer-dist --quiet

docs/CLI.md: $(CMDS)
	@rm -f $@
	@echo "---" >> $@
	@echo "layout: page" >> $@
	@echo "title: Moodle Plugin CI Commands" >> $@
	@echo "---" >> $@
	@echo "" >> $@
	@echo "<!-- AUTOMATICALLY GENERATED VIA: make $@ -->" >> $@
	@php bin/moodle-plugin-ci list --format md | sed 1,2d >> $@
	@echo "REGENERATED $@"
