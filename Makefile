# Makefile to automate Article bundle testing

.PHONY: help test test-unit test-api test-coverage test-watch install-deps clean-test

# Default configuration
PHP_BIN := php
COMPOSER_BIN := composer
PHPUNIT_BIN := php ./vendor/bin/simple-phpunit

help: ## Show this help message
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

install-deps: ## Install development dependencies
	$(COMPOSER_BIN) install --dev

test: ## Run all tests
	$(PHPUNIT_BIN)

test-no-coverage: ## Run all tests without code coverage
	$(PHPUNIT_BIN) --no-coverage

test-unit: ## Run only unit tests (entities, value objects, services)
	$(PHPUNIT_BIN) --exclude-group api

test-api: ## Run only API tests
	$(PHPUNIT_BIN) --group api

test-integration: ## Run only integration tests
	$(PHPUNIT_BIN) --group integration

test-coverage: ## Run tests with HTML coverage report
	XDEBUG_MODE=coverage $(PHPUNIT_BIN) --coverage-html var/coverage/html

test-coverage-text: ## Run tests with text coverage report
	XDEBUG_MODE=coverage $(PHPUNIT_BIN) --coverage-text

test-coverage-clover: ## Run tests with Clover coverage report (for CI)
	XDEBUG_MODE=coverage $(PHPUNIT_BIN) --coverage-clover var/coverage/clover.xml

test-specific: ## Run a specific test file (usage: make test-specific FILE=tests/Api/CommentApiTest.php)
	$(PHPUNIT_BIN) $(FILE)

test-watch: ## Watch files and run tests on changes (requires entr)
	find src tests -name "*.php" | entr -c make test-no-coverage

clean-test: ## Clean temporary test files
	rm -rf var/coverage/
	rm -rf .phpunit.cache/
	rm -rf var/cache/test/

clean-all: clean-test ## Clean all generated files
	rm -rf vendor/
	rm -rf var/

check-coverage-ext: ## Check if code coverage extension is available
	$(PHP_BIN) scripts/check-coverage.php

check-xdebug: ## Check Xdebug configuration
	$(PHP_BIN) scripts/check-xdebug.php

# Code quality tools
lint-php: ## Check PHP syntax
	find src tests -name "*.php" -exec $(PHP_BIN) -l {} \;

phpcs: ## Run PHP CodeSniffer
	$(COMPOSER_BIN) run lint:phpcs

phpcs-fix: ## Fix PHP CodeSniffer issues
	$(COMPOSER_BIN) run lint:phpcs-fix

phpstan: ## Run PHPStan static analysis
	$(COMPOSER_BIN) run lint:phpstan -- --memory-limit=1G

quality: lint-php phpcs phpstan ## Run all code quality tools

# CI/CD targets
ci: install-deps quality test-coverage-text ## Complete continuous integration pipeline
ci-no-coverage: install-deps quality test-no-coverage ## CI pipeline without coverage

# Tests by category
test-entities: ## Test entity classes
	$(PHPUNIT_BIN) tests/Entity/ --no-coverage

test-valueobjects: ## Test value object classes
	$(PHPUNIT_BIN) tests/ValueObject/ --no-coverage

test-listeners: ## Test event listeners
	$(PHPUNIT_BIN) tests/EventListener/ --no-coverage

test-builders: ## Test builder classes
	$(PHPUNIT_BIN) tests/Builder/ --no-coverage

test-state: ## Test state processors
	$(PHPUNIT_BIN) tests/State/ --no-coverage

test-security: ## Test security components
	$(PHPUNIT_BIN) tests/Security/ --no-coverage

# Development helpers
schema-create: ## Create test database schema
	$(PHP_BIN) -r "require 'tests/bootstrap.php'; (new ProjetNormandie\ArticleBundle\Tests\Helper\SchemaManager(\Symfony\Component\DependencyInjection\ContainerBuilder::class))->createSchema();"

schema-drop: ## Drop test database schema
	$(PHP_BIN) -r "require 'tests/bootstrap.php'; (new ProjetNormandie\ArticleBundle\Tests\Helper\SchemaManager(\Symfony\Component\DependencyInjection\ContainerBuilder::class))->dropSchema();"

debug-test: ## Run tests with verbose output for debugging
	$(PHPUNIT_BIN) --verbose --debug

# Documentation
coverage-open: test-coverage ## Open coverage report in browser
	@if command -v xdg-open > /dev/null; then \
		xdg-open var/coverage/html/index.html; \
	elif command -v open > /dev/null; then \
		open var/coverage/html/index.html; \
	else \
		echo "Coverage report generated in var/coverage/html/index.html"; \
	fi