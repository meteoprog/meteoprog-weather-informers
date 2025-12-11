# Changelog

All notable changes to **Meteoprog Weather Widget** will be documented in this file.  
This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [v1.0.3] - 2025-12-12

* 🆕 Confirmed full compatibility with WordPress 6.9.
* 🛠 Updated "Tested up to" to 6.9.
* 🔒 Disabled HTML editing mode in the block editor.
* 🧹 Internal cleanup for upcoming iframe mode.
* 🛡 Prefixed admin template variables for Plugin Check compliance.

## [v1.0.2] - 2025-10-29
* 🐞 Fixed issue where `[su_meteoprog_informer]` without an ID rendered an empty informer block when no default informer was set.
* ⚙️ Improved data layer generation — prevents empty IDs in `<head>` output.
* 🧹 Minor internal code cleanup for Shortcodes Ultimate integration.

## [v1.0.1] - 2025-10-27
* 🧩 Removed filtered 5-star reviews link (WP.org guideline compliance)
* ⚙️ Updated "Requires PHP" to 7.0 in plugin header and readme
* 🗒️ Added note explaining required `su_` prefix for Shortcodes Ultimate integration
* 🕹️ Maintains backward compatibility with PHP 5.6 (legacy mode)

## [v1.0] - 2025-10-19
### Added
- Initial public release.
- ✅ Gutenberg block (**Meteoprog Weather Widget**) with REST API integration.
- ✅ Elementor widget with live informer selector.
- ✅ Shortcodes Ultimate integration with dropdown and preview.
- ✅ Legacy widget for WP 4.9–5.7.
- ✅ Shortcodes and placeholders support.
- ✅ Admin page with API key, informer list, and default widget option.
- ✅ WP-CLI integration (set/get key, clear cache, etc.).
- ✅ REST API endpoint `/wp-json/meteoprog/v1/informers`.
- ✅ Responsive admin UI.
- ✅ Full backward compatibility (WordPress 4.9+, PHP 5.6+; tested up to PHP 8.4).
- ✅ Automated CI/CD: PHPUnit, PHPCS (WPCS), Plugin Check, multi-version testing.

---

[v1.0.3]: https://github.com/meteoprog/meteoprog-weather-informers/releases/tag/v1.0.3
[v1.0.2]: https://github.com/meteoprog/meteoprog-weather-informers/releases/tag/v1.0.2
[v1.0.1]: https://github.com/meteoprog/meteoprog-weather-informers/releases/tag/v1.0.1
[v1.0]: https://github.com/meteoprog/meteoprog-weather-informers/releases/tag/v1.0
