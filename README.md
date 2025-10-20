# Meteoprog Weather Widget

[![CI/CD](https://github.com/meteoprog/meteoprog-weather-informers/actions/workflows/ci.yml/badge.svg)](https://github.com/meteoprog/meteoprog-weather-informers/actions)
[![License: GPL v2 or later](https://img.shields.io/badge/License-GPLv2%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![PHP](https://img.shields.io/badge/PHP-5.6%20--%208.4-777bb3.svg?logo=php)](https://www.php.net/)
[![WordPress](https://img.shields.io/badge/WordPress-4.9%20--%206.8-blue.svg?logo=wordpress)](https://wordpress.org/)
[![Dockerized](https://img.shields.io/badge/Docker-ready-blue.svg?logo=docker)](https://hub.docker.com/)
[![Release](https://img.shields.io/github/v/release/meteoprog/meteoprog-weather-informers)](https://github.com/meteoprog/meteoprog-weather-informers/releases)
[![Last commit](https://img.shields.io/github/last-commit/meteoprog/meteoprog-weather-informers.svg)](https://github.com/meteoprog/meteoprog-weather-informers/commits/main)

This repository contains the **Meteoprog Weather Widget** WordPress plugin. It provides modern and legacy weather widgets (informers) from [Meteoprog](https://meteoprog.com), fully compatible with Gutenberg, Elementor, Shortcodes Ultimate, REST API, and WP-CLI.

---

## 🧩 Plugin Overview

* **WordPress versions:** 4.9 → 6.8+
* **PHP versions:** 5.6 → 8.4
* **Integrations:** Gutenberg, Elementor, Shortcodes Ultimate, WP-CLI, REST API
* **Compatibility:** Works on classic and block widgets, async frontend loader, optimized for Core Web Vitals.
* **API Requirement:** Requires a **free informer API key** from [billing.meteoprog.com/informer](https://billing.meteoprog.com/informer?utm_source=github&utm_medium=readme).

Widgets will not display without a valid key linked to your website domain.

---

## ⚙️ Repository Structure

```
.
├── assets/                # JS and CSS for admin, blocks, and frontend
├── includes/              # Core classes and integrations
├── views/                 # Admin UI templates and partials
├── tests/                 # PHPUnit test suite (WP_Compat_TestCase)
├── docker/                # Dockerfiles for PHP 5.6–8.4
├── Makefile               # Test, lint, build, and dist automation
├── languages/             # POT template for GlotPress
├── readme.txt             # WordPress.org readme
├── README.md              # GitHub readme (this file)
└── dist/                  # Built distributable ZIPs
```

---

## 🧪 Test Matrix

All PHP × WordPress combinations are tested using Docker via the Makefile. The following environments are covered:

| PHP | WordPress                                |
| --- | ---------------------------------------- |
| 5.6 | 4.9                                      |
| 7.4 | 5.8 – 5.9                                |
| 8.1 | 6.2 – 6.8                                |
| 8.3 | 6.2 – latest / nightly (daily scheduled) |
| 8.4 | 6.8.3 – latest / nightly                 |

Each suite spins up a temporary WordPress install inside Docker, installs PHPUnit + Yoast Polyfills, runs tests, and tears down the DB automatically.

---

## 🧪 Local Testing

You can run individual test suites or the full test matrix locally using Docker and Makefile targets.

**Run a specific test (PHP 8.3 + WordPress 6.8.3):**
```bash
make test-php83-wp683
```

**Run all test suites sequentially (verbose output):**
```bash
make testall
```

---

#### 🧩 Available Test Targets

| PHP Version | WordPress Versions |
|--------------|--------------------|
| **5.6** | `wp49` |
| **7.4** | `wp58`, `wp59` |
| **8.1** | `wp62`, `wp66`, `wp673`, `wp683`, `latest` |
| **8.3** | `wp62`, `wp66`, `wp673`, `wp683`, `latest`, `nightly` |
| **8.4** | `wp683`, `latest`, `nightly` |

Each test target automatically starts a dedicated MariaDB container, runs PHPUnit, and then stops the database container.  

Example:  
```bash
make test-php81-wp683
# Equivalent to:
# start-db php81-wp683
# stop-db
```

---

### 🧰 Useful Makefile Commands

| Command | Description |
|----------|-------------|
| `make i18n-pot` | Generate translation template (`.pot`) |
| `make phpcs` | Run PHPCS / WPCS checks |
| `make plugin-check` | Run WordPress Plugin Check tool |
| `make testall` | Run all test suites sequentially with detailed output |

---

📘 **Note:**  
For CI, tests are executed in parallel across the full matrix (PHP 5.6 → 8.4, WordPress 4.9 → 6.8+), but local runs use sequential mode (`make testall`) for clarity.

---

### 🧩 Plugin Check Validation

Run the official [WordPress Plugin Check](https://github.com/WordPress/plugin-check) locally to ensure compliance with Plugin Directory guidelines:

```bash
make test-plugin-check
```

This command runs `wp plugin-check` inside Docker and verifies:

* Proper plugin headers and text domain
* Correct use of escaping and sanitization functions
* No usage of disallowed APIs
* GPL license and translation readiness

---

### 🔍 Additional QA Checks

* **🧩 Plugin Check:** runs automatically in CI (`make test-plugin-check`) to verify plugin headers, i18n, sanitization, and GPL compliance.
* **🧹 Static Analysis:** enforces WordPress Coding Standards (WPCS), detects deprecated APIs, and ensures proper escaping/sanitization.
* **🌙 Nightly & Latest Tests:** cover PHP 8.3 (latest + nightly) and 8.4 (nightly) to ensure forward compatibility with upcoming WordPress releases and daily stability checks.
* **📅 Daily Schedule:** all nightly and latest builds (PHP 8.3/8.4) also run automatically every day at 03:00 UTC to validate the latest published release.

---

## 🧰 Linting and Standards

WordPress Coding Standards (WPCS) are enforced through Docker:

```bash
make phpcs-check   # run static analysis
make phpcs-fix     # auto-fix formatting
```

These use `dealerdirect/phpcodesniffer-composer-installer`, `wp-coding-standards/wpcs`, and `phpcompatibility/phpcompatibility-wp`.

---

## 🌐 i18n / Localization

Run POT generation with:

```bash
make i18n-pot
```

This updates `languages/meteoprog-weather-informers.pot` with metadata ready for translate.wordpress.org.

---

## 🚀 Distribution Build

Generate a clean distributable ZIP inside Docker:

```bash
make dist-docker
```

The `.distignore` excludes all development files (tests, docker, node_modules, etc.). The result appears in `/dist/` as `meteoprog-weather-informers.zip`.

---

## 🧱 Continuous Integration

GitHub Actions workflow (`.github/workflows/ci.yml`) runs tests for all supported PHP and WordPress versions using a matrix build strategy. It mirrors local Makefile logic, ensuring every push and PR passes:

* ✅ Build Docker images per PHP version
* ✅ Run unit tests for each WP version
* ✅ Run PHPCS linting
* ✅ Run Plugin Check validation
* ✅ Run nightly and latest scheduled builds daily
* ✅ Upload coverage reports (optional)

---

## 📜 License

GPLv2 or later — see [license.txt](license.txt)

---

**© 2025 Meteoprog — [https://meteoprog.com](https://meteoprog.com)**