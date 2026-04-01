#!/bin/bash
#
# LiteQuote — Setup script
# Installs WordPress, WooCommerce, and configures the dev environment.

set -e

echo "Waiting for database..."
sleep 5

echo "Installing WordPress..."
wp core install \
  --url="http://localhost:8080" \
  --title="LiteQuote Dev" \
  --admin_user="admin" \
  --admin_password="admin" \
  --admin_email="admin@litequote.local" \
  --skip-email

echo "Setting language to French..."
wp language core install fr_FR || true
wp site switch-language fr_FR || true

echo "Installing WooCommerce..."
wp plugin install woocommerce --activate

echo "Activating LiteQuote..."
wp plugin activate litequote

echo "Creating sample WooCommerce pages..."
wp wc tool run install_pages --user=1 2>/dev/null || true

echo "Creating test products..."

# Product 1: Simple product with price (normal behavior)
wp wc product create \
  --name="T-Shirt Standard" \
  --type=simple \
  --regular_price="29.99" \
  --description="Produit normal avec prix — le bouton Ajouter au panier doit rester." \
  --short_description="Produit test avec prix normal." \
  --sku="TSH-001" \
  --user=1

# Product 2: Simple product with price 0 (should trigger quote mode)
wp wc product create \
  --name="Meuble Sur Mesure" \
  --type=simple \
  --regular_price="0" \
  --description="Produit a prix zero — doit activer le mode devis automatiquement." \
  --short_description="Meuble personnalise, prix sur devis." \
  --sku="MSM-001" \
  --user=1

# Product 3: Simple product with no price (empty)
wp wc product create \
  --name="Prestation Conseil" \
  --type=simple \
  --description="Produit sans prix defini — doit activer le mode devis." \
  --short_description="Conseil personnalise, tarif sur demande." \
  --sku="CSL-001" \
  --user=1

echo "Installing Storefront theme..."
wp theme install storefront --activate

echo "Setting permalinks..."
wp rewrite structure '/%postname%/'
wp rewrite flush

echo ""
echo "Setup complete!"
echo ""
echo "  WordPress : http://localhost:8080"
echo "  Admin     : http://localhost:8080/wp-admin  (admin / admin)"
echo "  Mailpit   : http://localhost:8025"
echo "  phpMyAdmin: http://localhost:8081"
echo "  Redis     : localhost:6379"
echo ""
echo "  Test products created:"
echo "    - T-Shirt Standard (29.99 EUR) — comportement normal"
echo "    - Meuble Sur Mesure (0 EUR)    — mode devis auto"
echo "    - Prestation Conseil (no price) — mode devis auto"
echo ""
