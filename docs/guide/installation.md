# Installation

## From a ZIP file (CodeCanyon / AmazScript)

1. Download `litequote-pro.zip` or `litequote-extended.zip` from your purchase
2. Go to **Plugins > Add New > Upload Plugin** in your WordPress admin
3. Select the ZIP file and click **Install Now**
4. Click **Activate Plugin**

## Verify Installation

After activation:

1. Go to **WooCommerce > LiteQuote** -- you should see the settings page with 6 tabs
2. Go to **WooCommerce > Quotes** -- the quote dashboard (empty for now)
3. Check that no PHP errors appear

## First Product Setup

To test that everything works:

1. Go to **Products > Add New** (or edit an existing product)
2. In the **General** tab, check **"Price on request"**
3. Save the product
4. Visit the product page on your shop -- you should see:
   - "Price on request" instead of the price
   - A "Request a Quote" button instead of "Add to Cart"

## Automatic Detection

If you prefer, you can skip the checkbox. LiteQuote automatically detects products with a price of $0 or no price set. Configure this in **WooCommerce > LiteQuote > General > Trigger Mode**.

## Uninstallation

When you **delete** the plugin (not just deactivate):

- All `litequote_*` options are removed from the database
- All `_litequote_*` product meta are removed
- Archived PDFs in `wp-content/uploads/litequote-quotes/` are deleted
- WP-Cron tasks are unscheduled

::: tip Deactivation vs Deletion
**Deactivating** the plugin preserves all your settings and data. You can reactivate later without losing anything. **Deleting** removes everything permanently.
:::
