# Send Quotes with Price

The core v2.0 feature: receive a request, type a price, click send. Your customer gets a professional quote in seconds.

## Workflow

1. A customer submits a quote request on your shop
2. You receive an email notification + the request appears in your dashboard
3. Click **Reply** on the quote in **WooCommerce > Quotes**
4. Fill in the pricing form
5. Click **"Generate & Send Quote"**
6. The customer receives an email with a price table and PDF attachment
7. The quote status changes to "Quoted"

## Pricing Form

| Field | Description | Required |
|---|---|---|
| **Unit Price** | Price per unit in your store currency | Yes |
| **Quantity** | Pre-filled with the customer's requested quantity | Yes |
| **Discount (%)** | Percentage discount to apply | No |
| **Total** | Calculated automatically in real-time | Auto |
| **Valid for (days)** | How long the quote is valid | No (default: 30) |
| **Notes / Conditions** | Terms, delivery info, payment conditions | No |

The **Total** updates in real-time as you type:

```
Total = (Unit Price x Quantity) - (Subtotal x Discount%)
```

## Save as Draft

Click **"Save Draft"** to save your pricing without sending. You can come back later to review and send.

## What the Customer Receives

### Email

A professional HTML email with:
- Greeting with the customer's name
- Price table: product, unit price, quantity, subtotal, discount, total
- Your notes and conditions
- Validity period
- Your shop's branding

### PDF Attachment (Extended tier)

A professional A4 PDF with:
- Shop header (logo + address)
- Blue title bar: "QUOTE LQ-2026-0042"
- Customer information
- Price table with totals
- Notes and conditions
- Validity statement
- Footer with reference number

## Re-sending a Quote

You can modify the price and re-send a quote at any time by clicking **Reply** again on the same request. The previous quote data is pre-filled.

## After Sending

- The status automatically changes to **Quoted** (blue)
- The sent date is recorded
- You can manually change the status to **Accepted** or **Rejected** later
