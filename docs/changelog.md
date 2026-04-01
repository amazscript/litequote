# Changelog

## v2.0.0 -- April 2026

### Quote Dashboard & One-Click Quoting

- **Quote Dashboard**: View all quote requests in a clean admin table under WooCommerce > Quotes
- **Status tracking**: Pending, Quoted, Accepted, Rejected -- with color-coded indicators
- **One-click quoting**: Type a price, click "Generate & Send Quote" -- customer gets a professional email + PDF
- **Quote reply form**: Unit price, quantity, discount, total (real-time calculation), validity, notes
- **Professional quote PDF**: A4 document with price table, shop header, legal mentions, branding
- **CSV export**: Export all quotes with full data, respects active filters
- **Bulk actions**: Mark as accepted/rejected, delete -- with checkboxes
- **Menu badge**: Red counter showing pending quotes in the admin menu
- **Search**: Find quotes by customer name, email, product, or reference
- **Status management**: Change status from the detail page with a dropdown

## v1.5.0 -- April 2026

### PDF Generation

- **PDF attachments**: Every quote request generates a PDF attached to the admin email
- **PDF content**: Reference number, customer info, product details, message
- **Local archive**: Optionally save PDFs to `wp-content/uploads/litequote-quotes/`
- **Auto-purge**: WP-Cron deletes old PDFs based on configurable retention period
- **Shop logo**: Upload your logo to appear in the PDF header

## v1.2.0 -- April 2026

### Catalogue Mode & Advanced

- **Catalogue mode**: Transform entire shop into quote-only showcase
- **Exclusions**: Exclude products by ID or category from catalogue mode
- **Cart page message**: Informative message when cart is accessed in catalogue mode
- **Admin notice**: Reminder when catalogue mode is active
- **Honeypot anti-spam**: Invisible bot protection without CAPTCHA
- **Custom CSS**: Override styles from the Advanced settings tab
- **Debug mode**: Log honeypot and rate limit events

## v1.1.0 -- April 2026

### WhatsApp & Auto-Reply

- **WhatsApp integration**: 3 modes (form only, WhatsApp only, both)
- **Pre-filled WhatsApp messages**: Product name, SKU, URL, variation
- **Customizable WhatsApp template**: With variables
- **Auto-reply email**: Optional confirmation email to customers
- **Customizable auto-reply template**: HTML with variables
- **Rate limiting**: 5 requests per 5 minutes per IP

## v1.0.0 -- April 2026

### Initial Release

- **Quote button**: Replace "Add to Cart" with "Request a Quote" on selected products
- **Trigger modes**: Zero price, checkbox, or both
- **Price label**: Customizable text replacing the price display
- **Quote modal**: AJAX popup with form, validation, pre-filled message
- **Form fields**: Name, company, email, phone, quantity, message
- **Variable products**: Capture selected variation
- **Admin email**: Professional HTML notification with structured data
- **Nonce protection**: CSRF protection on every submission
- **Input sanitization**: All fields sanitized server-side
- **Conditional loading**: JS/CSS only on WooCommerce pages
- **Admin settings**: 6-tab settings page under WooCommerce
- **Color picker**: Customize button colors with live preview
- **i18n ready**: All strings translatable, French translation included
- **Accessibility**: ARIA attributes, focus trap, keyboard navigation
- **Responsive**: Full-screen modal on mobile, reduced motion support
- **Clean uninstall**: Removes all data on plugin deletion
