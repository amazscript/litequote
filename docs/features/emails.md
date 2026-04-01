# Email Notifications

LiteQuote sends professional HTML emails for every quote interaction.

## Admin Notification

Sent immediately when a visitor submits a quote request.

**Subject:** `[LiteQuote] New quote request -- [Product Name]`

**Content:**
- Header with shop name and gradient background
- Structured table with icons:
  - Customer: name, company, email (mailto link), phone (tel link)
  - Product: name, SKU, quantity, variation, links (view product / edit)
- Customer message in a styled blockquote
- **"Reply to Customer"** button -- opens a pre-filled mailto
- Footer with LiteQuote branding

**Headers:**
- `Reply-To` is set to the customer's email for easy direct reply

## Auto-Reply to Customer

Optional -- enable in **WooCommerce > LiteQuote > Emails**.

Sends a confirmation email to the customer after their submission.

**Subject:** `Your quote request confirmation -- [Shop Name]`

**Default template:**
```
Hello {client_name},

We have received your quote request for {product_name}.

We will get back to you shortly with our best offer.

Best regards,
{shop_name}
```

### Customizable Template

Edit the HTML template in the Emails tab. Available variables:

| Variable | Replaced with |
|---|---|
| `{client_name}` | Customer's full name |
| `{product_name}` | Product name |
| `{product_url}` | Link to the product page |
| `{shop_name}` | Your store name |
| `{date}` | Current date |

## Quote Email (v2.0)

Sent when you reply to a quote request with a price from the dashboard.

**Subject:** `[Shop Name] -- Your Quote LQ-2026-0042`

**Content:**
- Professional header with shop name
- Greeting with customer name
- Price table: product, unit price, quantity, subtotal, discount, total
- Notes and conditions
- Validity period
- PDF quote attached (if PDF module is enabled)

## PDF Attachments

When PDF generation is enabled (Extended tier), both the admin notification and quote emails include a PDF attachment:

- **Admin notification**: PDF summary of the request
- **Quote email**: Professional PDF with price table, references, and legal mentions

## SMTP Requirement

::: warning
LiteQuote uses WordPress's native `wp_mail()` function. An SMTP plugin is required for reliable delivery. See [SMTP Setup](/guide/smtp-setup).
:::
