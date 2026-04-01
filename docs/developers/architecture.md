# Architecture

LiteQuote follows a modular class-based architecture with no external dependencies.

## Directory Structure

```
litequote/
  litequote.php                          # Main plugin file, constants, autoloader, init
  uninstall.php                          # Clean removal of all data
  index.php                              # Directory protection
  includes/
    class-litequote-core.php             # Product detection, button/price replacement
    class-litequote-form.php             # AJAX handler for form submissions
    class-litequote-email.php            # Email templates and sending
    class-litequote-whatsapp.php         # WhatsApp URL builder
    class-litequote-pdf.php              # PDF generation (FPDF)
    class-litequote-security.php         # Nonce, honeypot, sanitization, rate limiting
    class-litequote-settings.php         # Options retrieval and defaults
    class-litequote-quote-cpt.php        # Quote CPT, dashboard, reply, CSV export
    lib/fpdf/                            # FPDF 1.86 library (Extended tier)
  admin/
    class-litequote-admin.php            # Settings page with tabs
  assets/
    js/litequote-modal.js                # Modal, form, validation, AJAX (vanilla JS)
    css/litequote.css                    # All frontend styles
  languages/
    litequote.pot                        # Translation template
    litequote-fr_FR.po                   # French translation (source)
    litequote-fr_FR.mo                   # French translation (compiled)
```

## Autoloading

Classes are autoloaded by naming convention:

```
LiteQuote_Core → includes/class-litequote-core.php
LiteQuote_Admin → admin/class-litequote-admin.php
```

The autoloader is registered via `spl_autoload_register()` in `litequote.php`.

## Initialization Flow

```
plugins_loaded → litequote_check_early()    # Verify WooCommerce is active
init           → litequote_init()           # Instantiate all modules
init           → litequote_load_textdomain() # Load translations
wp_enqueue_scripts → litequote_enqueue_assets() # Conditional JS/CSS loading
```

## Conditional Loading

Assets are only loaded on WooCommerce pages:

```php
if ( ! is_product() && ! is_shop() && ! is_product_category() ) {
    return; // No JS/CSS loaded
}
```

## Data Flow

```
[Visitor clicks quote button]
        ↓
[JS: Modal opens, form pre-filled]
        ↓
[JS: Client-side validation]
        ↓
[JS: fetch() POST to admin-ajax.php]
        ↓
[PHP: Nonce verification]
        ↓
[PHP: Rate limit check]
        ↓
[PHP: Honeypot check]
        ↓
[PHP: Input sanitization]
        ↓
[PHP: wp_mail() admin notification + PDF attachment]
        ↓
[PHP: wp_mail() auto-reply (if enabled)]
        ��
[PHP: Save to CPT litequote_quote]
        ↓
[JS: Success message, modal closes]
```

## Performance Budget

| Asset | Budget | Actual |
|---|---|---|
| Plugin (without FPDF) | < 150 KB | ~115 KB |
| JS | < 15 KB | ~17 KB |
| CSS | < 8 KB | ~8.7 KB |
| FPDF library | excluded | ~120 KB |
| Database queries (non-WC pages) | 0 | 0 |
| External HTTP requests | 0 | 0 |

## Security Model

| Layer | Protection |
|---|---|
| CSRF | WordPress nonce on every AJAX request |
| XSS | All outputs escaped (esc_html, esc_attr, esc_url) |
| SQL Injection | Options API and Post Meta API only (no raw $wpdb) |
| Spam | Honeypot + rate limiting (no reCAPTCHA) |
| Direct access | ABSPATH check on every PHP file |
| File traversal | .htaccess + index.php in all directories |
