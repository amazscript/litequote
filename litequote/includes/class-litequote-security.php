<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security module — Nonce verification, honeypot, input sanitization.
 */
class LiteQuote_Security {

	/**
	 * Constructor — no hooks needed, methods are called statically.
	 */
	public function __construct() {
		// Hooks will be registered in Sprint 3.
	}

	/**
	 * Verify the LiteQuote nonce.
	 *
	 * @param string $nonce The nonce value to verify.
	 * @return bool
	 */
	public static function verify_nonce( $nonce ) {
		return (bool) wp_verify_nonce( $nonce, 'litequote_nonce' );
	}

	/**
	 * Sanitize quote submission data.
	 *
	 * @param array $data Raw POST data.
	 * @return array|WP_Error Sanitized data or WP_Error on validation failure.
	 */
	public static function sanitize_submission( $data ) {
		$clean = array();

		// Name — required.
		$name = isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '';
		if ( empty( $name ) ) {
			return new WP_Error( 'invalid_name', __( 'Please enter your name.', 'litequote' ) );
		}
		$clean['name'] = $name;

		// Email — required.
		$email = isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
		if ( ! is_email( $email ) ) {
			return new WP_Error( 'invalid_email', __( 'Please enter a valid email address.', 'litequote' ) );
		}
		$clean['email'] = $email;

		// Phone — optional.
		$phone = isset( $data['phone'] ) ? sanitize_text_field( $data['phone'] ) : '';
		if ( ! empty( $phone ) && ! preg_match( '/^\+?[0-9\s\-().]{6,20}$/', $phone ) ) {
			return new WP_Error( 'invalid_phone', __( 'Invalid phone number.', 'litequote' ) );
		}
		$clean['phone'] = $phone;

		// Company — optional.
		$clean['company'] = isset( $data['company'] ) ? sanitize_text_field( $data['company'] ) : '';

		// Quantity — optional, defaults to 1.
		$clean['quantity'] = isset( $data['quantity'] ) ? max( 1, absint( $data['quantity'] ) ) : 1;

		// Message.
		$clean['message'] = isset( $data['message'] ) ? wp_kses_post( $data['message'] ) : '';

		// Product ID.
		$clean['product_id'] = isset( $data['product_id'] ) ? absint( $data['product_id'] ) : 0;

		// Product name.
		$clean['product_name'] = isset( $data['product_name'] ) ? sanitize_text_field( $data['product_name'] ) : '';

		// SKU.
		$clean['sku'] = isset( $data['sku'] ) ? sanitize_text_field( $data['sku'] ) : '';

		// Variation.
		$clean['variation'] = isset( $data['variation'] ) ? sanitize_text_field( $data['variation'] ) : '';

		return $clean;
	}

	/**
	 * Check the honeypot field.
	 *
	 * @param array $data Raw POST data.
	 * @param string $honeypot_name The dynamic honeypot field name.
	 * @return bool True if spam detected.
	 */
	public static function is_spam( $data, $honeypot_name ) {
		if ( ! empty( $data[ $honeypot_name ] ) ) {
			if ( 'yes' === get_option( 'litequote_debug_mode', 'no' ) ) {
				$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : 'unknown';
				// Anonymize IP: keep only first 3 octets.
				$ip_parts = explode( '.', $ip );
				if ( count( $ip_parts ) === 4 ) {
					$ip_parts[3] = 'xxx';
					$ip = implode( '.', $ip_parts );
				}
				error_log( sprintf(
					'[LiteQuote] Honeypot triggered — IP: %s — Date: %s',
					$ip,
					current_time( 'mysql' )
				) );
			}
			return true;
		}

		return false;
	}

	/**
	 * Check rate limiting for quote submissions.
	 *
	 * Limits each IP to a maximum number of submissions per time window.
	 * Uses WordPress transients (no extra database table).
	 *
	 * @since 1.0.0
	 *
	 * @param int $max_requests Maximum requests allowed per window. Default 5.
	 * @param int $window_seconds Time window in seconds. Default 300 (5 minutes).
	 * @return bool True if rate limit exceeded.
	 */
	public static function is_rate_limited( $max_requests = 5, $window_seconds = 300 ) {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : 'unknown';
		$key = 'litequote_rl_' . md5( $ip );

		$data = get_transient( $key );

		if ( false === $data ) {
			// First request from this IP.
			set_transient( $key, array( 'count' => 1, 'start' => time() ), $window_seconds );
			return false;
		}

		if ( $data['count'] >= $max_requests ) {
			if ( 'yes' === get_option( 'litequote_debug_mode', 'no' ) ) {
				$ip_parts = explode( '.', $ip );
				if ( count( $ip_parts ) === 4 ) {
					$ip_parts[3] = 'xxx';
					$ip = implode( '.', $ip_parts );
				}
				error_log( sprintf(
					'[LiteQuote] Rate limit exceeded — IP: %s — Count: %d — Date: %s',
					$ip,
					$data['count'],
					current_time( 'mysql' )
				) );
			}
			return true;
		}

		// Increment counter.
		$data['count']++;
		$remaining = $window_seconds - ( time() - $data['start'] );
		if ( $remaining > 0 ) {
			set_transient( $key, $data, $remaining );
		}

		return false;
	}
}
