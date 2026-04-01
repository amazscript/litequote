<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email module — Admin notification & client auto-reply.
 *
 * Handles:
 * - Sending structured HTML email to the site admin on each quote submission.
 * - Sending an auto-reply confirmation to the client (if enabled).
 * - Reply-To header set to the client's email for easy response.
 *
 * @since 1.0.0
 */
class LiteQuote_Email {

	/**
	 * Constructor — no hooks needed, methods are called directly by LiteQuote_Form.
	 */
	public function __construct() {
		// Static methods called by LiteQuote_Form::handle_submission().
	}

	/**
	 * Send the admin notification email.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Sanitized submission data.
	 * @return bool Whether the email was sent successfully.
	 */
	public static function send_admin_notification( $data ) {
		$to = get_option( 'litequote_admin_email', get_bloginfo( 'admin_email' ) );
		if ( empty( $to ) ) {
			$to = get_bloginfo( 'admin_email' );
		}

		$subject = sprintf(
			/* translators: %s: product name */
			__( '[LiteQuote] New quote request — %s', 'litequote' ),
			$data['product_name']
		);

		$body = self::build_admin_email_body( $data );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'Reply-To: ' . sanitize_email( $data['email'] ),
		);

		$attachments = apply_filters( 'litequote_email_attachments', array(), $data );

		return wp_mail( $to, $subject, $body, $headers, $attachments );
	}

	/**
	 * Send the auto-reply email to the client.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Sanitized submission data.
	 * @return bool Whether the email was sent successfully.
	 */
	public static function send_auto_reply( $data ) {
		$to = $data['email'];

		$subject = sprintf(
			/* translators: %s: shop name */
			__( 'Your quote request confirmation — %s', 'litequote' ),
			get_bloginfo( 'name' )
		);

		$template = get_option( 'litequote_auto_reply_template', '' );
		if ( empty( $template ) ) {
			$template = LiteQuote_Settings::get_default_auto_reply_template();
		}

		// Replace template variables.
		$body = str_replace(
			array( '{client_name}', '{product_name}', '{product_url}', '{shop_name}', '{date}' ),
			array(
				esc_html( $data['name'] ),
				esc_html( $data['product_name'] ),
				esc_url( $data['product_url'] ?? get_permalink( $data['product_id'] ) ),
				esc_html( get_bloginfo( 'name' ) ),
				esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ),
			),
			$template
		);

		$body = self::wrap_email_html( $body );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
		);

		$attachments = apply_filters( 'litequote_email_attachments', array(), $data );

		return wp_mail( $to, $subject, $body, $headers, $attachments );
	}

	/**
	 * Build the admin notification email body in HTML.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Sanitized submission data.
	 * @return string HTML email body.
	 */
	private static function build_admin_email_body( $data ) {
		$date        = wp_date( get_option( 'date_format' ) . ' — ' . get_option( 'time_format' ) );
		$edit_link   = admin_url( 'post.php?post=' . absint( $data['product_id'] ) . '&action=edit' );
		$front_link  = get_permalink( $data['product_id'] );

		// Client section.
		$client_html = '';
		$client_html .= self::email_row( '&#128100;', __( 'Name', 'litequote' ), esc_html( $data['name'] ) );
		$client_html .= self::email_row( '&#127970;', __( 'Company', 'litequote' ), ! empty( $data['company'] ) ? esc_html( $data['company'] ) : '—' );
		$client_html .= self::email_row(
			'&#9993;',
			__( 'Email', 'litequote' ),
			'<a href="mailto:' . esc_attr( $data['email'] ) . '" style="color:#0073aa;text-decoration:none;">' . esc_html( $data['email'] ) . '</a>'
		);
		if ( ! empty( $data['phone'] ) ) {
			$client_html .= self::email_row(
				'&#128222;',
				__( 'Phone', 'litequote' ),
				'<a href="tel:' . esc_attr( $data['phone'] ) . '" style="color:#0073aa;text-decoration:none;">' . esc_html( $data['phone'] ) . '</a>'
			);
		}

		// Product section.
		$product_html = '';
		$product_html .= self::email_row( '&#128230;', __( 'Product', 'litequote' ), '<strong>' . esc_html( $data['product_name'] ) . '</strong>' );
		$product_html .= self::email_row( '&#128290;', __( 'Quantity', 'litequote' ), esc_html( $data['quantity'] ?? 1 ) );
		if ( ! empty( $data['sku'] ) ) {
			$product_html .= self::email_row( '&#128196;', __( 'SKU', 'litequote' ), esc_html( $data['sku'] ) );
		}
		if ( ! empty( $data['variation'] ) ) {
			$product_html .= self::email_row( '&#127912;', __( 'Variation', 'litequote' ), esc_html( $data['variation'] ) );
		}
		$product_html .= self::email_row(
			'&#128279;',
			__( 'Links', 'litequote' ),
			'<a href="' . esc_url( $front_link ) . '" style="color:#0073aa;text-decoration:none;">' . __( 'View Product', 'litequote' ) . '</a>'
			. ' &nbsp;|&nbsp; '
			. '<a href="' . esc_url( $edit_link ) . '" style="color:#0073aa;text-decoration:none;">' . __( 'Edit', 'litequote' ) . '</a>'
		);

		// Message section.
		$message_html = '';
		if ( ! empty( $data['message'] ) ) {
			$message_html = '<div style="margin-top:20px;padding:16px;background:#f8f9fa;border-left:4px solid #0073aa;border-radius:0 4px 4px 0;">'
				. '<p style="margin:0 0 6px;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:#888;font-weight:600;">' . __( 'Customer Message', 'litequote' ) . '</p>'
				. '<p style="margin:0;color:#333;line-height:1.6;">' . nl2br( esc_html( $data['message'] ) ) . '</p>'
				. '</div>';
		}

		// Assemble.
		$body = '<h2 style="margin:0 0 4px;font-size:20px;color:#1e1e1e;">' . __( 'New Quote Request', 'litequote' ) . '</h2>'
			. '<p style="margin:0 0 24px;color:#888;font-size:13px;">&#128197; ' . esc_html( $date ) . '</p>';

		// Client table.
		$body .= '<p style="margin:0 0 8px;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:#888;font-weight:600;">' . __( 'Customer', 'litequote' ) . '</p>';
		$body .= '<table cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;margin-bottom:20px;">' . $client_html . '</table>';

		// Product table.
		$body .= '<p style="margin:0 0 8px;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:#888;font-weight:600;">' . __( 'Product', 'litequote' ) . '</p>';
		$body .= '<table cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;margin-bottom:4px;">' . $product_html . '</table>';

		// Message.
		$body .= $message_html;

		// CTA button.
		$body .= '<div style="margin-top:24px;text-align:center;">'
			. '<a href="mailto:' . esc_attr( $data['email'] ) . '?subject=' . rawurlencode( __( 'Re: Your quote request — ', 'litequote' ) . $data['product_name'] ) . '" '
			. 'style="display:inline-block;padding:12px 32px;background:#0073aa;color:#fff;text-decoration:none;border-radius:4px;font-weight:600;font-size:14px;">'
			. __( 'Reply to Customer', 'litequote' )
			. '</a></div>';

		return self::wrap_email_html( $body );
	}

	/**
	 * Build a single row for the email table.
	 *
	 * @param string $icon  HTML entity icon.
	 * @param string $label The row label.
	 * @param string $value The row value (can contain HTML).
	 * @return string HTML table row.
	 */
	private static function email_row( $icon, $label, $value ) {
		return '<tr>'
			. '<td style="padding:10px 8px 10px 0;border-bottom:1px solid #f0f0f0;width:20px;vertical-align:top;font-size:16px;">' . $icon . '</td>'
			. '<td style="padding:10px 8px;border-bottom:1px solid #f0f0f0;font-weight:600;color:#555;width:100px;vertical-align:top;font-size:13px;">' . $label . '</td>'
			. '<td style="padding:10px 8px;border-bottom:1px solid #f0f0f0;color:#1e1e1e;font-size:14px;">' . $value . '</td>'
			. '</tr>';
	}

	/**
	 * Public wrapper for the email HTML template.
	 *
	 * @since 2.0.0
	 *
	 * @param string $content The inner HTML content.
	 * @return string Complete HTML email.
	 */
	public static function wrap_email_html_public( $content ) {
		return self::wrap_email_html( $content );
	}

	/**
	 * Wrap email content in a professional HTML template.
	 *
	 * @param string $content The inner HTML content.
	 * @return string Complete HTML email.
	 */
	private static function wrap_email_html( $content ) {
		$shop_name = esc_html( get_bloginfo( 'name' ) );

		return '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>'
			. '<body style="margin:0;padding:0;background:#f0f2f5;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;">'
			. '<div style="max-width:600px;margin:24px auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">'
			// Header.
			. '<div style="background:linear-gradient(135deg,#0073aa 0%,#005a87 100%);color:#fff;padding:28px 32px;">'
			. '<h1 style="margin:0;font-size:22px;font-weight:700;letter-spacing:-0.3px;">' . $shop_name . '</h1>'
			. '<p style="margin:6px 0 0;font-size:12px;opacity:0.8;">via LiteQuote for WooCommerce</p>'
			. '</div>'
			// Body.
			. '<div style="padding:32px;">'
			. $content
			. '</div>'
			// Footer.
			. '<div style="padding:20px 32px;background:#f8f9fa;border-top:1px solid #eee;text-align:center;">'
			. '<p style="margin:0;color:#999;font-size:11px;">'
			. sprintf(
				/* translators: %s: LiteQuote */
				__( 'Email automatically generated by %s', 'litequote' ),
				'<strong>LiteQuote</strong>'
			)
			. '</p>'
			. '</div>'
			. '</div></body></html>';
	}
}
