<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Quote CPT module — Custom Post Type for storing quote requests.
 *
 * Handles:
 * - Registering the litequote_quote CPT.
 * - Registering custom post statuses (pending, quoted, accepted, rejected).
 * - Saving quote submissions as CPT posts.
 * - Generating unique quote reference numbers.
 * - Adding a badge counter in the admin menu.
 *
 * @since 2.0.0
 */
class LiteQuote_Quote_CPT {

	/** @var string Post type slug. */
	const POST_TYPE = 'litequote_quote';

	/**
	 * Constructor — register hooks.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_post_statuses' ) );
		add_action( 'admin_menu', array( $this, 'add_quotes_menu' ) );
		add_filter( 'add_menu_classes', array( $this, 'add_pending_badge' ) );
		add_action( 'admin_init', array( $this, 'handle_csv_export' ) );
		add_action( 'admin_init', array( $this, 'handle_bulk_actions' ) );
	}

	/**
	 * Register the litequote_quote Custom Post Type.
	 *
	 * @since 2.0.0
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => __( 'Quotes', 'litequote' ),
			'singular_name'      => __( 'Quote', 'litequote' ),
			'menu_name'          => __( 'Quotes', 'litequote' ),
			'all_items'          => __( 'All Quotes', 'litequote' ),
			'search_items'       => __( 'Search Quotes', 'litequote' ),
			'not_found'          => __( 'No quotes found.', 'litequote' ),
			'not_found_in_trash' => __( 'No quotes found in trash.', 'litequote' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => false,
			'show_ui'             => false, // We handle the UI ourselves.
			'show_in_menu'        => false,
			'show_in_rest'        => false,
			'supports'            => array( 'title', 'custom-fields' ),
			'capability_type'     => 'post',
			'capabilities'        => array(
				'create_posts' => 'manage_woocommerce',
				'edit_posts'   => 'manage_woocommerce',
				'delete_posts' => 'manage_woocommerce',
			),
			'map_meta_cap'        => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'has_archive'         => false,
		);

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * Register custom post statuses for quotes.
	 *
	 * @since 2.0.0
	 */
	public function register_post_statuses() {
		$statuses = array(
			'lq-pending'  => array(
				'label'       => __( 'Pending', 'litequote' ),
				'label_count' => _n_noop( 'Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'litequote' ),
				'color'       => '#f0ad4e',
			),
			'lq-quoted'   => array(
				'label'       => __( 'Quoted', 'litequote' ),
				'label_count' => _n_noop( 'Quoted <span class="count">(%s)</span>', 'Quoted <span class="count">(%s)</span>', 'litequote' ),
				'color'       => '#0073aa',
			),
			'lq-accepted' => array(
				'label'       => __( 'Accepted', 'litequote' ),
				'label_count' => _n_noop( 'Accepted <span class="count">(%s)</span>', 'Accepted <span class="count">(%s)</span>', 'litequote' ),
				'color'       => '#46b450',
			),
			'lq-rejected' => array(
				'label'       => __( 'Rejected', 'litequote' ),
				'label_count' => _n_noop( 'Rejected <span class="count">(%s)</span>', 'Rejected <span class="count">(%s)</span>', 'litequote' ),
				'color'       => '#dc3232',
			),
		);

		foreach ( $statuses as $slug => $status ) {
			register_post_status( $slug, array(
				'label'                  => $status['label'],
				'label_count'            => $status['label_count'],
				'public'                 => false,
				'internal'               => true,
				'show_in_admin_all_list' => true,
			) );
		}
	}

	/**
	 * Add the Quotes submenu under WooCommerce.
	 *
	 * @since 2.0.0
	 */
	public function add_quotes_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Quotes', 'litequote' ),
			__( 'Quotes', 'litequote' ),
			'manage_woocommerce',
			'litequote-quotes',
			array( $this, 'render_quotes_page' )
		);
	}

	/**
	 * Add a pending count badge to the Quotes menu item.
	 *
	 * @since 2.0.0
	 *
	 * @param array $menu The admin menu items.
	 * @return array
	 */
	public function add_pending_badge( $menu ) {
		$count = self::get_count_by_status( 'lq-pending' );
		if ( $count < 1 ) {
			return $menu;
		}

		foreach ( $menu as &$item ) {
			if ( isset( $item[2] ) && 'woocommerce' === $item[2] ) {
				// Find the Quotes submenu.
				global $submenu;
				if ( isset( $submenu['woocommerce'] ) ) {
					foreach ( $submenu['woocommerce'] as &$sub_item ) {
						if ( 'litequote-quotes' === $sub_item[2] ) {
							$sub_item[0] .= sprintf(
								' <span class="awaiting-mod update-plugins count-%d"><span class="pending-count">%d</span></span>',
								$count,
								$count
							);
							break;
						}
					}
				}
				break;
			}
		}

		return $menu;
	}

	/**
	 * Render the quotes list page (placeholder — Sprint 10 will build the full table).
	 *
	 * @since 2.0.0
	 */
	public function render_quotes_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		// Route to view/reply pages.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action   = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$quote_id = isset( $_GET['quote_id'] ) ? absint( $_GET['quote_id'] ) : 0;

		if ( 'view' === $action && $quote_id ) {
			$this->render_quote_detail( $quote_id );
			return;
		}

		if ( 'reply' === $action && $quote_id ) {
			$this->render_quote_reply( $quote_id );
			return;
		}

		if ( 'update_status' === $action && $quote_id ) {
			$this->handle_status_update( $quote_id );
			return;
		}

		$counts = array(
			'all'      => self::get_count_by_status(),
			'pending'  => self::get_count_by_status( 'lq-pending' ),
			'quoted'   => self::get_count_by_status( 'lq-quoted' ),
			'accepted' => self::get_count_by_status( 'lq-accepted' ),
			'rejected' => self::get_count_by_status( 'lq-rejected' ),
		);

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all';

		$query_args = array(
			'post_type'      => self::POST_TYPE,
			'posts_per_page' => 20,
			'paged'          => max( 1, absint( $_GET['paged'] ?? 1 ) ),
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		if ( 'all' !== $current_status ) {
			$query_args['post_status'] = 'lq-' . $current_status;
		} else {
			$query_args['post_status'] = array( 'lq-pending', 'lq-quoted', 'lq-accepted', 'lq-rejected' );
		}

		// Search.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
		if ( ! empty( $search ) ) {
			$query_args['s'] = $search;
		}

		$quotes = new WP_Query( $query_args );

		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Quotes', 'litequote' ); ?></h1>
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=litequote-quotes&action=export_csv&status=' . $current_status ), 'litequote_export_csv' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Export CSV', 'litequote' ); ?></a>

			<!-- Status filters -->
			<ul class="subsubsub">
				<?php
				$filters = array(
					'all'      => __( 'All', 'litequote' ),
					'pending'  => __( 'Pending', 'litequote' ),
					'quoted'   => __( 'Quoted', 'litequote' ),
					'accepted' => __( 'Accepted', 'litequote' ),
					'rejected' => __( 'Rejected', 'litequote' ),
				);
				$i = 0;
				foreach ( $filters as $key => $label ) :
					$i++;
					$url   = admin_url( 'admin.php?page=litequote-quotes&status=' . $key );
					$class = $current_status === $key ? 'current' : '';
					$count = $counts[ $key ];
					?>
					<li>
						<a href="<?php echo esc_url( $url ); ?>" class="<?php echo esc_attr( $class ); ?>">
							<?php echo esc_html( $label ); ?>
							<span class="count">(<?php echo esc_html( $count ); ?>)</span>
						</a>
						<?php echo $i < count( $filters ) ? '|' : ''; ?>
					</li>
				<?php endforeach; ?>
			</ul>

			<!-- Search -->
			<form method="get" style="float:right;margin-top:4px;">
				<input type="hidden" name="page" value="litequote-quotes">
				<input type="hidden" name="status" value="<?php echo esc_attr( $current_status ); ?>">
				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search quotes...', 'litequote' ); ?>">
				<input type="submit" class="button" value="<?php esc_attr_e( 'Search', 'litequote' ); ?>">
			</form>

			<!-- Bulk actions -->
			<form method="post" action="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=litequote-quotes&action=bulk' ), 'litequote_bulk' ) ); ?>">
			<div class="tablenav top" style="margin-top:12px;">
				<div class="alignleft actions bulkactions">
					<select name="bulk_action">
						<option value=""><?php esc_html_e( 'Bulk Actions', 'litequote' ); ?></option>
						<option value="lq-accepted"><?php esc_html_e( 'Mark as Accepted', 'litequote' ); ?></option>
						<option value="lq-rejected"><?php esc_html_e( 'Mark as Rejected', 'litequote' ); ?></option>
						<option value="delete"><?php esc_html_e( 'Delete', 'litequote' ); ?></option>
					</select>
					<input type="submit" class="button action" value="<?php esc_attr_e( 'Apply', 'litequote' ); ?>">
				</div>
			</div>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<td class="manage-column column-cb check-column" style="width:30px;"><input type="checkbox" id="cb-select-all"></td>
						<th style="width:120px;"><?php esc_html_e( 'Reference', 'litequote' ); ?></th>
						<th><?php esc_html_e( 'Customer', 'litequote' ); ?></th>
						<th><?php esc_html_e( 'Product', 'litequote' ); ?></th>
						<th style="width:60px;"><?php esc_html_e( 'Qty', 'litequote' ); ?></th>
						<th style="width:130px;"><?php esc_html_e( 'Date', 'litequote' ); ?></th>
						<th style="width:100px;"><?php esc_html_e( 'Status', 'litequote' ); ?></th>
						<th style="width:140px;"><?php esc_html_e( 'Actions', 'litequote' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( $quotes->have_posts() ) : while ( $quotes->have_posts() ) : $quotes->the_post();
						$post_id = get_the_ID();
						$ref     = get_the_title();
						$name    = get_post_meta( $post_id, '_lq_name', true );
						$company = get_post_meta( $post_id, '_lq_company', true );
						$email   = get_post_meta( $post_id, '_lq_email', true );
						$product = get_post_meta( $post_id, '_lq_product_name', true );
						$qty     = get_post_meta( $post_id, '_lq_quantity', true ) ?: 1;
						$status  = get_post_status();
						$date    = get_the_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );

						$status_labels = array(
							'lq-pending'  => '<span style="color:#f0ad4e;font-weight:600;">&#9679; ' . esc_html__( 'Pending', 'litequote' ) . '</span>',
							'lq-quoted'   => '<span style="color:#0073aa;font-weight:600;">&#9679; ' . esc_html__( 'Quoted', 'litequote' ) . '</span>',
							'lq-accepted' => '<span style="color:#46b450;font-weight:600;">&#9679; ' . esc_html__( 'Accepted', 'litequote' ) . '</span>',
							'lq-rejected' => '<span style="color:#dc3232;font-weight:600;">&#9679; ' . esc_html__( 'Rejected', 'litequote' ) . '</span>',
						);

						$detail_url = admin_url( 'admin.php?page=litequote-quotes&action=view&quote_id=' . $post_id );
						$reply_url  = admin_url( 'admin.php?page=litequote-quotes&action=reply&quote_id=' . $post_id );
					?>
					<tr>
						<th scope="row" class="check-column"><input type="checkbox" name="quote_ids[]" value="<?php echo esc_attr( $post_id ); ?>"></th>
						<td><a href="<?php echo esc_url( $detail_url ); ?>"><strong><?php echo esc_html( $ref ); ?></strong></a></td>
						<td>
							<?php echo esc_html( $name ); ?>
							<?php if ( $company ) : ?><br><small style="color:#888;"><?php echo esc_html( $company ); ?></small><?php endif; ?>
						</td>
						<td><?php echo esc_html( $product ); ?></td>
						<td><?php echo esc_html( $qty ); ?></td>
						<td><?php echo esc_html( $date ); ?></td>
						<td><?php echo $status_labels[ $status ] ?? esc_html( $status ); ?></td>
						<td>
							<a href="<?php echo esc_url( $detail_url ); ?>" class="button button-small"><?php esc_html_e( 'View', 'litequote' ); ?></a>
							<?php if ( 'lq-pending' === $status ) : ?>
								<a href="<?php echo esc_url( $reply_url ); ?>" class="button button-primary button-small"><?php esc_html_e( 'Reply', 'litequote' ); ?></a>
							<?php endif; ?>
						</td>
					</tr>
					<?php endwhile; else : ?>
					<tr>
						<td colspan="8"><?php esc_html_e( 'No quotes found.', 'litequote' ); ?></td>
					</tr>
					<?php endif; wp_reset_postdata(); ?>
				</tbody>
			</table>
			</form>

			<script>document.getElementById('cb-select-all')?.addEventListener('change',function(){document.querySelectorAll('input[name="quote_ids[]"]').forEach(function(cb){cb.checked=this.checked}.bind(this));});</script>

			<?php
			// Pagination.
			$total_pages = $quotes->max_num_pages;
			if ( $total_pages > 1 ) :
				$current_page = max( 1, absint( $_GET['paged'] ?? 1 ) );
				echo '<div class="tablenav"><div class="tablenav-pages">';
				echo paginate_links( array(
					'base'    => add_query_arg( 'paged', '%#%' ),
					'format'  => '',
					'current' => $current_page,
					'total'   => $total_pages,
				) );
				echo '</div></div>';
			endif;
			?>
		</div>
		<?php
	}

	/**
	 * Save a quote submission as a CPT post.
	 *
	 * Called from LiteQuote_Form::handle_submission() after validation.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data Sanitized submission data.
	 * @return int|false The post ID on success, false on failure.
	 */
	public static function save_quote( $data ) {
		$reference = self::generate_reference();

		$post_id = wp_insert_post( array(
			'post_type'   => self::POST_TYPE,
			'post_title'  => $reference,
			'post_status' => 'lq-pending',
			'meta_input'  => array(
				'_lq_reference'    => $reference,
				'_lq_name'         => $data['name'] ?? '',
				'_lq_company'      => $data['company'] ?? '',
				'_lq_email'        => $data['email'] ?? '',
				'_lq_phone'        => $data['phone'] ?? '',
				'_lq_quantity'     => $data['quantity'] ?? 1,
				'_lq_message'      => $data['message'] ?? '',
				'_lq_product_id'   => $data['product_id'] ?? 0,
				'_lq_product_name' => $data['product_name'] ?? '',
				'_lq_sku'          => $data['sku'] ?? '',
				'_lq_variation'    => $data['variation'] ?? '',
			),
		) );

		if ( is_wp_error( $post_id ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( '[LiteQuote] Failed to save quote: ' . $post_id->get_error_message() );
			}
			return false;
		}

		return $post_id;
	}

	/**
	 * Generate a unique quote reference number.
	 *
	 * Format: LQ-YYYY-NNNN (e.g., LQ-2026-0042).
	 *
	 * @since 2.0.0
	 *
	 * @return string The generated reference.
	 */
	public static function generate_reference() {
		$counter = (int) get_option( 'litequote_pdf_counter', 0 ) + 1;
		update_option( 'litequote_pdf_counter', $counter );
		return sprintf( 'LQ-%s-%04d', date( 'Y' ), $counter );
	}

	/**
	 * Get count of quotes by status.
	 *
	 * @since 2.0.0
	 *
	 * @param string|null $status The status slug (e.g., 'lq-pending'). Null for all.
	 * @return int
	 */
	public static function get_count_by_status( $status = null ) {
		$args = array(
			'post_type'      => self::POST_TYPE,
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);

		if ( $status ) {
			$args['post_status'] = $status;
		} else {
			$args['post_status'] = array( 'lq-pending', 'lq-quoted', 'lq-accepted', 'lq-rejected' );
		}

		$query = new WP_Query( $args );
		return $query->found_posts;
	}

	/**
	 * Get all available quote statuses with labels and colors.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public static function get_statuses() {
		return array(
			'lq-pending'  => array( 'label' => __( 'Pending', 'litequote' ), 'color' => '#f0ad4e' ),
			'lq-quoted'   => array( 'label' => __( 'Quoted', 'litequote' ), 'color' => '#0073aa' ),
			'lq-accepted' => array( 'label' => __( 'Accepted', 'litequote' ), 'color' => '#46b450' ),
			'lq-rejected' => array( 'label' => __( 'Rejected', 'litequote' ), 'color' => '#dc3232' ),
		);
	}

	/**
	 * Render the quote detail page (View).
	 *
	 * @since 2.0.0
	 *
	 * @param int $quote_id The quote post ID.
	 */
	private function render_quote_detail( $quote_id ) {
		$post = get_post( $quote_id );
		if ( ! $post || self::POST_TYPE !== $post->post_type ) {
			echo '<div class="wrap"><p>' . esc_html__( 'Quote not found.', 'litequote' ) . '</p></div>';
			return;
		}

		$m = function( $key ) use ( $quote_id ) {
			return get_post_meta( $quote_id, '_lq_' . $key, true );
		};

		$status     = get_post_status( $quote_id );
		$statuses   = self::get_statuses();
		$back_url   = admin_url( 'admin.php?page=litequote-quotes' );
		$reply_url  = admin_url( 'admin.php?page=litequote-quotes&action=reply&quote_id=' . $quote_id );
		$product_id = absint( $m( 'product_id' ) );

		?>
		<div class="wrap">
			<h1>
				<a href="<?php echo esc_url( $back_url ); ?>" style="text-decoration:none;">&larr;</a>
				<?php echo esc_html( get_the_title( $quote_id ) ); ?>
				<span style="color:<?php echo esc_attr( $statuses[ $status ]['color'] ?? '#888' ); ?>;font-size:14px;margin-left:8px;">
					&#9679; <?php echo esc_html( $statuses[ $status ]['label'] ?? $status ); ?>
				</span>
			</h1>

			<div style="display:flex;gap:24px;margin-top:16px;">
				<!-- Left column -->
				<div style="flex:1;">
					<!-- Client -->
					<div class="postbox" style="padding:16px;">
						<h3 style="margin:0 0 12px;"><?php esc_html_e( 'Customer', 'litequote' ); ?></h3>
						<table class="form-table" style="margin:0;">
							<tr><th style="width:120px;"><?php esc_html_e( 'Name', 'litequote' ); ?></th><td><strong><?php echo esc_html( $m( 'name' ) ); ?></strong></td></tr>
							<?php if ( $m( 'company' ) ) : ?>
							<tr><th><?php esc_html_e( 'Company', 'litequote' ); ?></th><td><?php echo esc_html( $m( 'company' ) ); ?></td></tr>
							<?php endif; ?>
							<tr><th><?php esc_html_e( 'Email', 'litequote' ); ?></th><td><a href="mailto:<?php echo esc_attr( $m( 'email' ) ); ?>"><?php echo esc_html( $m( 'email' ) ); ?></a></td></tr>
							<?php if ( $m( 'phone' ) ) : ?>
							<tr><th><?php esc_html_e( 'Phone', 'litequote' ); ?></th><td><a href="tel:<?php echo esc_attr( $m( 'phone' ) ); ?>"><?php echo esc_html( $m( 'phone' ) ); ?></a></td></tr>
							<?php endif; ?>
						</table>
					</div>

					<!-- Product -->
					<div class="postbox" style="padding:16px;">
						<h3 style="margin:0 0 12px;"><?php esc_html_e( 'Product', 'litequote' ); ?></h3>
						<table class="form-table" style="margin:0;">
							<tr><th style="width:120px;"><?php esc_html_e( 'Product', 'litequote' ); ?></th><td><strong><?php echo esc_html( $m( 'product_name' ) ); ?></strong></td></tr>
							<?php if ( $m( 'sku' ) ) : ?>
							<tr><th><?php esc_html_e( 'SKU', 'litequote' ); ?></th><td><?php echo esc_html( $m( 'sku' ) ); ?></td></tr>
							<?php endif; ?>
							<?php if ( $m( 'variation' ) ) : ?>
							<tr><th><?php esc_html_e( 'Variation', 'litequote' ); ?></th><td><?php echo esc_html( $m( 'variation' ) ); ?></td></tr>
							<?php endif; ?>
							<tr><th><?php esc_html_e( 'Quantity', 'litequote' ); ?></th><td><?php echo esc_html( $m( 'quantity' ) ?: 1 ); ?></td></tr>
							<?php if ( $product_id ) : ?>
							<tr><th><?php esc_html_e( 'Links', 'litequote' ); ?></th><td>
								<a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>" target="_blank"><?php esc_html_e( 'View Product', 'litequote' ); ?></a>
								&nbsp;|&nbsp;
								<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $product_id . '&action=edit' ) ); ?>" target="_blank"><?php esc_html_e( 'Edit', 'litequote' ); ?></a>
							</td></tr>
							<?php endif; ?>
						</table>
					</div>

					<!-- Message -->
					<?php if ( $m( 'message' ) ) : ?>
					<div class="postbox" style="padding:16px;">
						<h3 style="margin:0 0 12px;"><?php esc_html_e( 'Customer Message', 'litequote' ); ?></h3>
						<div style="background:#f8f9fa;padding:12px 16px;border-left:4px solid #0073aa;border-radius:0 4px 4px 0;">
							<?php echo nl2br( esc_html( $m( 'message' ) ) ); ?>
						</div>
					</div>
					<?php endif; ?>

					<!-- Quote response (if sent) -->
					<?php
					$price = get_post_meta( $quote_id, '_lq_reply_price', true );
					if ( $price ) :
						$discount  = get_post_meta( $quote_id, '_lq_reply_discount', true );
						$notes     = get_post_meta( $quote_id, '_lq_reply_notes', true );
						$validity  = get_post_meta( $quote_id, '_lq_reply_validity', true );
						$qty       = $m( 'quantity' ) ?: 1;
						$subtotal  = floatval( $price ) * intval( $qty );
						$disc_amt  = $discount ? $subtotal * floatval( $discount ) / 100 : 0;
						$total     = $subtotal - $disc_amt;
						$currency  = get_woocommerce_currency_symbol();
					?>
					<div class="postbox" style="padding:16px;border-left:3px solid #0073aa;">
						<h3 style="margin:0 0 12px;"><?php esc_html_e( 'Your Quote', 'litequote' ); ?></h3>
						<table class="form-table" style="margin:0;">
							<tr><th style="width:120px;"><?php esc_html_e( 'Unit Price', 'litequote' ); ?></th><td><strong><?php echo esc_html( $currency . ' ' . number_format( floatval( $price ), 2 ) ); ?></strong></td></tr>
							<tr><th><?php esc_html_e( 'Quantity', 'litequote' ); ?></th><td><?php echo esc_html( $qty ); ?></td></tr>
							<?php if ( $discount ) : ?>
							<tr><th><?php esc_html_e( 'Discount', 'litequote' ); ?></th><td><?php echo esc_html( $discount . '%' ); ?></td></tr>
							<?php endif; ?>
							<tr><th><?php esc_html_e( 'Total', 'litequote' ); ?></th><td><strong style="font-size:16px;"><?php echo esc_html( $currency . ' ' . number_format( $total, 2 ) ); ?></strong></td></tr>
							<?php if ( $validity ) : ?>
							<tr><th><?php esc_html_e( 'Valid for', 'litequote' ); ?></th><td><?php echo esc_html( $validity . ' ' . __( 'days', 'litequote' ) ); ?></td></tr>
							<?php endif; ?>
							<?php if ( $notes ) : ?>
							<tr><th><?php esc_html_e( 'Notes', 'litequote' ); ?></th><td><?php echo nl2br( esc_html( $notes ) ); ?></td></tr>
							<?php endif; ?>
						</table>
					</div>
					<?php endif; ?>
				</div>

				<!-- Right column — Actions -->
				<div style="width:250px;">
					<div class="postbox" style="padding:16px;">
						<h3 style="margin:0 0 12px;"><?php esc_html_e( 'Actions', 'litequote' ); ?></h3>

						<?php if ( 'lq-pending' === $status ) : ?>
							<a href="<?php echo esc_url( $reply_url ); ?>" class="button button-primary" style="width:100%;text-align:center;margin-bottom:8px;">
								<?php esc_html_e( 'Send a Quote', 'litequote' ); ?>
							</a>
						<?php endif; ?>

						<a href="mailto:<?php echo esc_attr( $m( 'email' ) ); ?>?subject=<?php echo rawurlencode( 'Re: ' . get_the_title( $quote_id ) ); ?>" class="button" style="width:100%;text-align:center;margin-bottom:8px;">
							<?php esc_html_e( 'Reply by Email', 'litequote' ); ?>
						</a>

						<!-- Change status -->
						<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=litequote-quotes&action=update_status&quote_id=' . $quote_id ) ); ?>" style="margin-top:12px;">
							<?php wp_nonce_field( 'litequote_status_' . $quote_id ); ?>
							<label style="display:block;margin-bottom:4px;font-weight:600;"><?php esc_html_e( 'Change Status', 'litequote' ); ?></label>
							<select name="new_status" style="width:100%;margin-bottom:8px;">
								<?php foreach ( $statuses as $slug => $s ) : ?>
									<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $status, $slug ); ?>><?php echo esc_html( $s['label'] ); ?></option>
								<?php endforeach; ?>
							</select>
							<button type="submit" class="button" style="width:100%;"><?php esc_html_e( 'Update', 'litequote' ); ?></button>
						</form>
					</div>

					<!-- Info -->
					<div class="postbox" style="padding:16px;">
						<h3 style="margin:0 0 12px;"><?php esc_html_e( 'Info', 'litequote' ); ?></h3>
						<p style="margin:0 0 4px;color:#888;font-size:12px;"><?php esc_html_e( 'Date', 'litequote' ); ?></p>
						<p style="margin:0 0 12px;"><?php echo esc_html( get_the_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $quote_id ) ); ?></p>
						<p style="margin:0 0 4px;color:#888;font-size:12px;"><?php esc_html_e( 'Reference', 'litequote' ); ?></p>
						<p style="margin:0;"><strong><?php echo esc_html( get_the_title( $quote_id ) ); ?></strong></p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle status update from the detail page.
	 *
	 * @since 2.0.0
	 *
	 * @param int $quote_id The quote post ID.
	 */
	private function handle_status_update( $quote_id ) {
		check_admin_referer( 'litequote_status_' . $quote_id );

		$new_status = isset( $_POST['new_status'] ) ? sanitize_text_field( $_POST['new_status'] ) : '';
		$valid      = array( 'lq-pending', 'lq-quoted', 'lq-accepted', 'lq-rejected' );

		if ( in_array( $new_status, $valid, true ) ) {
			wp_update_post( array(
				'ID'          => $quote_id,
				'post_status' => $new_status,
			) );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=litequote-quotes&action=view&quote_id=' . $quote_id . '&updated=1' ) );
		exit;
	}

	/**
	 * Render the quote reply page — the merchant sets a price and sends the quote.
	 *
	 * @since 2.0.0
	 *
	 * @param int $quote_id The quote post ID.
	 */
	private function render_quote_reply( $quote_id ) {
		$post = get_post( $quote_id );
		if ( ! $post || self::POST_TYPE !== $post->post_type ) {
			echo '<div class="wrap"><p>' . esc_html__( 'Quote not found.', 'litequote' ) . '</p></div>';
			return;
		}

		// Handle form submission.
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['litequote_send_quote'] ) ) {
			check_admin_referer( 'litequote_reply_' . $quote_id );
			$this->process_quote_reply( $quote_id );
			return;
		}

		$m = function( $key ) use ( $quote_id ) {
			return get_post_meta( $quote_id, '_lq_' . $key, true );
		};

		$saved_price    = get_post_meta( $quote_id, '_lq_reply_price', true );
		$saved_discount = get_post_meta( $quote_id, '_lq_reply_discount', true );
		$saved_notes    = get_post_meta( $quote_id, '_lq_reply_notes', true );
		$saved_validity = get_post_meta( $quote_id, '_lq_reply_validity', true ) ?: 30;
		$quantity       = $m( 'quantity' ) ?: 1;
		$currency       = get_woocommerce_currency_symbol();
		$back_url       = admin_url( 'admin.php?page=litequote-quotes&action=view&quote_id=' . $quote_id );

		?>
		<div class="wrap">
			<h1>
				<a href="<?php echo esc_url( $back_url ); ?>" style="text-decoration:none;">&larr;</a>
				<?php printf( esc_html__( 'Send Quote — %s', 'litequote' ), esc_html( get_the_title( $quote_id ) ) ); ?>
			</h1>

			<!-- Request summary -->
			<div class="postbox" style="padding:16px;margin-top:16px;background:#f8f9fa;">
				<div style="display:flex;gap:32px;flex-wrap:wrap;">
					<div>
						<strong><?php esc_html_e( 'Customer', 'litequote' ); ?>:</strong>
						<?php echo esc_html( $m( 'name' ) ); ?>
						<?php if ( $m( 'company' ) ) echo '(' . esc_html( $m( 'company' ) ) . ')'; ?>
						— <a href="mailto:<?php echo esc_attr( $m( 'email' ) ); ?>"><?php echo esc_html( $m( 'email' ) ); ?></a>
					</div>
					<div>
						<strong><?php esc_html_e( 'Product', 'litequote' ); ?>:</strong>
						<?php echo esc_html( $m( 'product_name' ) ); ?>
						<?php if ( $m( 'sku' ) ) echo '(Ref. ' . esc_html( $m( 'sku' ) ) . ')'; ?>
						— <?php esc_html_e( 'Qty', 'litequote' ); ?>: <?php echo esc_html( $quantity ); ?>
					</div>
				</div>
				<?php if ( $m( 'message' ) ) : ?>
					<div style="margin-top:8px;padding:8px 12px;background:#fff;border-left:3px solid #0073aa;border-radius:0 4px 4px 0;">
						<small style="color:#888;"><?php esc_html_e( 'Customer Message', 'litequote' ); ?>:</small><br>
						<?php echo esc_html( $m( 'message' ) ); ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- Reply form -->
			<form method="post" style="margin-top:16px;">
				<?php wp_nonce_field( 'litequote_reply_' . $quote_id ); ?>

				<div class="postbox" style="padding:24px;">
					<h3 style="margin:0 0 16px;"><?php esc_html_e( 'Your Quote', 'litequote' ); ?></h3>

					<table class="form-table" style="margin:0;">
						<tr>
							<th style="width:150px;"><label for="lq_price"><?php esc_html_e( 'Unit Price', 'litequote' ); ?> (<?php echo esc_html( $currency ); ?>) <span style="color:red;">*</span></label></th>
							<td>
								<input type="number" id="lq_price" name="lq_price" value="<?php echo esc_attr( $saved_price ); ?>"
									step="0.01" min="0" class="regular-text" required style="max-width:200px;">
							</td>
						</tr>
						<tr>
							<th><label for="lq_quantity"><?php esc_html_e( 'Quantity', 'litequote' ); ?></label></th>
							<td>
								<input type="number" id="lq_quantity" name="lq_quantity" value="<?php echo esc_attr( $quantity ); ?>"
									min="1" class="regular-text" style="max-width:100px;">
							</td>
						</tr>
						<tr>
							<th><label for="lq_discount"><?php esc_html_e( 'Discount (%)', 'litequote' ); ?></label></th>
							<td>
								<input type="number" id="lq_discount" name="lq_discount" value="<?php echo esc_attr( $saved_discount ); ?>"
									step="0.1" min="0" max="100" class="regular-text" style="max-width:100px;">
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Total', 'litequote' ); ?></th>
							<td>
								<span id="lq_total" style="font-size:24px;font-weight:700;color:#0073aa;"><?php echo esc_html( $currency ); ?> 0.00</span>
							</td>
						</tr>
						<tr>
							<th><label for="lq_validity"><?php esc_html_e( 'Valid for (days)', 'litequote' ); ?></label></th>
							<td>
								<input type="number" id="lq_validity" name="lq_validity" value="<?php echo esc_attr( $saved_validity ); ?>"
									min="1" max="365" class="regular-text" style="max-width:100px;">
							</td>
						</tr>
						<tr>
							<th><label for="lq_notes"><?php esc_html_e( 'Notes / Conditions', 'litequote' ); ?></label></th>
							<td>
								<textarea id="lq_notes" name="lq_notes" rows="4" class="large-text"><?php echo esc_textarea( $saved_notes ); ?></textarea>
							</td>
						</tr>
					</table>
				</div>

				<p style="margin-top:16px;">
					<button type="submit" name="litequote_send_quote" value="send" class="button button-primary button-hero">
						<?php esc_html_e( 'Generate & Send Quote', 'litequote' ); ?>
					</button>
					<button type="submit" name="litequote_send_quote" value="draft" class="button button-large" style="margin-left:8px;">
						<?php esc_html_e( 'Save Draft', 'litequote' ); ?>
					</button>
					<a href="<?php echo esc_url( $back_url ); ?>" class="button button-large" style="margin-left:8px;">
						<?php esc_html_e( 'Cancel', 'litequote' ); ?>
					</a>
				</p>
			</form>
		</div>

		<script>
		(function(){
			var currency = <?php echo wp_json_encode( html_entity_decode( $currency ) ); ?>;
			function calc(){
				var price = parseFloat(document.getElementById('lq_price').value) || 0;
				var qty = parseInt(document.getElementById('lq_quantity').value) || 1;
				var disc = parseFloat(document.getElementById('lq_discount').value) || 0;
				var subtotal = price * qty;
				var total = subtotal - (subtotal * disc / 100);
				document.getElementById('lq_total').textContent = currency + ' ' + total.toFixed(2);
			}
			document.getElementById('lq_price').addEventListener('input', calc);
			document.getElementById('lq_quantity').addEventListener('input', calc);
			document.getElementById('lq_discount').addEventListener('input', calc);
			calc();
		})();
		</script>
		<?php
	}

	/**
	 * Process the quote reply form — save data, generate PDF, send email.
	 *
	 * @since 2.0.0
	 *
	 * @param int $quote_id The quote post ID.
	 */
	private function process_quote_reply( $quote_id ) {
		$action = sanitize_text_field( $_POST['litequote_send_quote'] );
		$price  = floatval( $_POST['lq_price'] ?? 0 );
		$qty    = max( 1, absint( $_POST['lq_quantity'] ?? 1 ) );
		$disc   = floatval( $_POST['lq_discount'] ?? 0 );
		$notes  = sanitize_textarea_field( $_POST['lq_notes'] ?? '' );
		$valid  = absint( $_POST['lq_validity'] ?? 30 );

		// Save quote reply data.
		update_post_meta( $quote_id, '_lq_reply_price', $price );
		update_post_meta( $quote_id, '_lq_reply_discount', $disc );
		update_post_meta( $quote_id, '_lq_reply_notes', $notes );
		update_post_meta( $quote_id, '_lq_reply_validity', $valid );
		update_post_meta( $quote_id, '_lq_quantity', $qty );

		if ( 'draft' === $action ) {
			wp_safe_redirect( admin_url( 'admin.php?page=litequote-quotes&action=view&quote_id=' . $quote_id . '&saved=1' ) );
			exit;
		}

		// Send the quote — build data for email.
		$m = function( $key ) use ( $quote_id ) {
			return get_post_meta( $quote_id, '_lq_' . $key, true );
		};

		$client_email = $m( 'email' );
		$reference    = get_the_title( $quote_id );
		$subtotal     = $price * $qty;
		$disc_amount  = $disc ? $subtotal * $disc / 100 : 0;
		$total        = $subtotal - $disc_amount;
		$currency     = get_woocommerce_currency_symbol();
		$shop_name    = get_bloginfo( 'name' );

		// Build quote email body.
		$body = '<h2 style="margin:0 0 4px;font-size:20px;color:#1e1e1e;">' . sprintf( esc_html__( 'Quote %s', 'litequote' ), esc_html( $reference ) ) . '</h2>';
		$body .= '<p style="margin:0 0 24px;color:#888;font-size:13px;">' . esc_html( wp_date( get_option( 'date_format' ) ) ) . '</p>';

		$body .= '<p>' . sprintf( esc_html__( 'Dear %s,', 'litequote' ), esc_html( $m( 'name' ) ) ) . '</p>';
		$body .= '<p>' . esc_html__( 'Thank you for your quote request. Please find our offer below:', 'litequote' ) . '</p>';

		// Price table.
		$body .= '<table cellpadding="10" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;margin:20px 0;border:1px solid #eee;">';
		$body .= '<tr style="background:#f8f9fa;"><th style="text-align:left;padding:10px 12px;border-bottom:1px solid #eee;">' . esc_html__( 'Product', 'litequote' ) . '</th>';
		$body .= '<th style="text-align:center;padding:10px 12px;border-bottom:1px solid #eee;">' . esc_html__( 'Unit Price', 'litequote' ) . '</th>';
		$body .= '<th style="text-align:center;padding:10px 12px;border-bottom:1px solid #eee;">' . esc_html__( 'Qty', 'litequote' ) . '</th>';
		$body .= '<th style="text-align:right;padding:10px 12px;border-bottom:1px solid #eee;">' . esc_html__( 'Subtotal', 'litequote' ) . '</th></tr>';

		$body .= '<tr>';
		$body .= '<td style="padding:10px 12px;border-bottom:1px solid #eee;">' . esc_html( $m( 'product_name' ) );
		if ( $m( 'sku' ) ) $body .= '<br><small style="color:#888;">Ref. ' . esc_html( $m( 'sku' ) ) . '</small>';
		$body .= '</td>';
		$body .= '<td style="text-align:center;padding:10px 12px;border-bottom:1px solid #eee;">' . esc_html( $currency . ' ' . number_format( $price, 2 ) ) . '</td>';
		$body .= '<td style="text-align:center;padding:10px 12px;border-bottom:1px solid #eee;">' . esc_html( $qty ) . '</td>';
		$body .= '<td style="text-align:right;padding:10px 12px;border-bottom:1px solid #eee;">' . esc_html( $currency . ' ' . number_format( $subtotal, 2 ) ) . '</td>';
		$body .= '</tr>';

		if ( $disc > 0 ) {
			$body .= '<tr><td colspan="3" style="text-align:right;padding:8px 12px;">' . esc_html__( 'Discount', 'litequote' ) . ' (' . esc_html( $disc ) . '%)</td>';
			$body .= '<td style="text-align:right;padding:8px 12px;color:#dc3232;">-' . esc_html( $currency . ' ' . number_format( $disc_amount, 2 ) ) . '</td></tr>';
		}

		$body .= '<tr style="background:#f8f9fa;"><td colspan="3" style="text-align:right;padding:12px;font-weight:700;font-size:16px;">' . esc_html__( 'Total', 'litequote' ) . '</td>';
		$body .= '<td style="text-align:right;padding:12px;font-weight:700;font-size:16px;color:#0073aa;">' . esc_html( $currency . ' ' . number_format( $total, 2 ) ) . '</td></tr>';
		$body .= '</table>';

		if ( $notes ) {
			$body .= '<div style="margin:16px 0;padding:12px 16px;background:#f8f9fa;border-radius:4px;">';
			$body .= '<p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;color:#888;font-weight:600;">' . esc_html__( 'Notes / Conditions', 'litequote' ) . '</p>';
			$body .= '<p style="margin:0;">' . nl2br( esc_html( $notes ) ) . '</p>';
			$body .= '</div>';
		}

		$body .= '<p style="color:#888;font-size:13px;">' . sprintf( esc_html__( 'This quote is valid for %d days.', 'litequote' ), $valid ) . '</p>';
		$body .= '<p>' . esc_html__( 'Best regards,', 'litequote' ) . '<br><strong>' . esc_html( $shop_name ) . '</strong></p>';

		// Wrap in HTML template.
		$full_body = LiteQuote_Email::wrap_email_html_public( $body );

		// Subject.
		$subject = sprintf(
			/* translators: 1: shop name, 2: quote reference */
			__( '%1$s — Your Quote %2$s', 'litequote' ),
			$shop_name,
			$reference
		);

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		// Generate quote PDF if module is available.
		$attachments = array();
		if ( 'yes' === get_option( 'litequote_pdf_enabled', 'no' ) ) {
			$pdf_gen = new LiteQuote_PDF();
			$pdf_path = $pdf_gen->generate_quote_pdf( $quote_id );
			if ( $pdf_path && file_exists( $pdf_path ) ) {
				$attachments[] = $pdf_path;
			}
		}

		// Send email.
		$sent = wp_mail( $client_email, $subject, $full_body, $headers, $attachments );

		if ( $sent ) {
			wp_update_post( array(
				'ID'          => $quote_id,
				'post_status' => 'lq-quoted',
			) );
			update_post_meta( $quote_id, '_lq_quoted_date', current_time( 'mysql' ) );
		}

		$redirect_url = admin_url( 'admin.php?page=litequote-quotes&action=view&quote_id=' . $quote_id );
		$redirect_url = add_query_arg( $sent ? 'sent' : 'send_error', '1', $redirect_url );
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Handle CSV export of quotes.
	 *
	 * @since 2.0.0
	 */
	public function handle_csv_export() {
		if ( ! isset( $_GET['page'] ) || 'litequote-quotes' !== $_GET['page'] ) {
			return;
		}
		if ( ! isset( $_GET['action'] ) || 'export_csv' !== $_GET['action'] ) {
			return;
		}
		if ( ! current_user_can( 'manage_woocommerce' ) || ! check_admin_referer( 'litequote_export_csv' ) ) {
			return;
		}

		$status_filter = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all';

		$args = array(
			'post_type'      => self::POST_TYPE,
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		if ( 'all' !== $status_filter ) {
			$args['post_status'] = 'lq-' . $status_filter;
		} else {
			$args['post_status'] = array( 'lq-pending', 'lq-quoted', 'lq-accepted', 'lq-rejected' );
		}

		$quotes = get_posts( $args );

		$filename = 'litequote-quotes-' . date( 'Y-m-d' ) . '.csv';

		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );

		// UTF-8 BOM for Excel.
		fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

		// Header row.
		fputcsv( $output, array(
			__( 'Reference', 'litequote' ),
			__( 'Date', 'litequote' ),
			__( 'Status', 'litequote' ),
			__( 'Name', 'litequote' ),
			__( 'Company', 'litequote' ),
			__( 'Email', 'litequote' ),
			__( 'Phone', 'litequote' ),
			__( 'Product', 'litequote' ),
			__( 'SKU', 'litequote' ),
			__( 'Quantity', 'litequote' ),
			__( 'Message', 'litequote' ),
			__( 'Unit Price', 'litequote' ),
			__( 'Discount', 'litequote' ),
			__( 'Total', 'litequote' ),
		) );

		$status_labels = array(
			'lq-pending'  => __( 'Pending', 'litequote' ),
			'lq-quoted'   => __( 'Quoted', 'litequote' ),
			'lq-accepted' => __( 'Accepted', 'litequote' ),
			'lq-rejected' => __( 'Rejected', 'litequote' ),
		);

		foreach ( $quotes as $quote ) {
			$id    = $quote->ID;
			$price = floatval( get_post_meta( $id, '_lq_reply_price', true ) );
			$qty   = max( 1, intval( get_post_meta( $id, '_lq_quantity', true ) ) );
			$disc  = floatval( get_post_meta( $id, '_lq_reply_discount', true ) );
			$total = $price > 0 ? ( $price * $qty ) - ( $price * $qty * $disc / 100 ) : '';

			fputcsv( $output, array(
				get_the_title( $id ),
				get_the_date( 'Y-m-d H:i', $id ),
				$status_labels[ $quote->post_status ] ?? $quote->post_status,
				get_post_meta( $id, '_lq_name', true ),
				get_post_meta( $id, '_lq_company', true ),
				get_post_meta( $id, '_lq_email', true ),
				get_post_meta( $id, '_lq_phone', true ),
				get_post_meta( $id, '_lq_product_name', true ),
				get_post_meta( $id, '_lq_sku', true ),
				$qty,
				get_post_meta( $id, '_lq_message', true ),
				$price > 0 ? number_format( $price, 2 ) : '',
				$disc > 0 ? $disc . '%' : '',
				is_numeric( $total ) ? number_format( $total, 2 ) : '',
			) );
		}

		fclose( $output );
		exit;
	}

	/**
	 * Handle bulk actions (change status, delete).
	 *
	 * @since 2.0.0
	 */
	public function handle_bulk_actions() {
		if ( ! isset( $_POST['bulk_action'] ) || empty( $_POST['bulk_action'] ) ) {
			return;
		}
		if ( ! isset( $_GET['page'] ) || 'litequote-quotes' !== $_GET['page'] ) {
			return;
		}
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		check_admin_referer( 'litequote_bulk' );

		$action    = sanitize_text_field( $_POST['bulk_action'] );
		$quote_ids = isset( $_POST['quote_ids'] ) ? array_map( 'absint', (array) $_POST['quote_ids'] ) : array();

		if ( empty( $quote_ids ) ) {
			return;
		}

		$valid_statuses = array( 'lq-pending', 'lq-quoted', 'lq-accepted', 'lq-rejected' );

		foreach ( $quote_ids as $id ) {
			if ( 'delete' === $action ) {
				wp_delete_post( $id, true );
			} elseif ( in_array( $action, $valid_statuses, true ) ) {
				wp_update_post( array(
					'ID'          => $id,
					'post_status' => $action,
				) );
			}
		}

		wp_safe_redirect( admin_url( 'admin.php?page=litequote-quotes&bulk_done=' . count( $quote_ids ) ) );
		exit;
	}
}
