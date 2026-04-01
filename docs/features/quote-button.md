# Quote Button

The quote button replaces the native WooCommerce "Add to Cart" button on products that are in quote mode.

## How It Works

When a product enters quote mode:

1. The **price is hidden** and replaced by a configurable label (e.g., "Price on request")
2. The **"Add to Cart" button is removed** (including the quantity field)
3. A **"Request a Quote" button** appears in its place
4. The product **cannot be added to cart** via URL either (`?add-to-cart=ID` is blocked)

## On Product Pages

The quote button renders on the single product page with these `data-*` attributes:

- `data-product-id` -- WooCommerce product ID
- `data-product-name` -- Product name
- `data-product-sku` -- Product SKU
- `data-product-url` -- Product permalink

Clicking it opens the [quote modal](/features/modal).

## On Shop/Category Pages

On listing pages (shop, category), products in quote mode show a **"View Product"** link instead of "Add to Cart". This redirects to the product page where the full modal is available.

## Variable Products

For variable products:

- The **variation selector remains visible** (color, size, etc.)
- The visitor selects their variation, then clicks the quote button
- The **selected variation is captured** and included in the quote request
- If no variation is selected, the form still opens (variation is optional)

## Customization

Go to **WooCommerce > LiteQuote > Button** to customize:

- Button text
- Background color
- Text color  
- Position (before or after the product form)
- Price label text

All colors are applied via CSS custom properties (`--litequote-btn-bg`, `--litequote-btn-color`) for easy theme integration.
