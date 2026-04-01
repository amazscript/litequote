# WhatsApp Integration

LiteQuote is the only WooCommerce quote plugin with native WhatsApp support. Let your customers contact you directly on WhatsApp with a pre-filled message containing product information.

## Setup

1. Go to **WooCommerce > LiteQuote > WhatsApp**
2. Enter your **WhatsApp number** in international format (e.g., `33612345678` for France)
3. Choose a **display mode**
4. Customize the **message template** (optional)
5. Save

::: tip Number Format
Enter the number **without** the `+` sign and **without** the leading `0`. Example: for `+33 6 12 34 56 78`, enter `33612345678`.
:::

## Display Modes

### Form Only (default)
Standard behavior. The quote button opens the modal popup with the form. WhatsApp is not shown.

### WhatsApp Only
The quote button skips the modal entirely and opens WhatsApp directly with a pre-filled message. No form, no email -- just WhatsApp.

### Both
The modal opens with the standard form **plus** a green "Chat on WhatsApp" button at the bottom. The customer chooses their preferred channel.

## Message Template

The default pre-filled message:

```
Hello! I am interested in the product {product_name} (Ref. {sku}). 
Could you send me your best price? {product_url}
```

### Available Variables

| Variable | Replaced with |
|---|---|
| `{product_name}` | Product name |
| `{sku}` | Product SKU/reference |
| `{product_url}` | Full URL to the product page |
| `{variation}` | Selected variation (e.g., "Color: Red / Size: L") |

## How It Works Technically

- Uses the official `wa.me` URL scheme by Meta
- URL format: `https://wa.me/{number}?text={encoded_message}`
- Opens WhatsApp native app on mobile (iOS/Android)
- Opens WhatsApp Web on desktop
- No WhatsApp API, no tokens, no external dependencies
- GDPR-safe: no data sent to third parties

## Why WhatsApp?

- **200M+ professional users** in Europe
- **Dominant channel** in francophone and MENA markets
- Response rate on WhatsApp is **3x higher** than email
- No competitor offers this natively
