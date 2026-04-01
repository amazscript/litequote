# Security & Anti-Spam

LiteQuote includes multiple layers of protection without requiring any external service or CAPTCHA.

## Spam Protection

### Honeypot Field

An invisible form field that humans never see but bots fill automatically.

- Hidden via `display:none` + `tabindex="-1"`
- Field name is **randomized** on every page load (not a predictable name)
- If filled: **silent rejection** (HTTP 200, fake success message -- bots don't know they were blocked)
- No CAPTCHA, no friction for real users

### Rate Limiting

Prevents abuse by limiting submissions per IP address.

- **5 requests per 5 minutes** per IP address
- Uses WordPress transients (works with or without Redis/Memcached)
- If exceeded: error message "Too many requests. Please try again in a few minutes."
- Logged when debug mode is enabled

## CSRF Protection

Every form submission includes a **WordPress nonce** token:

- Generated with `wp_create_nonce('litequote_nonce')`
- Verified server-side with `wp_verify_nonce()`
- Renewed on every page load
- Invalid/expired nonce: HTTP 403 rejection

## Input Sanitization

All submitted data is sanitized server-side:

| Field | Sanitization |
|---|---|
| Name | `sanitize_text_field()` |
| Email | `sanitize_email()` + `is_email()` |
| Phone | `sanitize_text_field()` + regex validation |
| Company | `sanitize_text_field()` |
| Quantity | `absint()` (minimum 1) |
| Message | `wp_kses_post()` |
| Product ID | `absint()` |

## Output Escaping

All data displayed in HTML is escaped:

- `esc_html()` for text content
- `esc_attr()` for HTML attributes
- `esc_url()` for URLs
- `sanitize_hex_color()` for color values

## File Protection

- Every PHP file starts with `if (!defined('ABSPATH')) exit;`
- Every directory contains an `index.php` guard file
- PDF archive directory has a `.htaccess` with `Deny from all`

## GDPR Compliance

LiteQuote is GDPR-safe by design:

- **No external API calls** -- no data leaves your server
- **No cookies** -- nothing stored in the visitor's browser
- **No Google reCAPTCHA** -- no Google tracking
- **No CDN/fonts** -- no external resource loaded
- **No analytics** -- no tracking of any kind
- **Full data deletion** on plugin uninstall

## Debug Mode

Enable in **WooCommerce > LiteQuote > Advanced** to log:

- Honeypot triggers (with anonymized IP)
- Rate limit hits (with anonymized IP)
- PDF generation errors

Logs go to `wp-content/debug.log` via `error_log()`. **Never enable in production** unless troubleshooting.
