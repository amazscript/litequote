# Hooks & Filters

LiteQuote provides hooks for developers who want to customize behavior without modifying the plugin core.

## Filters

### `litequote_modal_data`

Filter the data object passed to the JavaScript frontend via `wp_localize_script`.

```php
add_filter( 'litequote_modal_data', function( $data ) {
    // Add custom data for the JS frontend
    $data['custom_field'] = 'value';
    return $data;
} );
```

**Parameters:**
- `$data` (array) -- The litequoteData object containing ajaxUrl, nonce, i18n, whatsapp config

### `litequote_email_attachments`

Add or modify email attachments (used by the PDF module).

```php
add_filter( 'litequote_email_attachments', function( $attachments, $data ) {
    // Add a custom file to the email
    $attachments[] = '/path/to/custom-file.pdf';
    return $attachments;
}, 10, 2 );
```

**Parameters:**
- `$attachments` (array) -- Array of file paths to attach
- `$data` (array) -- Sanitized submission data

### `woocommerce_is_purchasable`

LiteQuote hooks into this WooCommerce filter to make quote products non-purchasable.

```php
// Force a specific product to always be purchasable (override LiteQuote)
add_filter( 'woocommerce_is_purchasable', function( $purchasable, $product ) {
    if ( $product->get_id() === 42 ) {
        return true;
    }
    return $purchasable;
}, 20, 2 ); // Priority 20 to run after LiteQuote (priority 10)
```

### `woocommerce_get_price_html`

LiteQuote hooks into this to replace the price with the configured label.

## Actions

### WordPress AJAX

LiteQuote registers the AJAX action `litequote_submit_quote` for both logged-in and logged-out users:

```php
// Run custom code after a quote is submitted
add_action( 'wp_ajax_nopriv_litequote_submit_quote', function() {
    // Your custom logic (runs before LiteQuote's handler at priority 10)
}, 5 );
```

### WP-Cron

- `litequote_pdf_purge` -- Runs daily to delete expired PDF archives

## CSS Custom Properties

Override the button styling via CSS variables:

```css
:root {
    --litequote-btn-bg: #e74c3c;
    --litequote-btn-color: #ffffff;
}
```

Or use the Custom CSS field in **WooCommerce > LiteQuote > Advanced**.

## Key Classes

| Class | File | Purpose |
|---|---|---|
| `LiteQuote_Core` | `includes/class-litequote-core.php` | Product detection, button/price replacement |
| `LiteQuote_Form` | `includes/class-litequote-form.php` | AJAX handler for submissions |
| `LiteQuote_Email` | `includes/class-litequote-email.php` | Email building and sending |
| `LiteQuote_Security` | `includes/class-litequote-security.php` | Nonce, honeypot, sanitization, rate limiting |
| `LiteQuote_WhatsApp` | `includes/class-litequote-whatsapp.php` | WhatsApp URL builder |
| `LiteQuote_PDF` | `includes/class-litequote-pdf.php` | PDF generation (FPDF) |
| `LiteQuote_Settings` | `includes/class-litequote-settings.php` | Options retrieval, defaults |
| `LiteQuote_Admin` | `admin/class-litequote-admin.php` | Settings page with tabs |
| `LiteQuote_Quote_CPT` | `includes/class-litequote-quote-cpt.php` | Quote CPT, dashboard, reply, CSV export |

## Product Meta

| Meta Key | Value | Description |
|---|---|---|
| `_litequote_enabled` | `yes` / `no` | Whether the product is in quote mode |

## Plugin Options

All options use the prefix `litequote_` and are stored with `autoload = yes`.

See [Configuration](/guide/configuration) for the full list.
