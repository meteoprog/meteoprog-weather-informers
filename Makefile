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

# -------------------------------------
# Isolated MariaDB 10.6 for PHP tests
# Used both locally and in CI.
# -------------------------------------
DB_CONTAINER_NAME := meteoprog-wp-mariadb
DB_IMAGE := mariadb:10.6
DB_NETWORK := meteoprog-weather-informers-network
DB_PORT := 3306

start-db:
	@echo "[DB] Ensuring isolated test network $(DB_NETWORK)..."; \
	docker network inspect $(DB_NETWORK) >/dev/null 2>&1 || docker network create $(DB_NETWORK) >/dev/null; \
	if ! docker ps --format '{{.Names}}' | grep -q '^$(DB_CONTAINER_NAME)$$'; then \
	  echo "[DB] Starting MariaDB container $(DB_CONTAINER_NAME)..."; \
	  docker run -d --rm \
	    --name $(DB_CONTAINER_NAME) \
	    --network $(DB_NETWORK) \
	    -e MARIADB_ROOT_PASSWORD=$(DB_PASS) \
	    -e MARIADB_DATABASE=$(DB_NAME) \
	    $(DB_IMAGE) >/dev/null; \
	  echo "[DB] Waiting for MariaDB to become healthy..."; \
	  until docker exec $(DB_CONTAINER_NAME) mariadb-admin ping -h localhost -p$(DB_PASS) --silent >/dev/null 2>&1; do \
	    sleep 2; \
	  done; \
	  echo "[DB] ✅ MariaDB ready in network $(DB_NETWORK)"; \
	else \
	  echo "[DB] MariaDB already running."; \
	fi

stop-db:
	@echo "[DB] Cleaning up MariaDB and network..."; \
	docker ps -a --format '{{.Names}}' | grep -q '^$(DB_CONTAINER_NAME)$$' && docker rm -f $(DB_CONTAINER_NAME) >/dev/null || true; \
	docker network inspect $(DB_NETWORK) >/dev/null 2>&1 && docker network rm $(DB_NETWORK) >/dev/null || true; \
	echo "[DB] 🧹 Cleanup complete."

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
	# Always use the same isolated Docker network for DB + tests
	echo "[Network] Using isolated network $(DB_NETWORK)"; \
	NETWORK_OPT="--network $(DB_NETWORK)"; \
	DB_HOST_REAL="$(DB_CONTAINER_NAME)"; \
	RUN_ID=$$(date +%s%N | sha1sum | cut -c1-6); \
	echo "[Run] WP=$(2) RUN_ID=$$RUN_ID"; \
	docker run --rm $$NETWORK_OPT \
	  -u $(UID):$(GID) \
	  -e METEOPROG_DEBUG=$(METEOPROG_DEBUG) \
	  -e METEOPROG_DEBUG_API_KEY=$(METEOPROG_DEBUG_API_KEY) \
	  -e WP_VERSION=$(2) \
	  -e TEST_DB_NAME=$(DB_NAME)_$(subst .,_,$(2))_$$RUN_ID \
	  -e DB_HOST=$$DB_HOST_REAL \
	  -e DB_HOST_REAL=$$DB_HOST_REAL \
	  -e DB_USER=$(DB_USER) \
	  -e DB_PASS=$(DB_PASS) \
	  -v $(SRC_PLUGIN):/src-plugin $(1) \
	  bash -c 'set -euo pipefail; \
	    echo "[Step 0] Priming DNS resolver..."; \
	    if ! getent hosts wordpress.org > /dev/null 2>&1; then \
	      echo "ERROR: DNS resolution failed"; exit 1; fi; \
	    WP_PATH="/tmp/wordpress-$${WP_VERSION}"; \
	    DB_NAME="$${TEST_DB_NAME}"; \
	    echo "[Step 1] Download WordPress $${WP_VERSION}"; \
	    php -d memory_limit=-1 /usr/local/bin/wp core download --path="$$WP_PATH" --version="$${WP_VERSION}" --allow-root; \
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
	    phpunit --colors=always --bootstrap tests/bootstrap-extra.php --configuration phpunit.xml.dist; \
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
# Single test wrappers (auto DB lifecycle)
# -------------------------------------
test-php56-wp49: start-db php56-wp49 stop-db
test-php74-wp58: start-db php74-wp58 stop-db
test-php74-wp59: start-db php74-wp59 stop-db
test-php81-wp62: start-db php81-wp62 stop-db
test-php81-wp66: start-db php81-wp66 stop-db
test-php81-wp673: start-db php81-wp673 stop-db
test-php81-wp683: start-db php81-wp683 stop-db
test-php81-latest: start-db php81-latest stop-db
test-php83-wp62: start-db php83-wp62 stop-db
test-php83-wp66: start-db php83-wp66 stop-db
test-php83-wp673: start-db php83-wp673 stop-db
test-php83-wp683: start-db php83-wp683 stop-db
test-php83-latest: start-db php83-latest stop-db
test-php83-nightly: start-db php83-nightly stop-db

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
testall: start-db \
	php56-wp49 \
	php74-wp58 php74-wp59 \
	php81-wp62 php81-wp66 php81-wp673 php81-wp683 php81-latest \
	php83-wp62 php83-wp66 php83-wp673 php83-wp683 php83-latest php83-nightly \
	stop-db
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