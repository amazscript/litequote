<?php
/**
 * LiteQuote Uninstall
 *
 * Removes all plugin data from the database when the plugin is deleted.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove all plugin options.
$options = array(
	'litequote_trigger_mode',
	'litequote_catalogue_mode',
	'litequote_catalogue_exclude_cats',
	'litequote_catalogue_exclude_ids',
	'litequote_button_text',
	'litequote_button_bg_color',
	'litequote_button_text_color',
	'litequote_button_position',
	'litequote_price_label',
	'litequote_admin_email',
	'litequote_auto_reply',
	'litequote_auto_reply_template',
	'litequote_whatsapp_number',
	'litequote_whatsapp_mode',
	'litequote_whatsapp_template',
	'litequote_pdf_enabled',
	'litequote_pdf_logo',
	'litequote_pdf_archive',
	'litequote_pdf_retention_days',
	'litequote_pdf_counter',
	'litequote_debug_mode',
	'litequote_custom_css',
);

foreach ( $options as $option ) {
	delete_option( $option );
}

// Remove all product meta.
global $wpdb;

$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
		$wpdb->esc_like( '_litequote_' ) . '%'
	)
);

// Remove PDF archive directory.
$upload_dir = wp_upload_dir();
$pdf_dir    = $upload_dir['basedir'] . '/litequote-quotes/';

if ( is_dir( $pdf_dir ) ) {
	$files = glob( $pdf_dir . '*' );
	if ( is_array( $files ) ) {
		foreach ( $files as $file ) {
			if ( is_file( $file ) ) {
				unlink( $file );
			}
		}
	}
	rmdir( $pdf_dir );
}

// Unschedule WP-Cron events.
$timestamp = wp_next_scheduled( 'litequote_pdf_purge' );
if ( $timestamp ) {
	wp_unschedule_event( $timestamp, 'litequote_pdf_purge' );
}
