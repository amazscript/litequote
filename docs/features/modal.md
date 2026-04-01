# Quote Modal

The quote modal is a popup form that opens when a visitor clicks the quote button. It allows them to submit a quote request without leaving the product page.

## Form Fields

| Field | Type | Required | Pre-filled |
|---|---|---|---|
| Full Name | text | Yes | No |
| Company | text | No | No |
| Email | email | Yes | No |
| Phone | tel | No | No |
| Quantity | number | No | Default: 1 |
| Message | textarea | No | Yes -- with product info |

The message is automatically pre-filled with:

```
Hello, I would like a quote for: [Product Name] -- Ref. [SKU]
Variation: [Color: Red / Size: L]
```

## User Experience

- **No page reload** -- everything happens via AJAX
- **CSS3 animations** -- smooth 200ms open/close transitions
- **Backdrop blur** -- semi-transparent overlay with blur effect
- **Mobile-friendly** -- full-screen modal on screens under 480px
- **Reduced motion** -- respects `prefers-reduced-motion` system setting

## Accessibility (a11y)

The modal is fully accessible:

- `role="dialog"` and `aria-modal="true"`
- `aria-labelledby` pointing to the modal title
- **Focus trap** -- Tab key stays within the modal
- **Auto-focus** on the first field when opened
- **Focus restore** to the quote button when closed
- Close button has `aria-label="Close"`

## Closing the Modal

Three ways to close:

1. Click the **X** button (top right)
2. Click the **overlay** (dark background)
3. Press the **Escape** key

## After Submission

1. A **spinner** appears on the submit button (disabled to prevent double-click)
2. On success: a **green message** appears, modal auto-closes after 3 seconds
3. On error: a **red message** appears, modal stays open
4. The form resets after successful submission

## Anti-Spam

The modal includes invisible spam protection:

- **Honeypot field** -- hidden input that bots fill out (triggers silent rejection)
- **Rate limiting** -- max 5 submissions per IP per 5 minutes
- **Nonce verification** -- CSRF protection via WordPress nonce
