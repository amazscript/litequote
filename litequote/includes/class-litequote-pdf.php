<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PDF module — Quote PDF generation via FPDF (Extended tier only).
 *
 * Handles:
 * - Generating a structured PDF for each quote submission.
 * - Attaching the PDF to admin and client emails.
 * - Archiving PDFs locally with automatic purge via WP-Cron.
 * - Auto-incrementing quote reference numbers (LQ-YEAR-ID).
 *
 * @since 1.5.0
 */
class LiteQuote_PDF {

	/**
	 * Constructor — register hooks.
	 */
	public function __construct() {
		if ( 'yes' !== get_option( 'litequote_pdf_enabled', 'no' ) ) {
			return;
		}

		add_filter( 'litequote_email_attachments', array( $this, 'generate_and_attach' ), 10, 2 );

		// Schedule purge cron if archive is enabled.
		if ( 'yes' === get_option( 'litequote_pdf_archive', 'no' ) ) {
			if ( ! wp_next_scheduled( 'litequote_pdf_purge' ) ) {
				wp_schedule_event( time(), 'daily', 'litequote_pdf_purge' );
			}
			add_action( 'litequote_pdf_purge', array( $this, 'purge_old_pdfs' ) );
		}
	}

	/**
	 * Generate a PDF and return it as an email attachment.
	 *
	 * @since 1.5.0
	 *
	 * @param array $attachments Existing email attachments.
	 * @param array $data        Sanitized submission data.
	 * @return array Attachments with the PDF file path added.
	 */
	public function generate_and_attach( $attachments, $data ) {
		$pdf_path = $this->generate_pdf( $data );

		if ( $pdf_path && file_exists( $pdf_path ) ) {
			$attachments[] = $pdf_path;
		}

		return $attachments;
	}

	/**
	 * Generate the quote PDF.
	 *
	 * @since 1.5.0
	 *
	 * @param array $data Sanitized submission data.
	 * @return string|false The file path of the generated PDF, or false on failure.
	 */
	public function generate_pdf( $data ) {
		$fpdf_path = LITEQUOTE_PLUGIN_DIR . 'includes/lib/fpdf/fpdf.php';
		if ( ! file_exists( $fpdf_path ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( '[LiteQuote] FPDF library not found at: ' . $fpdf_path );
			}
			return false;
		}

		require_once $fpdf_path;

		// Generate quote reference number.
		$counter   = (int) get_option( 'litequote_pdf_counter', 0 ) + 1;
		update_option( 'litequote_pdf_counter', $counter );
		$reference = sprintf( 'LQ-%s-%04d', date( 'Y' ), $counter );
		$filename  = $reference . '.pdf';

		$shop_name = get_bloginfo( 'name' );
		$date      = wp_date( get_option( 'date_format' ) . ' — ' . get_option( 'time_format' ) );

		try {
			$pdf = new \FPDF( 'P', 'mm', 'A4' );
			$pdf->SetAutoPageBreak( true, 20 );
			$pdf->AddPage();

			// --- Header ---
			$logo = get_option( 'litequote_pdf_logo', '' );

			if ( ! empty( $logo ) ) {
				$logo_path = $this->url_to_path( $logo );
				if ( $logo_path && file_exists( $logo_path ) ) {
					$pdf->Image( $logo_path, 10, 10, 25 );
				}
			}

			// Shop name + reference — always right-aligned.
			$pdf->SetY( 10 );
			$pdf->SetFont( 'Helvetica', 'B', 18 );
			$pdf->Cell( 0, 10, $this->utf8( $shop_name ), 0, 1, 'R' );
			$pdf->SetFont( 'Helvetica', '', 10 );
			$pdf->SetTextColor( 120, 120, 120 );
			$pdf->Cell( 0, 6, $this->utf8( __( 'Quote Request', 'litequote' ) . ' — ' . $reference ), 0, 1, 'R' );
			$pdf->Cell( 0, 6, $this->utf8( $date ), 0, 1, 'R' );
			$pdf->SetTextColor( 0, 0, 0 );

			// Force content below header.
			$pdf->SetY( 40 );

			// --- Separator ---
			$pdf->Ln( 2 );
			$pdf->SetDrawColor( 0, 115, 170 );
			$pdf->SetLineWidth( 0.8 );
			$pdf->Line( 10, $pdf->GetY(), 200, $pdf->GetY() );
			$pdf->Ln( 8 );

			// --- Customer Section ---
			$pdf->SetFont( 'Helvetica', 'B', 12 );
			$pdf->SetTextColor( 0, 115, 170 );
			$pdf->Cell( 0, 8, $this->utf8( __( 'Customer', 'litequote' ) ), 0, 1 );
			$pdf->SetTextColor( 0, 0, 0 );

			$pdf->SetFont( 'Helvetica', '', 10 );
			$this->pdf_row( $pdf, __( 'Name', 'litequote' ), $data['name'] );
			if ( ! empty( $data['company'] ) ) {
				$this->pdf_row( $pdf, __( 'Company', 'litequote' ), $data['company'] );
			}
			$this->pdf_row( $pdf, __( 'Email', 'litequote' ), $data['email'] );
			if ( ! empty( $data['phone'] ) ) {
				$this->pdf_row( $pdf, __( 'Phone', 'litequote' ), $data['phone'] );
			}

			$pdf->Ln( 6 );

			// --- Product Section ---
			$pdf->SetFont( 'Helvetica', 'B', 12 );
			$pdf->SetTextColor( 0, 115, 170 );
			$pdf->Cell( 0, 8, $this->utf8( __( 'Product', 'litequote' ) ), 0, 1 );
			$pdf->SetTextColor( 0, 0, 0 );

			$pdf->SetFont( 'Helvetica', '', 10 );
			$this->pdf_row( $pdf, __( 'Product', 'litequote' ), $data['product_name'] );
			if ( ! empty( $data['sku'] ) ) {
				$this->pdf_row( $pdf, __( 'SKU', 'litequote' ), $data['sku'] );
			}
			if ( ! empty( $data['variation'] ) ) {
				$this->pdf_row( $pdf, __( 'Variation', 'litequote' ), $data['variation'] );
			}
			$this->pdf_row( $pdf, __( 'Quantity', 'litequote' ), $data['quantity'] ?? 1 );

			if ( ! empty( $data['product_id'] ) ) {
				$url = get_permalink( $data['product_id'] );
				if ( $url ) {
					$this->pdf_row( $pdf, __( 'URL', 'litequote' ), $url );
				}
			}

			$pdf->Ln( 6 );

			// --- Message Section ---
			if ( ! empty( $data['message'] ) ) {
				$pdf->SetFont( 'Helvetica', 'B', 12 );
				$pdf->SetTextColor( 0, 115, 170 );
				$pdf->Cell( 0, 8, $this->utf8( __( 'Customer Message', 'litequote' ) ), 0, 1 );
				$pdf->SetTextColor( 0, 0, 0 );

				$pdf->SetFont( 'Helvetica', '', 10 );
				$pdf->SetFillColor( 245, 245, 245 );
				$pdf->MultiCell( 0, 6, $this->utf8( $data['message'] ), 0, 'L', true );
			}

			// --- Footer ---
			$pdf->SetY( -25 );
			$pdf->SetFont( 'Helvetica', 'I', 8 );
			$pdf->SetTextColor( 150, 150, 150 );
			$pdf->Cell( 0, 5, $this->utf8(
				sprintf( __( 'Automatically generated by LiteQuote — %s', 'litequote' ), $reference )
			), 0, 1, 'C' );

			// --- Output ---
			$archive_enabled = 'yes' === get_option( 'litequote_pdf_archive', 'no' );

			if ( $archive_enabled ) {
				$dir = $this->get_archive_dir();
				$filepath = $dir . $filename;
				$pdf->Output( 'F', $filepath );
			} else {
				// Save to temp directory.
				$filepath = wp_tempnam( $filename );
				$pdf->Output( 'F', $filepath );
			}

			return $filepath;

		} catch ( \Exception $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( '[LiteQuote] PDF generation failed: ' . $e->getMessage() );
			}
			return false;
		}
	}

	/**
	 * Render a label/value row in the PDF.
	 *
	 * @param FPDF   $pdf   The FPDF instance.
	 * @param string $label The row label.
	 * @param string $value The row value.
	 */
	private function pdf_row( $pdf, $label, $value ) {
		$pdf->SetFont( 'Helvetica', 'B', 10 );
		$pdf->Cell( 45, 6, $this->utf8( $label . ':' ), 0, 0 );
		$pdf->SetFont( 'Helvetica', '', 10 );
		$pdf->Cell( 0, 6, $this->utf8( (string) $value ), 0, 1 );
	}

	/**
	 * Convert UTF-8 text to ISO-8859-1 for FPDF compatibility.
	 *
	 * @param string $text UTF-8 text.
	 * @return string ISO-8859-1 text.
	 */
	private function utf8( $text ) {
		return iconv( 'UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', (string) $text );
	}

	/**
	 * Convert a URL to a local file path.
	 *
	 * @param string $url The URL to convert.
	 * @return string|false The local file path, or false.
	 */
	private function url_to_path( $url ) {
		$upload_dir = wp_upload_dir();
		if ( strpos( $url, $upload_dir['baseurl'] ) !== false ) {
			return str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $url );
		}
		return false;
	}

	/**
	 * Get (and create) the archive directory.
	 *
	 * @return string The directory path with trailing slash.
	 */
	private function get_archive_dir() {
		$upload_dir = wp_upload_dir();
		$dir        = $upload_dir['basedir'] . '/litequote-quotes/';

		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );

			// Protect with .htaccess.
			file_put_contents( $dir . '.htaccess', "Order deny,allow\nDeny from all\n" );

			// Add index.php.
			file_put_contents( $dir . 'index.php', "<?php\n// Silence is golden.\n" );
		}

		return $dir;
	}

	/**
	 * Purge archived PDFs older than the retention period.
	 *
	 * Called daily by WP-Cron when archive is enabled.
	 *
	 * @since 1.5.0
	 */
	public function purge_old_pdfs() {
		$retention = (int) get_option( 'litequote_pdf_retention_days', 90 );
		$dir       = $this->get_archive_dir();

		if ( ! is_dir( $dir ) ) {
			return;
		}

		$files = glob( $dir . 'LQ-*.pdf' );
		if ( ! is_array( $files ) ) {
			return;
		}

		$cutoff = time() - ( $retention * DAY_IN_SECONDS );

		foreach ( $files as $file ) {
			if ( filemtime( $file ) < $cutoff ) {
				unlink( $file );
			}
		}
	}

	/**
	 * Generate a professional quote PDF with price table.
	 *
	 * Used when the merchant sends a quote reply (v2.0).
	 *
	 * @since 2.0.0
	 *
	 * @param int $quote_id The quote CPT post ID.
	 * @return string|false The file path of the generated PDF, or false on failure.
	 */
	public function generate_quote_pdf( $quote_id ) {
		$fpdf_path = LITEQUOTE_PLUGIN_DIR . 'includes/lib/fpdf/fpdf.php';
		if ( ! file_exists( $fpdf_path ) ) {
			return false;
		}

		require_once $fpdf_path;

		$m = function( $key ) use ( $quote_id ) {
			return get_post_meta( $quote_id, '_lq_' . $key, true );
		};

		$reference = get_the_title( $quote_id );
		$filename  = $reference . '-quote.pdf';
		$shop_name = get_bloginfo( 'name' );
		$date      = wp_date( get_option( 'date_format' ) );
		$currency  = html_entity_decode( get_woocommerce_currency_symbol() );

		$price    = floatval( get_post_meta( $quote_id, '_lq_reply_price', true ) );
		$qty      = max( 1, intval( $m( 'quantity' ) ) );
		$discount = floatval( get_post_meta( $quote_id, '_lq_reply_discount', true ) );
		$notes    = get_post_meta( $quote_id, '_lq_reply_notes', true );
		$validity = get_post_meta( $quote_id, '_lq_reply_validity', true ) ?: 30;

		$subtotal  = $price * $qty;
		$disc_amt  = $discount ? $subtotal * $discount / 100 : 0;
		$total     = $subtotal - $disc_amt;

		try {
			$pdf = new \FPDF( 'P', 'mm', 'A4' );
			$pdf->SetAutoPageBreak( true, 25 );
			$pdf->AddPage();

			// --- Header ---
			$logo = get_option( 'litequote_pdf_logo', '' );
			$has_logo = false;

			if ( ! empty( $logo ) ) {
				$logo_path = $this->url_to_path( $logo );
				if ( $logo_path && file_exists( $logo_path ) ) {
					$pdf->Image( $logo_path, 10, 10, 30 );
					$has_logo = true;
				}
			}

			// Shop info — right aligned.
			$pdf->SetY( 10 );
			$pdf->SetFont( 'Helvetica', 'B', 16 );
			$pdf->Cell( 0, 8, $this->utf8( $shop_name ), 0, 1, 'R' );
			$pdf->SetFont( 'Helvetica', '', 9 );
			$pdf->SetTextColor( 120, 120, 120 );

			$store_address = get_option( 'woocommerce_store_address', '' );
			$store_city    = get_option( 'woocommerce_store_city', '' );
			$store_postcode = get_option( 'woocommerce_store_postcode', '' );
			if ( $store_address ) {
				$pdf->Cell( 0, 5, $this->utf8( $store_address ), 0, 1, 'R' );
			}
			if ( $store_city || $store_postcode ) {
				$pdf->Cell( 0, 5, $this->utf8( trim( $store_postcode . ' ' . $store_city ) ), 0, 1, 'R' );
			}
			$pdf->Cell( 0, 5, $this->utf8( get_bloginfo( 'admin_email' ) ), 0, 1, 'R' );
			$pdf->SetTextColor( 0, 0, 0 );

			// Move below logo if present.
			if ( $has_logo ) {
				$pdf->SetY( max( $pdf->GetY(), 42 ) );
			}
			$pdf->Ln( 4 );

			// --- DEVIS title bar ---
			$pdf->SetFillColor( 0, 115, 170 );
			$pdf->SetTextColor( 255, 255, 255 );
			$pdf->SetFont( 'Helvetica', 'B', 14 );
			$pdf->Cell( 0, 12, $this->utf8( '  ' . __( 'QUOTE', 'litequote' ) . '  ' . $reference ), 0, 1, 'L', true );
			$pdf->SetTextColor( 0, 0, 0 );

			$pdf->SetFont( 'Helvetica', '', 10 );
			$pdf->Ln( 2 );
			$pdf->Cell( 95, 6, $this->utf8( __( 'Date', 'litequote' ) . ': ' . $date ), 0, 0 );
			$pdf->Cell( 0, 6, $this->utf8( __( 'Valid for', 'litequote' ) . ': ' . $validity . ' ' . __( 'days', 'litequote' ) ), 0, 1, 'R' );

			$pdf->Ln( 6 );

			// --- Client box ---
			$pdf->SetFillColor( 248, 249, 250 );
			$pdf->SetFont( 'Helvetica', 'B', 10 );
			$pdf->Cell( 0, 7, $this->utf8( '  ' . __( 'Customer', 'litequote' ) ), 0, 1, 'L', true );
			$pdf->SetFont( 'Helvetica', '', 10 );
			$pdf->Cell( 0, 6, $this->utf8( $m( 'name' ) . ( $m( 'company' ) ? ' — ' . $m( 'company' ) : '' ) ), 0, 1 );
			$pdf->Cell( 0, 6, $this->utf8( $m( 'email' ) . ( $m( 'phone' ) ? '  |  ' . $m( 'phone' ) : '' ) ), 0, 1 );

			$pdf->Ln( 6 );

			// --- Price table ---
			// Header row.
			$pdf->SetFillColor( 0, 115, 170 );
			$pdf->SetTextColor( 255, 255, 255 );
			$pdf->SetFont( 'Helvetica', 'B', 10 );
			$pdf->Cell( 80, 8, $this->utf8( '  ' . __( 'Product', 'litequote' ) ), 0, 0, 'L', true );
			$pdf->Cell( 30, 8, $this->utf8( __( 'Unit Price', 'litequote' ) ), 0, 0, 'C', true );
			$pdf->Cell( 20, 8, $this->utf8( __( 'Qty', 'litequote' ) ), 0, 0, 'C', true );
			$pdf->Cell( 0, 8, $this->utf8( __( 'Subtotal', 'litequote' ) . '  ' ), 0, 1, 'R', true );
			$pdf->SetTextColor( 0, 0, 0 );

			// Product row.
			$pdf->SetFont( 'Helvetica', '', 10 );
			$pdf->SetFillColor( 255, 255, 255 );
			$product_desc = $m( 'product_name' );
			if ( $m( 'sku' ) ) {
				$product_desc .= ' (Ref. ' . $m( 'sku' ) . ')';
			}
			if ( $m( 'variation' ) ) {
				$product_desc .= "\n" . $m( 'variation' );
			}
			$pdf->Cell( 80, 8, $this->utf8( '  ' . $product_desc ), 'B', 0, 'L' );
			$pdf->Cell( 30, 8, $this->utf8( $currency . ' ' . number_format( $price, 2 ) ), 'B', 0, 'C' );
			$pdf->Cell( 20, 8, $this->utf8( (string) $qty ), 'B', 0, 'C' );
			$pdf->Cell( 0, 8, $this->utf8( $currency . ' ' . number_format( $subtotal, 2 ) . '  ' ), 'B', 1, 'R' );

			// Discount row.
			if ( $discount > 0 ) {
				$pdf->SetFont( 'Helvetica', '', 9 );
				$pdf->Cell( 130, 7, '', 0, 0 );
				$pdf->Cell( 30, 7, $this->utf8( __( 'Discount', 'litequote' ) . ' (' . $discount . '%)' ), 0, 0, 'R' );
				$pdf->SetTextColor( 220, 50, 50 );
				$pdf->Cell( 0, 7, $this->utf8( '-' . $currency . ' ' . number_format( $disc_amt, 2 ) . '  ' ), 0, 1, 'R' );
				$pdf->SetTextColor( 0, 0, 0 );
			}

			// Total row.
			$pdf->Ln( 2 );
			$pdf->SetFillColor( 248, 249, 250 );
			$pdf->SetFont( 'Helvetica', 'B', 12 );
			$pdf->Cell( 130, 10, '', 0, 0 );
			$pdf->Cell( 30, 10, $this->utf8( __( 'Total', 'litequote' ) ), 0, 0, 'R', true );
			$pdf->SetTextColor( 0, 115, 170 );
			$pdf->Cell( 0, 10, $this->utf8( $currency . ' ' . number_format( $total, 2 ) . '  ' ), 0, 1, 'R', true );
			$pdf->SetTextColor( 0, 0, 0 );

			$pdf->Ln( 8 );

			// --- Notes ---
			if ( $notes ) {
				$pdf->SetFont( 'Helvetica', 'B', 10 );
				$pdf->Cell( 0, 7, $this->utf8( __( 'Notes / Conditions', 'litequote' ) ), 0, 1 );
				$pdf->SetFont( 'Helvetica', '', 9 );
				$pdf->SetFillColor( 248, 249, 250 );
				$pdf->MultiCell( 0, 5, $this->utf8( $notes ), 0, 'L', true );
				$pdf->Ln( 4 );
			}

			// --- Validity ---
			$pdf->SetFont( 'Helvetica', 'I', 9 );
			$pdf->SetTextColor( 120, 120, 120 );
			$pdf->Cell( 0, 6, $this->utf8( sprintf( __( 'This quote is valid for %d days.', 'litequote' ), $validity ) ), 0, 1 );

			// --- Legal mentions ---
			$legal = get_option( 'litequote_legal_mentions', '' );
			if ( $legal ) {
				$pdf->Ln( 4 );
				$pdf->SetFont( 'Helvetica', '', 7 );
				$pdf->MultiCell( 0, 4, $this->utf8( $legal ), 0, 'L' );
			}

			// --- Footer ---
			$pdf->SetY( -20 );
			$pdf->SetFont( 'Helvetica', 'I', 7 );
			$pdf->SetTextColor( 170, 170, 170 );
			$pdf->Cell( 0, 5, $this->utf8( $shop_name . ' — ' . $reference . ' — ' . __( 'Generated by LiteQuote', 'litequote' ) ), 0, 0, 'C' );

			// --- Save ---
			$dir      = $this->get_archive_dir();
			$filepath = $dir . $filename;
			$pdf->Output( 'F', $filepath );

			update_post_meta( $quote_id, '_lq_quote_pdf_path', $filepath );

			return $filepath;

		} catch ( \Exception $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( '[LiteQuote] Quote PDF generation failed: ' . $e->getMessage() );
			}
			return false;
		}
	}
}
