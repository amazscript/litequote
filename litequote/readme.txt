=== LiteQuote for WooCommerce ===
Contributors: amazscript
Tags: woocommerce, quote, request a quote, devis, b2b, whatsapp, catalogue mode, presupuesto, preventivo, angebot
Requires at least: 6.6
Tested up to: 6.9
Requires PHP: 8.0
WC requires at least: 8.0
WC tested up to: 10.6
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Ultra-lightweight quote request plugin for WooCommerce. Replace "Add to Cart" with a quote button in under 150 KB, zero jQuery.

== Description ==

**LiteQuote** lets your WooCommerce customers request a quote directly from the product page in under 10 seconds — no page reload, no friction.

**Why LiteQuote?**

* **Under 150 KB** — 30x lighter than YITH Request a Quote (4-6 MB)
* **$24 one-time** — no subscription, no recurring fees (vs $89/year for YITH)
* **Zero jQuery** — pure vanilla JS, no legacy dependencies
* **WhatsApp built-in** — the only quote plugin with native WhatsApp support
* **GDPR-safe** — no external API calls, no cookies, no Google reCAPTCHA
* **One-click quoting** — receive a request, type a price, send a professional PDF quote

**Core Features:**

* Replace "Add to Cart" with a customizable quote button
* AJAX modal popup with pre-filled product information
* Professional HTML email notifications with PDF attachment
* WhatsApp integration (3 modes: form only, WhatsApp only, both)
* Catalogue mode — transform your entire shop into a quote-only showcase
* Quote dashboard — manage all requests with status tracking
* One-click quoting — type a price, generate & send a PDF quote
* CSV export of all quote requests
* Anti-spam: honeypot + rate limiting (no CAPTCHA)
* Fully translatable (French translation included)

**Perfect for:**

* B2B wholesale shops
* Custom/bespoke products (furniture, jewelry, clothing)
* Service providers (consulting, design, development)
* Shops with negotiable pricing

== Installation ==

1. Upload the `litequote` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. **Important:** Install an SMTP plugin (FluentSMTP recommended) for reliable email delivery
4. Go to WooCommerce > LiteQuote to configure
5. Edit a product and check "Price on request" — or set the price to $0

That's it. No complex setup required.

== Frequently Asked Questions ==

= Do I need a WhatsApp Business account? =

No. The WhatsApp integration uses the standard wa.me link, which works with any WhatsApp account (personal or business).

= Does this plugin slow down my site? =

No. LiteQuote loads its JS and CSS only on WooCommerce pages (product, shop, category). Total asset size is under 25 KB. No impact on PageSpeed score.

= Is it compatible with my theme? =

LiteQuote uses standard WooCommerce hooks and works with all major themes: Storefront, Astra, Divi, Flatsome, GeneratePress, and more.

= Does it work with variable products? =

Yes. The variation selector remains visible. When the customer selects a variation (size, color, etc.), it's automatically captured and included in the quote request.

= How does anti-spam work without reCAPTCHA? =

LiteQuote uses two invisible protection layers: a honeypot field (traps bots silently) and rate limiting (max 5 requests per 5 minutes per IP). No CAPTCHA = no friction for real users, and no Google tracking on your site.

= Can I customize the emails? =

Yes. The auto-reply email template is fully customizable with HTML and variables ({client_name}, {product_name}, etc.). The admin notification uses a professional fixed template.

= What happens when I uninstall? =

All plugin data is removed: options, product meta, archived PDFs, and scheduled tasks. Nothing is left behind.

= Is it GDPR compliant? =

Yes. LiteQuote makes zero external API calls, sets no cookies, loads no external resources (fonts, scripts, images), and uses no third-party services. All data stays on your server.

== Screenshots ==

1. Quote button on product page — replaces "Add to Cart"
2. Quote modal — AJAX popup with pre-filled form
3. Admin email notification — professional HTML with one-click reply
4. WhatsApp integration — pre-filled message with product info
5. Settings page — 6 tabs for full customization
6. Quote dashboard — manage all requests with status tracking
7. Send quote — type a price and send a PDF quote in one click
8. PDF quote — professional A4 document with price table

== Changelog ==

= 2.0.0 =
* NEW: Quote dashboard — view and manage all quote requests
* NEW: One-click quoting — type a price, generate & send a professional PDF quote
* NEW: Status tracking — Pending, Quoted, Accepted, Rejected
* NEW: CSV export of all quotes
* NEW: Bulk actions — mark as accepted/rejected, delete
* NEW: Professional quote PDF with price table, discount, conditions
* NEW: Company and Quantity fields in the quote form
* NEW: Rate limiting (5 requests per 5 minutes per IP)
* FIX: Checkbox settings save correctly
* FIX: Translation loading timing (no more _load_textdomain warning)

= 1.5.0 =
* NEW: PDF generation for quote requests (Extended tier)
* NEW: PDF attached to admin and client emails
* NEW: Local PDF archive with auto-purge via WP-Cron
* NEW: Shop logo in PDF header

= 1.2.0 =
* NEW: Catalogue mode — transform entire shop into quote showcase
* NEW: Exclude products/categories from catalogue mode
* NEW: Honeypot anti-spam (invisible, no CAPTCHA)
* NEW: Custom CSS field in Advanced settings
* NEW: Debug mode for spam logging

= 1.1.0 =
* NEW: WhatsApp integration with 3 display modes
* NEW: Auto-reply email to customers
* NEW: Customizable email and WhatsApp message templates

= 1.0.0 =
* Initial release
* Quote button with customizable text, colors, and position
* AJAX modal popup with form validation
* Admin email notification with Reply-To header
* WordPress nonce CSRF protection
* Conditional asset loading (WooCommerce pages only)
* Full i18n support with French translation

== Upgrade Notice ==

= 2.0.0 =
Major update: Quote dashboard, one-click quoting with price, PDF quotes, CSV export. Recommended for all users.
