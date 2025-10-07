/* global window, wp */
/*!
 * MeteoprogWeatherInformers — Block Editor script
 * (c) 2003-2025 Meteoprog. All rights reserved.
 * https://meteoprog.com
 *
 * This script registers the Gutenberg block for the Meteoprog Weather Informer,
 * renders the block UI in the editor, and provides real-time informer preview.
 */

(function () {
	// Guard: Gutenberg availability (for WP < 5.0).
	if (typeof wp === 'undefined' || typeof wp.blocks === 'undefined' || typeof wp.blocks.registerBlockType !== 'function') {
		return;
	}

	const { registerBlockType } = wp.blocks;
	const { SelectControl, Spinner, Notice } = wp.components;
	const { useState, useEffect, createElement: el, Fragment } = wp.element;
	const { __ } = wp.i18n;

	// Helpers: URL host + ID masking (parity with PHP)
	function getHostFromUrl(url) {
		if (!url) return '';
		try {
			return new URL(url).hostname.toLowerCase();
		} catch (e) {
			return String(url)
				.replace(/^https?:\/\//i, '')
				.replace(/\/.*$/, '')
				.toLowerCase();
		}
	}
	function maskId(id) {
		if (!id || id.length < 16) return id || '';
		return id.substring(0, 8) + '-***-' + id.substring(id.length - 8);
	}

	// Preview box component
	function PreviewBox({ informerId, domainMatch, showDefaultMsg }) {
		const boxStyle = {
			border: '1px solid #dcdcde',
			borderRadius: '6px',
			background: '#fff',
			boxShadow: '0 1px 2px rgba(0,0,0,0.05)',
			padding: '16px',
			marginTop: '10px',
			textAlign: 'center',
			minHeight: '120px',
			display: 'flex',
			flexDirection: 'column',
			alignItems: 'center',
			justifyContent: 'center'
		};

		// Case: no effective ID at all → default static preview
		if (showDefaultMsg) {
			return el('div', { style: boxStyle }, [
				el('div', { style: { marginBottom: '6px', fontWeight: 'bold' } },
					__('No informer selected — default preview', 'meteoprog-weather-informers')
				),
				el('div', { style: { fontSize: '12px', color: '#666' } },
					__('Preview is visible only on frontend', 'meteoprog-weather-informers')
				)
			]);
		}

		// Normal preview (effective ID known)
		const statusText  = domainMatch ? __('Domain OK', 'meteoprog-weather-informers') : __('Domain mismatch', 'meteoprog-weather-informers');
		const statusColor = domainMatch ? '#46b450' : '#dc3232';

		return el('div', { style: boxStyle }, [
			// Header with inline SVG icon
			el('div', {
				style: {
					display: 'flex',
					alignItems: 'center',
					justifyContent: 'center',
					gap: '8px',
					marginBottom: '10px'
				}
			}, [
				el('span', {
					style: {
						display: 'inline-block',
						width: '1em',
						height: '1em',
						background: "url('data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"%23007acc\"><path d=\"M6 19a4 4 0 0 1 0-8 5.5 5.5 0 0 1 10.74-1.62A4.5 4.5 0 1 1 18 19H6z\"/></svg>') no-repeat center",
						backgroundSize: '1em 1em'
					}
				}),
				el('strong', null, __('Meteoprog Weather Informer', 'meteoprog-weather-informers'))
			]),
			// Effective informer ID (monospace)
			el('div', {
				style: { fontFamily: 'monospace', fontSize: '13px', color: '#555', marginBottom: '6px' }
			}, informerId),
			// Info line
			el('div', {
				style: { fontSize: '12px', color: '#666', marginBottom: '10px' }
			}, __('Preview is visible only on frontend', 'meteoprog-weather-informers')),
			// Domain badge
			el('span', {
				style: {
					display: 'inline-block',
					padding: '4px 8px',
					borderRadius: '3px',
					fontSize: '12px',
					fontWeight: '600',
					background: statusColor,
					color: '#fff'
				}
			}, statusText)
		]);
	}

	// Default informer from PHP (localized in enqueue_editor).
	const defaultInformerId = (typeof MeteoprogSettings !== 'undefined' && MeteoprogSettings.defaultInformerId)
		? MeteoprogSettings.defaultInformerId
		: '';

	registerBlockType('meteoprog/informer', {
		title: __('Meteoprog Weather Widget', 'meteoprog-weather-informers'),
		icon: 'cloud',
		category: 'widgets',
		attributes: {
			// Important: keep attribute default empty → select shows "Default"
			id: { type: 'string', default: '' }
		},

		edit: (props) => {
			const { attributes, setAttributes } = props;
			const [informers, setInformers] = useState(null);
			const [error, setError] = useState(null);

			// Load informers list for dropdown
			useEffect(() => {
				wp.apiFetch({ path: '/meteoprog/v1/informers' })
					.then(setInformers)
					.catch((err) => {
						// Graceful fallback: show empty list + error notice
						console.error('Failed to load informers', err);
						setError(__('Failed to load informers. Check your API key.', 'meteoprog-weather-informers'));
						setInformers([]);
					});
			}, []);

			if (error) {
				return el(Notice, { status: 'error', isDismissible: false }, error);
			}

			if (!informers) {
				return el('div', { style: { textAlign: 'center', padding: '20px' } }, el(Spinner));
			}

			// Build select options
			const siteHost = window.location.hostname.toLowerCase();
			const options = [
				{ label: __('Default widget (from settings)', 'meteoprog-weather-informers'), value: '' },
				...informers.map(i => {
					const domainHost = getHostFromUrl(i.domain);
					const match = domainHost === siteHost;
					return {
						label: `${i.domain || __('No domain', 'meteoprog-weather-informers')} — ${maskId(i.informer_id)} [${match ? __('OK', 'meteoprog-weather-informers') : __('Domain mismatch', 'meteoprog-weather-informers')}]`,
						value: i.informer_id
					};
				})
			];

			// Effective informer:
			// - when select is "Default" (''), use defaultInformerId (if present)
			const effectiveId = attributes.id || defaultInformerId;

			// Domain match computed against effective informer
			const selectedInformer = informers.find(j => j.informer_id === effectiveId);
			const selectedMatch = selectedInformer
				? (getHostFromUrl(selectedInformer.domain) === siteHost)
				: false;

			// Show "default preview" box only when there is truly no ID at all
			const showDefaultMsg = (attributes.id === '' && !defaultInformerId);

			return el(Fragment, null, [
				el(SelectControl, {
					label: __('Select Meteoprog Weather Informer', 'meteoprog-weather-informers'),
					value: attributes.id,
					options: options,
					onChange: (val) => setAttributes({ id: val })
				}),
				el(PreviewBox, {
					informerId: effectiveId,
					domainMatch: selectedMatch,
					showDefaultMsg
				})
			]);
		},

		// Dynamic render — server side via render_callback
		save: () => null
	});
})();
