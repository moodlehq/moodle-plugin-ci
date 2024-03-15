COMPOSER := composer
PHPUNIT  := vendor/bin/phpunit
FIXER    := vendor/bin/php-cs-fixer
PSALM    := vendor/bin/psalm
CMDS     = $(wildcard src/Command/*.php)

.PHONY:test
test: check-init test-fixer psalm test-phpunit check-docs

.PHONY:test-fixer
test-fixer: check-init
	$(FIXER) fix -v || true

.PHONY:test-phpunit
test-phpunit: check-init
	$(PHPUNIT) 

.PHONY:validate
validate: check-init validate-version psalm check-docs
	$(FIXER) fix --dry-run --stop-on-violation
	$(COMPOSER) validate
	XDEBUG_MODE=coverage $(PHPUNIT) --coverage-text

.PHONY:coverage-phpunit
coverage-phpunit: check-init
	XDEBUG_MODE=coverage $(PHPUNIT) --coverage-clover build/logs/clover.xml

.PHONY:build
build: build/moodle-plugin-ci.phar

.PHONY:validate-version
validate-version:
	bin/validate-version

.PHONY:psalm
psalm: check-init
	$(PSALM) --show-info=true

.PHONY:psalm-update-baseline
psalm-update-baseline: check-init
	$(PSALM) --update-baseline

.PHONY:check-docs
check-docs: docs/CLI.md
	@echo "CHECKING if 'docs/CLI.md' needs to be committed due to changes.  If this fails, simply commit the changes."
	git diff-files docs/CLI.md

# Setup for testing.
.PHONY: init
init: composer.lock composer.json
	$(COMPOSER) selfupdate
	$(COMPOSER) install --no-progress

.PHONY: update
update: check-init
	$(COMPOSER) selfupdate
	$(FIXER) selfupdate
	$(COMPOSER) update

.PHONY: clean
clean:
	rm -f build/*.phar
	rm -rf build/logs
	rm -rf vendor
	rm -f .php_cs.cache
	rm -rf .psalm.cache

# Output error if not initialised.
check-init:
ifeq (, $(wildcard vendor))
	$(error Run 'make init' first)
endif

build/box.phar:
	curl -LSs https://github.com/box-project/box/releases/download/4.6.1/box.phar -o build/box.phar

build/moodle-plugin-ci.phar: build/box.phar
	$(COMPOSER) install --no-dev --prefer-dist --classmap-authoritative --quiet
	php -d memory_limit=-1 -d phar.readonly=false build/box.phar compile
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
	@sed -i "s~${PWD}~/path/to~" $@
	@echo "REGENERATED $@"
