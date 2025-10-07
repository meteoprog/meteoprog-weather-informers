/* global window, document, wp */
/*!
 * MeteoprogWeatherInformers — Admin scripts
 * (c) 2003-2025 Meteoprog. All rights reserved.
 * https://meteoprog.com
 *
 * This file contains JavaScript used exclusively on the WordPress Admin pages.
 * It handles copy buttons, informer previews, and collapsible sections.
 */

var __ = function( s ) { return s; };
if (typeof wp !== 'undefined' && wp.i18n && typeof wp.i18n.__ === 'function') {
    __ = wp.i18n.__;
}

document.addEventListener('click', function(e) {

  // === COPY BUTTON ===
  if (e.target.classList.contains('meteoprog-copy')) {
    e.preventDefault();
    var val = e.target.getAttribute('data-copy');
    navigator.clipboard.writeText(val).then(function() {
      e.target.classList.add('copied');
      e.target.textContent = '✔ ' + __('Copied', 'meteoprog-weather-informers');
      setTimeout(() => {
        e.target.classList.remove('copied');
        e.target.textContent = '📋 ' + __('Copy', 'meteoprog-weather-informers');
      }, 1500);
    });
  }

  // === PREVIEW BUTTON ===
  if (e.target.classList.contains('meteoprog-preview')) {
    e.preventDefault();
    var id = e.target.getAttribute('data-id');
    var box = document.getElementById('meteoprog-preview-' + id);

    if (box.style.display === 'none' || box.style.display === '') {
      // Show loading state
      box.style.display = 'block';
      box.innerHTML = `<div class="meteoprog-loading">⏳ ${__('Loading...', 'meteoprog-weather-informers')}</div>
                       <div id="meteoprogData_${id}" style="visibility:hidden;"></div>`;

      // Inject data push script
      var s1 = document.createElement('script');
      s1.text = `
        window.meteoprogDataLayer = window.meteoprogDataLayer || [];
        window.meteoprogDataLayer.push({ id: "${id}" });
      `;
      box.appendChild(s1);

      // Load remote loader.js
      var s2 = document.createElement('script');
      s2.src = "https://cdn.meteoprog.net/informerv4/1/loader.js";
      s2.async = true;

      s2.onload = function() {
        var loadingEl = box.querySelector('.meteoprog-loading');
        if (loadingEl) loadingEl.remove();
        var dataEl = document.getElementById(`meteoprogData_${id}`);
        if (dataEl) dataEl.style.visibility = 'visible';
      };

      box.appendChild(s2);

      e.target.textContent = '✖ ' + __('Close', 'meteoprog-weather-informers');
    } else {
      // Close preview
      box.style.display = 'none';
      box.innerHTML = '';
      e.target.textContent = '👁 ' + __('Preview', 'meteoprog-weather-informers');
    }
  }

  // === COLLAPSIBLE HEADER ===
  if (e.target.classList.contains('collapsible-toggle') || e.target.closest('.collapsible-toggle')) {
    const header = e.target.closest('.collapsible-toggle');
    const content = header.nextElementSibling;
    const icon = header.querySelector('.toggle-icon');

    const isExpanded = header.getAttribute('aria-expanded') === 'true';

    if (isExpanded) {
      // Collapse section
      header.setAttribute('aria-expanded', 'false');
      content.setAttribute('aria-hidden', 'true');
      icon.classList.remove('dashicons-arrow-up-alt2');
      icon.classList.add('dashicons-arrow-down-alt2');
    } else {
      // Expand section
      header.setAttribute('aria-expanded', 'true');
      content.setAttribute('aria-hidden', 'false');
      icon.classList.remove('dashicons-arrow-down-alt2');
      icon.classList.add('dashicons-arrow-up-alt2');
    }
  }

});