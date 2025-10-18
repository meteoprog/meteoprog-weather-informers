# Meteoprog Weather Widget

This repository contains the **Meteoprog Weather Widget** WordPress plugin. It provides modern and legacy weather widgets (informers) from [Meteoprog](https://meteoprog.com), fully compatible with Gutenberg, Elementor, Shortcodes Ultimate, REST API, and WP-CLI.

---

## 🧩 Plugin Overview
- **WordPress versions:** 4.9 → 6.8+
- **PHP versions:** 5.6 → 8.4
- **Integrations:** Gutenberg, Elementor, Shortcodes Ultimate, WP-CLI, REST API
- **Compatibility:** Works on classic and block widgets, async frontend loader, optimized for Core Web Vitals.
- **API Requirement:** Requires a **free informer API key** from [billing.meteoprog.com/informer](https://billing.meteoprog.com/informer?utm_source=github&utm_medium=readme).

Widgets will not display without a valid key linked to your website domain.

---

## ⚙️ Repository Structure
```
.
├── assets/                # JS and CSS for admin, blocks, and frontend
├── includes/              # Core classes and integrations
├── views/                 # Admin UI templates and partials
├── tests/                 # PHPUnit test suite (WP_Compat_TestCase)
├── docker/                # Dockerfiles for PHP 5.6–8.3
├── Makefile               # Test, lint, build, and dist automation
├── languages/             # POT template for GlotPress
├── readme.txt             # WordPress.org readme
├── README.md              # GitHub readme (this file)
└── dist/                  # Built distributable ZIPs
```

---

## 🧪 Test Matrix
All PHP × WordPress combinations are tested using Docker via the Makefile. The following environments are covered:

| PHP | WordPress |
|-----|------------|
| 5.6 | 4.9        |
| 7.4 | 5.8 – 5.9  |
| 8.1 | 6.2 – 6.8  |
| 8.3 | 6.2 – nightly |

Run tests locally:
```bash
make php83-wp683
```
Run all test suites in parallel:
```bash
make -j4 testall
```

Each suite spins up a temporary WordPress install inside Docker, installs PHPUnit + Yoast Polyfills, runs tests, and tears down the DB automatically.

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
- ✅ Build Docker images per PHP version
- ✅ Run unit tests for each WP version
- ✅ Run PHPCS linting
- ✅ Upload coverage reports (optional)

---

## 📜 License
GPLv2 or later — see [license.txt](license.txt)

---

**© 2025 Meteoprog — https://meteoprog.com**
