=== Meteoprog Weather Widget ===
Contributors: meteoprog
Tags: weather, widget, shortcode, block, forecast
Requires at least: 4.9
Tested up to: 6.8
Requires PHP: 7.0
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Embed free Meteoprog weather widgets with Gutenberg, Elementor, Shortcodes Ultimate, REST API, and legacy WP/PHP compatibility.

== Description ==

Meteoprog Weather Widgets allows you to quickly embed **free weather informers (widgets)** from [Meteoprog](https://meteoprog.com).

⚠️ **Important:** This plugin uses a **separate API key for widgets (informers)**.  
It is **NOT the same** as the Meteoprog Weather API key.  
Informer API keys are **always free** and have **no usage limits**.  
You can create new informers here: [https://billing.meteoprog.com/informer](https://billing.meteoprog.com/informer?utm_source=wp-plugin&utm_medium=readme&utm_campaign=meteoprog-weather-widgets).

**Features:**
* Easy setup with free informer API key.
* Widgets are fully customizable via Meteoprog dashboard.
* Supports Gutenberg block: **Meteoprog Weather Widget** (in the *Widgets* category).
* ✅ Legacy Widget (WordPress 4.9–5.7) for classic widget screen.
* Supports shortcodes:
  - `[meteoprog_informer id="YOUR_INFORMER_ID"]`
  - `[meteoprog_informer]` (uses default widget)
* Supports placeholders in content:
  - `{meteoprog_informer_YOUR_INFORMER_ID}`
  - `{meteoprog_informer}` (uses default widget)
* Default widget option: set once in admin, use everywhere.
* Admin preview with "Copy" buttons.
* Responsive admin interface (mobile-friendly).
* WP-CLI support for managing keys, defaults, and cache.
* ✅ Legacy support: works on WordPress 4.9+ and PHP 5.6+ (also tested on PHP 8.3/8.4).

**Integrations:**
* ✅ **Gutenberg Block** — modern block with dynamic informer selector (REST API).
* ✅ **Shortcodes Ultimate** — custom integration with dropdown and admin preview.
* ✅ **Elementor** — native widget in the Elementor editor.
* ✅ **Legacy Widget** — for WP 4.9–5.7 classic widget screens.
* ✅ **Shortcodes & Placeholders** — for flexible embedding in content and templates.
* ✅ **WP-CLI** — manage API keys, defaults, and cache from the command line.
* ✅ **REST API** — exposes `/wp-json/meteoprog/v1/informers` (secured by `edit_posts`) for block integration.

Widgets are **free, unlimited, and without API limits**.

== Installation ==

1. Upload plugin to `/wp-content/plugins/` or install via Plugins → Add New.
2. Activate the plugin in the WordPress admin.
3. Go to *Settings → Meteoprog Widgets*.
4. Enter your **Informer API key** from [billing.meteoprog.com](https://billing.meteoprog.com/?utm_source=wp-plugin&utm_medium=readme&utm_campaign=meteoprog-weather-widgets).
5. Refresh the informer list.
6. Insert widgets with Gutenberg block, Elementor, Shortcodes Ultimate, Legacy Widget, shortcodes, or placeholders.

== Frequently Asked Questions ==

= Where do I get the API key? =
You can generate a **widget (informer) API key** at [billing.meteoprog.com/informer](https://billing.meteoprog.com/informer?utm_source=wp-plugin&utm_medium=readme&utm_campaign=meteoprog-weather-widgets).

= Is this the same key as the Meteoprog Weather API? =
**No.**  
Informer API keys are **different**.  
The Meteoprog Weather API requires a separate subscription.  
Informer API keys are **free, unlimited, and without limits.**

= What shortcodes are available? =
You can use the following shortcodes:

* `[meteoprog_informer id="YOUR_INFORMER_ID"]`  
  → Embed a specific informer by its ID.

* `[meteoprog_informer]`  
  → Embed the **default informer** (set in plugin settings).

You can also use placeholders directly in post/page content:

* `{meteoprog_informer_YOUR_INFORMER_ID}`  
  → Replaced with the widget matching the ID.

* `{meteoprog_informer}`  
  → Replaced with the **default informer**.

= Does it support the old "Legacy Widgets" system? =
Yes. For older WordPress versions (4.9–5.7) the plugin registers a **Legacy Widget** that you can add via *Appearance → Widgets*.  
On modern WordPress (5.8+) we recommend using the **Gutenberg block (Meteoprog Weather Widget)**, but the Legacy Widget remains for maximum backward compatibility.

= What PHP versions are supported? =
The plugin works with PHP versions from 5.6 up to 8.3 inclusive.

= Can I use multiple widgets? =
Yes. Create multiple informers at [billing.meteoprog.com/informer](https://billing.meteoprog.com/informer?utm_source=wp-plugin&utm_medium=readme&utm_campaign=meteoprog-weather-widgets), then insert them with their IDs.

= What if I want one default widget everywhere? =
You can set a "Default Widget" in plugin settings. Then just use `[meteoprog_informer]` or `{meteoprog_informer}` without ID.

= Can I use it in the Gutenberg editor? =
Yes. Use the block **Meteoprog Weather Widget** from the *Widgets* category.

= Does it support Elementor? =
Yes. The plugin includes a **native Elementor widget** that you can insert from the Elementor panel.

= Does it support Shortcodes Ultimate? =
Yes. The plugin integrates with **Shortcodes Ultimate**, adding a custom *Meteoprog Weather* shortcode with a dropdown and live preview.

= Does it support WP-CLI? =
Yes. Example commands:

- `wp meteoprog-weather-informers set-key <key>` — set API key  
- `wp meteoprog-weather-informers get-key` — show current API key (masked)  
- `wp meteoprog-weather-informers set-default <id>` — set default informer  
- `wp meteoprog-weather-informers get-default` — show default informer  
- `wp meteoprog-weather-informers refresh` — clear cache and reload informers  
- `wp meteoprog-weather-informers clear-cache` — clear cache only  

= Will the informer slow down my site? =
**No.**  

The plugin first enqueues a **local script** `loader-fallback.js` from your WordPress site.  
That script then **asynchronously** loads the actual `loader.js` from the Meteoprog CDN.  

This approach is required by [WordPress.org plugin guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/#7-plugins-may-not-track-users-without-their-consent), and ensures that:

* No external scripts are loaded during the initial HTML render.
* Informers are added asynchronously and **do not block rendering**.
* Core Web Vitals and page performance are unaffected.
* The widget is embedded **after the main content has loaded**, similar to YouTube or Twitter embeds.

✅ As a result, informers **do not slow down your site** and work even on caching/CDN setups without issues.


= Development & Testing =

The source code is available on [GitHub](https://github.com/meteoprog/meteoprog-weather-widgets).

This plugin is developed in the open and tested automatically via Travis CI on GitHub.  
The test matrix covers multiple WordPress (4.9–6.8+) and PHP (5.6–8.4) versions to ensure broad compatibility and legacy support.

We welcome issues and pull requests on GitHub.



== Privacy ==
This plugin itself does not collect or store any personal data. However, when the widget is displayed on the frontend, visitors’ browsers load the widget script from the Meteoprog CDN, which receives standard request information (IP address, User-Agent, Referrer).

The CDN may also set technical cookies required for content delivery or security. These cookies are managed by Meteoprog and are subject to their privacy policy.

This plugin adds a suggested section to WordPress's default Privacy Policy page, explaining what data is transmitted when widgets are displayed.

== Links ==
* [Meteoprog Homepage](https://meteoprog.com) — main weather portal
* [Meteoprog Informer Dashboard](https://billing.meteoprog.com/informer) — create and manage your free informers
* [GitHub repository](https://github.com/meteoprog/meteoprog-weather-widgets)


== External Services ==

This plugin connects to the Meteoprog services to display widgets.

1. https://billing.meteoprog.com — used by the plugin to fetch your informer list via a secure API request (Authorization header with your informer API key and site domain).

2. https://cdn.meteoprog.net — the visitor’s browser loads a small JavaScript file from the Meteoprog CDN to render the widgets. As with any CDN, the visitor’s IP address and browser information are transmitted as part of the HTTPS request. This is standard browser behavior.

No personal data is collected or stored by the plugin itself.
   

== Screenshots ==

1. Main settings screen: add your API key, create and preview weather informers.
2. Plugin interface in WordPress admin with shortcode and placeholder examples.


== Changelog ==

= 1.0.2 =
* 🐞 Fixed issue where `[su_meteoprog_informer]` without an ID rendered an empty informer block when no default informer was set.
* ⚙️ Improved data layer generation — prevents empty IDs in `<head>` output.
* 🧹 Minor internal code cleanup for Shortcodes Ultimate integration.

= 1.0.1 =
* 🧩 Removed filtered 5-star reviews link (WP.org guideline compliance)
* ⚙️ Updated "Requires PHP" to 7.0 in plugin header and readme
* 🗒️ Added note explaining required `su_` prefix for Shortcodes Ultimate integration
* 🕹️ Maintains backward compatibility with PHP 5.6 (legacy mode)

= 1.0 =
* Initial release.
* ✅ Gutenberg block (**Meteoprog Weather Widget**) with REST API integration.
* ✅ Shortcodes Ultimate integration with dropdown and preview.
* ✅ Elementor widget integration.
* ✅ Legacy Widget, shortcode, and placeholder support.
* ✅ Default widget option.
* ✅ Responsive admin UI.
* ✅ WP-CLI integration (optional).
* ✅ REST API endpoint `/wp-json/meteoprog/v1/informers`.
* ✅ Legacy WordPress/PHP support (4.9+, PHP 5.6+; tested on PHP 8.3/8.4).

== Upgrade Notice ==

= 1.0.2 =
🐞 Fixed issue with empty `[su_meteoprog_informer]` blocks when no default informer ID is configured.

= 1.0.1 =
🧩 Minor update for WordPress.org review compliance and metadata corrections.

= 1.0 =
First stable release.


