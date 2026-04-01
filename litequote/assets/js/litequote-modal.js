/**
 * LiteQuote for WooCommerce — Modal & Form Handler
 *
 * Zero jQuery. Vanilla JS ES6+ only.
 * Handles: modal open/close, focus trap, a11y, form validation, AJAX submit.
 *
 * @since 1.0.0
 */
( function () {
	'use strict';

	/** @type {HTMLElement|null} */
	let overlay = null;

	/** @type {HTMLElement|null} */
	let lastFocusedElement = null;

	/** @type {string} Honeypot field name (randomized per page load). */
	const honeypotName = 'lq_' + Math.random().toString( 36 ).substring( 2, 8 );

	/**
	 * Initialize LiteQuote when DOM is ready.
	 */
	document.addEventListener( 'DOMContentLoaded', function () {
		buildModal();
		bindButtons();
		bindVariationListener();
	} );

	/**
	 * Build the modal HTML and inject it into the DOM.
	 */
	function buildModal() {
		const data = window.litequoteData || {};
		const i18n = data.i18n || {};
		const wa   = data.whatsapp || { enabled: false, mode: 'form_only' };

		overlay = document.createElement( 'div' );
		overlay.className = 'litequote-overlay';
		overlay.setAttribute( 'aria-hidden', 'true' );

		overlay.innerHTML = `
			<div class="litequote-modal" role="dialog" aria-modal="true" aria-labelledby="litequote-title">
				<button type="button" class="litequote-close" aria-label="${esc( i18n.close || 'Fermer' )}">&#x2715;</button>
				<h2 class="litequote-modal__title" id="litequote-title">${esc( data.i18n?.modalTitle || 'Demander un devis' )}</h2>

				<div class="litequote-message litequote-message--success" id="litequote-success"></div>
				<div class="litequote-message litequote-message--error" id="litequote-error-msg"></div>

				<form class="litequote-form" id="litequote-form" novalidate>
					<input type="hidden" name="action" value="litequote_submit_quote">
					<input type="hidden" name="nonce" value="${esc( data.nonce || '' )}">
					<input type="hidden" name="product_id" id="litequote-product-id" value="">
					<input type="hidden" name="product_name" id="litequote-product-name" value="">
					<input type="hidden" name="sku" id="litequote-sku" value="">
					<input type="hidden" name="product_url" id="litequote-product-url" value="">
					<input type="hidden" name="variation" id="litequote-variation" value="">

					<!-- Honeypot -->
					<div style="display:none" aria-hidden="true">
						<input type="text" name="${honeypotName}" tabindex="-1" autocomplete="off" value="">
					</div>
					<input type="hidden" name="honeypot_field" value="${honeypotName}">

					<div class="litequote-form__group">
						<label class="litequote-form__label litequote-form__label--required" for="litequote-name">
							${esc( i18n.labelName || 'Full Name' )}
						</label>
						<input type="text" class="litequote-form__input" id="litequote-name" name="name"
							placeholder="${esc( i18n.placeholderName || 'Your name' )}" required>
						<span class="litequote-form__error" id="litequote-name-error">${esc( i18n.invalidName || 'Please enter your name.' )}</span>
					</div>

					<div class="litequote-form__group">
						<label class="litequote-form__label" for="litequote-company">
							${esc( i18n.labelCompany || 'Company' )}
						</label>
						<input type="text" class="litequote-form__input" id="litequote-company" name="company"
							placeholder="${esc( i18n.placeholderCompany || 'Company name (optional)' )}">
					</div>

					<div class="litequote-form__group">
						<label class="litequote-form__label litequote-form__label--required" for="litequote-email">
							${esc( i18n.labelEmail || 'Email' )}
						</label>
						<input type="email" class="litequote-form__input" id="litequote-email" name="email"
							placeholder="${esc( i18n.placeholderEmail || 'your@email.com' )}" required>
						<span class="litequote-form__error" id="litequote-email-error">${esc( i18n.invalidEmail || 'Please enter a valid email address.' )}</span>
					</div>

					<div class="litequote-form__group">
						<label class="litequote-form__label" for="litequote-phone">
							${esc( i18n.labelPhone || 'Phone' )}
						</label>
						<input type="tel" class="litequote-form__input" id="litequote-phone" name="phone"
							placeholder="${esc( i18n.placeholderPhone || '+1 555 123 4567' )}">
						<span class="litequote-form__error" id="litequote-phone-error">${esc( i18n.invalidPhone || 'Invalid phone number.' )}</span>
					</div>

					<div class="litequote-form__group">
						<label class="litequote-form__label" for="litequote-quantity">
							${esc( i18n.labelQuantity || 'Quantity' )}
						</label>
						<input type="number" class="litequote-form__input" id="litequote-quantity" name="quantity"
							min="1" value="1" placeholder="1">
					</div>

					<div class="litequote-form__group">
						<label class="litequote-form__label" for="litequote-message">
							${esc( i18n.labelMessage || 'Message' )}
						</label>
						<textarea class="litequote-form__textarea" id="litequote-message" name="message" rows="4"></textarea>
					</div>

					<button type="submit" class="litequote-form__submit" id="litequote-submit">
						<span class="litequote-spinner"></span>
						<span class="litequote-form__submit-text">${esc( i18n.send || 'Send Request' )}</span>
					</button>

					${wa.enabled && ( wa.mode === 'both' ) ? `
					<a href="#" class="litequote-whatsapp-btn" id="litequote-wa-btn" target="_blank" rel="noopener noreferrer">
						<span class="litequote-whatsapp-icon"></span>
						${esc( wa.btnText || 'Chat on WhatsApp' )}
					</a>` : ''}
				</form>
			</div>
		`;

		document.body.appendChild( overlay );

		// Close events.
		overlay.querySelector( '.litequote-close' ).addEventListener( 'click', closeModal );
		overlay.addEventListener( 'click', function ( e ) {
			if ( e.target === overlay ) {
				closeModal();
			}
		} );
		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' && overlay.classList.contains( 'is-active' ) ) {
				closeModal();
			}
			if ( e.key === 'Tab' && overlay.classList.contains( 'is-active' ) ) {
				trapFocus( e );
			}
		} );

		// Form submit.
		document.getElementById( 'litequote-form' ).addEventListener( 'submit', handleSubmit );
	}

	/**
	 * Bind click events to all LiteQuote buttons on the page.
	 */
	function bindButtons() {
		document.querySelectorAll( '.litequote-btn:not(.litequote-btn--loop)' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				openModal( btn );
			} );
		} );
	}

	/**
	 * Listen for WooCommerce variation changes and update the hidden field.
	 */
	function bindVariationListener() {
		const variationForm = document.querySelector( '.variations_form' );
		if ( ! variationForm ) {
			return;
		}

		// WooCommerce triggers this jQuery event when a variation is selected.
		// We listen on the native DOM and also try jQuery if available.
		variationForm.addEventListener( 'change', updateVariationField );

		// Also listen for the WooCommerce jQuery event if jQuery exists.
		if ( window.jQuery ) {
			window.jQuery( variationForm ).on( 'found_variation', function () {
				const parts = [];
				variationForm.querySelectorAll( '.variations select' ).forEach( function ( select ) {
					if ( select.value ) {
						const label = select.closest( 'tr' )?.querySelector( 'label' )?.textContent?.trim()
							|| select.getAttribute( 'data-attribute_name' )?.replace( 'attribute_', '' ) || '';
						parts.push( label + ': ' + select.value );
					}
				} );
				document.getElementById( 'litequote-variation' ).value = parts.join( ' / ' );
			} );

			window.jQuery( variationForm ).on( 'reset_data', function () {
				document.getElementById( 'litequote-variation' ).value = '';
			} );
		}
	}

	/**
	 * Read selected variation attributes from the form.
	 */
	function updateVariationField() {
		const variationForm = document.querySelector( '.variations_form' );
		if ( ! variationForm ) {
			return;
		}

		const parts = [];
		variationForm.querySelectorAll( '.variations select' ).forEach( function ( select ) {
			if ( select.value ) {
				const label = select.closest( 'tr' )?.querySelector( 'label' )?.textContent?.trim()
					|| select.getAttribute( 'data-attribute_name' )?.replace( 'attribute_', '' ) || '';
				parts.push( label + ': ' + select.value );
			}
		} );
		document.getElementById( 'litequote-variation' ).value = parts.join( ' / ' );
	}

	/**
	 * Open the modal and populate product data.
	 *
	 * @param {HTMLElement} btn The quote button that was clicked.
	 */
	function openModal( btn ) {
		lastFocusedElement = btn;

		const data = window.litequoteData || {};
		const wa   = data.whatsapp || { enabled: false, mode: 'form_only' };

		// Populate hidden fields from button data attributes.
		const productId   = btn.getAttribute( 'data-product-id' ) || '';
		const productName = btn.getAttribute( 'data-product-name' ) || '';
		const sku         = btn.getAttribute( 'data-product-sku' ) || '';
		const productUrl  = btn.getAttribute( 'data-product-url' ) || '';

		// WhatsApp only mode — skip modal, open WhatsApp directly.
		if ( wa.enabled && wa.mode === 'whatsapp_only' ) {
			const waUrl = buildWhatsAppUrl( wa, productName, sku, productUrl, '' );
			window.open( waUrl, '_blank', 'noopener,noreferrer' );
			return;
		}

		document.getElementById( 'litequote-product-id' ).value   = productId;
		document.getElementById( 'litequote-product-name' ).value = productName;
		document.getElementById( 'litequote-sku' ).value          = sku;
		document.getElementById( 'litequote-product-url' ).value  = productUrl;

		// Pre-fill message.
		const messageField = document.getElementById( 'litequote-message' );
		const i18n = ( window.litequoteData || {} ).i18n || {};
		let message = ( i18n.prefillMessage || 'Hello, I would like a quote for:' ) + ' ' + productName;
		if ( sku ) {
			message += ' — ' + ( i18n.prefillRef || 'Ref.' ) + ' ' + sku;
		}
		const variation = document.getElementById( 'litequote-variation' ).value;
		if ( variation ) {
			message += '\n' + ( i18n.prefillVariation || 'Variation:' ) + ' ' + variation;
		}
		messageField.value = message;

		// Update WhatsApp button URL if mode is "both".
		const waBtn = document.getElementById( 'litequote-wa-btn' );
		if ( waBtn && wa.enabled ) {
			waBtn.href = buildWhatsAppUrl( wa, productName, sku, productUrl, variation );
		}

		// Reset form state.
		hideAllErrors();
		hideMessage( 'litequote-success' );
		hideMessage( 'litequote-error-msg' );
		showForm();

		// Show modal.
		overlay.classList.add( 'is-active' );
		overlay.setAttribute( 'aria-hidden', 'false' );
		document.body.style.overflow = 'hidden';

		// Focus first field.
		setTimeout( function () {
			document.getElementById( 'litequote-name' ).focus();
		}, 220 );
	}

	/**
	 * Build a WhatsApp wa.me URL from the template and product data.
	 *
	 * @param {Object} wa          WhatsApp config from litequoteData.
	 * @param {string} productName Product name.
	 * @param {string} sku         Product SKU.
	 * @param {string} productUrl  Product URL.
	 * @param {string} variation   Selected variation string.
	 * @return {string} The complete wa.me URL.
	 */
	function buildWhatsAppUrl( wa, productName, sku, productUrl, variation ) {
		let message = wa.template || '';
		message = message.replace( /\{product_name\}/g, productName );
		message = message.replace( /\{sku\}/g, sku || '' );
		message = message.replace( /\{product_url\}/g, productUrl || '' );
		message = message.replace( /\{variation\}/g, variation || '' );

		// Clean up empty ref if no SKU.
		if ( ! sku ) {
			message = message.replace( /\s*\(Ref\.\s*\)/, '' );
			message = message.replace( /\s*\(Réf\.\s*\)/, '' );
		}

		return 'https://wa.me/' + wa.number + '?text=' + encodeURIComponent( message );
	}

	/**
	 * Close the modal and restore page state.
	 */
	function closeModal() {
		overlay.classList.remove( 'is-active' );
		overlay.setAttribute( 'aria-hidden', 'true' );
		document.body.style.overflow = '';

		// Restore focus to the button that opened the modal.
		if ( lastFocusedElement ) {
			lastFocusedElement.focus();
		}
	}

	/**
	 * Trap keyboard focus inside the modal (a11y).
	 *
	 * @param {KeyboardEvent} e
	 */
	function trapFocus( e ) {
		const modal       = overlay.querySelector( '.litequote-modal' );
		const focusables  = modal.querySelectorAll(
			'button, [href], input:not([type="hidden"]):not([tabindex="-1"]), textarea, select, [tabindex]:not([tabindex="-1"])'
		);
		const firstEl     = focusables[ 0 ];
		const lastEl      = focusables[ focusables.length - 1 ];

		if ( e.shiftKey ) {
			if ( document.activeElement === firstEl ) {
				lastEl.focus();
				e.preventDefault();
			}
		} else {
			if ( document.activeElement === lastEl ) {
				firstEl.focus();
				e.preventDefault();
			}
		}
	}

	/**
	 * Validate the form fields.
	 *
	 * @return {boolean} True if valid.
	 */
	function validateForm() {
		let isValid = true;
		hideAllErrors();

		// Name.
		const name = document.getElementById( 'litequote-name' ).value.trim();
		if ( ! name ) {
			showError( 'litequote-name', 'litequote-name-error' );
			isValid = false;
		}

		// Email (RFC 5322 simplified).
		const email = document.getElementById( 'litequote-email' ).value.trim();
		const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		if ( ! email || ! emailRegex.test( email ) ) {
			showError( 'litequote-email', 'litequote-email-error' );
			isValid = false;
		}

		// Phone (optional — validate only if filled).
		const phone = document.getElementById( 'litequote-phone' ).value.trim();
		if ( phone ) {
			const phoneRegex = /^\+?[0-9\s\-().]{6,20}$/;
			if ( ! phoneRegex.test( phone ) ) {
				showError( 'litequote-phone', 'litequote-phone-error' );
				isValid = false;
			}
		}

		// Focus first error field.
		if ( ! isValid ) {
			const firstError = overlay.querySelector( '.litequote-form__input--error, .litequote-form__textarea--error' );
			if ( firstError ) {
				firstError.focus();
			}
		}

		return isValid;
	}

	/**
	 * Handle form submission via AJAX (fetch).
	 *
	 * @param {Event} e The submit event.
	 */
	function handleSubmit( e ) {
		e.preventDefault();

		if ( ! validateForm() ) {
			return;
		}

		const form      = document.getElementById( 'litequote-form' );
		const submitBtn = document.getElementById( 'litequote-submit' );
		const data      = window.litequoteData || {};
		const i18n      = data.i18n || {};

		// Disable submit button (anti double-click).
		submitBtn.disabled = true;

		// Update variation field one last time before submit.
		updateVariationField();

		// Build form data.
		const formData = new FormData( form );

		fetch( data.ajaxUrl || '/wp-admin/admin-ajax.php', {
			method: 'POST',
			credentials: 'same-origin',
			body: formData,
		} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( result ) {
				if ( result.success ) {
					// Show success message, hide form.
					hideForm();
					showMessage( 'litequote-success', i18n.success || 'Merci ! Votre demande a été envoyée.' );

					// Auto-close after 3 seconds.
					setTimeout( function () {
						closeModal();
						form.reset();
						showForm();
					}, 3000 );
				} else {
					const errorMsg = result.data?.message || i18n.error || 'Une erreur est survenue.';
					showMessage( 'litequote-error-msg', errorMsg );
				}
			} )
			.catch( function () {
				showMessage( 'litequote-error-msg', i18n.error || 'Une erreur est survenue.' );
			} )
			.finally( function () {
				submitBtn.disabled = false;
			} );
	}

	/* =====================================================================
	   Helper functions
	   ===================================================================== */

	/**
	 * Show a field validation error.
	 *
	 * @param {string} inputId The input element ID.
	 * @param {string} errorId The error message element ID.
	 */
	function showError( inputId, errorId ) {
		const input = document.getElementById( inputId );
		const error = document.getElementById( errorId );
		if ( input ) {
			input.classList.add( 'litequote-form__input--error' );
		}
		if ( error ) {
			error.classList.add( 'is-visible' );
		}
	}

	/** Hide all field validation errors. */
	function hideAllErrors() {
		overlay.querySelectorAll( '.litequote-form__input--error' ).forEach( function ( el ) {
			el.classList.remove( 'litequote-form__input--error' );
		} );
		overlay.querySelectorAll( '.litequote-form__error.is-visible' ).forEach( function ( el ) {
			el.classList.remove( 'is-visible' );
		} );
	}

	/**
	 * Show a success or error message block.
	 *
	 * @param {string} id   The message element ID.
	 * @param {string} text The message text.
	 */
	function showMessage( id, text ) {
		const el = document.getElementById( id );
		if ( el ) {
			el.textContent = text;
			el.classList.add( 'is-visible' );
		}
	}

	/**
	 * Hide a message block.
	 *
	 * @param {string} id The message element ID.
	 */
	function hideMessage( id ) {
		const el = document.getElementById( id );
		if ( el ) {
			el.classList.remove( 'is-visible' );
			el.textContent = '';
		}
	}

	/** Hide the form (after successful submit). */
	function hideForm() {
		const form = document.getElementById( 'litequote-form' );
		if ( form ) {
			form.style.display = 'none';
		}
	}

	/** Show the form (reset after close). */
	function showForm() {
		const form = document.getElementById( 'litequote-form' );
		if ( form ) {
			form.style.display = '';
		}
	}

	/**
	 * Escape HTML entities for safe insertion.
	 *
	 * @param {string} str The string to escape.
	 * @return {string}
	 */
	function esc( str ) {
		const div = document.createElement( 'div' );
		div.appendChild( document.createTextNode( str || '' ) );
		return div.innerHTML;
	}

} )();
