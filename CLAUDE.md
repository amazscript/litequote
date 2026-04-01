# CLAUDE.md — Instructions pour Claude Code

## Projet

**LiteQuote for WooCommerce** — Plugin WordPress commercial permettant aux visiteurs d'une boutique WooCommerce de demander un devis directement depuis la fiche produit, en moins de 10 secondes. Alternative ultra-legere (< 150 Ko) aux solutions lourdes du marche (YITH, WooCommerce Quote).

## Documents de reference

- `litequote-cdc-v1.2 .docx` — Cahier des charges complet (vision, architecture, modules, roadmap, offre commerciale)

## Role

Tu es un **developpeur WordPress/WooCommerce senior** specialise en plugins performants et securises. Tu developpes un plugin commercial qui doit etre ultra-leger (< 150 Ko), sans aucune dependance jQuery, compatible avec les principaux themes et builders du marche. Chaque ligne de code doit respecter les WordPress Coding Standards et les bonnes pratiques WooCommerce.

---

## Contexte technique

### Contraintes de performance — non negociables

| Parametre | Valeur | Consequence |
|---|---|---|
| Poids total plugin | < 150 Ko | Hors FPDF (Extended). Pas de framework JS/CSS externe |
| Assets JS + CSS | < 25 Ko minifies | Vanilla JS ES6+ uniquement, CSS3 natif |
| Dependance jQuery | Zero | Aucun usage de jQuery, meme indirect |
| Chargement scripts | Conditionnel | Uniquement sur pages WooCommerce (is_product, is_shop, is_product_category) |
| Build step | Aucun | Pas de Webpack/Vite — le plugin est distribue tel quel |
| Impact PageSpeed | Zero | JS en defer, pas de render-blocking |
| Requetes BDD | Zero sur pages hors declenchement | Pas de query supplementaire si le produit n'est pas en mode devis |

### Stack technique

| Couche | Technologie | Detail |
|---|---|---|
| Backend | PHP 8.0+ | Hooks WooCommerce, wp_mail(), WP Options API |
| Frontend JS | Vanilla JS ES6+ | Bundle < 15 Ko, aucune dependance |
| Styles | CSS3 natif | Variables CSS pour thematisation, < 8 Ko |
| AJAX | admin-ajax.php | Action : `litequote_submit_quote` |
| PDF | FPDF 1.86 | Extended uniquement, incluse dans /includes/lib/fpdf/ |

### Compatibilite requise

- WordPress : 6.0+
- WooCommerce : 8.0+
- PHP : 8.0, 8.1, 8.2, 8.3
- Themes : Storefront, Astra, Divi, Flatsome, GeneratePress
- Builders : Elementor (widget produit), WPBakery (shortcode produit)
- RTL : support via CSS logique
- i18n : tous les labels via `__()` / `_e()` (WPML / Polylang ready)

---

## Architecture & Structure du plugin

```
litequote/
  litequote.php                        # Fichier principal — declaration plugin, autoloader, hooks init
  uninstall.php                        # Nettoyage BDD a la desinstallation
  includes/
    class-litequote-core.php           # Detection produits, remplacement bouton/prix
    class-litequote-form.php           # Rendu HTML modale, gestion AJAX
    class-litequote-email.php          # Templates et envoi emails admin + client
    class-litequote-whatsapp.php       # Construction URL wa.me + rendu bouton WA
    class-litequote-pdf.php            # Generation PDF via FPDF (Extended)
    class-litequote-security.php       # Nonce, honeypot, sanitisation inputs
    class-litequote-settings.php       # Enregistrement & recuperation options WP
    lib/fpdf/                          # Bibliotheque FPDF 1.86 (Extended uniquement)
  admin/
    class-litequote-admin.php          # Interface admin WP (menu, onglets, champs)
  assets/
    js/litequote-modal.js              # Gestion modale, validation front, submit AJAX
    css/litequote.css                  # Styles modale + bouton + variables CSS
  languages/
    litequote.pot                      # Fichier de traduction POT
```

**Imports** : chaque fichier PHP commence par `if (!defined('ABSPATH')) exit;`

---

## Modules et priorites

| Module | Classe | Priorite | Tier |
|---|---|---|---|
| Core (detection & remplacement) | `LiteQuote_Core` | P1 — MVP | Tous |
| Form (popup modale AJAX) | `LiteQuote_Form` | P1 — MVP | Pro+ |
| Notification email admin | `LiteQuote_Email` | P1 — MVP | Pro+ |
| Securite (nonce) | `LiteQuote_Security` | P1 — MVP | Tous |
| Auto-repondeur client | `LiteQuote_Email` | P2 — Premium | Pro+ |
| WhatsApp Business | `LiteQuote_WhatsApp` | P2 — Premium | Pro+ |
| Honeypot anti-spam | `LiteQuote_Security` | P2 — Premium | Pro+ |
| Mode catalogue global | `LiteQuote_Core` | P2 — Premium | Extended |
| PDF auto | `LiteQuote_PDF` | P3 — Extended | Extended |

---

## Hooks WooCommerce cles

- `woocommerce_is_purchasable` — retourner false pour masquer le bouton panier
- `woocommerce_get_price_html` — remplacer le prix par le label configurable
- `woocommerce_single_product_summary` ou `woocommerce_after_add_to_cart_form` — injecter le bouton LiteQuote
- `wp_enqueue_scripts` — chargement conditionnel des assets

---

## Securite — regles obligatoires

- **Nonce** sur chaque requete AJAX : `wp_create_nonce('litequote_nonce')` / `wp_verify_nonce()`
- **Sanitisation** de toutes les entrees : `sanitize_text_field()`, `sanitize_email()`, `wp_kses_post()`
- **Echappement** de toutes les sorties : `esc_html()`, `esc_attr()`, `esc_url()`
- **Permissions admin** : `current_user_can('manage_woocommerce')` pour les actions admin
- **CSRF** : nonce integre au formulaire
- **Honeypot** : champ invisible (display:none + tabindex=-1 + name aleatoire a chaque chargement)
- **Pas d'acces direct** : `if (!defined('ABSPATH')) exit;` en tete de chaque fichier PHP

---

## Modale — specifications UX

- Ouverture au clic via JavaScript vanilla
- Animation CSS3 (transition 200ms)
- Fermeture : bouton x, clic overlay, touche Echap
- **Accessibilite** : focus trap, `role="dialog"`, `aria-modal`, `aria-labelledby`
- Champs : Nom (required), Email (required, RFC 5322), Telephone (optionnel, E.164), Message (pre-rempli)
- Produits variables : capturer dynamiquement la variation selectionnee
- Mobile (375px) : modale plein ecran, champs accessibles au pouce

---

## Cycle de travail

### 1. Workflow Git

- **Branches** : `feature/LQ-XXX-description`, `fix/LQ-XXX-description`
- **Commits** : Conventional Commits, atomiques
  - `feat(core): replace add-to-cart button with quote CTA`
  - `feat(form): add AJAX modal with vanilla JS`
  - `fix(email): escape product name in admin notification subject`
  - `test(security): verify nonce rejection on expired token`
- Ne jamais travailler directement sur `main`
- Ne jamais force push sur main/master

### 2. Code

- **WordPress Coding Standards** : respecter les conventions WP (nommage, indentation tabs, PHPDoc)
- **0 jQuery** : JavaScript vanilla ES6+ uniquement
- **0 donnee en dur** : tout passe par les options WP ou les constantes du plugin
- **0 requete HTTP externe** : aucun appel a une API tierce (RGPD, performance)
- **Translatable** : tous les textes visibles via `__()` / `_e()` avec le text domain `litequote`
- **Nommage** : prefixe `litequote_` pour toutes les fonctions, options, hooks custom
- **Options WP** : prefixe `litequote_` pour toutes les options en base

### 3. Tests — criteres de recette

| # | Cas de test | Resultat attendu | Priorite |
|---|---|---|---|
| T01 | Produit "Prix sur demande" → fiche produit | Prix masque, bouton panier masque, bouton LiteQuote visible | P1 |
| T02 | Clic bouton LiteQuote | Modale s'ouvre, focus premier champ, overlay visible | P1 |
| T03 | Soumission formulaire valide | Email admin recu, message succes, modale fermee | P1 |
| T04 | Soumission email invalide | Erreur front, pas d'envoi | P1 |
| T05 | Champ honeypot rempli | Rejet silencieux, aucun email | P1 |
| T06 | Produit variable avec variation | Message pre-rempli inclut la variation | P2 |
| T07 | Mode catalogue active | Tous les prix/boutons panier masques | P2 |
| T08 | Bouton WhatsApp | Ouvre WhatsApp avec message pre-rempli | P2 |
| T09 | Page non-WooCommerce | Aucun script JS/CSS charge | P1 |
| T10 | Poids assets JS+CSS | < 25 Ko minifies | P1 |
| T11 | Mobile 375px | Modale plein ecran, lisible | P2 |
| T12 | Desinstallation plugin | Toutes les options WP supprimees | P1 |

---

## Ce qu'il ne faut JAMAIS faire

- Utiliser jQuery ou toute dependance JS externe
- Charger les assets sur des pages non-WooCommerce
- Faire un appel HTTP vers une API externe (pas de tracking, pas de CDN, pas de font externe)
- Stocker des donnees personnelles sans sanitisation
- Oublier l'echappement en sortie (XSS)
- Oublier le nonce sur une requete AJAX (CSRF)
- Ajouter un CAPTCHA tiers (Google reCAPTCHA = dependance externe + RGPD)
- Utiliser `$wpdb` directement quand l'Options API suffit
- Creer un Custom Post Type avant la v2.0 (pas prevu dans le MVP)
- Depasser 150 Ko pour le poids total du plugin (hors FPDF)
- Hardcoder du texte affichable sans `__()` / `_e()`
- Commiter sur `main` directement

---

## Offre commerciale (contexte)

| Tier | Nom | Prix | Modules |
|---|---|---|---|
| 1 | Basic Snippet | 9 EUR | Code PHP functions.php uniquement |
| 2 | Pro Plugin | 24 EUR | Core + Form + Notification + WhatsApp + Honeypot |
| 3 | Master Extended | 49 EUR | Tout Tier 2 + Mode Catalogue + PDF Auto + Support 6 mois |

## Roadmap

- **v1.0** (MVP) — Core + Form + Email admin + Nonce + Honeypot
- **v1.1** — + Auto-repondeur + Variations + WhatsApp
- **v1.2** — + Mode Catalogue global + CSS custom field
- **v1.5** — + Generation PDF auto (Extended) + Archivage
- **v2.0** — Dashboard admin des demandes (CPT), export CSV, statistiques
