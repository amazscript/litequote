# PDF Generation

Generate professional PDF documents for every quote request and quote reply. Available in the **Extended tier**.

## Two Types of PDFs

### 1. Request PDF (automatic)

Generated when a customer submits a quote request. Attached to the admin notification email.

Contains:
- Shop name and date
- Reference number (LQ-2026-0042)
- Customer: name, company, email, phone
- Product: name, SKU, variation, quantity, URL
- Customer message
- Footer

### 2. Quote PDF (on send)

Generated when you send a quote reply with a price. Attached to the customer's email.

Contains:
- Shop header with logo and address
- Blue title bar: "QUOTE LQ-2026-0042"
- Date and validity period
- Customer information
- **Price table**: product, unit price, quantity, subtotal, discount, total
- Notes and conditions
- Legal mentions (if configured)
- Professional footer

## Setup

1. Go to **WooCommerce > LiteQuote > PDF**
2. Enable **"Generate a PDF for each quote request"**
3. Optionally upload your **shop logo**
4. Optionally enable **local archive**
5. Save

## Archive

When enabled, PDFs are saved to:

```
wp-content/uploads/litequote-quotes/LQ-2026-0001.pdf
```

The directory is protected with a `.htaccess` file (`Deny from all`).

## Auto-Purge

Set a retention period (default: 90 days). A daily WP-Cron task automatically deletes PDFs older than this period.

## Technical Details

- Library: **FPDF 1.86** (included in the plugin, no Composer required)
- Format: A4 portrait
- Encoding: ISO-8859-1 (with UTF-8 transliteration for accented characters)
- No external dependencies
- Logo formats: PNG, JPG (auto-resized to max 150px width)
- Reference numbers: auto-incremented, never duplicated
