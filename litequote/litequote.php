<?php
/**
 * Plugin Name:       LiteQuote for WooCommerce
 * Plugin URI:        https://amazscript.com/litequote
 * Description:       Ultra-lightweight quote request plugin for WooCommerce. Replace "Add to Cart" with a quote button in under 150 KB, zero jQuery.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      8.0
 * Author:            Denis — AmazScript / ByteSproutLab
 * Author URI:        https://amazscript.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       litequote
 * Domain Path:       /languages
 * WC requires at least: 8.0
 * WC tested up to:      9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin constants.
 */
define( 'LITEQUOTE_VERSION', '1.0.0' );
define( 'LITEQUOTE_PLUGIN_FILE', __FILE__ );
define( 'LITEQUOTE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LITEQUOTE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LITEQUOTE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Check requirements before loading the plugin.
 *
 * @return bool
 */
function litequote_check_requirements() {
	$errors = array();

	if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
		$errors[] = 'LiteQuote requires PHP 8.0 or higher.';
	}

	if ( ! class_exists( 'WooCommerce' ) ) {
		$errors[] = 'LiteQuote requires WooCommerce to be installed and activated.';
	}

	if ( class_exists( 'WooCommerce' ) && defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '8.0', '<' ) ) {
		$errors[] = 'LiteQuote requires WooCommerce 8.0 or higher.';
	}

	if ( ! empty( $errors ) ) {
		add_action( 'admin_notices', function () use ( $errors ) {
			foreach ( $errors as $error ) {
				printf(
					'<div class="notice notice-error"><p><strong>LiteQuote:</strong> %s</p></div>',
					esc_html( $error )
				);
			}
		} );
		return false;
	}

	return true;
}

/**
 * Load plugin text domain for translations.
 */
function litequote_load_textdomain() {
	load_plugin_textdomain( 'litequote', false, dirname( LITEQUOTE_PLUGIN_BASENAME ) . '/languages' );
}
add_action( 'init', 'litequote_load_textdomain' );

/**
 * Autoload plugin classes.
 *
 * @param string $class_name The class name to load.
 */
function litequote_autoloader( $class_name ) {
	$prefix = 'LiteQuote_';

	if ( strpos( $class_name, $prefix ) !== 0 ) {
		return;
	}

	$class_file = str_replace( $prefix, '', $class_name );
	$class_file = strtolower( str_replace( '_', '-', $class_file ) );

	$paths = array(
		LITEQUOTE_PLUGIN_DIR . 'includes/class-litequote-' . $class_file . '.php',
		LITEQUOTE_PLUGIN_DIR . 'admin/class-litequote-' . $class_file . '.php',
	);

	foreach ( $paths as $path ) {
		if ( file_exists( $path ) ) {
			require_once $path;
			return;
		}
	}
}
spl_autoload_register( 'litequote_autoloader' );

/**
 * Plugin activation.
 */
function litequote_activate() {
	if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
		deactivate_plugins( LITEQUOTE_PLUGIN_BASENAME );
		wp_die(
			'LiteQuote requires PHP 8.0 or higher.',
			'Plugin Activation Error',
			array( 'back_link' => true )
		);
	}

	$defaults = array(
		'litequote_trigger_mode'       => 'both',
		'litequote_catalogue_mode'     => 'no',
		'litequote_catalogue_exclude_cats' => array(),
		'litequote_catalogue_exclude_ids' => '',
		'litequote_button_text'        => 'Request a Quote',
		'litequote_button_bg_color'    => '#0073aa',
		'litequote_button_text_color'  => '#ffffff',
		'litequote_button_position'    => 'after',
		'litequote_price_label'        => 'Price on request',
		'litequote_admin_email'        => get_bloginfo( 'admin_email' ),
		'litequote_auto_reply'         => 'no',
		'litequote_auto_reply_template' => '',
		'litequote_whatsapp_number'    => '',
		'litequote_whatsapp_mode'      => 'form_only',
		'litequote_whatsapp_template'  => '',
		'litequote_pdf_enabled'        => 'no',
		'litequote_pdf_logo'           => '',
		'litequote_pdf_archive'        => 'no',
		'litequote_pdf_retention_days' => 90,
		'litequote_debug_mode'         => 'no',
		'litequote_custom_css'         => '',
	);

	foreach ( $defaults as $option => $value ) {
		if ( get_option( $option ) === false ) {
			add_option( $option, $value, '', 'yes' );
		}
	}
}
register_activation_hook( __FILE__, 'litequote_activate' );

/**
 * Plugin deactivation.
 */
function litequote_deactivate() {
	wp_clear_scheduled_hook( 'litequote_pdf_purge' );
}
register_deactivation_hook( __FILE__, 'litequote_deactivate' );

/**
 * Enqueue front-end assets on WooCommerce pages only.
 */
function litequote_enqueue_assets() {
	if ( ! is_product() && ! is_shop() && ! is_product_category() ) {
		return;
	}

	wp_enqueue_style(
		'litequote',
		LITEQUOTE_PLUGIN_URL . 'assets/css/litequote.css',
		array(),
		LITEQUOTE_VERSION
	);

	wp_enqueue_script(
		'litequote-modal',
		LITEQUOTE_PLUGIN_URL . 'assets/js/litequote-modal.js',
		array(),
		LITEQUOTE_VERSION,
		array( 'strategy' => 'defer', 'in_footer' => true )
	);

	$localize_data = apply_filters( 'litequote_modal_data', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'litequote_nonce' ),
		'i18n'    => array(
			'modalTitle'       => get_option( 'litequote_button_text', __( 'Request a Quote', 'litequote' ) ),
			'sending'          => __( 'Sending...', 'litequote' ),
			'success'          => __( 'Thank you! Your quote request has been sent. We will get back to you shortly.', 'litequote' ),
			'error'            => __( 'An error occurred. Please try again.', 'litequote' ),
			'send'             => __( 'Send Request', 'litequote' ),
			'close'            => __( 'Close', 'litequote' ),
			'labelName'           => __( 'Full Name', 'litequote' ),
			'labelCompany'       => __( 'Company', 'litequote' ),
			'labelEmail'         => __( 'Email', 'litequote' ),
			'labelPhone'         => __( 'Phone', 'litequote' ),
			'labelQuantity'      => __( 'Quantity', 'litequote' ),
			'labelMessage'       => __( 'Message', 'litequote' ),
			'placeholderName'    => __( 'Your name', 'litequote' ),
			'placeholderCompany' => __( 'Company name (optional)', 'litequote' ),
			'placeholderEmail'   => __( 'your@email.com', 'litequote' ),
			'placeholderPhone'   => __( '+1 555 123 4567', 'litequote' ),
			'invalidName'      => __( 'Please enter your name.', 'litequote' ),
			'invalidEmail'     => __( 'Please enter a valid email address.', 'litequote' ),
			'invalidPhone'     => __( 'Invalid phone number.', 'litequote' ),
			'prefillMessage'   => __( 'Hello, I would like a quote for:', 'litequote' ),
			'prefillRef'       => __( 'Ref.', 'litequote' ),
			'prefillVariation' => __( 'Variation:', 'litequote' ),
		),
	) );

	wp_localize_script( 'litequote-modal', 'litequoteData', $localize_data );

	$custom_css = get_option( 'litequote_custom_css', '' );
	if ( ! empty( $custom_css ) ) {
		wp_add_inline_style( 'litequote', wp_strip_all_tags( $custom_css ) );
	}

	$btn_bg    = sanitize_hex_color( get_option( 'litequote_button_bg_color', '#0073aa' ) );
	$btn_color = sanitize_hex_color( get_option( 'litequote_button_text_color', '#ffffff' ) );
	$inline_vars = sprintf(
		':root { --litequote-btn-bg: %s; --litequote-btn-color: %s; }',
		$btn_bg ?: '#0073aa',
		$btn_color ?: '#ffffff'
	);
	wp_add_inline_style( 'litequote', $inline_vars );
}
add_action( 'wp_enqueue_scripts', 'litequote_enqueue_assets' );

/**
 * Check requirements early (needs WooCommerce to be loaded).
 */
function litequote_check_early() {
	litequote_check_requirements();
}
add_action( 'plugins_loaded', 'litequote_check_early' );

/**
 * Initialize plugin modules after translations are loaded.
 */
function litequote_init() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	// Core — Detection & button replacement.
	new LiteQuote_Core();

	// Form — Modal rendering & AJAX handler.
	new LiteQuote_Form();

	// Email — Admin notification & auto-reply.
	new LiteQuote_Email();

	// Security — Nonce verification, honeypot, sanitization.
	new LiteQuote_Security();

	// WhatsApp — wa.me link builder & button.
	new LiteQuote_WhatsApp();

	// PDF — Quote PDF generation (Extended tier).
	new LiteQuote_PDF();

	// Quote CPT — Store submissions in database (v2.0).
	new LiteQuote_Quote_CPT();

	// Settings — Options registration & retrieval.
	new LiteQuote_Settings();

	// Admin — Settings page (admin only).
	if ( is_admin() ) {
		new LiteQuote_Admin();
	}

	// Dev SMTP (Mailpit) — only when WP_DEBUG is true.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		require_once LITEQUOTE_PLUGIN_DIR . 'includes/class-litequote-smtp-dev.php';
		new LiteQuote_SMTP_Dev();
	}
}
add_action( 'init', 'litequote_init', 0 );
