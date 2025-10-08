/* global window, document */
/*!
 * MeteoprogWeatherInformers
 * (c) 2003-2025 Meteoprog. All rights reserved.
 * https://meteoprog.com
 *
 * Meteoprog loader wrapper
 *
 * This local script is enqueued by WordPress.
 * It dynamically inserts the external loader.js from cdn.meteoprog.net.
 *
 * Why is this needed?
 * - WordPress.org review guidelines recommend enqueuing ONLY local scripts.
 * - External scripts should be loaded via async/dynamic injection, not enqueued directly.
 * - This file acts as a safe local wrapper.
 *
 * Behavior:
 * - Creates a <script> element with async=true.
 * - Points to the official Meteoprog loader.js (CDN).
 * - Appends it into <head>, so it loads non-blocking.
 *
 * Result:
 * - No performance impact: async loading.
 * - No conflicts with other plugins or themes.
 * - Clear separation between local assets and external dependencies.
 */

(function() {
    if (typeof MeteoprogLoaderConfig === 'undefined') return;
    var s = document.createElement('script');
    s.src = MeteoprogLoaderConfig.url;
    s.async = true;
    document.head.appendChild(s);
})();