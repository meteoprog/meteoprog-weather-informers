# Makefile — part of Meteoprog Weather Widgets
# Copyright (c) 2025 Meteoprog
# Licensed under GPL-2.0-or-later
PLUGIN_NAME=meteoprog-weather-informers
SRC_PLUGIN=$(PWD)
CONTAINER_PLUGIN=/tmp/wordpress/wp-content/plugins/$(PLUGIN_NAME)
DB_NAME=wordpress_test_suite
DB_USER=root
DB_PASS=rootpass
DB_HOST=db

UID := $(shell id -u)
GID := $(shell id -g)

IMAGE_PHP56=custom-php56
IMAGE_PHP74=custom-php74
IMAGE_PHP81=custom-php81
IMAGE_PHP83=custom-php83

METEOPROG_DEBUG=1
METEOPROG_DEBUG_API_KEY=



# ------------------------------
# Build containers
# ------------------------------

build-php56:
	DOCKER_BUILDKIT=1 docker build --network=host -f ./docker/Dockerfile.php56 -t $(IMAGE_PHP56) \
	  --build-arg CACHEBUST=$(shell date +%s) --build-arg UID=$(UID) --build-arg GID=$(GID) .

build-php74:
	DOCKER_BUILDKIT=1 docker build --network=host -f ./docker/Dockerfile.php74 -t $(IMAGE_PHP74) \
	  --build-arg CACHEBUST=$(shell date +%s) --build-arg UID=$(UID) --build-arg GID=$(GID) .

build-php81:
	DOCKER_BUILDKIT=1 docker build --network=host -f ./docker/Dockerfile.php81 -t $(IMAGE_PHP81) \
	  --build-arg CACHEBUST=$(shell date +%s) --build-arg UID=$(UID) --build-arg GID=$(GID) .

build-php83:
	DOCKER_BUILDKIT=1 docker build --network=host -f ./docker/Dockerfile.php83 -t $(IMAGE_PHP83) \
	  --build-arg CACHEBUST=$(shell date +%s) --build-arg UID=$(UID) --build-arg GID=$(GID) .

# ------------------------------
# Run tests (template)
# ------------------------------
# Params: IMAGE, WP_VERSION
# ------------------------------

define RUN_TESTS
	# Determine Docker network and DB host based on environment
	@if [ "$$CI" = "true" ]; then \
	  echo "[CI detected] Using --network host"; \
	  NETWORK_OPT="--network host"; \
	  DB_HOST_REAL="127.0.0.1"; \
	else \
	  if docker network inspect wordpress_proxy >/dev/null 2>&1; then \
	    echo "[Local] Using network wordpress_proxy"; \
	    NETWORK_OPT="--network wordpress_proxy"; \
	    DB_HOST_REAL="$(DB_HOST)"; \
	  else \
	    echo "[Local] No network found, running isolated"; \
	    NETWORK_OPT=""; \
	    DB_HOST_REAL="$(DB_HOST)"; \
	  fi; \
	fi; \
	docker run --rm $$NETWORK_OPT \
	  -u $(UID):$(GID) \
	  -e METEOPROG_DEBUG=$(METEOPROG_DEBUG) \
	  -e METEOPROG_DEBUG_API_KEY=$(METEOPROG_DEBUG_API_KEY) \
	  -e WP_VERSION=$(2) \
	  -e TEST_DB_NAME=$(DB_NAME)_$(subst .,_,$(2)) \
	  -e DB_HOST=$$DB_HOST_REAL \
	  -e DB_USER=$(DB_USER) \
	  -e DB_PASS=$(DB_PASS) \
	  -e DB_HOST_REAL=$$DB_HOST_REAL \
	  -v $(SRC_PLUGIN):/src-plugin $(1) \
	  bash -c 'set -euo pipefail; \
	    echo "[Step 0] Priming DNS resolver..."; \
	    if ! nslookup wordpress.org > /dev/null 2>&1; then \
	      echo "ERROR: DNS resolution failed"; exit 1; fi; \
	    WP_PATH="/tmp/wordpress-$${WP_VERSION}"; \
	    DB_NAME="$${TEST_DB_NAME}"; \
	    echo "[Step 1.0] Waiting for database connection..."; \
	    for i in $$(seq 1 30); do \
	      if mysqladmin ping -h$$DB_HOST_REAL -p$(DB_PASS) --silent >/dev/null 2>&1; then \
	        echo "[OK] Database is ready"; break; \
	      fi; \
	      echo "[Wait $$i/30] MariaDB not ready yet..."; sleep 2; \
	    done; \
	    echo "[Step 1.1] Drop test database $$DB_NAME"; \
	    mysql --user=$(DB_USER) --password=$(DB_PASS) --host=$$DB_HOST_REAL -e "DROP DATABASE IF EXISTS $$DB_NAME;"; \
	    echo "[Step 1.2] Create database $$DB_NAME"; \
	    mysql --user=$(DB_USER) --password=$(DB_PASS) --host=$$DB_HOST_REAL -e "CREATE DATABASE IF NOT EXISTS $$DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"; \
	    echo "[Step 2] Create wp-config.php"; \
	    wp config create --path="$$WP_PATH" \
	      --dbname="$$DB_NAME" --dbuser=$(DB_USER) --dbpass=$(DB_PASS) --dbhost=$$DB_HOST_REAL \
	      --skip-check --force --allow-root; \
	    echo "[Step 3] Install WordPress"; \
	    wp core install --path="$$WP_PATH" \
	      --url=http://localhost --title="Test" \
	      --admin_user=admin --admin_password=admin --admin_email=admin@example.com \
	      --skip-email --allow-root; \
	    echo "[Step 4] Copy plugin into WordPress"; \
	    mkdir -p "$$WP_PATH/wp-content/plugins"; \
	    cp -r /src-plugin "$$WP_PATH/wp-content/plugins/$(PLUGIN_NAME)"; \
	    echo "[Step 5] Install PHPUnit polyfills"; \
	    cd "$$WP_PATH/wp-content/plugins/$(PLUGIN_NAME)"; \
	    composer require --dev $(3) --no-interaction; \
	    echo "[Step 6] Scaffold plugin test files"; \
	    wp scaffold plugin-tests $(PLUGIN_NAME) \
	      --path="$$WP_PATH" --dir="$$WP_PATH/wp-content/plugins/$(PLUGIN_NAME)" --force --allow-root; \
	    echo "[Step 7] Install WordPress test suite"; \
	    echo -e "y\n" | bash bin/install-wp-tests.sh $$DB_NAME $(DB_USER) $(DB_PASS) $$DB_HOST_REAL $${WP_VERSION}; \
	    echo "[Step 8] Run PHPUnit"; \
	    phpunit --bootstrap tests/bootstrap-extra.php --configuration phpunit.xml.dist; \
	    echo "[Step 10] Done";'
endef


# -------------------------------------
# PHP 5.6 — Legacy support
# -------------------------------------
php56-wp49: build-php56
	$(call RUN_TESTS,$(IMAGE_PHP56),4.9,phpunit/phpunit:5.7.27 yoast/phpunit-polyfills:^2.0)

# -------------------------------------
# PHP 7.4 — WordPress 5.x LTS support
# -------------------------------------
php74-wp58: build-php74
	$(call RUN_TESTS,$(IMAGE_PHP74),5.8,phpunit/phpunit:7.5.20 yoast/phpunit-polyfills:1.0.1)

php74-wp59: build-php74
	$(call RUN_TESTS,$(IMAGE_PHP74),5.9,phpunit/phpunit:7.5.20 yoast/phpunit-polyfills:1.0.1)

# -------------------------------------
# PHP 8.1 — Stable WP 6.x support
# -------------------------------------
php81-wp62: build-php81
	$(call RUN_TESTS,$(IMAGE_PHP81),6.2.2,phpunit/phpunit:9.6.29 yoast/phpunit-polyfills:^4.0)

php81-wp66: build-php81
	$(call RUN_TESTS,$(IMAGE_PHP81),6.6.2,phpunit/phpunit:9.6.29 yoast/phpunit-polyfills:^4.0)

php81-wp673: build-php81
	$(call RUN_TESTS,$(IMAGE_PHP81),6.7.3,phpunit/phpunit:9.6.29 yoast/phpunit-polyfills:^4.0)

php81-wp683: build-php81
	$(call RUN_TESTS,$(IMAGE_PHP81),6.8.3,phpunit/phpunit:9.6.29 yoast/phpunit-polyfills:^4.0)

php81-latest: build-php81
	$(call RUN_TESTS,$(IMAGE_PHP81),latest,phpunit/phpunit:9.6.29 yoast/phpunit-polyfills:^4.0)

# -------------------------------------
# PHP 8.3 — Current stable PHP support
# -------------------------------------
php83-wp62: build-php83
	$(call RUN_TESTS,$(IMAGE_PHP83),6.2.2,phpunit/phpunit:9.6.29 yoast/phpunit-polyfills:^4.0)

php83-wp66: build-php83
	$(call RUN_TESTS,$(IMAGE_PHP83),6.6.2,phpunit/phpunit:9.6.29 yoast/phpunit-polyfills:^4.0)

php83-wp673: build-php83
	$(call RUN_TESTS,$(IMAGE_PHP83),6.7.3,phpunit/phpunit:9.6.29 yoast/phpunit-polyfills:^4.0)

php83-wp683: build-php83
	$(call RUN_TESTS,$(IMAGE_PHP83),6.8.3,phpunit/phpunit:9.6.29 yoast/phpunit-polyfills:^4.0)

php83-latest: build-php83
	$(call RUN_TESTS,$(IMAGE_PHP83),latest,phpunit/phpunit:9.6.29 yoast/phpunit-polyfills:^4.0)

php83-nightly: build-php83
	$(call RUN_TESTS,$(IMAGE_PHP83),nightly,phpunit/phpunit:9.6.29 yoast/phpunit-polyfills:^4.0)


# -------------------------------------
# PHPCS / PHPCBF — Code style checks & auto-fixes
# -------------------------------------
# Uses the PHP 8.3 container to run WordPress Coding Standards (WPCS) checks.
#
# Targets:
# - phpcs-check: Runs static code analysis against WPCS and PHPCompatibility rules.
# - phpcs-fix:   Automatically fixes code formatting issues where possible.
#
# Inside the container, the following Composer packages are installed globally:
# - dealerdirect/phpcodesniffer-composer-installer — enables external standards
# - wp-coding-standards/wpcs — the official WordPress Coding Standards
# - phpcompatibility/phpcompatibility-wp — checks compatibility with multiple PHP versions
#
# Excluded directories: node_modules, vendor, tests, bin, assets/test.
# Only *.php files are scanned.
# -------------------------------------

phpcs-check: build-php83
	docker run --rm -u $(UID):$(GID) \
		-v $(SRC_PLUGIN):/src-plugin -w /src-plugin $(IMAGE_PHP83) \
		bash -lc 'set -euo pipefail; \
			composer global config --no-plugins allow-plugins.dealerdirect/phpcodesniffer-composer-installer true; \
			composer global require dealerdirect/phpcodesniffer-composer-installer:^1.0 \
				wp-coding-standards/wpcs:^3.0 \
				phpcompatibility/phpcompatibility-wp:^2.1; \
			~/.composer/vendor/bin/phpcs --standard=WordPress \
				--extensions=php \
				--ignore=node_modules,vendor,tests,bin,assets/test .'

phpcs-fix: build-php83
	docker run --rm -u $(UID):$(GID) \
		-v $(SRC_PLUGIN):/src-plugin -w /src-plugin $(IMAGE_PHP83) \
		bash -lc 'set -euo pipefail; \
			composer global config --no-plugins allow-plugins.dealerdirect/phpcodesniffer-composer-installer true; \
			composer global require dealerdirect/phpcodesniffer-composer-installer:^1.0 \
				wp-coding-standards/wpcs:^3.0 \
				phpcompatibility/phpcompatibility-wp:^2.1; \
			~/.composer/vendor/bin/phpcbf --standard=WordPress \
				--extensions=php \
				--ignore=node_modules,vendor,tests,bin,assets/test .'
				
# -------------------------------------
# Run all test suites in parallel
# -------------------------------------
# Usage:
#   make -j4 testall
# This will run all PHP + WordPress version combinations concurrently.
testall: \
	php56-wp49 \
	php74-wp58 php74-wp59 \
	php81-wp62 php81-wp66 php81-wp673 php81-wp683 php81-latest \
	php83-wp62 php83-wp66 php83-wp673 php83-wp683 php83-latest php83-nightly
	@echo "All test suites have finished."



# -------------------------------------
# i18n / Localization (translate.wordpress.org workflow)
# -------------------------------------
# Generates a single POT file from the plugin source.
# This POT file is committed to the /languages directory.
# All actual translations (.po/.mo/.json) are managed automatically
# by translate.wordpress.org (GlotPress) — no manual .po/.mo files are stored.
#
# Usage:
#   make i18n-pot
#
# After running this, commit and push the updated POT file to SVN.
# WordPress.org will scan it, update strings, and generate translations.
# -------------------------------------

# Paths
PLUGIN_FILE := meteoprog-weather-informers.php

# Автоматически вытаскиваем Plugin Name и Version из шапки плагина
PLUGIN_NAME_HEADER := $(shell grep -E '^ \* Plugin Name:' $(PLUGIN_FILE) | sed -E 's/^ \* Plugin Name:[[:space:]]*//')
PLUGIN_VERSION := $(shell grep -E '^ \* Version:' $(PLUGIN_FILE) | sed -E 's/^ \* Version:[[:space:]]*//')

i18n-pot: build-php83
	docker run --rm -u $(UID):$(GID) -v $(SRC_PLUGIN):/src-plugin -w /src-plugin $(IMAGE_PHP83) \
	  wp i18n make-pot . languages/$(PLUGIN_NAME).pot \
	    --exclude=node_modules,vendor,tests,bin,assets/test \
	    --allow-root
	@echo "[i18n] POT file updated: languages/$(PLUGIN_NAME).pot"

	sed -i -E \
		-e 's|^# Copyright.*|# Copyright (C) 2025 Meteoprog|' \
		-e 's|^# This file is distributed.*|# This file is distributed under the same license as the $(PLUGIN_NAME_HEADER) plugin.|' \
		-e 's|^"Project-Id-Version:.*|"Project-Id-Version: $(PLUGIN_NAME_HEADER) $(PLUGIN_VERSION)\\n"|' \
		-e 's|^"Report-Msgid-Bugs-To:.*|"Report-Msgid-Bugs-To: https://wordpress.org/support/plugin/meteoprog-weather-informers\\n"|' \
		-e 's|^"Last-Translator:.*|"Last-Translator: meteoprog <app@meteoprog.com>\\n"|' \
		-e 's|^"Language-Team:.*|"Language-Team: English <app@meteoprog.com>\\n"|' \
		-e 's|^"POT-Creation-Date:.*|"POT-Creation-Date: $(shell date -u +"%Y-%m-%dT%H:%M:%S+00:00")\\n"|' \
		-e 's|^"PO-Revision-Date:.*|"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\\n"|' \
		-e 's|^"X-Domain:.*|"X-Domain: meteoprog-weather-informers\\n"|' \
		languages/$(PLUGIN_NAME).pot

	@echo "[i18n] POT file updated with Project-Id-Version: $(PLUGIN_NAME_HEADER) $(PLUGIN_VERSION)"


# -------------------------------------
# Build clean distributable ZIP inside Docker (PHP 8.3)
# -------------------------------------
# Usage:
#   make dist-docker
# This runs wp dist-archive inside the PHP 8.3 container.
# -------------------------------------

dist-docker: build-php83
	docker run --rm -u 0:0 \
	  -v $(SRC_PLUGIN):/src-plugin -w /src-plugin $(IMAGE_PHP83) \
	  sh -c 'mkdir -p dist /tmp/$(PLUGIN_NAME) \
	    && cp -a . /tmp/$(PLUGIN_NAME)/ \
	    && wp dist-archive /tmp/$(PLUGIN_NAME) dist/$(PLUGIN_NAME).zip --allow-root --force \
	    && chown -R $(UID):$(GID) dist'
	@echo "[dist] ✅ Built dist/$(PLUGIN_NAME).zip with proper root folder"