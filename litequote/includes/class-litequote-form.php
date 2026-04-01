<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form module — AJAX handler for quote submissions.
 *
 * Handles:
 * - Nonce verification via LiteQuote_Security.
 * - Honeypot check.
 * - Input sanitization.
 * - Delegation to LiteQuote_Email for notifications.
 *
 * @since 1.0.0
 */
class LiteQuote_Form {

	/**
	 * Constructor — register AJAX hooks.
	 */
	public function __construct() {
		add_action( 'wp_ajax_litequote_submit_quote', array( $this, 'handle_submission' ) );
		add_action( 'wp_ajax_nopriv_litequote_submit_quote', array( $this, 'handle_submission' ) );
	}

	/**
	 * Handle the AJAX quote submission.
	 *
	 * Flow:
	 * 1. Verify nonce (CSRF protection).
	 * 2. Check honeypot (spam protection).
	 * 3. Sanitize all inputs.
	 * 4. Send admin email notification.
	 * 5. Send auto-reply if enabled.
	 * 6. Return JSON response.
	 *
	 * @since 1.0.0
	 */
	public function handle_submission() {
		// 1. Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
		if ( ! LiteQuote_Security::verify_nonce( $nonce ) ) {
			wp_send_json_error( array(
				'message' => __( 'Session expired. Please reload the page and try again.', 'litequote' ),
			), 403 );
		}

		// 2. Check rate limiting (5 requests per 5 minutes per IP).
		if ( LiteQuote_Security::is_rate_limited() ) {
			wp_send_json_error( array(
				'message' => __( 'Too many requests. Please try again in a few minutes.', 'litequote' ),
			) );
		}

		// 3. Check honeypot.
		$honeypot_name = isset( $_POST['honeypot_field'] ) ? sanitize_text_field( $_POST['honeypot_field'] ) : '';
		if ( ! empty( $honeypot_name ) && LiteQuote_Security::is_spam( $_POST, $honeypot_name ) ) {
			// Silent rejection — return success to not tip off bots.
			wp_send_json_success( array(
				'message' => __( 'Thank you! Your quote request has been sent.', 'litequote' ),
			) );
		}

		// 4. Sanitize inputs.
		$data = LiteQuote_Security::sanitize_submission( $_POST );

		if ( is_wp_error( $data ) ) {
			wp_send_json_error( array(
				'message' => $data->get_error_message(),
			) );
		}

		// 4. Send admin email.
		$email_sent = LiteQuote_Email::send_admin_notification( $data );

		if ( ! $email_sent ) {
			// Log the error but don't tell the visitor.
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( '[LiteQuote] Failed to send admin notification for product: ' . $data['product_name'] );
			}
		}

		// 5. Send auto-reply to client if enabled.
		if ( 'yes' === get_option( 'litequote_auto_reply', 'no' ) ) {
			LiteQuote_Email::send_auto_reply( $data );
		}

		// 6. Save quote to database (CPT).
		if ( class_exists( 'LiteQuote_Quote_CPT' ) ) {
			LiteQuote_Quote_CPT::save_quote( $data );
		}

		// 7. Success response.
		wp_send_json_success( array(
			'message' => __( 'Thank you! Your quote request has been sent. We will get back to you shortly.', 'litequote' ),
		) );
	}
}
