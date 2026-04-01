# CSV Export

Export all your quote requests to a CSV file for analysis in Excel, Google Sheets, or CRM import.

## How to Export

1. Go to **WooCommerce > Quotes**
2. Optionally filter by status (Pending, Quoted, Accepted, etc.)
3. Click **"Export CSV"** at the top of the page
4. A file `litequote-quotes-YYYY-MM-DD.csv` downloads automatically

## CSV Columns

| Column | Description |
|---|---|
| Reference | Quote ID (LQ-2026-0042) |
| Date | Submission date (YYYY-MM-DD HH:MM) |
| Status | Pending / Quoted / Accepted / Rejected |
| Name | Customer name |
| Company | Company name |
| Email | Customer email |
| Phone | Phone number |
| Product | Product name |
| SKU | Product reference |
| Quantity | Requested quantity |
| Message | Customer message |
| Unit Price | Your quoted price (if sent) |
| Discount | Applied discount (if any) |
| Total | Final quoted total (if sent) |

## Features

- **UTF-8 with BOM** -- compatible with Excel (no encoding issues with accented characters)
- **Respects active filters** -- exports only the currently filtered results
- **Secure** -- requires `manage_woocommerce` capability + nonce verification
