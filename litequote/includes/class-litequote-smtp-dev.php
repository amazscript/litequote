<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dev-only SMTP configuration for Mailpit.
 *
 * Redirects all wp_mail() to the Mailpit container (port 1025).
 * This file is auto-loaded only when WP_DEBUG is true.
 *
 * In production, the merchant uses FluentSMTP or WP Mail SMTP instead.
 */
class LiteQuote_SMTP_Dev {

	/**
	 * Constructor — register SMTP hooks when WP_DEBUG is enabled.
	 */
	public function __construct() {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			add_action( 'phpmailer_init', array( $this, 'configure_mailpit' ) );
			add_filter( 'wp_mail_from', array( $this, 'set_from_email' ) );
			add_filter( 'wp_mail_from_name', array( $this, 'set_from_name' ) );
		}
	}

	/**
	 * Set the From email address for dev environment.
	 *
	 * @return string
	 */
	public function set_from_email() {
		return 'noreply@litequote.local';
	}

	/**
	 * Set the From name for dev environment.
	 *
	 * @return string
	 */
	public function set_from_name() {
		return get_bloginfo( 'name' ) ?: 'LiteQuote Dev';
	}

	/**
	 * Configure PHPMailer to use Mailpit SMTP.
	 *
	 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer The PHPMailer instance.
	 */
	public function configure_mailpit( $phpmailer ) {
		$phpmailer->isSMTP();
		$phpmailer->Host       = 'mailpit';
		$phpmailer->Port       = 1025;
		$phpmailer->SMTPAuth   = false;
		$phpmailer->SMTPSecure = '';
		$phpmailer->From       = 'noreply@litequote.local';
		$phpmailer->FromName   = get_bloginfo( 'name' ) ?: 'LiteQuote Dev';
	}
}
