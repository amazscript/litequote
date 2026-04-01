<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core module — Product detection & button/price replacement.
 *
 * Handles:
 * - Detection of quote-mode products (checkbox, zero price, catalogue mode).
 * - Hiding the native WooCommerce price and "Add to Cart" button.
 * - Rendering the LiteQuote quote button on product pages and shop loops.
 * - Adding the "Prix sur demande" checkbox in the product admin panel.
 *
 * @since 1.0.0
 */
class LiteQuote_Core {

	/**
	 * Constructor — register all WooCommerce hooks.
	 */
	public function __construct() {
		// Make quote products non-purchasable (hides Add to Cart + blocks URL add).
		add_filter( 'woocommerce_is_purchasable', array( $this, 'filter_purchasable' ), 10, 2 );

		// Replace price HTML with configurable label.
		add_filter( 'woocommerce_get_price_html', array( $this, 'filter_price_html' ), 10, 2 );

		// Render quote button on single product pages.
		add_action( 'woocommerce_single_product_summary', array( $this, 'render_quote_button' ), 31 );

		// Replace loop "Add to Cart" button for quote products.
		add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'filter_loop_button' ), 10, 2 );

		// Admin: add "Prix sur demande" checkbox in product general tab.
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'render_admin_checkbox' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_admin_checkbox' ) );

		// Adjust button position based on settings.
		$position = get_option( 'litequote_button_position', 'after' );
		if ( 'before' === $position ) {
			remove_action( 'woocommerce_single_product_summary', array( $this, 'render_quote_button' ), 31 );
			add_action( 'woocommerce_single_product_summary', array( $this, 'render_quote_button' ), 29 );
		}

		// Catalogue mode: hide mini-cart widget and show cart page message.
		if ( 'yes' === get_option( 'litequote_catalogue_mode', 'no' ) ) {
			$exclude_ids  = get_option( 'litequote_catalogue_exclude_ids', '' );
			$exclude_cats = get_option( 'litequote_catalogue_exclude_cats', array() );
			$has_exclusions = ! empty( $exclude_ids ) || ! empty( $exclude_cats );

			// Hide mini-cart if no exclusions (no purchasable products at all).
			if ( ! $has_exclusions ) {
				add_filter( 'woocommerce_widget_cart_is_hidden', '__return_true' );
			}

			// Show message on cart page.
			add_action( 'woocommerce_before_cart', array( $this, 'render_catalogue_cart_message' ) );

			// Admin notice.
			if ( is_admin() ) {
				add_action( 'admin_notices', array( $this, 'render_catalogue_admin_notice' ) );
			}
		}
	}

	/**
	 * Filter whether a product is purchasable.
	 *
	 * Returns false for quote-mode products, which hides the native
	 * "Add to Cart" button, quantity field, and blocks direct URL cart additions.
	 *
	 * @since 1.0.0
	 *
	 * @param bool       $purchasable Whether the product is purchasable.
	 * @param WC_Product $product     The product object.
	 * @return bool
	 */
	public function filter_purchasable( $purchasable, $product ) {
		if ( is_admin() ) {
			return $purchasable;
		}

		if ( self::is_quote_enabled( $product ) ) {
			return false;
		}

		return $purchasable;
	}

	/**
	 * Replace the price HTML with a configurable label for quote products.
	 *
	 * @since 1.0.0
	 *
	 * @param string     $price_html The original price HTML.
	 * @param WC_Product $product    The product object.
	 * @return string
	 */
	public function filter_price_html( $price_html, $product ) {
		if ( is_admin() ) {
			return $price_html;
		}

		if ( self::is_quote_enabled( $product ) ) {
			$label = get_option( 'litequote_price_label', __( 'Price on request', 'litequote' ) );
			return '<span class="litequote-price-label">' . esc_html( $label ) . '</span>';
		}

		return $price_html;
	}

	/**
	 * Render the quote button on single product pages.
	 *
	 * Only renders for quote-mode products. Includes data attributes
	 * for the JS modal to read product context.
	 *
	 * @since 1.0.0
	 */
	public function render_quote_button() {
		global $product;

		if ( ! $product instanceof WC_Product || ! self::is_quote_enabled( $product ) ) {
			return;
		}

		$button_text = get_option( 'litequote_button_text', __( 'Request a Quote', 'litequote' ) );
		$sku         = $product->get_sku();

		printf(
			'<button type="button" class="litequote-btn" data-product-id="%d" data-product-name="%s" data-product-sku="%s" data-product-url="%s">%s</button>',
			esc_attr( $product->get_id() ),
			esc_attr( $product->get_name() ),
			esc_attr( $sku ),
			esc_attr( get_permalink( $product->get_id() ) ),
			esc_html( $button_text )
		);
	}

	/**
	 * Replace the loop "Add to Cart" button with a link to the product page.
	 *
	 * On shop/category pages, quote products show a styled link
	 * that redirects to the single product page (no modal in loop).
	 *
	 * @since 1.0.0
	 *
	 * @param string     $button_html The original button HTML.
	 * @param WC_Product $product     The product object.
	 * @return string
	 */
	public function filter_loop_button( $button_html, $product ) {
		if ( is_product() || ! self::is_quote_enabled( $product ) ) {
			return $button_html;
		}

		return sprintf(
			'<a href="%s" class="litequote-btn litequote-btn--loop">%s</a>',
			esc_url( get_permalink( $product->get_id() ) ),
			esc_html__( 'View Product', 'litequote' )
		);
	}

	/**
	 * Render the "Prix sur demande" checkbox in the product admin panel.
	 *
	 * Appears in the General tab of the product data metabox.
	 *
	 * @since 1.0.0
	 */
	public function render_admin_checkbox() {
		woocommerce_wp_checkbox( array(
			'id'          => '_litequote_enabled',
			'label'       => __( 'Price on request', 'litequote' ),
			'description' => __( 'Enable LiteQuote quote mode for this product.', 'litequote' ),
			'desc_tip'    => true,
		) );
	}

	/**
	 * Save the "Prix sur demande" checkbox value.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id The product post ID.
	 */
	public function save_admin_checkbox( $post_id ) {
		$value = isset( $_POST['_litequote_enabled'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_litequote_enabled', $value );
	}

	/**
	 * Check if a product has quote mode enabled.
	 *
	 * Evaluates the trigger mode setting (checkbox, zero price, or both)
	 * and catalogue mode to determine if the product should show
	 * a quote button instead of the standard purchase flow.
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Product $product The product object.
	 * @return bool True if quote mode is active for this product.
	 */
	public static function is_quote_enabled( $product ) {
		if ( ! $product instanceof WC_Product ) {
			return false;
		}

		$trigger_mode = get_option( 'litequote_trigger_mode', 'both' );
		$catalogue    = get_option( 'litequote_catalogue_mode', 'no' );

		// Catalogue mode overrides individual settings.
		if ( 'yes' === $catalogue ) {
			return ! self::is_excluded( $product );
		}

		$meta_enabled  = 'yes' === $product->get_meta( '_litequote_enabled' );
		$price_is_zero = self::has_zero_price( $product );

		switch ( $trigger_mode ) {
			case 'checkbox':
				return $meta_enabled;
			case 'zero_price':
				return $price_is_zero;
			case 'both':
			default:
				return $meta_enabled || $price_is_zero;
		}
	}

	/**
	 * Check if a product has a zero or empty price.
	 *
	 * For variable products, returns true only if ALL variations
	 * have a zero or empty price.
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Product $product The product object.
	 * @return bool
	 */
	private static function has_zero_price( $product ) {
		if ( $product->is_type( 'variable' ) ) {
			$prices = $product->get_variation_prices( true );
			if ( empty( $prices['price'] ) ) {
				return true;
			}
			foreach ( $prices['price'] as $price ) {
				if ( '' !== $price && floatval( $price ) > 0 ) {
					return false;
				}
			}
			return true;
		}

		$price = $product->get_price();
		return '' === $price || 0.0 === floatval( $price );
	}

	/**
	 * Check if a product is excluded from catalogue mode.
	 *
	 * Products can be excluded by ID or by category.
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Product $product The product object.
	 * @return bool True if the product is excluded (should NOT show quote mode).
	 */
	private static function is_excluded( $product ) {
		$excluded_ids = get_option( 'litequote_catalogue_exclude_ids', '' );
		if ( ! empty( $excluded_ids ) ) {
			$ids = array_map( 'absint', array_map( 'trim', explode( ',', $excluded_ids ) ) );
			if ( in_array( $product->get_id(), $ids, true ) ) {
				return true;
			}
		}

		$excluded_cats = get_option( 'litequote_catalogue_exclude_cats', array() );
		if ( ! empty( $excluded_cats ) ) {
			$product_cats = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'ids' ) );
			if ( array_intersect( $product_cats, array_map( 'absint', $excluded_cats ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Show a message on the cart page when catalogue mode is active.
	 *
	 * @since 1.2.0
	 */
	public function render_catalogue_cart_message() {
		echo '<div class="woocommerce-info">'
			. esc_html__( 'This shop works on a quote basis. Use the quote button on each product page to request pricing.', 'litequote' )
			. '</div>';
	}

	/**
	 * Show an admin notice when catalogue mode is active.
	 *
	 * @since 1.2.0
	 */
	public function render_catalogue_admin_notice() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'woocommerce' ) === false ) {
			return;
		}

		printf(
			'<div class="notice notice-info is-dismissible"><p><strong>LiteQuote:</strong> %s</p></div>',
			esc_html__( 'Catalogue mode is active. All prices and add-to-cart buttons are hidden across the entire shop.', 'litequote' )
		);
	}
}
