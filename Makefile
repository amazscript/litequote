# LiteQuote — Makefile
# Raccourcis pour les commandes Docker courantes

.PHONY: up down restart logs setup wp shell db-shell test-options test-active clean

## Demarrer tous les services
up:
	docker compose up -d

## Arreter tous les services
down:
	docker compose down

## Redemarrer WordPress (apres modif PHP)
restart:
	docker compose restart wordpress

## Voir les logs WordPress en temps reel
logs:
	docker compose logs -f wordpress

## Setup initial (WordPress + WooCommerce + produits test)
setup:
	docker compose up -d
	@echo "Waiting for services to start..."
	@sleep 12
	docker compose exec wordpress chown -R www-data:www-data /var/www/html
	docker compose --profile cli run --rm wpcli sh -c "$$(cat setup.sh)"

## Executer une commande WP-CLI (ex: make wp CMD="option list --search=litequote_*")
wp:
	docker compose --profile cli run --rm wpcli wp $(CMD)

## Shell dans le container WordPress
shell:
	docker compose exec wordpress bash

## Shell MySQL
db-shell:
	docker compose exec db mysql -u litequote -plitequote_dev_2026 litequote

## Verifier les options LiteQuote en base
test-options:
	docker compose --profile cli run --rm wpcli wp option list --search=litequote_* --format=table

## Verifier que le plugin est actif
test-active:
	docker compose --profile cli run --rm wpcli wp plugin list

## Supprimer tous les volumes (reset complet)
clean:
	docker compose down -v
	@echo "All volumes deleted. Run 'make setup' to start fresh."
