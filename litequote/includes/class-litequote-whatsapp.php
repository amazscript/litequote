<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WhatsApp module — wa.me URL builder & button rendering.
 *
 * Handles:
 * - Building the WhatsApp wa.me URL with pre-filled message.
 * - Rendering the WhatsApp button inside the modal (mode "both").
 * - Redirecting directly to WhatsApp (mode "whatsapp_only").
 * - Passing WhatsApp config to the JS frontend.
 *
 * @since 1.1.0
 */
class LiteQuote_WhatsApp {

	/**
	 * Constructor — register hooks.
	 */
	public function __construct() {
		add_filter( 'litequote_modal_data', array( $this, 'add_whatsapp_data' ) );
	}

	/**
	 * Add WhatsApp configuration to the JS data object.
	 *
	 * @since 1.1.0
	 *
	 * @param array $data The litequoteData array passed to wp_localize_script.
	 * @return array
	 */
	public function add_whatsapp_data( $data ) {
		$number   = self::clean_number( get_option( 'litequote_whatsapp_number', '' ) );
		$mode     = get_option( 'litequote_whatsapp_mode', 'form_only' );
		$template = get_option( 'litequote_whatsapp_template', '' );

		if ( empty( $template ) ) {
			$template = LiteQuote_Settings::get_default_whatsapp_template();
		}

		// If no number configured, force form_only mode.
		if ( empty( $number ) ) {
			$mode = 'form_only';
		}

		$data['whatsapp'] = array(
			'enabled'  => ! empty( $number ),
			'number'   => $number,
			'mode'     => $mode,
			'template' => $template,
			'btnText'  => __( 'Chat on WhatsApp', 'litequote' ),
		);

		return $data;
	}

	/**
	 * Clean a phone number — keep only digits and leading +.
	 *
	 * @since 1.1.0
	 *
	 * @param string $number Raw phone number.
	 * @return string Cleaned number (digits only with optional leading +).
	 */
	public static function clean_number( $number ) {
		// Keep only digits and leading +.
		$cleaned = preg_replace( '/[^0-9+]/', '', trim( $number ) );

		// If starts with +, remove it but keep the country code.
		if ( str_starts_with( $cleaned, '+' ) ) {
			$cleaned = substr( $cleaned, 1 );
		}

		// If starts with 00 (international prefix), remove it.
		if ( str_starts_with( $cleaned, '00' ) ) {
			$cleaned = substr( $cleaned, 2 );
		}

		return $cleaned;
	}

	/**
	 * Build the wa.me URL with a pre-filled message.
	 *
	 * This is used server-side if needed, but primarily
	 * the URL is built client-side in JS for dynamic variation support.
	 *
	 * @since 1.1.0
	 *
	 * @param string $number  The WhatsApp number (digits only).
	 * @param string $message The pre-filled message.
	 * @return string The complete wa.me URL.
	 */
	public static function build_url( $number, $message = '' ) {
		$url = 'https://wa.me/' . $number;
		if ( ! empty( $message ) ) {
			$url .= '?text=' . rawurlencode( $message );
		}
		return $url;
	}
}
