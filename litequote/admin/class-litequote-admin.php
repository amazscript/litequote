<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin module — Settings page with tabs under WooCommerce menu.
 *
 * Provides a full settings interface organized in tabs:
 * General, Button, Emails, WhatsApp, PDF, Advanced.
 *
 * @since 1.0.0
 */
class LiteQuote_Admin {

	/** @var string Current active tab. */
	private $current_tab = 'general';

	/** @var array Tab definitions. */
	private $tabs = array();

	/**
	 * Constructor — register admin hooks.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'init', array( $this, 'init_tabs' ) );
	}

	/**
	 * Initialize tabs after translations are loaded.
	 */
	public function init_tabs() {
		$this->tabs = array(
			'general'  => __( 'General', 'litequote' ),
			'button'   => __( 'Button', 'litequote' ),
			'emails'   => __( 'Emails', 'litequote' ),
			'whatsapp' => __( 'WhatsApp', 'litequote' ),
			'pdf'      => __( 'PDF', 'litequote' ),
			'advanced' => __( 'Advanced', 'litequote' ),
		);
	}

	/**
	 * Add LiteQuote submenu under WooCommerce.
	 */
	public function add_menu_page() {
		add_submenu_page(
			'woocommerce',
			__( 'LiteQuote Settings', 'litequote' ),
			'LiteQuote',
			'manage_woocommerce',
			'litequote-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Enqueue color picker and admin styles.
	 *
	 * @param string $hook The current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( 'woocommerce_page_litequote-settings' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_media();
	}

	/**
	 * Register all plugin settings.
	 */
	public function register_settings() {
		// General.
		register_setting( 'litequote_general', 'litequote_trigger_mode', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'litequote_general', 'litequote_catalogue_mode', array(
			'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
		) );
		register_setting( 'litequote_general', 'litequote_catalogue_exclude_ids', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'litequote_general', 'litequote_catalogue_exclude_cats', array(
			'sanitize_callback' => function ( $value ) {
				return is_array( $value ) ? array_map( 'absint', $value ) : array();
			},
		) );

		// Button.
		register_setting( 'litequote_button', 'litequote_button_text', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'litequote_button', 'litequote_button_bg_color', array( 'sanitize_callback' => 'sanitize_hex_color' ) );
		register_setting( 'litequote_button', 'litequote_button_text_color', array( 'sanitize_callback' => 'sanitize_hex_color' ) );
		register_setting( 'litequote_button', 'litequote_button_position', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'litequote_button', 'litequote_price_label', array( 'sanitize_callback' => 'sanitize_text_field' ) );

		// Emails.
		register_setting( 'litequote_emails', 'litequote_admin_email', array( 'sanitize_callback' => 'sanitize_email' ) );
		register_setting( 'litequote_emails', 'litequote_auto_reply', array( 'sanitize_callback' => array( $this, 'sanitize_checkbox' ) ) );
		register_setting( 'litequote_emails', 'litequote_auto_reply_template', array( 'sanitize_callback' => 'wp_kses_post' ) );

		// WhatsApp.
		register_setting( 'litequote_whatsapp', 'litequote_whatsapp_number', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'litequote_whatsapp', 'litequote_whatsapp_mode', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'litequote_whatsapp', 'litequote_whatsapp_template', array( 'sanitize_callback' => 'sanitize_textarea_field' ) );

		// PDF.
		register_setting( 'litequote_pdf', 'litequote_pdf_enabled', array( 'sanitize_callback' => array( $this, 'sanitize_checkbox' ) ) );
		register_setting( 'litequote_pdf', 'litequote_pdf_logo', array( 'sanitize_callback' => 'esc_url_raw' ) );
		register_setting( 'litequote_pdf', 'litequote_pdf_archive', array( 'sanitize_callback' => array( $this, 'sanitize_checkbox' ) ) );
		register_setting( 'litequote_pdf', 'litequote_pdf_retention_days', array( 'sanitize_callback' => 'absint' ) );

		// Advanced.
		register_setting( 'litequote_advanced', 'litequote_debug_mode', array( 'sanitize_callback' => array( $this, 'sanitize_checkbox' ) ) );
		register_setting( 'litequote_advanced', 'litequote_custom_css', array(
			'sanitize_callback' => function ( $value ) {
				return wp_strip_all_tags( $value );
			},
		) );
	}

	/**
	 * Sanitize a checkbox value. Returns 'yes' or 'no'.
	 *
	 * @param mixed $value The submitted value.
	 * @return string 'yes' or 'no'.
	 */
	public function sanitize_checkbox( $value ) {
		return 'yes' === $value ? 'yes' : 'no';
	}

	/**
	 * Render the main settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
		if ( ! array_key_exists( $this->current_tab, $this->tabs ) ) {
			$this->current_tab = 'general';
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'LiteQuote Settings', 'litequote' ); ?></h1>

			<nav class="nav-tab-wrapper">
				<?php foreach ( $this->tabs as $tab_key => $tab_label ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=litequote-settings&tab=' . $tab_key ) ); ?>"
					   class="nav-tab <?php echo $this->current_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
						<?php echo esc_html( $tab_label ); ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<form method="post" action="options.php" style="margin-top:12px;">
				<?php
				settings_fields( 'litequote_' . $this->current_tab );

				switch ( $this->current_tab ) {
					case 'general':
						$this->render_tab_general();
						break;
					case 'button':
						$this->render_tab_button();
						break;
					case 'emails':
						$this->render_tab_emails();
						break;
					case 'whatsapp':
						$this->render_tab_whatsapp();
						break;
					case 'pdf':
						$this->render_tab_pdf();
						break;
					case 'advanced':
						$this->render_tab_advanced();
						break;
				}

				submit_button( __( 'Save Changes', 'litequote' ) );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * General tab.
	 */
	private function render_tab_general() {
		$trigger_mode  = get_option( 'litequote_trigger_mode', 'both' );
		$catalogue     = get_option( 'litequote_catalogue_mode', 'no' );
		$exclude_ids   = get_option( 'litequote_catalogue_exclude_ids', '' );
		$exclude_cats  = get_option( 'litequote_catalogue_exclude_cats', array() );
		?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Trigger Mode', 'litequote' ); ?></th>
				<td>
					<select name="litequote_trigger_mode">
						<option value="zero_price" <?php selected( $trigger_mode, 'zero_price' ); ?>><?php esc_html_e( 'Products with price = 0', 'litequote' ); ?></option>
						<option value="checkbox" <?php selected( $trigger_mode, 'checkbox' ); ?>><?php esc_html_e( 'Products with checkbox enabled', 'litequote' ); ?></option>
						<option value="both" <?php selected( $trigger_mode, 'both' ); ?>><?php esc_html_e( 'Both (zero price + checkbox)', 'litequote' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Choose how products enter quote mode.', 'litequote' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Catalogue Mode', 'litequote' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="litequote_catalogue_mode" value="yes" <?php checked( $catalogue, 'yes' ); ?>>
						<?php esc_html_e( 'Enable catalogue mode for the entire shop', 'litequote' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Hides all prices and add-to-cart buttons. Every product shows a quote button.', 'litequote' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Exclude Product IDs', 'litequote' ); ?></th>
				<td>
					<input type="text" name="litequote_catalogue_exclude_ids" value="<?php echo esc_attr( $exclude_ids ); ?>" class="regular-text" placeholder="42, 108, 256">
					<p class="description"><?php esc_html_e( 'Comma-separated product IDs to exclude from catalogue mode.', 'litequote' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Exclude Categories', 'litequote' ); ?></th>
				<td>
					<?php
					$categories = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
					if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) :
						foreach ( $categories as $cat ) :
							?>
							<label style="display:block;margin-bottom:4px;">
								<input type="checkbox" name="litequote_catalogue_exclude_cats[]" value="<?php echo esc_attr( $cat->term_id ); ?>"
									<?php echo in_array( $cat->term_id, array_map( 'intval', (array) $exclude_cats ), true ) ? 'checked' : ''; ?>>
								<?php echo esc_html( $cat->name ); ?>
							</label>
						<?php endforeach;
					else :
						echo '<p class="description">' . esc_html__( 'No product categories found.', 'litequote' ) . '</p>';
					endif;
					?>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Button tab.
	 */
	private function render_tab_button() {
		$btn_text     = get_option( 'litequote_button_text', 'Request a Quote' );
		$btn_bg       = get_option( 'litequote_button_bg_color', '#0073aa' );
		$btn_color    = get_option( 'litequote_button_text_color', '#ffffff' );
		$btn_position = get_option( 'litequote_button_position', 'after' );
		$price_label  = get_option( 'litequote_price_label', 'Price on request' );
		?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Button Text', 'litequote' ); ?></th>
				<td>
					<input type="text" name="litequote_button_text" value="<?php echo esc_attr( $btn_text ); ?>" class="regular-text">
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Background Color', 'litequote' ); ?></th>
				<td>
					<input type="text" name="litequote_button_bg_color" value="<?php echo esc_attr( $btn_bg ); ?>" class="litequote-color-picker">
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Text Color', 'litequote' ); ?></th>
				<td>
					<input type="text" name="litequote_button_text_color" value="<?php echo esc_attr( $btn_color ); ?>" class="litequote-color-picker">
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Button Position', 'litequote' ); ?></th>
				<td>
					<select name="litequote_button_position">
						<option value="before" <?php selected( $btn_position, 'before' ); ?>><?php esc_html_e( 'Before add-to-cart form', 'litequote' ); ?></option>
						<option value="after" <?php selected( $btn_position, 'after' ); ?>><?php esc_html_e( 'After add-to-cart form', 'litequote' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Price Label', 'litequote' ); ?></th>
				<td>
					<input type="text" name="litequote_price_label" value="<?php echo esc_attr( $price_label ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Text displayed instead of the price (e.g. "Price on request").', 'litequote' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Preview', 'litequote' ); ?></th>
				<td>
					<div style="padding:20px;background:#f9f9f9;border-radius:4px;display:inline-block;">
						<span style="font-style:italic;color:#666;display:block;margin-bottom:8px;" id="litequote-preview-label"><?php echo esc_html( $price_label ); ?></span>
						<button type="button" id="litequote-preview-btn"
							style="padding:10px 24px;border:none;border-radius:4px;font-weight:600;cursor:default;background:<?php echo esc_attr( $btn_bg ); ?>;color:<?php echo esc_attr( $btn_color ); ?>;">
							<?php echo esc_html( $btn_text ); ?>
						</button>
					</div>
				</td>
			</tr>
		</table>
		<script>
		jQuery(document).ready(function($){
			$('.litequote-color-picker').wpColorPicker({
				change: function(){
					setTimeout(function(){
						var bg = $('input[name="litequote_button_bg_color"]').val();
						var color = $('input[name="litequote_button_text_color"]').val();
						$('#litequote-preview-btn').css({background: bg, color: color});
					}, 50);
				}
			});
			$('input[name="litequote_button_text"]').on('input', function(){
				$('#litequote-preview-btn').text($(this).val());
			});
			$('input[name="litequote_price_label"]').on('input', function(){
				$('#litequote-preview-label').text($(this).val());
			});
		});
		</script>
		<?php
	}

	/**
	 * Emails tab.
	 */
	private function render_tab_emails() {
		$admin_email = get_option( 'litequote_admin_email', get_bloginfo( 'admin_email' ) );
		$auto_reply  = get_option( 'litequote_auto_reply', 'no' );
		$template    = get_option( 'litequote_auto_reply_template', '' );
		if ( empty( $template ) ) {
			$template = LiteQuote_Settings::get_default_auto_reply_template();
		}
		?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Admin Email', 'litequote' ); ?></th>
				<td>
					<input type="email" name="litequote_admin_email" value="<?php echo esc_attr( $admin_email ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Quote requests will be sent to this address.', 'litequote' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Auto-Reply', 'litequote' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="litequote_auto_reply" value="yes" <?php checked( $auto_reply, 'yes' ); ?>>
						<?php esc_html_e( 'Send a confirmation email to the customer after submission', 'litequote' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Auto-Reply Template', 'litequote' ); ?></th>
				<td>
					<textarea name="litequote_auto_reply_template" rows="8" class="large-text code"><?php echo esc_textarea( $template ); ?></textarea>
					<p class="description">
						<?php esc_html_e( 'Available variables:', 'litequote' ); ?>
						<code>{client_name}</code> <code>{product_name}</code> <code>{product_url}</code> <code>{shop_name}</code> <code>{date}</code>
					</p>
					<p>
						<button type="button" class="button" id="litequote-reset-template">
							<?php esc_html_e( 'Restore Default Template', 'litequote' ); ?>
						</button>
					</p>
				</td>
			</tr>
		</table>
		<script>
		document.getElementById('litequote-reset-template')?.addEventListener('click', function(){
			var def = <?php echo wp_json_encode( LiteQuote_Settings::get_default_auto_reply_template() ); ?>;
			document.querySelector('textarea[name="litequote_auto_reply_template"]').value = def;
		});
		</script>
		<?php
	}

	/**
	 * WhatsApp tab.
	 */
	private function render_tab_whatsapp() {
		$number   = get_option( 'litequote_whatsapp_number', '' );
		$mode     = get_option( 'litequote_whatsapp_mode', 'form_only' );
		$template = get_option( 'litequote_whatsapp_template', '' );
		if ( empty( $template ) ) {
			$template = LiteQuote_Settings::get_default_whatsapp_template();
		}
		?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'WhatsApp Number', 'litequote' ); ?></th>
				<td>
					<input type="text" name="litequote_whatsapp_number" value="<?php echo esc_attr( $number ); ?>" class="regular-text" placeholder="+1 555 123 4567">
					<p class="description"><?php esc_html_e( 'International format with country code.', 'litequote' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Display Mode', 'litequote' ); ?></th>
				<td>
					<select name="litequote_whatsapp_mode">
						<option value="form_only" <?php selected( $mode, 'form_only' ); ?>><?php esc_html_e( 'Form only', 'litequote' ); ?></option>
						<option value="whatsapp_only" <?php selected( $mode, 'whatsapp_only' ); ?>><?php esc_html_e( 'WhatsApp only', 'litequote' ); ?></option>
						<option value="both" <?php selected( $mode, 'both' ); ?>><?php esc_html_e( 'Both (form + WhatsApp button)', 'litequote' ); ?></option>
					</select>
					<?php if ( empty( $number ) ) : ?>
						<p class="description" style="color:#d63638;"><?php esc_html_e( 'Enter a WhatsApp number to enable WhatsApp mode.', 'litequote' ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Message Template', 'litequote' ); ?></th>
				<td>
					<textarea name="litequote_whatsapp_template" rows="4" class="large-text code"><?php echo esc_textarea( $template ); ?></textarea>
					<p class="description">
						<?php esc_html_e( 'Available variables:', 'litequote' ); ?>
						<code>{product_name}</code> <code>{sku}</code> <code>{product_url}</code> <code>{variation}</code>
					</p>
					<p>
						<button type="button" class="button" id="litequote-reset-wa-template">
							<?php esc_html_e( 'Restore Default Message', 'litequote' ); ?>
						</button>
					</p>
				</td>
			</tr>
		</table>
		<script>
		document.getElementById('litequote-reset-wa-template')?.addEventListener('click', function(){
			var def = <?php echo wp_json_encode( LiteQuote_Settings::get_default_whatsapp_template() ); ?>;
			document.querySelector('textarea[name="litequote_whatsapp_template"]').value = def;
		});
		</script>
		<?php
	}

	/**
	 * PDF tab.
	 */
	private function render_tab_pdf() {
		$enabled   = get_option( 'litequote_pdf_enabled', 'no' );
		$logo      = get_option( 'litequote_pdf_logo', '' );
		$archive   = get_option( 'litequote_pdf_archive', 'no' );
		$retention = get_option( 'litequote_pdf_retention_days', 90 );
		?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable PDF Generation', 'litequote' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="litequote_pdf_enabled" value="yes" <?php checked( $enabled, 'yes' ); ?>>
						<?php esc_html_e( 'Generate a PDF for each quote request', 'litequote' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Shop Logo', 'litequote' ); ?></th>
				<td>
					<input type="hidden" name="litequote_pdf_logo" id="litequote-pdf-logo" value="<?php echo esc_url( $logo ); ?>">
					<?php if ( $logo ) : ?>
						<img src="<?php echo esc_url( $logo ); ?>" id="litequote-logo-preview" style="max-width:150px;display:block;margin-bottom:8px;">
					<?php else : ?>
						<img src="" id="litequote-logo-preview" style="max-width:150px;display:none;margin-bottom:8px;">
					<?php endif; ?>
					<button type="button" class="button" id="litequote-upload-logo"><?php esc_html_e( 'Select Logo', 'litequote' ); ?></button>
					<button type="button" class="button" id="litequote-remove-logo" <?php echo empty( $logo ) ? 'style="display:none;"' : ''; ?>><?php esc_html_e( 'Remove', 'litequote' ); ?></button>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Local Archive', 'litequote' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="litequote_pdf_archive" value="yes" <?php checked( $archive, 'yes' ); ?>>
						<?php esc_html_e( 'Save PDFs to wp-content/uploads/litequote-quotes/', 'litequote' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Retention (days)', 'litequote' ); ?></th>
				<td>
					<input type="number" name="litequote_pdf_retention_days" value="<?php echo esc_attr( $retention ); ?>" min="1" max="365" class="small-text">
					<p class="description"><?php esc_html_e( 'Archived PDFs older than this will be automatically deleted.', 'litequote' ); ?></p>
				</td>
			</tr>
		</table>
		<script>
		jQuery(document).ready(function($){
			$('#litequote-upload-logo').on('click', function(e){
				e.preventDefault();
				var frame = wp.media({title: 'Select Logo', multiple: false, library: {type: 'image'}});
				frame.on('select', function(){
					var url = frame.state().get('selection').first().toJSON().url;
					$('#litequote-pdf-logo').val(url);
					$('#litequote-logo-preview').attr('src', url).show();
					$('#litequote-remove-logo').show();
				});
				frame.open();
			});
			$('#litequote-remove-logo').on('click', function(){
				$('#litequote-pdf-logo').val('');
				$('#litequote-logo-preview').hide();
				$(this).hide();
			});
		});
		</script>
		<?php
	}

	/**
	 * Advanced tab.
	 */
	private function render_tab_advanced() {
		$debug      = get_option( 'litequote_debug_mode', 'no' );
		$custom_css = get_option( 'litequote_custom_css', '' );
		?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Debug Mode', 'litequote' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="litequote_debug_mode" value="yes" <?php checked( $debug, 'yes' ); ?>>
						<?php esc_html_e( 'Log blocked submissions (honeypot) to error_log', 'litequote' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Custom CSS', 'litequote' ); ?></th>
				<td>
					<textarea name="litequote_custom_css" rows="8" class="large-text code"><?php echo esc_textarea( $custom_css ); ?></textarea>
					<p class="description" style="color:#d63638;"><?php esc_html_e( 'Only modify if you know what you are doing. No &lt;script&gt; tags allowed.', 'litequote' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}
}
