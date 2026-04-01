# Quick Configuration

All settings are in **WooCommerce > LiteQuote**. The settings page has 6 tabs.

## General Tab

| Setting | Description | Default |
|---|---|---|
| **Trigger Mode** | How products enter quote mode | Both (zero price + checkbox) |
| **Catalogue Mode** | Hide all prices and cart buttons site-wide | Off |
| **Exclude Product IDs** | Comma-separated IDs to exclude from catalogue mode | Empty |
| **Exclude Categories** | Categories to exclude from catalogue mode | None |

### Trigger Modes Explained

- **Products with price = 0**: Any product with an empty or zero price automatically shows a quote button
- **Products with checkbox enabled**: Only products where you manually check "Price on request" in the product editor
- **Both**: Either condition triggers quote mode (recommended)

## Button Tab

| Setting | Description | Default |
|---|---|---|
| **Button Text** | Text displayed on the quote button | "Request a Quote" |
| **Background Color** | Button background color (hex) | #0073aa |
| **Text Color** | Button text color (hex) | #ffffff |
| **Button Position** | Before or after the add-to-cart form | After |
| **Price Label** | Text shown instead of the price | "Price on request" |

A live preview is shown at the bottom of this tab.

## Emails Tab

| Setting | Description | Default |
|---|---|---|
| **Admin Email** | Where quote requests are sent | WordPress admin email |
| **Auto-Reply** | Send confirmation to the customer | Off |
| **Auto-Reply Template** | HTML template for the confirmation email | Default template |

### Template Variables

Use these in your auto-reply template:

- `{client_name}` -- Customer's name
- `{product_name}` -- Product name
- `{product_url}` -- Link to the product page
- `{shop_name}` -- Your store name
- `{date}` -- Current date

## WhatsApp Tab

| Setting | Description | Default |
|---|---|---|
| **WhatsApp Number** | Your WhatsApp Business number (international format) | Empty |
| **Display Mode** | Form only / WhatsApp only / Both | Form only |
| **Message Template** | Pre-filled WhatsApp message | Default template |

### Display Modes

- **Form only**: Standard behavior -- modal popup with form
- **WhatsApp only**: Clicking the button opens WhatsApp directly (no modal)
- **Both**: Modal shows the form + a green "Chat on WhatsApp" button

### Message Variables

- `{product_name}` -- Product name
- `{sku}` -- Product SKU/reference
- `{product_url}` -- Product page URL
- `{variation}` -- Selected variation (if applicable)

## PDF Tab (Extended)

| Setting | Description | Default |
|---|---|---|
| **Enable PDF** | Generate a PDF for each request | Off |
| **Shop Logo** | Logo displayed in PDF header | None |
| **Local Archive** | Save PDFs to server | Off |
| **Retention** | Days to keep archived PDFs | 90 |

## Advanced Tab

| Setting | Description | Default |
|---|---|---|
| **Debug Mode** | Log honeypot blocks to error_log | Off |
| **Custom CSS** | Add custom CSS to override styles | Empty |
