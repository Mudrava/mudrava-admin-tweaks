/**
 * Admin Tweaks — module JavaScript.
 *
 * Handles: live search with highlight, per-tab AJAX save,
 * media pickers, force-logout, test-email.
 *
 * @package Mudrava\Kit\Modules\AdminTweaks
 */
( function () {
	'use strict';

	const cfg = () => window.mudravaAdminTweaksAdmin || window.mudravaKitAdmin || window.mdkitAdmin || {};

	/* ==============================================================
	   Live Search
	   ============================================================== */

	function initSearch() {
		const input    = document.getElementById( 'mdkit-at-search' );
		const tabNav   = document.querySelector( '.mdkit-tabs' );
		const panels   = document.querySelectorAll( '.mdkit-tab-panel' );
		const noResult = document.getElementById( 'mdkit-at-no-results' );

		if ( ! input ) return;

		/* Remember the active tab so we can restore it when search is cleared. */
		let savedTab = 'general';
		if ( tabNav ) {
			const active = tabNav.querySelector( '.mdkit-tabs__item--active' );
			if ( active && active.dataset.tab ) savedTab = active.dataset.tab;
		}

		input.addEventListener( 'input', function () {
			const q = this.value.trim().toLowerCase();

			/* ---- clear state ---- */
			clearHighlights();

			if ( ! q ) {
				/* Restore normal tabs. */
				if ( tabNav ) tabNav.style.display = '';
				panels.forEach( p => { p.hidden = p.id !== 'mdkit-panel-' + savedTab; } );
				document.querySelectorAll( '.mdkit-at-item' ).forEach( i => i.classList.remove( 'mdkit-at-hidden' ) );
				document.querySelectorAll( '.mdkit-card' ).forEach( c => { c.style.display = ''; } );
				document.querySelectorAll( '.mdkit-at-save' ).forEach( b => {
					b.style.display = '';
					b.parentElement.style.display = '';
				} );
				if ( noResult ) noResult.hidden = true;
				return;
			}

			/* Search mode: show all panels, hide tabs. */
			if ( tabNav ) tabNav.style.display = 'none';
			panels.forEach( p => { p.hidden = false; } );

			let found = 0;
			document.querySelectorAll( '.mdkit-at-item' ).forEach( item => {
				const keywords = ( item.dataset.keywords || '' ).toLowerCase();
				const text     = item.textContent.toLowerCase();
				const match    = keywords.includes( q ) || text.includes( q );

				item.classList.toggle( 'mdkit-at-hidden', ! match );
				if ( match ) {
					found++;
					highlightText( item, q );
				}
			} );

			/* Hide empty cards. */
			document.querySelectorAll( '.mdkit-card' ).forEach( card => {
				const visible = card.querySelectorAll( '.mdkit-at-item:not(.mdkit-at-hidden)' );
				card.style.display = visible.length ? '' : 'none';
			} );

			/* Show save buttons only for panels that have visible results. */
			panels.forEach( panel => {
				const hasVisible = panel.querySelector( '.mdkit-at-item:not(.mdkit-at-hidden)' );
				const saveBtn    = panel.querySelector( '.mdkit-at-save' );
				if ( saveBtn ) {
					saveBtn.style.display = hasVisible ? '' : 'none';
					if ( saveBtn.parentElement ) saveBtn.parentElement.style.display = hasVisible ? '' : 'none';
				}
			} );

			if ( noResult ) noResult.hidden = found > 0;
		} );

		/* Save current tab when user switches tabs (so restore works). */
		if ( tabNav ) {
			tabNav.addEventListener( 'click', function ( e ) {
				const t = e.target.closest( '.mdkit-tabs__item' );
				if ( t && t.dataset.tab ) savedTab = t.dataset.tab;
			} );
		}
	}

	/* ---- Highlight helpers ---- */

	function escapeRegex( s ) {
		return s.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' );
	}

	function clearHighlights() {
		document.querySelectorAll( 'mark.mdkit-at-hl' ).forEach( m => {
			const parent = m.parentNode;
			m.replaceWith( m.textContent );
			if ( parent ) parent.normalize();
		} );
	}

	function highlightText( container, query ) {
		const regex  = new RegExp( '(' + escapeRegex( query ) + ')', 'gi' );
		const walker = document.createTreeWalker( container, NodeFilter.SHOW_TEXT, {
			acceptNode( node ) {
				const tag = node.parentElement?.tagName;
				if ( ! tag ) return NodeFilter.FILTER_REJECT;
				if ( [ 'INPUT', 'SELECT', 'TEXTAREA', 'SCRIPT', 'STYLE' ].includes( tag ) ) {
					return NodeFilter.FILTER_REJECT;
				}
				if ( node.parentElement.classList.contains( 'mdkit-at-hl' ) ) {
					return NodeFilter.FILTER_REJECT;
				}
				return node.textContent.trim() ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
			},
		} );

		const nodes = [];
		while ( walker.nextNode() ) nodes.push( walker.currentNode );

		nodes.forEach( textNode => {
			if ( ! regex.test( textNode.textContent ) ) return;
			regex.lastIndex = 0;
			const wrapper = document.createElement( 'span' );
			wrapper.innerHTML = textNode.textContent
				.replace( /&/g, '&amp;' )
				.replace( /</g, '&lt;' )
				.replace( regex, '<mark class="mdkit-at-hl">$1</mark>' );
			textNode.replaceWith( wrapper );
		} );
	}

	/* ==============================================================
	   Per-tab AJAX Save
	   ============================================================== */

	function initSave() {
		document.addEventListener( 'click', function ( e ) {
			const btn = e.target.closest( '.mdkit-at-save' );
			if ( ! btn ) return;

			const tab   = btn.dataset.tab;
			const panel = document.getElementById( 'mdkit-panel-' + tab );
			if ( ! panel ) return;

			const data = new FormData();
			data.append( 'action', 'mudrava_mt_admin_tweaks_save' );
			data.append( 'nonce', cfg().nonce );
			data.append( 'tab', tab );

			/* Toggles (checkboxes with mdkit-toggle__input). */
			panel.querySelectorAll( '.mdkit-toggle__input' ).forEach( input => {
				data.append( input.name, input.checked ? '1' : '0' );
			} );

			/* Regular checkboxes (mdkit-checkbox__input). */
			panel.querySelectorAll( '.mdkit-checkbox__input' ).forEach( cb => {
				data.append( cb.name, cb.checked ? '1' : '0' );
			} );

			/* Text / number / url / color / email inputs. */
			panel.querySelectorAll( 'input[type="text"], input[type="number"], input[type="url"], input[type="color"], input[type="email"]' ).forEach( input => {
				if ( ! input.classList.contains( 'mdkit-toggle__input' ) && ! input.classList.contains( 'mdkit-checkbox__input' ) ) {
					data.append( input.name, input.value );
				}
			} );

			/* Hidden inputs (media pickers). */
			panel.querySelectorAll( 'input[type="hidden"]' ).forEach( input => {
				if ( input.name && input.name !== '_wpnonce' && input.name !== 'tab' ) {
					data.append( input.name, input.value );
				}
			} );

			/* Selects. */
			panel.querySelectorAll( 'select' ).forEach( sel => {
				data.append( sel.name, sel.value );
			} );

			/* Textareas. */
			panel.querySelectorAll( 'textarea' ).forEach( ta => {
				data.append( ta.name, ta.value );
			} );

			const origText = btn.textContent;
			btn.disabled   = true;
			btn.textContent = cfg().i18n?.saving || 'Saving…';

			fetch( cfg().ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data } )
				.then( r => r.json() )
				.then( res => {
					btn.disabled    = false;
					btn.textContent = origText;
					if ( res.success ) location.reload();
					else alert( res.data?.message || 'Error' );
				} )
				.catch( () => {
					btn.disabled    = false;
					btn.textContent = origText;
					alert( cfg().i18n?.error || 'Error' );
				} );
		} );
	}

	/* ==============================================================
	   Media Pickers (Branding tab)
	   ============================================================== */

	function initMediaPickers() {
		setupPicker( 'mdkit-at-choose-footer-logo', 'mdkit-at-remove-footer-logo', 'mdkit-at-footer-logo-id', 'mdkit-at-footer-logo-preview', 40 );
		setupPicker( 'mdkit-at-choose-bar-logo', 'mdkit-at-remove-bar-logo', 'mdkit-at-bar-logo-id', 'mdkit-at-bar-logo-preview', 30 );
	}

	function setupPicker( chooseId, removeId, inputId, previewId, maxH ) {
		const chooseBtn  = document.getElementById( chooseId );
		const removeBtn  = document.getElementById( removeId );
		const hiddenInput = document.getElementById( inputId );
		const preview     = document.getElementById( previewId );

		if ( ! chooseBtn || ! hiddenInput ) return;

		chooseBtn.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			const frame = wp.media( { title: 'Choose Image', multiple: false, library: { type: 'image' } } );
			frame.on( 'select', function () {
				const a = frame.state().get( 'selection' ).first().toJSON();
				hiddenInput.value = a.id;
				if ( preview ) {
					preview.textContent = '';
					const img = document.createElement( 'img' );
					img.src = a.url;
					img.style.maxHeight = maxH + 'px';
					img.style.borderRadius = '4px';
					preview.appendChild( img );
				}
			} );
			frame.open();
		} );

		if ( removeBtn ) {
			removeBtn.addEventListener( 'click', function () {
				hiddenInput.value = '0';
				if ( preview ) preview.innerHTML = '';
			} );
		}
	}

	/* ==============================================================
	   Force Logout
	   ============================================================== */

	function initForceLogout() {
		const btn = document.getElementById( 'mdkit-at-force-logout' );
		if ( ! btn ) return;

		btn.addEventListener( 'click', function () {
			if ( ! confirm( cfg().i18n?.confirmForceLogout || 'Force logout all other users? They will need to log in again.' ) ) return;

			const origText  = btn.textContent;
			btn.disabled    = true;
			btn.textContent = cfg().i18n?.processing || 'Processing…';

			const data = new FormData();
			data.append( 'action', 'mudrava_mt_admin_tweaks_force_logout' );
			data.append( 'nonce', cfg().nonce );

			fetch( cfg().ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data } )
				.then( r => r.json() )
				.then( res => {
					btn.disabled    = false;
					btn.textContent = origText;
					alert( res.data?.message || ( res.success ? 'Done' : 'Error' ) );
				} )
				.catch( () => {
					btn.disabled    = false;
					btn.textContent = origText;
					alert( cfg().i18n?.error || 'Error' );
				} );
		} );
	}

	/* ==============================================================
	   Test Email
	   ============================================================== */

	/* ---- Safe text renderer for AJAX feedback (no innerHTML injection). ---- */

	function setMessage( el, text, colorVar ) {
		if ( ! el ) return;
		el.textContent = '';
		const span = document.createElement( 'span' );
		span.style.color = 'var(' + colorVar + ')';
		span.textContent = text;
		el.appendChild( span );
	}

	function initTestEmail() {
		const btn    = document.getElementById( 'mdkit-at-send-test' );
		const result = document.getElementById( 'mdkit-at-test-result' );
		if ( ! btn ) return;

		btn.addEventListener( 'click', function () {
			const emailInput = document.getElementById( 'mdkit-input-test_email' );
			const to = emailInput ? emailInput.value.trim() : '';

			if ( ! to ) {
				setMessage( result, cfg().i18n?.enterEmail || 'Please enter an email address.', '--mdkit-color-danger' );
				return;
			}

			const origText  = btn.textContent;
			btn.disabled    = true;
			btn.textContent = cfg().i18n?.sending || 'Sending…';

			const data = new FormData();
			data.append( 'action', 'mudrava_mt_admin_tweaks_send_test' );
			data.append( 'nonce', cfg().nonce );
			data.append( 'test_email', to );

			fetch( cfg().ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data } )
				.then( r => r.json() )
				.then( res => {
					btn.disabled    = false;
					btn.textContent = origText;
					setMessage(
						result,
						res.data?.message || '',
						res.success ? '--mdkit-color-success' : '--mdkit-color-danger'
					);
				} )
				.catch( () => {
					btn.disabled    = false;
					btn.textContent = origText;
					setMessage( result, cfg().i18n?.networkError || 'Network error.', '--mdkit-color-danger' );
				} );
		} );
	}

	/* ==============================================================
	   Init
	   ============================================================== */

	document.addEventListener( 'DOMContentLoaded', function () {
		initSearch();
		initSave();
		initMediaPickers();
		initForceLogout();
		initTestEmail();
	} );
} )();
