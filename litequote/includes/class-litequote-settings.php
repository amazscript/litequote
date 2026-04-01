<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings module — Options registration & retrieval.
 */
class LiteQuote_Settings {

	/**
	 * Constructor — no hooks needed, methods are called statically.
	 */
	public function __construct() {
		// Settings registration will be completed in Sprint 4.
	}

	/**
	 * Get a plugin option with default fallback.
	 *
	 * @param string $key     Option key (without litequote_ prefix).
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public static function get( $key, $default = '' ) {
		return get_option( 'litequote_' . $key, $default );
	}

	/**
	 * Get the default auto-reply email template.
	 *
	 * @return string
	 */
	public static function get_default_auto_reply_template() {
		return sprintf(
			'<p>%s</p><p>%s <strong>{product_name}</strong></p><p>%s</p><p>%s<br>{shop_name}</p>',
			/* translators: %s: client name */
			__( 'Hello {client_name},', 'litequote' ),
			__( 'We have received your quote request for', 'litequote' ),
			__( 'We will get back to you shortly with our best offer.', 'litequote' ),
			__( 'Best regards,', 'litequote' )
		);
	}

	/**
	 * Get the default WhatsApp message template.
	 *
	 * @return string
	 */
	public static function get_default_whatsapp_template() {
		return __( 'Hello! I am interested in the product {product_name} (Ref. {sku}). Could you send me your best price? {product_url}', 'litequote' );
	}
}
