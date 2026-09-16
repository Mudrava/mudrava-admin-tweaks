/**
 * MUDRAVA Kit — Admin JavaScript
 *
 * Vanilla JS — no jQuery dependency.
 * Handles: module toggles (AJAX in-place), tab navigation, hub search,
 * category filter, favorites, confirm dialogs, copy-to-clipboard,
 * alert dismissal, data tables.
 *
 * @package Mudrava\Kit
 */

( function () {
	'use strict';

	/** @type {{ ajaxUrl: string, nonce: string, i18n: Object }} */
	const config = window.mudravaAdminTweaksAdmin || window.mudravaKitAdmin || window.mdkitAdmin || {};

	/* ==============================================================
	   Module Toggle (enable/disable via AJAX — in-place)
	   ============================================================== */

	function initModuleToggles() {
		document.addEventListener( 'change', function ( e ) {
			const input = e.target.closest( '.mdkit-toggle__input' );
			if ( ! input ) return;

			// Find parent: card or table row.
			const card = input.closest( '.mdkit-module-card' );
			const row  = input.closest( '.mdkit-hub-table__row' );
			const el   = card || row;
			if ( ! el ) return;

			const moduleId = el.dataset.moduleId;
			if ( ! moduleId ) return;

			const enable = input.checked;

			// Disable toggle while processing.
			input.disabled = true;

			const formData = new FormData();
			formData.append( 'action', 'mudrava_mt_toggle_module' );
			formData.append( 'nonce', config.nonce );
			formData.append( 'module_id', moduleId );
			formData.append( 'enable', enable ? '1' : '0' );

			fetch( config.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData,
			} )
				.then( ( res ) => res.json() )
				.then( ( data ) => {
					input.disabled = false;

					if ( data.success ) {
						updateModuleState( moduleId, enable, data.data?.settings_url || '' );
						updateEnabledPillCount();
					} else {
						alert( data.data?.message || config.i18n?.error || 'Error' );
						input.checked = ! enable;
					}
				} )
				.catch( () => {
					alert( config.i18n?.error || 'Error' );
					input.checked = ! enable;
					input.disabled = false;
				} );
		} );
	}

	/**
	 * Update ALL representations of a module (cards + table rows) in-place.
	 */
	function updateModuleState( moduleId, isEnabled, settingsUrl ) {
		// Update cards.
		document.querySelectorAll( '.mdkit-module-card[data-module-id="' + moduleId + '"]' ).forEach( ( card ) => {
			updateCardState( card, isEnabled, settingsUrl );
		} );

		// Update table rows.
		document.querySelectorAll( '.mdkit-hub-table__row[data-module-id="' + moduleId + '"]' ).forEach( ( row ) => {
			updateRowState( row, isEnabled, settingsUrl );
		} );
	}

	/**
	 * Update card visual state without page reload.
	 */
	function updateCardState( card, isEnabled, settingsUrl ) {
		card.classList.toggle( 'mdkit-module-card--enabled', isEnabled );
		card.dataset.enabled = isEnabled ? '1' : '0';

		// Badge.
		const badge = card.querySelector( '.mdkit-badge' );
		if ( badge ) {
			badge.className = 'mdkit-badge mdkit-badge--' + ( isEnabled ? 'success' : 'default' );
			badge.textContent = isEnabled
				? ( config.i18n?.enabled || 'Enabled' )
				: ( config.i18n?.disabled || 'Disabled' );
		}

		// Icon container.
		const icon = card.querySelector( '.mdkit-module-card__icon' );
		if ( icon ) {
			// Updating colors is handled by CSS via the parent class.
		}

		// Settings footer.
		let footer = card.querySelector( '.mdkit-module-card__footer' );

		if ( isEnabled && settingsUrl ) {
			const moduleId = card.dataset.moduleId;
			if ( ! footer ) {
				footer = document.createElement( 'div' );
				footer.className = 'mdkit-module-card__footer';
				card.appendChild( footer );
			}
			footer.innerHTML = '<a class="mdkit-btn mdkit-btn--link" href="' + settingsUrl + '">'
				+ '<span class="mdkit-btn__icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg></span>'
				+ '<span class="mdkit-btn__label">' + ( config.i18n?.settings || 'Settings' ) + '</span></a>'
				+ '<button type="button" class="mdkit-standalone-toggle" data-module-id="' + moduleId + '"'
				+ ' title="' + ( config.i18n?.addToMenu || 'Add to admin menu' ) + '">'
				+ '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mdkit-icon"><path d="M12 17v5"/><path d="M9 10.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 5 15.24V16a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 15 10.76V7a1 1 0 0 1 1-1 2 2 0 0 0 0-4H8a2 2 0 0 0 0 4 1 1 0 0 1 1 1z"/></svg>'
				+ '</button>';
			footer.hidden = false;
		} else if ( footer ) {
			footer.remove();
		}

		// Re-apply search highlighting if active.
		const searchInput = document.getElementById( 'mdkit-hub-search' );
		if ( searchInput && searchInput.value.trim() ) {
			applyHubSearch( searchInput.value.trim() );
		}
	}

	/**
	 * Update table row visual state without page reload.
	 */
	function updateRowState( row, isEnabled, settingsUrl ) {
		row.classList.toggle( 'mdkit-hub-table__row--enabled', isEnabled );
		row.dataset.enabled = isEnabled ? '1' : '0';

		// Badge.
		const badge = row.querySelector( '.mdkit-badge' );
		if ( badge ) {
			badge.className = 'mdkit-badge mdkit-badge--' + ( isEnabled ? 'success' : 'default' );
			badge.textContent = isEnabled
				? ( config.i18n?.enabled || 'Enabled' )
				: ( config.i18n?.disabled || 'Disabled' );
		}

		// Icon.
		const icon = row.querySelector( '.mdkit-hub-table__icon' );
		if ( icon ) {
			// Color handled by CSS via parent class.
		}

		// Toggle checkbox sync.
		const toggle = row.querySelector( '.mdkit-toggle__input' );
		if ( toggle ) {
			toggle.checked = isEnabled;
		}

		// Settings link + standalone toggle.
		const actionsWrap = row.querySelector( '.mdkit-hub-table__actions-wrap' );
		if ( actionsWrap ) {
			const moduleId = row.dataset.moduleId;
			let link = actionsWrap.querySelector( '.mdkit-btn--link' );
			let pinBtn = actionsWrap.querySelector( '.mdkit-standalone-toggle' );
			if ( isEnabled && settingsUrl ) {
				if ( ! link ) {
					link = document.createElement( 'a' );
					link.className = 'mdkit-btn mdkit-btn--link mdkit-btn--sm';
					link.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mdkit-icon"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>';
					actionsWrap.appendChild( link );
				}
				link.href = settingsUrl;
				link.hidden = false;

				if ( ! pinBtn ) {
					pinBtn = document.createElement( 'button' );
					pinBtn.type = 'button';
					pinBtn.className = 'mdkit-standalone-toggle mdkit-standalone-toggle--sm';
					pinBtn.dataset.moduleId = moduleId;
					pinBtn.title = config.i18n?.addToMenu || 'Add to admin menu';
					pinBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mdkit-icon"><path d="M12 17v5"/><path d="M9 10.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 5 15.24V16a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 15 10.76V7a1 1 0 0 1 1-1 2 2 0 0 0 0-4H8a2 2 0 0 0 0 4 1 1 0 0 1 1 1z"/></svg>';
					actionsWrap.appendChild( pinBtn );
				}
				pinBtn.hidden = false;
			} else {
				if ( link ) link.remove();
				if ( pinBtn ) pinBtn.remove();
			}
		}
	}

	/* ==============================================================
	   Hub Search (live filtering + highlighting)
	   ============================================================== */

	function initHubSearch() {
		const searchInput = document.getElementById( 'mdkit-hub-search' );
		if ( ! searchInput ) return;

		let debounceTimer = null;

		searchInput.addEventListener( 'input', () => {
			clearTimeout( debounceTimer );
			debounceTimer = setTimeout( () => {
				applyHubSearch( searchInput.value.trim() );
			}, 150 );
		} );

		// / to focus.
		document.addEventListener( 'keydown', ( e ) => {
			if ( e.key === '/' && document.activeElement !== searchInput
				&& ! [ 'INPUT', 'TEXTAREA', 'SELECT' ].includes( document.activeElement?.tagName ) ) {
				e.preventDefault();
				searchInput.focus();
			}
		} );

		// Escape to clear.
		searchInput.addEventListener( 'keydown', ( e ) => {
			if ( e.key === 'Escape' ) {
				searchInput.value = '';
				applyHubSearch( '' );
				searchInput.blur();
			}
		} );
	}

	function applyHubSearch( query ) {
		const gridView  = document.getElementById( 'mdkit-view-grid' );
		const tableView = document.getElementById( 'mdkit-view-table' );
		const emptyEl   = document.querySelector( '.mdkit-hub-empty' );

		// Get active category filter.
		const activePill = document.querySelector( '.mdkit-hub-filters__pill--active' );
		const activeCat  = activePill ? activePill.dataset.category : 'all';

		let visibleCount = 0;

		// ---- Grid view ----
		if ( gridView ) {
			const cards    = gridView.querySelectorAll( '.mdkit-module-card' );
			const sections = gridView.querySelectorAll( '.mdkit-module-category' );

			// Clear previous highlights.
			cards.forEach( ( card ) => {
				card.querySelectorAll( 'mark.mdkit-hl' ).forEach( ( m ) => {
					m.replaceWith( document.createTextNode( m.textContent ) );
				} );
			} );

			if ( ! query ) {
				let gridVisible = 0;
				cards.forEach( ( card ) => {
					const catMatch = isCatMatch( card, activeCat );
					card.hidden = ! catMatch;
					if ( catMatch ) gridVisible++;
					const details = card.querySelector( '.mdkit-module-card__features' );
					if ( details ) details.removeAttribute( 'open' );
				} );
				sections.forEach( ( sec ) => {
					if ( activeCat === 'all' ) {
						sec.hidden = false;
					} else if ( activeCat === 'favorites' || activeCat === 'enabled' ) {
						sec.hidden = sec.querySelectorAll( '.mdkit-module-card:not([hidden])' ).length === 0;
					} else {
						sec.hidden = sec.dataset.catSection !== activeCat;
					}
				} );
				if ( activeCat !== 'all' ) visibleCount += gridVisible;
			} else {
				const lowerQ = query.toLowerCase();
				cards.forEach( ( card ) => {
					const catMatch = isCatMatch( card, activeCat );
					if ( ! catMatch ) { card.hidden = true; return; }

					const match = matchModule( card, lowerQ );
					card.hidden = ! match;

					if ( match ) {
						visibleCount++;
						highlightCard( card, query, lowerQ );
					}
				} );
				sections.forEach( ( sec ) => {
					sec.hidden = sec.querySelectorAll( '.mdkit-module-card:not([hidden])' ).length === 0;
				} );
			}
		}

		// ---- Table view ----
		if ( tableView ) {
			const rows     = tableView.querySelectorAll( '.mdkit-hub-table__row' );
			const sections = tableView.querySelectorAll( '.mdkit-hub-table__section' );

			// Clear previous highlights.
			rows.forEach( ( row ) => {
				row.querySelectorAll( 'mark.mdkit-hl' ).forEach( ( m ) => {
					m.replaceWith( document.createTextNode( m.textContent ) );
				} );
			} );

			if ( ! query ) {
				let tableVisible = 0;
				rows.forEach( ( row ) => {
					const catMatch = isCatMatch( row, activeCat );
					row.hidden = ! catMatch;
					if ( catMatch ) tableVisible++;
					const details = row.querySelector( '.mdkit-hub-table__features' );
					if ( details ) details.removeAttribute( 'open' );
				} );
				sections.forEach( ( sec ) => {
					if ( activeCat === 'all' ) {
						sec.hidden = false;
					} else if ( activeCat === 'favorites' || activeCat === 'enabled' ) {
						sec.hidden = sec.querySelectorAll( '.mdkit-hub-table__row:not([hidden])' ).length === 0;
					} else {
						sec.hidden = sec.dataset.catSection !== activeCat;
					}
				} );
				if ( activeCat !== 'all' ) visibleCount += tableVisible;
			} else {
				const lowerQ = query.toLowerCase();
				rows.forEach( ( row ) => {
					const catMatch = isCatMatch( row, activeCat );
					if ( ! catMatch ) { row.hidden = true; return; }

					const match = matchModule( row, lowerQ );
					row.hidden = ! match;

					if ( match ) {
						visibleCount++;
						highlightRow( row, query, lowerQ );
					}
				} );
				sections.forEach( ( sec ) => {
					const hasVisible = sec.querySelectorAll( '.mdkit-hub-table__row:not([hidden])' ).length > 0;
					sec.hidden = ! hasVisible;
				} );
			}
		}

		if ( emptyEl ) emptyEl.hidden = ( activeCat === 'all' && ! query ) || visibleCount > 0;
	}

	/** Check if a module element matches the active category filter. */
	function isCatMatch( el, activeCat ) {
		if ( activeCat === 'all' ) return true;
		if ( activeCat === 'favorites' ) {
			const star = el.querySelector( '.mdkit-module-card__fav' );
			return star && star.classList.contains( 'mdkit-module-card__fav--active' );
		}
		if ( activeCat === 'enabled' ) {
			return el.dataset.enabled === '1';
		}
		return el.dataset.category === activeCat;
	}

	/** Check if a module element (card or row) matches a search query. */
	function matchModule( el, lowerQ ) {
		const nameEl = el.querySelector( '.mdkit-module-card__title' ) || el.querySelector( '.mdkit-hub-table__name' );
		const descEl = el.querySelector( '.mdkit-module-card__desc' ) || el.querySelector( '.mdkit-hub-table__desc' );
		const tags     = ( el.dataset.tags || '' ).toLowerCase();
		const features = ( el.dataset.features || '' ).toLowerCase();
		const name     = ( nameEl?.textContent || '' ).toLowerCase();
		const descTxt  = ( descEl?.textContent || '' ).toLowerCase();

		return name.includes( lowerQ )
			|| descTxt.includes( lowerQ )
			|| tags.includes( lowerQ )
			|| features.includes( lowerQ );
	}

	/** Highlight search matches inside a card. */
	function highlightCard( card, query, lowerQ ) {
		const title = card.querySelector( '.mdkit-module-card__title' );
		const desc  = card.querySelector( '.mdkit-module-card__desc' );
		highlightText( title, query );
		highlightText( desc, query );

		card.querySelectorAll( '.mdkit-tag' ).forEach( ( tag ) => {
			highlightText( tag, query );
		} );

		const details = card.querySelector( '.mdkit-module-card__features' );
		if ( details ) {
			const items = details.querySelectorAll( '.mdkit-module-card__features-list li' );
			let featureMatch = false;
			items.forEach( ( li ) => {
				if ( li.textContent.toLowerCase().includes( lowerQ ) ) {
					highlightText( li, query );
					featureMatch = true;
				}
			} );
			details.toggleAttribute( 'open', featureMatch );
		}
	}

	/** Highlight search matches inside a table row. */
	function highlightRow( row, query, lowerQ ) {
		const nameEl = row.querySelector( '.mdkit-hub-table__name' );
		const descEl = row.querySelector( '.mdkit-hub-table__desc' );
		highlightText( nameEl, query );
		highlightText( descEl, query );

		row.querySelectorAll( '.mdkit-tag' ).forEach( ( tag ) => {
			highlightText( tag, query );
		} );

		const details = row.querySelector( '.mdkit-hub-table__features' );
		if ( details ) {
			const items = details.querySelectorAll( 'li' );
			let featureMatch = false;
			items.forEach( ( li ) => {
				if ( li.textContent.toLowerCase().includes( lowerQ ) ) {
					highlightText( li, query );
					featureMatch = true;
				}
			} );
			details.toggleAttribute( 'open', featureMatch );
		}
	}

	/** Highlight occurrences of `query` inside a DOM node's text children. */
	function highlightText( node, query ) {
		if ( ! node ) return;

		const walker = document.createTreeWalker( node, NodeFilter.SHOW_TEXT );
		const re = new RegExp( '(' + escapeRegex( query ) + ')', 'gi' );
		const textNodes = [];

		while ( walker.nextNode() ) {
			textNodes.push( walker.currentNode );
		}

		textNodes.forEach( ( tn ) => {
			if ( ! re.test( tn.textContent ) ) return;
			re.lastIndex = 0;

			const frag = document.createDocumentFragment();
			let last = 0;
			let match;

			while ( ( match = re.exec( tn.textContent ) ) !== null ) {
				if ( match.index > last ) {
					frag.appendChild( document.createTextNode( tn.textContent.slice( last, match.index ) ) );
				}
				const mark = document.createElement( 'mark' );
				mark.className = 'mdkit-hl';
				mark.textContent = match[1];
				frag.appendChild( mark );
				last = re.lastIndex;
			}

			if ( last < tn.textContent.length ) {
				frag.appendChild( document.createTextNode( tn.textContent.slice( last ) ) );
			}

			tn.parentNode.replaceChild( frag, tn );
		} );
	}

	function escapeRegex( s ) {
		return s.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' );
	}

	/* ==============================================================
	   Category Filter (pills)
	   ============================================================== */

	function initCategoryFilter() {
		const pills = document.querySelectorAll( '.mdkit-hub-filters__pill' );
		if ( ! pills.length ) return;

		pills.forEach( ( pill ) => {
			pill.addEventListener( 'click', () => {
				pills.forEach( ( p ) => p.classList.remove( 'mdkit-hub-filters__pill--active' ) );
				pill.classList.add( 'mdkit-hub-filters__pill--active' );

				const cat = pill.dataset.category;

				// Update URL hash.
				if ( cat && cat !== 'all' ) {
					history.replaceState( null, '', '#' + cat );
				} else {
					history.replaceState( null, '', window.location.pathname + window.location.search );
				}

				// Re-apply search with new category filter.
				const searchInput = document.getElementById( 'mdkit-hub-search' );
				applyHubSearch( searchInput ? searchInput.value.trim() : '' );
			} );
		} );

		// Restore from URL hash.
		const hash = window.location.hash.replace( '#', '' );
		if ( hash ) {
			const target = document.querySelector( '.mdkit-hub-filters__pill[data-category="' + hash + '"]' );
			if ( target ) {
				target.click();
			}
		}
	}

	/* ==============================================================
	   Favorites (star toggle via AJAX)
	   ============================================================== */

	function initFavorites() {
		document.addEventListener( 'click', function ( e ) {
			const btn = e.target.closest( '.mdkit-module-card__fav' );
			if ( ! btn ) return;

			e.preventDefault();
			e.stopPropagation();

			const moduleId = btn.dataset.moduleId;
			if ( ! moduleId ) return;

			const formData = new FormData();
			formData.append( 'action', 'mudrava_mt_toggle_favorite' );
			formData.append( 'nonce', config.nonce );
			formData.append( 'module_id', moduleId );

			btn.classList.add( 'mdkit-module-card__fav--loading' );

			fetch( config.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData,
			} )
				.then( ( res ) => res.json() )
				.then( ( data ) => {
					btn.classList.remove( 'mdkit-module-card__fav--loading' );

					if ( data.success ) {
						const isFav = data.data.favorite;
						btn.classList.toggle( 'mdkit-module-card__fav--active', isFav );
						btn.title = isFav
							? ( config.i18n?.unfavorite || 'Remove from favorites' )
							: ( config.i18n?.favorite || 'Add to favorites' );

						// Update ALL stars for this module (cards + table rows).
						document.querySelectorAll( '.mdkit-module-card__fav[data-module-id="' + moduleId + '"]' ).forEach( ( b ) => {
							b.classList.toggle( 'mdkit-module-card__fav--active', isFav );
							b.title = btn.title;
						} );

						// Dynamic favorites section in table.
						updateTableFavorites( moduleId, isFav );

						// Update favorites pill count.
						updateFavPillCount();

						// Re-apply filter if favorites pill is active.
						const activePill = document.querySelector( '.mdkit-hub-filters__pill--active' );
						if ( activePill && activePill.dataset.category === 'favorites' ) {
							const searchInput = document.getElementById( 'mdkit-hub-search' );
							applyHubSearch( searchInput ? searchInput.value.trim() : '' );
						}
					}
				} )
				.catch( () => {
					btn.classList.remove( 'mdkit-module-card__fav--loading' );
				} );
		} );
	}

	/* ==============================================================
	   Standalone Admin Menu Toggle (AJAX)
	   ============================================================== */

	function initStandaloneToggles() {
		document.addEventListener( 'click', function ( e ) {
			const btn = e.target.closest( '.mdkit-standalone-toggle' );
			if ( ! btn ) return;

			e.preventDefault();
			e.stopPropagation();

			const moduleId = btn.dataset.moduleId;
			if ( ! moduleId ) return;

			btn.classList.add( 'mdkit-standalone-toggle--loading' );

			const formData = new FormData();
			formData.append( 'action', 'mudrava_mt_toggle_standalone' );
			formData.append( 'nonce', config.nonce );
			formData.append( 'module_id', moduleId );

			fetch( config.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData,
			} )
				.then( ( res ) => res.json() )
				.then( ( data ) => {
					btn.classList.remove( 'mdkit-standalone-toggle--loading' );

					if ( data.success ) {
						const isStandalone = data.data.standalone;

						// Update all standalone buttons for this module.
						document.querySelectorAll( '.mdkit-standalone-toggle[data-module-id="' + moduleId + '"]' ).forEach( ( el ) => {
							el.classList.toggle( 'mdkit-standalone-toggle--active', isStandalone );
							el.title = isStandalone
								? ( config.i18n?.removeFromMenu || 'Remove from admin menu' )
								: ( config.i18n?.addToMenu || 'Add to admin menu' );
						} );

						// Reload page so the WP admin menu reflects the change.
						window.location.reload();
					}
				} )
				.catch( () => {
					btn.classList.remove( 'mdkit-standalone-toggle--loading' );
				} );
		} );
	}

	/* ==============================================================
	   View Toggle (grid ↔ table)
	   ============================================================== */

	function initViewToggle() {
		const btns = document.querySelectorAll( '.mdkit-view-btn' );
		if ( ! btns.length ) return;

		// Restore from localStorage.
		const saved = localStorage.getItem( 'mdkitHubView' );
		if ( saved === 'table' || saved === 'grid' ) {
			switchView( saved );
		}

		btns.forEach( ( btn ) => {
			btn.addEventListener( 'click', () => {
				const view = btn.dataset.view;
				if ( ! view ) return;
				switchView( view );
				localStorage.setItem( 'mdkitHubView', view );
			} );
		} );

		function switchView( view ) {
			const gridEl  = document.getElementById( 'mdkit-view-grid' );
			const tableEl = document.getElementById( 'mdkit-view-table' );
			if ( ! gridEl || ! tableEl ) return;

			gridEl.hidden  = view !== 'grid';
			tableEl.hidden = view !== 'table';

			btns.forEach( ( b ) => {
				b.classList.toggle( 'mdkit-view-btn--active', b.dataset.view === view );
			} );

			// Re-apply search when switching.
			const searchInput = document.getElementById( 'mdkit-hub-search' );
			if ( searchInput && searchInput.value.trim() ) {
				applyHubSearch( searchInput.value.trim() );
			}
		}
	}

	/* ==============================================================
	   Table Sections (collapsible categories)
	   ============================================================== */

	function initTableSections() {
		document.addEventListener( 'click', function ( e ) {
			const toggle = e.target.closest( '.mdkit-hub-table__section-toggle' );
			if ( ! toggle ) return;

			const section = toggle.closest( '.mdkit-hub-table__section' );
			if ( ! section ) return;

			section.classList.toggle( 'mdkit-hub-table__section--collapsed' );
		} );
	}

	/* ==============================================================
	   Dynamic Favorites in Table
	   ============================================================== */

	function updateTableFavorites( moduleId, isFav ) {
		const favSection = document.querySelector( '.mdkit-hub-table__section[data-cat-section="favorites"]' );
		if ( ! favSection ) return;

		// Find existing row in favorites.
		const existingRow = favSection.querySelector( '.mdkit-hub-table__row[data-module-id="' + moduleId + '"]' );
		const emptyRow    = favSection.querySelector( '.mdkit-hub-table__empty-row' );

		if ( isFav && ! existingRow ) {
			// Find the row in category sections (not favorites).
			const sourceRow = document.querySelector(
				'.mdkit-hub-table__section:not([data-cat-section="favorites"]) .mdkit-hub-table__row[data-module-id="' + moduleId + '"]'
			);
			if ( sourceRow ) {
				const clone = sourceRow.cloneNode( true );
				// Remove empty row if present.
				if ( emptyRow ) emptyRow.remove();
				favSection.appendChild( clone );
			}
		} else if ( ! isFav && existingRow ) {
			existingRow.remove();
			// Check if favorites is now empty.
			const remaining = favSection.querySelectorAll( '.mdkit-hub-table__row' );
			if ( remaining.length === 0 ) {
				const empt = document.createElement( 'tr' );
				empt.className = 'mdkit-hub-table__empty-row';
				empt.innerHTML = '<td colspan="6">'
					+ ( config.i18n?.noFavorites || 'No favorite modules yet. Click the star to add.' )
					+ '</td>';
				favSection.appendChild( empt );
			}
		}

		// Update favorites count.
		const countEl = favSection.querySelector( '.mdkit-hub-table__section-count' );
		if ( countEl ) {
			countEl.textContent = favSection.querySelectorAll( '.mdkit-hub-table__row' ).length;
		}
	}

	/** Update the favorites pill count in the category filter bar. */
	function updateFavPillCount() {
		const pill = document.querySelector( '.mdkit-hub-filters__pill[data-category="favorites"]' );
		if ( ! pill ) return;
		const count = document.querySelectorAll( '.mdkit-module-card .mdkit-module-card__fav--active' ).length;
		const countEl = pill.querySelector( '.mdkit-hub-filters__count' );
		if ( countEl ) {
			countEl.textContent = count;
		}
	}

	/** Update the enabled pill count in the category filter bar. */
	function updateEnabledPillCount() {
		const pill = document.querySelector( '.mdkit-hub-filters__pill[data-category="enabled"]' );
		if ( ! pill ) return;
		const count = document.querySelectorAll( '.mdkit-module-card[data-enabled="1"]' ).length;
		const countEl = pill.querySelector( '.mdkit-hub-filters__count' );
		if ( countEl ) {
			countEl.textContent = count;
		}

		// Also update stats bar enabled/disabled counts.
		const totalCards = document.querySelectorAll( '.mdkit-module-card[data-module-id]' ).length;
		const statsEnabled = document.querySelector( '.mdkit-hub-stats__item--success .mdkit-hub-stats__value' );
		const statsMuted = document.querySelector( '.mdkit-hub-stats__item--muted .mdkit-hub-stats__value' );
		if ( statsEnabled ) statsEnabled.textContent = count;
		if ( statsMuted ) statsMuted.textContent = totalCards - count;

		// If enabled filter is active, re-apply to update visibility.
		const activePill = document.querySelector( '.mdkit-hub-filters__pill--active' );
		if ( activePill && activePill.dataset.category === 'enabled' ) {
			const searchInput = document.getElementById( 'mdkit-hub-search' );
			applyHubSearch( searchInput?.value?.trim() || '' );
		}
	}

	/* ==============================================================
	   Tab Navigation
	   ============================================================== */

	function initTabs() {
		document.querySelectorAll( '.mdkit-tabs__item' ).forEach( ( tab ) => {
			tab.addEventListener( 'click', function ( e ) {
				const target = this.dataset.tab;
				if ( ! target ) return;

				e.preventDefault();

				// Update active state.
				this.closest( '.mdkit-tabs' ).querySelectorAll( '.mdkit-tabs__item' ).forEach( ( t ) => {
					t.classList.remove( 'mdkit-tabs__item--active' );
					t.setAttribute( 'aria-selected', 'false' );
				} );

				this.classList.add( 'mdkit-tabs__item--active' );
				this.setAttribute( 'aria-selected', 'true' );

				// Show/hide panels.
				const container = document.querySelector( '.mdkit-tab-panels' );
				if ( container ) {
					container.querySelectorAll( '.mdkit-tab-panel' ).forEach( ( panel ) => {
						panel.hidden = panel.id !== 'mdkit-panel-' + target;
					} );
				}
			} );
		} );
	}

	/* ==============================================================
	   Confirm Dialogs
	   ============================================================== */

	function initConfirmDialogs() {
		// Open dialog: any element with data-mdkit-confirm="<dialog-id>".
		document.addEventListener( 'click', function ( e ) {
			const trigger = e.target.closest( '[data-mdkit-confirm]' );
			if ( ! trigger ) return;

			e.preventDefault();

			const dialogId = trigger.dataset.mdkitConfirm;
			const dialog   = document.getElementById( 'mdkit-dialog-' + dialogId );
			if ( ! dialog ) return;

			// Store the trigger so we can use its href/form after confirm.
			dialog._trigger = trigger;
			dialog.hidden = false;
		} );

		// Cancel button.
		document.addEventListener( 'click', function ( e ) {
			const cancel = e.target.closest( '.mdkit-dialog__cancel' );
			if ( ! cancel ) return;

			const dialog = cancel.closest( '.mdkit-dialog' );
			if ( dialog ) dialog.hidden = true;
		} );

		// Overlay click = cancel.
		document.addEventListener( 'click', function ( e ) {
			if ( e.target.classList.contains( 'mdkit-dialog__overlay' ) ) {
				const dialog = e.target.closest( '.mdkit-dialog' );
				if ( dialog ) dialog.hidden = true;
			}
		} );

		// Confirm button.
		document.addEventListener( 'click', function ( e ) {
			const confirm = e.target.closest( '.mdkit-dialog__confirm' );
			if ( ! confirm ) return;

			const dialog  = confirm.closest( '.mdkit-dialog' );
			if ( ! dialog ) return;

			const trigger = dialog._trigger;
			dialog.hidden = true;

			if ( trigger ) {
				// If the trigger is a link, navigate to its href.
				if ( trigger.tagName === 'A' && trigger.href ) {
					window.location.href = trigger.href;
				}
				// If the trigger is inside a form, submit the form.
				else if ( trigger.form ) {
					trigger.form.submit();
				}
				// If trigger has data-mdkit-confirm-action, dispatch custom event.
				else {
					trigger.dispatchEvent( new CustomEvent( 'mdkit:confirmed', { bubbles: true } ) );
				}
			}
		} );

		// Escape key to close.
		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' ) {
				document.querySelectorAll( '.mdkit-dialog:not([hidden])' ).forEach( ( d ) => {
					d.hidden = true;
				} );
			}
		} );
	}

	/* ==============================================================
	   Alert Dismiss
	   ============================================================== */

	function initAlertDismiss() {
		document.addEventListener( 'click', function ( e ) {
			const btn = e.target.closest( '.mdkit-alert__dismiss' );
			if ( ! btn ) return;

			const alert = btn.closest( '.mdkit-alert' );
			if ( alert ) {
				alert.style.transition = 'opacity 0.2s, max-height 0.3s';
				alert.style.opacity = '0';
				alert.style.maxHeight = alert.scrollHeight + 'px';
				requestAnimationFrame( () => {
					alert.style.maxHeight = '0';
					alert.style.padding = '0';
					alert.style.margin = '0';
					alert.style.border = '0';
				} );
				setTimeout( () => alert.remove(), 300 );
			}
		} );
	}

	/* ==============================================================
	   Copy to Clipboard
	   ============================================================== */

	function initCopyButtons() {
		document.addEventListener( 'click', function ( e ) {
			const btn = e.target.closest( '.mdkit-copy-btn' );
			if ( ! btn ) return;

			const text = btn.dataset.mdkitCopy || '';
			if ( ! text ) return;

			navigator.clipboard.writeText( text ).then( () => {
				const originalHTML = btn.innerHTML;
				btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>';
				btn.style.color = 'var(--mdkit-color-success)';
				btn.style.borderColor = 'var(--mdkit-color-success)';

				setTimeout( () => {
					btn.innerHTML = originalHTML;
					btn.style.color = '';
					btn.style.borderColor = '';
				}, 1500 );
			} );
		} );
	}

	/* ==============================================================
	   AJAX Data Tables
	   ============================================================== */

	function initDataTables() {
		document.querySelectorAll( '[data-mdkit-table]' ).forEach( ( container ) => {
			const tableId  = container.dataset.mdkitTable;
			const action   = container.dataset.action;
			const nonce    = container.dataset.nonce;
			const perPage  = parseInt( container.dataset.perPage, 10 ) || 20;
			const tbody    = container.querySelector( '.mdkit-data-table__body' );
			const infoEl   = container.querySelector( '.mdkit-data-table__info' );
			const pagesEl  = container.querySelector( '.mdkit-data-table__pages' );
			const searchEl = container.querySelector( '.mdkit-data-table__search-input' );

			let currentPage  = 1;
			let currentSort  = container.dataset.sort || '';
			let currentOrder = container.dataset.order || 'asc';
			let searchQuery  = '';
			let filters      = {};
			let debounceTimer = null;

			// Collect initial filter values.
			container.querySelectorAll( '.mdkit-data-table__filter' ).forEach( ( sel ) => {
				filters[ sel.dataset.filter ] = sel.value;
			} );

			/** Fetch data from server */
			function loadData() {
				const formData = new FormData();
				formData.append( 'action', action );
				formData.append( 'nonce', nonce );
				formData.append( 'page', currentPage );
				formData.append( 'per_page', perPage );
				formData.append( 'sort', currentSort );
				formData.append( 'order', currentOrder );
				formData.append( 'search', searchQuery );

				Object.keys( filters ).forEach( ( k ) => {
					formData.append( 'filter_' + k, filters[ k ] );
				} );

				// Show loading.
				const colCount = container.querySelectorAll( '.mdkit-data-table__th' ).length;
				tbody.innerHTML = '<tr><td colspan="' + colCount + '" class="mdkit-data-table__loading"><div class="mdkit-preloader"><div class="mdkit-preloader__spinner"></div></div></td></tr>';

				fetch( config.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					body: formData,
				} )
					.then( ( r ) => r.json() )
					.then( ( res ) => {
						if ( ! res.success ) {
							tbody.innerHTML = '<tr><td colspan="' + colCount + '" class="mdkit-data-table__empty">' + ( res.data?.message || 'Error' ) + '</td></tr>';
							return;
						}

						const data  = res.data;
						const total = data.total || 0;
						const pages = Math.ceil( total / perPage );

						// Render rows.
						if ( data.rows && data.rows.length > 0 ) {
							tbody.innerHTML = data.rows;
						} else {
							tbody.innerHTML = '<tr><td colspan="' + colCount + '" class="mdkit-data-table__empty">' + ( config.i18n?.noResults || 'No results found.' ) + '</td></tr>';
						}

						// Render info.
						if ( infoEl ) {
							const from = total === 0 ? 0 : ( ( currentPage - 1 ) * perPage ) + 1;
							const to   = Math.min( currentPage * perPage, total );
							infoEl.textContent = from + '–' + to + ' / ' + total;
						}

						// Render pagination.
						renderPagination( pages );
					} )
					.catch( () => {
						tbody.innerHTML = '<tr><td colspan="' + colCount + '" class="mdkit-data-table__empty">Network error</td></tr>';
					} );
			}

			/** Build pagination buttons */
			function renderPagination( totalPages ) {
				if ( ! pagesEl ) return;
				pagesEl.innerHTML = '';

				if ( totalPages <= 1 ) return;

				// Prev.
				const prev = document.createElement( 'button' );
				prev.textContent = '‹';
				prev.disabled = currentPage <= 1;
				prev.addEventListener( 'click', () => { currentPage--; loadData(); } );
				pagesEl.appendChild( prev );

				// Page numbers — show max 7 with ellipsis.
				const pages = paginate( currentPage, totalPages );
				pages.forEach( ( p ) => {
					if ( p === '…' ) {
						const span = document.createElement( 'span' );
						span.textContent = '…';
						span.style.padding = '0 4px';
						pagesEl.appendChild( span );
					} else {
						const btn = document.createElement( 'button' );
						btn.textContent = p;
						if ( p === currentPage ) btn.classList.add( 'is-active' );
						btn.addEventListener( 'click', () => { currentPage = p; loadData(); } );
						pagesEl.appendChild( btn );
					}
				} );

				// Next.
				const next = document.createElement( 'button' );
				next.textContent = '›';
				next.disabled = currentPage >= totalPages;
				next.addEventListener( 'click', () => { currentPage++; loadData(); } );
				pagesEl.appendChild( next );
			}

			/** Generate page numbers with ellipsis */
			function paginate( current, total ) {
				if ( total <= 7 ) {
					return Array.from( { length: total }, ( _, i ) => i + 1 );
				}
				const pages = [ 1 ];
				if ( current > 3 ) pages.push( '…' );
				for ( let i = Math.max( 2, current - 1 ); i <= Math.min( total - 1, current + 1 ); i++ ) {
					pages.push( i );
				}
				if ( current < total - 2 ) pages.push( '…' );
				pages.push( total );
				return pages;
			}

			// Sort headers.
			container.querySelectorAll( '.mdkit-data-table__th--sortable' ).forEach( ( th ) => {
				th.addEventListener( 'click', () => {
					const key = th.dataset.sortKey;
					if ( currentSort === key ) {
						currentOrder = currentOrder === 'asc' ? 'desc' : 'asc';
					} else {
						currentSort  = key;
						currentOrder = 'asc';
					}

					// Update header classes.
					container.querySelectorAll( '.mdkit-data-table__th--sortable' ).forEach( ( h ) => {
						h.classList.remove( 'mdkit-data-table__th--sorted', 'mdkit-data-table__th--asc', 'mdkit-data-table__th--desc' );
					} );
					th.classList.add( 'mdkit-data-table__th--sorted', 'mdkit-data-table__th--' + currentOrder );

					currentPage = 1;
					loadData();
				} );
			} );

			// Search — debounced.
			if ( searchEl ) {
				searchEl.addEventListener( 'input', () => {
					clearTimeout( debounceTimer );
					debounceTimer = setTimeout( () => {
						searchQuery = searchEl.value.trim();
						currentPage = 1;
						loadData();
					}, 350 );
				} );
			}

			// Filters.
			container.querySelectorAll( '.mdkit-data-table__filter' ).forEach( ( sel ) => {
				sel.addEventListener( 'change', () => {
					filters[ sel.dataset.filter ] = sel.value;
					currentPage = 1;
					loadData();
				} );
			} );

			// Initial load.
			loadData();

			// Expose a reload helper on the DOM element.
			container.mdkitReload = loadData;
		} );
	}

	/* ==============================================================
	   Initialize
	   ============================================================== */

	document.addEventListener( 'DOMContentLoaded', function () {
		initModuleToggles();
		initTabs();
		initHubSearch();
		initCategoryFilter();
		initFavorites();
		initStandaloneToggles();
		initViewToggle();
		initTableSections();
		initConfirmDialogs();
		initAlertDismiss();
		initCopyButtons();
		initDataTables();
	} );

} )();
