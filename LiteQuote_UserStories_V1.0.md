# LiteQuote for WooCommerce — User Stories Completes

**Reference** : LITEQUOTE-WOO-2025
**Version** : v1.0
**Base** : CDC v1.2
**Date** : Avril 2026
**Auteur** : Denis — AmazScript / ByteSproutLab

---

## Conventions

- **ID** : `LQ-EXX-SYY` (E = Epic, S = Story)
- **Priorite** : P1 (MVP), P2 (Premium), P3 (Extended)
- **Estimation** : Story Points (SP) selon complexite Fibonacci (1, 2, 3, 5, 8, 13)
- **Format** : En tant que [persona], je veux [action] afin de [benefice]
- **Criteres d'acceptation** : checklists testables et mesurables

### Personas

| Persona | Description |
|---|---|
| **Visiteur** | Utilisateur anonyme naviguant sur la boutique WooCommerce |
| **Client** | Visiteur ayant soumis une demande de devis |
| **Marchand** | Proprietaire de la boutique, administrateur WooCommerce |
| **Developpeur** | Integrateur ou developpeur personnalisant le plugin |

---

## Sommaire des Epics

| Epic | Nom | Module | Priorite | Stories |
|---|---|---|---|---|
| E01 | Detection & Remplacement | Core | P1 | 6 |
| E02 | Bouton de devis | Core | P1 | 4 |
| E03 | Popup modale de devis | Form | P1 | 7 |
| E04 | Notification email admin | Notification | P1 | 4 |
| E05 | Auto-repondeur client | Notification | P2 | 4 |
| E06 | Securite & Anti-spam | Securite | P1/P2 | 5 |
| E07 | WhatsApp Business | WhatsApp | P2 | 5 |
| E08 | Mode catalogue global | Catalogue | P2 | 5 |
| E09 | Generation PDF | PDF | P3 | 5 |
| E10 | Interface d'administration | Admin | P1/P2 | 8 |
| E11 | Performance & Chargement | Technique | P1 | 4 |
| E12 | Compatibilite & i18n | Technique | P1/P2 | 5 |
| E13 | Installation & Desinstallation | Technique | P1 | 3 |
| **Total** | | | | **65** |

---

## EPIC 01 — Detection & Remplacement (Core)

> Module : `class-litequote-core.php`
> Priorite : P1 — MVP
> Hooks cles : `woocommerce_is_purchasable`, `woocommerce_get_price_html`

---

### LQ-E01-S01 — Declenchement par case a cocher produit

**En tant que** marchand,
**je veux** pouvoir cocher une option "Prix sur demande" dans la fiche produit admin,
**afin de** choisir individuellement quels produits proposent un devis au lieu d'un achat.

**Priorite** : P1 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Une meta box ou un champ checkbox apparait dans l'onglet "General" de la fiche produit WooCommerce admin
- [ ] Le champ est labellise "Prix sur demande" (translatable)
- [ ] La valeur est stockee en post meta : `_litequote_enabled` (yes/no)
- [ ] Le champ est visible pour les produits simples et variables
- [ ] La valeur par defaut est "no" (non coche)
- [ ] La meta est sauvegardee via le hook `woocommerce_process_product_meta`

---

### LQ-E01-S02 — Declenchement automatique par prix a zero

**En tant que** marchand,
**je veux** que les produits dont le prix est a 0 EUR activent automatiquement le mode devis,
**afin de** ne pas avoir a cocher manuellement chaque produit sans prix.

**Priorite** : P1 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Si le prix du produit est vide ou egal a 0, le mode devis s'active automatiquement
- [ ] Ce comportement est activable/desactivable dans les reglages generaux du plugin
- [ ] Le mode "prix a 0" et le mode "checkbox" peuvent fonctionner simultanement (option "Les deux" dans les reglages)
- [ ] Un produit variable dont toutes les variations sont a 0 EUR est aussi detecte

---

### LQ-E01-S03 — Masquage du prix affiche

**En tant que** visiteur,
**je veux** voir un label personnalise ("Prix sur demande") a la place du prix,
**afin de** comprendre que le tarif est disponible sur devis.

**Priorite** : P1 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Le prix natif WooCommerce est masque via le filtre `woocommerce_get_price_html`
- [ ] Un label configurable est affiche a la place (defaut : "Prix sur demande")
- [ ] Le label est translatable via `__()` avec le text domain `litequote`
- [ ] Le masquage fonctionne sur la fiche produit, la page boutique et les pages de categorie
- [ ] Le label respecte le style du theme actif (pas de style force)
- [ ] Le prix reste visible dans l'admin WooCommerce (back-office)

---

### LQ-E01-S04 — Masquage du bouton Ajouter au panier

**En tant que** visiteur,
**je veux** que le bouton "Ajouter au panier" soit masque pour les produits en mode devis,
**afin de** ne pas pouvoir acheter un produit dont le prix est sur demande.

**Priorite** : P1 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Le filtre `woocommerce_is_purchasable` retourne `false` pour les produits en mode devis
- [ ] Le bouton natif "Ajouter au panier" disparait de la fiche produit
- [ ] Le bouton disparait aussi des pages boutique et categorie (boutons loop)
- [ ] Le champ quantite est egalement masque
- [ ] Les produits en mode devis ne sont pas ajoutables au panier via URL directe (`?add-to-cart=ID`)
- [ ] Les autres produits de la boutique ne sont pas affectes

---

### LQ-E01-S05 — Gestion des produits variables

**En tant que** visiteur,
**je veux** que le mode devis fonctionne aussi sur les produits variables,
**afin de** pouvoir demander un devis en precisant ma variation.

**Priorite** : P1 | **SP** : 5

**Criteres d'acceptation** :
- [ ] Le selecteur de variations reste visible meme si le produit est en mode devis
- [ ] Le visiteur peut selectionner ses attributs (taille, couleur, etc.) avant de cliquer sur le bouton devis
- [ ] La combinaison d'attributs selectionnee est capturee dynamiquement (ex. : "Couleur : Rouge / Taille : L")
- [ ] Si aucune variation n'est selectionnee, le formulaire s'ouvre quand meme (variation optionnelle dans le message)
- [ ] Les variations dont le prix est a 0 EUR sont detectees individuellement si le mode "prix a 0" est actif

---

### LQ-E01-S06 — Coexistence avec les produits normaux

**En tant que** marchand,
**je veux** que seuls les produits marques "devis" soient affectes par le plugin,
**afin de** conserver le fonctionnement normal de ma boutique pour les autres produits.

**Priorite** : P1 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Un produit sans la meta `_litequote_enabled` et avec un prix > 0 conserve son prix et son bouton panier
- [ ] Les pages panier, commande et mon-compte ne sont pas impactees
- [ ] Les produits groupes et externes non marques restent inchanges
- [ ] Le plugin ne modifie aucune table WooCommerce native
- [ ] Aucun conflit avec le panier existant (un client peut avoir des produits normaux en panier tout en demandant un devis)

---

## EPIC 02 — Bouton de devis (Core)

> Module : `class-litequote-core.php`
> Priorite : P1 — MVP

---

### LQ-E02-S01 — Affichage du bouton de devis

**En tant que** visiteur,
**je veux** voir un bouton "Demander un devis" sur la fiche d'un produit en mode devis,
**afin de** pouvoir initier ma demande de prix.

**Priorite** : P1 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Un bouton apparait a la place du bouton "Ajouter au panier" sur les produits en mode devis
- [ ] Le texte par defaut est "Demander un devis" (configurable dans les reglages)
- [ ] Le bouton est rendu via un hook WooCommerce configurable (avant ou apres le formulaire d'ajout)
- [ ] Le bouton contient un attribut `data-product-id` avec l'ID du produit
- [ ] Le bouton contient un attribut `data-product-name` avec le nom du produit
- [ ] Le bouton est de type `button` (pas un lien, pas un submit)

---

### LQ-E02-S02 — Personnalisation visuelle du bouton

**En tant que** marchand,
**je veux** pouvoir modifier la couleur et le texte du bouton de devis,
**afin de** l'adapter a la charte graphique de ma boutique.

**Priorite** : P1 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Le texte du bouton est modifiable via un champ texte dans les reglages
- [ ] La couleur de fond est modifiable via un champ hexadecimal (color picker)
- [ ] La couleur du texte est modifiable via un champ hexadecimal (color picker)
- [ ] Les couleurs sont appliquees via des variables CSS inline (`--litequote-btn-bg`, `--litequote-btn-color`)
- [ ] Un apercu en temps reel n'est pas requis mais les changements sont visibles apres sauvegarde
- [ ] Les valeurs par defaut sont harmonieuses (ex. : fond bleu #0073aa, texte blanc #ffffff)

---

### LQ-E02-S03 — Position configurable du bouton

**En tant que** marchand,
**je veux** choisir ou placer le bouton de devis sur la fiche produit,
**afin de** l'integrer au mieux dans le design de mon theme.

**Priorite** : P1 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Un reglage permet de choisir entre "Avant le formulaire d'ajout au panier" et "Apres le formulaire d'ajout au panier"
- [ ] Le hook utilise change en consequence (`woocommerce_before_add_to_cart_form` ou `woocommerce_after_add_to_cart_form`)
- [ ] La valeur par defaut est "Apres le formulaire"
- [ ] Le changement de position est effectif sans purge de cache

---

### LQ-E02-S04 — Bouton sur les pages boutique et categorie (loop)

**En tant que** visiteur,
**je veux** voir le bouton de devis aussi sur les pages liste (boutique, categorie),
**afin de** pouvoir identifier visuellement les produits en mode devis sans ouvrir chaque fiche.

**Priorite** : P2 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Les produits en mode devis affichent le bouton LiteQuote dans la boucle produit (loop)
- [ ] Le bouton remplace le bouton "Ajouter au panier" natif dans la loop
- [ ] Le clic sur le bouton en loop redirige vers la fiche produit (pas d'ouverture modale depuis la loop)
- [ ] Le style du bouton en loop est coherent avec celui de la fiche produit

---

## EPIC 03 — Popup modale de devis (Form)

> Module : `class-litequote-form.php` + `assets/js/litequote-modal.js`
> Priorite : P1 — MVP

---

### LQ-E03-S01 — Ouverture de la modale

**En tant que** visiteur,
**je veux** qu'une fenetre modale s'ouvre quand je clique sur le bouton "Demander un devis",
**afin de** remplir ma demande sans quitter la page produit.

**Priorite** : P1 | **SP** : 5

**Criteres d'acceptation** :
- [ ] Le clic sur le bouton ouvre une modale en overlay centree
- [ ] L'ouverture est animee en CSS3 (opacity + transform, transition 200ms)
- [ ] Un overlay semi-transparent couvre le fond de page
- [ ] Le scroll de la page de fond est desactive (body overflow:hidden)
- [ ] La modale est generee en JavaScript vanilla ES6+ (zero jQuery)
- [ ] Le HTML de la modale est injecte dans le DOM au chargement de la page (pas de requete AJAX pour le template)

---

### LQ-E03-S02 — Fermeture de la modale

**En tant que** visiteur,
**je veux** pouvoir fermer la modale de plusieurs facons,
**afin de** ne pas me sentir bloque dans le formulaire.

**Priorite** : P1 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Un bouton "x" (close) en haut a droite ferme la modale
- [ ] Un clic sur l'overlay (fond sombre) ferme la modale
- [ ] La touche Echap ferme la modale
- [ ] La fermeture est animee (inverse de l'ouverture, 200ms)
- [ ] Le scroll de la page est restaure apres fermeture
- [ ] Le focus retourne sur le bouton de devis apres fermeture

---

### LQ-E03-S03 — Accessibilite de la modale (a11y)

**En tant que** visiteur utilisant un lecteur d'ecran ou la navigation clavier,
**je veux** que la modale soit pleinement accessible,
**afin de** pouvoir demander un devis sans barriere.

**Priorite** : P1 | **SP** : 3

**Criteres d'acceptation** :
- [ ] La modale possede l'attribut `role="dialog"`
- [ ] L'attribut `aria-modal="true"` est present
- [ ] Un `aria-labelledby` pointe vers le titre de la modale
- [ ] Le focus est automatiquement place sur le premier champ a l'ouverture
- [ ] Un focus trap empeche la tabulation de sortir de la modale tant qu'elle est ouverte
- [ ] Le bouton de fermeture est focusable et possede un `aria-label="Fermer"`

---

### LQ-E03-S04 — Champs du formulaire

**En tant que** visiteur,
**je veux** remplir un formulaire simple avec mes coordonnees et mon message,
**afin de** soumettre ma demande de devis rapidement.

**Priorite** : P1 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Champ "Nom complet" : type text, required, placeholder "Votre nom"
- [ ] Champ "Email" : type email, required, placeholder "votre@email.com"
- [ ] Champ "Telephone" : type tel, optionnel, placeholder "+33 6 12 34 56 78"
- [ ] Champ "Message" : textarea, pre-rempli avec "Bonjour, je souhaite un devis pour : [Nom du produit] — Ref. [SKU]"
- [ ] Tous les labels sont translatable via `__()` avec le text domain `litequote`
- [ ] Les champs required sont marques visuellement (asterisque ou bordure)
- [ ] L'ordre de tabulation est logique (nom → email → telephone → message → envoyer)

---

### LQ-E03-S05 — Pre-remplissage contextuel du message

**En tant que** visiteur,
**je veux** que le message soit pre-rempli avec les informations du produit,
**afin de** gagner du temps et que le marchand sache immediatement de quel produit il s'agit.

**Priorite** : P1 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Le textarea "Message" contient par defaut : "Bonjour, je souhaite un devis pour : {nom_produit} — Ref. {SKU}"
- [ ] Si le produit n'a pas de SKU, la mention "Ref." est omise
- [ ] Pour un produit variable, la variation selectionnee est ajoutee : "Variante : Couleur: Rouge / Taille: L"
- [ ] Si la variation change apres ouverture de la modale, le message est mis a jour dynamiquement
- [ ] Le visiteur peut modifier librement le message pre-rempli
- [ ] Les donnees produit sont injectees via des attributs `data-*` sur le bouton, lues par le JS

---

### LQ-E03-S06 — Validation front-end du formulaire

**En tant que** visiteur,
**je veux** etre averti si mes informations sont incorrectes avant l'envoi,
**afin de** corriger mes erreurs sans attendre une reponse serveur.

**Priorite** : P1 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Le nom est valide si non vide (apres trim)
- [ ] L'email est valide selon le format RFC 5322 (regex cote client)
- [ ] Le telephone, s'il est rempli, est valide selon un format international basique (chiffres, +, espaces, tirets)
- [ ] Les messages d'erreur sont affiches sous chaque champ invalide (pas d'alert())
- [ ] Les messages d'erreur sont en rouge et translatable
- [ ] Le focus est place sur le premier champ en erreur
- [ ] Le bouton "Envoyer" est desactive pendant la soumission (anti double-clic)

---

### LQ-E03-S07 — Soumission AJAX du formulaire

**En tant que** visiteur,
**je veux** que ma demande soit envoyee sans rechargement de page,
**afin de** avoir une experience fluide et rapide.

**Priorite** : P1 | **SP** : 5

**Criteres d'acceptation** :
- [ ] La soumission est faite en AJAX via `fetch()` vers `admin-ajax.php`
- [ ] L'action AJAX est `litequote_submit_quote`
- [ ] Le nonce WordPress est inclus dans la requete
- [ ] Un loader/spinner est affiche pendant l'envoi
- [ ] En cas de succes : message de confirmation affiche dans la modale, puis fermeture auto apres 3 secondes
- [ ] En cas d'erreur serveur : message d'erreur affiche dans la modale (sans fermeture)
- [ ] Le message de succes par defaut est : "Merci ! Votre demande de devis a ete envoyee. Nous vous repondrons dans les plus brefs delais."
- [ ] Le formulaire est reinitialise apres un envoi reussi
- [ ] Les donnees envoyees : nom, email, telephone, message, product_id, product_name, sku, variation, nonce

---

## EPIC 04 — Notification email admin (Notification)

> Module : `class-litequote-email.php`
> Priorite : P1 — MVP

---

### LQ-E04-S01 — Envoi de l'email admin a la soumission

**En tant que** marchand,
**je veux** recevoir un email immediatement quand un visiteur soumet une demande de devis,
**afin de** pouvoir y repondre rapidement.

**Priorite** : P1 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Un email est envoye via `wp_mail()` a chaque soumission validee
- [ ] L'adresse destinataire est celle configuree dans les reglages (defaut : `get_bloginfo('admin_email')`)
- [ ] L'envoi est declenche cote serveur apres validation du nonce et des donnees
- [ ] L'email est envoye meme si l'auto-repondeur client est desactive
- [ ] En cas d'echec d'envoi, l'erreur est loguee (pas de message d'erreur au visiteur)

---

### LQ-E04-S02 — Objet et contenu de l'email admin

**En tant que** marchand,
**je veux** que l'email de notification soit clair et structure,
**afin de** comprendre en un coup d'oeil la demande du client.

**Priorite** : P1 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Objet : "[LiteQuote] Nouvelle demande de devis — {nom_produit}"
- [ ] Le nom du produit dans l'objet est echappe (`esc_html`)
- [ ] Corps HTML structure contenant :
  - Nom du demandeur
  - Email du demandeur (lien mailto:)
  - Telephone (si renseigne)
  - Nom du produit
  - SKU (si disponible)
  - Variation selectionnee (si applicable)
  - Message du demandeur
  - Lien direct vers la fiche produit admin (edit post)
  - Date et heure de la demande
- [ ] Le content-type de l'email est `text/html`
- [ ] Toutes les donnees utilisateur dans le corps sont echappees

---

### LQ-E04-S03 — Adresse destinataire configurable

**En tant que** marchand,
**je veux** pouvoir configurer l'adresse email de reception des devis,
**afin de** recevoir les demandes sur l'adresse de mon choix (pas forcement l'admin WP).

**Priorite** : P1 | **SP** : 1

**Criteres d'acceptation** :
- [ ] Un champ email dans les reglages du plugin (onglet Emails)
- [ ] Valeur par defaut : `get_bloginfo('admin_email')`
- [ ] Le champ est valide avec `sanitize_email()`
- [ ] Si le champ est vide, l'email admin WP est utilise en fallback

---

### LQ-E04-S04 — Header Reply-To dynamique

**En tant que** marchand,
**je veux** pouvoir repondre directement a l'email de notification,
**afin de** contacter le client sans copier-coller son adresse.

**Priorite** : P1 | **SP** : 1

**Criteres d'acceptation** :
- [ ] Le header `Reply-To` de l'email contient l'adresse email du demandeur
- [ ] Le header `From` utilise le nom de la boutique et l'email WordPress natif
- [ ] Le Reply-To est sanitise via `sanitize_email()`

---

## EPIC 05 — Auto-repondeur client (Notification)

> Module : `class-litequote-email.php`
> Priorite : P2 — Premium

---

### LQ-E05-S01 — Activation de l'auto-repondeur

**En tant que** marchand,
**je veux** pouvoir activer ou desactiver l'envoi d'un email de confirmation au client,
**afin de** choisir si mes clients recoivent un accuse de reception.

**Priorite** : P2 | **SP** : 1

**Criteres d'acceptation** :
- [ ] Un toggle on/off dans les reglages (onglet Emails)
- [ ] Par defaut : desactive
- [ ] Si active, un email est envoye au client a chaque soumission validee
- [ ] L'auto-repondeur fonctionne independamment de l'email admin

---

### LQ-E05-S02 — Contenu de l'email client

**En tant que** client,
**je veux** recevoir un email de confirmation apres ma demande de devis,
**afin de** savoir que ma demande a bien ete prise en compte.

**Priorite** : P2 | **SP** : 3

**Criteres d'acceptation** :
- [ ] L'email contient : nom du client, nom du produit, lien vers le produit, nom de la boutique, date
- [ ] Le ton est professionnel et rassurant (ex. : "Nous avons bien recu votre demande...")
- [ ] Le content-type est `text/html`
- [ ] Toutes les variables sont echappees

---

### LQ-E05-S03 — Template email personnalisable

**En tant que** marchand,
**je veux** pouvoir personnaliser le contenu de l'email envoye au client,
**afin de** adapter le message a ma marque et mon ton de communication.

**Priorite** : P2 | **SP** : 5

**Criteres d'acceptation** :
- [ ] Un editeur HTML (textarea) dans les reglages (onglet Emails)
- [ ] Variables disponibles : `{client_name}`, `{product_name}`, `{product_url}`, `{shop_name}`, `{date}`
- [ ] Un template par defaut est fourni et restaurable en un clic
- [ ] Le contenu est sanitise via `wp_kses_post()` avant sauvegarde
- [ ] Un apercu du rendu final n'est pas requis mais les variables sont documentees dans l'interface

---

### LQ-E05-S04 — Email client avec logo de la boutique

**En tant que** marchand,
**je veux** que l'email client affiche le logo de ma boutique,
**afin de** renforcer l'image de marque dans la communication.

**Priorite** : P2 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Le template par defaut inclut un emplacement pour le logo en en-tete
- [ ] Le logo utilise est celui configure dans les Reglages WooCommerce (ou l'identite du site WP)
- [ ] Si aucun logo n'est configure, l'espace est simplement omis (pas d'image cassee)
- [ ] Le logo est affiche via une balise `<img>` avec l'URL absolue

---

## EPIC 06 — Securite & Anti-spam (Securite)

> Module : `class-litequote-security.php`
> Priorite : P1 (nonce) / P2 (honeypot)

---

### LQ-E06-S01 — Protection nonce CSRF

**En tant que** marchand,
**je veux** que chaque soumission de formulaire soit protegee par un nonce WordPress,
**afin de** prevenir les attaques CSRF et les soumissions frauduleuses.

**Priorite** : P1 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Un nonce est genere via `wp_create_nonce('litequote_nonce')` et inclus dans le formulaire en champ hidden
- [ ] Le handler AJAX verifie le nonce via `wp_verify_nonce()` avant tout traitement
- [ ] Si le nonce est invalide ou expire : rejet avec code HTTP 403 et message d'erreur
- [ ] Le nonce est renouvele a chaque chargement de page

---

### LQ-E06-S02 — Sanitisation des entrees serveur

**En tant que** marchand,
**je veux** que toutes les donnees soumises soient nettoyees cote serveur,
**afin de** proteger mon site contre les injections et le contenu malveillant.

**Priorite** : P1 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Nom : `sanitize_text_field()`
- [ ] Email : `sanitize_email()` + `is_email()` pour validation
- [ ] Telephone : `sanitize_text_field()` + regex de validation E.164 basique
- [ ] Message : `wp_kses_post()` (autorise le formatage basique, supprime les scripts)
- [ ] Product ID : `absint()`
- [ ] Toute donnee invalide entraine un rejet avec message d'erreur specifique

---

### LQ-E06-S03 — Honeypot anti-spam

**En tant que** marchand,
**je veux** un systeme anti-spam invisible qui bloque les robots sans gener les humains,
**afin de** ne pas recevoir de demandes de devis spam.

**Priorite** : P2 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Un champ input supplementaire est ajoute au formulaire
- [ ] Le champ est invisible pour les humains : `display:none` + `tabindex="-1"`
- [ ] Le `name` du champ est genere aleatoirement a chaque chargement de page (pas un nom predictible)
- [ ] Si le champ est rempli a la soumission → rejet silencieux (HTTP 200, pas de message d'erreur visible)
- [ ] Aucun email n'est envoye en cas de rejet honeypot
- [ ] Le champ n'a pas de label visible ni de `aria-label` (invisible pour les screenreaders aussi)

---

### LQ-E06-S04 — Log des tentatives bloquees

**En tant que** marchand,
**je veux** pouvoir consulter un log des soumissions bloquees par le honeypot,
**afin de** verifier que l'anti-spam fonctionne et ajuster si necessaire.

**Priorite** : P2 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Un toggle "Mode debug" dans les reglages avances active le logging
- [ ] Les tentatives bloquees sont loguees via `error_log()` avec : date, IP (anonymisee), champ honeypot rempli
- [ ] Le log n'est actif que si le mode debug est active (pas de log en production par defaut)
- [ ] Aucune donnee personnelle complete n'est loguee (IP tronquee, pas d'email)

---

### LQ-E06-S05 — Protection contre l'acces direct aux fichiers

**En tant que** developpeur,
**je veux** que chaque fichier PHP du plugin soit protege contre l'acces direct,
**afin de** prevenir toute execution de code hors du contexte WordPress.

**Priorite** : P1 | **SP** : 1

**Criteres d'acceptation** :
- [ ] Chaque fichier PHP du plugin commence par `if (!defined('ABSPATH')) exit;`
- [ ] Le repertoire du plugin contient un fichier `index.php` vide (ou avec juste le guard ABSPATH)
- [ ] Les sous-repertoires (`includes/`, `admin/`, `assets/`) contiennent aussi un `index.php` vide

---

## EPIC 07 — WhatsApp Business (WhatsApp)

> Module : `class-litequote-whatsapp.php`
> Priorite : P2 — Premium (differenciateur cle)

---

### LQ-E07-S01 — Configuration du numero WhatsApp

**En tant que** marchand,
**je veux** renseigner mon numero WhatsApp Business dans les reglages du plugin,
**afin de** recevoir les demandes de devis directement sur WhatsApp.

**Priorite** : P2 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Un champ texte dans l'onglet "WhatsApp" des reglages
- [ ] Placeholder : "+33612345678"
- [ ] Le numero est stocke au format international (avec indicatif pays)
- [ ] Validation : le numero ne contient que des chiffres et le prefixe "+"
- [ ] Le numero est sanitise et stocke via l'Options API

---

### LQ-E07-S02 — Mode d'affichage WhatsApp configurable

**En tant que** marchand,
**je veux** choisir comment le canal WhatsApp est propose aux visiteurs,
**afin de** adapter l'experience a ma strategie de communication.

**Priorite** : P2 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Un reglage propose 3 modes : "Formulaire uniquement" / "WhatsApp uniquement" / "Les deux"
- [ ] Mode "Formulaire uniquement" : comportement standard (modale)
- [ ] Mode "WhatsApp uniquement" : le clic sur le bouton ouvre directement WhatsApp (pas de modale)
- [ ] Mode "Les deux" : la modale affiche le formulaire + un bouton secondaire "Contacter via WhatsApp"
- [ ] La valeur par defaut est "Formulaire uniquement"
- [ ] Si aucun numero WhatsApp n'est configure, le mode "WhatsApp uniquement" est desactive avec un avertissement

---

### LQ-E07-S03 — Construction du lien WhatsApp

**En tant que** visiteur,
**je veux** etre redirige vers WhatsApp avec un message pre-rempli contenant les infos du produit,
**afin de** contacter le marchand en un clic sans avoir a tout retaper.

**Priorite** : P2 | **SP** : 3

**Criteres d'acceptation** :
- [ ] L'URL est construite au format : `https://wa.me/{numero}?text={message_encode}`
- [ ] Le message par defaut : "Bonjour ! Je suis interesse par le produit {nom} (Ref. {SKU}). Pourriez-vous me faire parvenir votre meilleur prix ? {URL}"
- [ ] Le message est encode en UTF-8 via `encodeURIComponent()` cote JS
- [ ] Si le produit est variable, la variation selectionnee est incluse dans le message
- [ ] Si le produit n'a pas de SKU, la mention "Ref." est omise
- [ ] Le lien ouvre un nouvel onglet (`target="_blank"` + `rel="noopener noreferrer"`)

---

### LQ-E07-S04 — Template du message WhatsApp personnalisable

**En tant que** marchand,
**je veux** pouvoir personnaliser le message WhatsApp pre-rempli,
**afin de** adapter le ton et le contenu a ma clientele.

**Priorite** : P2 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Un champ textarea dans les reglages (onglet WhatsApp)
- [ ] Variables disponibles : `{product_name}`, `{sku}`, `{product_url}`, `{variation}`
- [ ] Un message par defaut est fourni et restaurable
- [ ] Le message est sanitise via `sanitize_textarea_field()`

---

### LQ-E07-S05 — Compatibilite mobile et desktop WhatsApp

**En tant que** visiteur,
**je veux** que le lien WhatsApp fonctionne sur mobile et sur desktop,
**afin de** contacter le marchand quel que soit mon appareil.

**Priorite** : P2 | **SP** : 1

**Criteres d'acceptation** :
- [ ] Sur mobile : le lien ouvre l'application WhatsApp native (iOS/Android)
- [ ] Sur desktop : le lien ouvre WhatsApp Web dans le navigateur
- [ ] L'URL `https://wa.me/` est utilisee (schema officiel Meta, compatible partout)
- [ ] Aucune API WhatsApp privee n'est utilisee

---

## EPIC 08 — Mode catalogue global (Catalogue)

> Module : `class-litequote-core.php`
> Priorite : P2 — Premium (Tier Extended)

---

### LQ-E08-S01 — Activation du mode catalogue

**En tant que** marchand,
**je veux** pouvoir activer un mode catalogue global sur toute ma boutique,
**afin de** transformer mon site en vitrine sans possibilite d'achat direct.

**Priorite** : P2 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Un toggle on/off dans les reglages generaux : "Activer le mode catalogue pour toute la boutique"
- [ ] Quand active : TOUS les prix et boutons "Ajouter au panier" sont masques sur la boutique
- [ ] Le bouton LiteQuote remplace le bouton panier sur CHAQUE produit
- [ ] Le mode catalogue prend le dessus sur la meta individuelle `_litequote_enabled`
- [ ] La page panier et la page commande affichent un message ou redirigent vers la boutique

---

### LQ-E08-S02 — Exclusion de produits ou categories

**En tant que** marchand,
**je veux** pouvoir exclure certains produits ou categories du mode catalogue,
**afin de** conserver la vente directe sur une partie de mon catalogue.

**Priorite** : P2 | **SP** : 5

**Criteres d'acceptation** :
- [ ] Un champ multi-select dans les reglages permet de selectionner des categories a exclure
- [ ] Un champ texte permet de saisir des IDs de produits a exclure (separes par des virgules)
- [ ] Les produits/categories exclus conservent leur prix et leur bouton panier
- [ ] L'exclusion fonctionne sur les pages boutique, categorie et fiches produit
- [ ] Les exclusions sont stockees dans les options WP

---

### LQ-E08-S03 — Compatibilite avec tous les types de produits

**En tant que** marchand,
**je veux** que le mode catalogue fonctionne avec tous les types de produits WooCommerce,
**afin de** couvrir l'ensemble de mon catalogue.

**Priorite** : P2 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Fonctionne avec les produits simples
- [ ] Fonctionne avec les produits variables (selecteur de variation visible, bouton panier masque)
- [ ] Fonctionne avec les produits groupes
- [ ] Fonctionne avec les produits externes/affilies (le bouton externe est remplace)
- [ ] Aucun type de produit ne provoque d'erreur PHP

---

### LQ-E08-S04 — Masquage des elements panier en mode catalogue

**En tant que** visiteur,
**je veux** que le panier soit coherent avec le mode catalogue,
**afin de** ne pas voir d'elements confus (panier vide, mini-cart inutile).

**Priorite** : P2 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Le widget mini-cart est masque quand le mode catalogue est actif et aucune exclusion n'est definie
- [ ] Si des exclusions existent (produits achetables), le mini-cart reste visible
- [ ] La page panier affiche un message : "Cette boutique fonctionne sur devis. Utilisez le bouton de devis sur chaque produit."
- [ ] Le message est translatable

---

### LQ-E08-S05 — Indication visuelle du mode catalogue en admin

**En tant que** marchand,
**je veux** voir clairement quand le mode catalogue est actif,
**afin de** ne pas oublier que ma boutique est en mode vitrine.

**Priorite** : P2 | **SP** : 1

**Criteres d'acceptation** :
- [ ] Un avis admin (notice) est affiche en haut des pages WooCommerce admin quand le mode catalogue est actif
- [ ] Le message : "LiteQuote : le mode catalogue est actif. Tous les prix et boutons d'achat sont masques."
- [ ] Le notice est de type "info" (bleu) et fermable (dismissible)
- [ ] Le notice n'apparait que pour les utilisateurs ayant la capacite `manage_woocommerce`

---

## EPIC 09 — Generation PDF (PDF)

> Module : `class-litequote-pdf.php` + `includes/lib/fpdf/`
> Priorite : P3 — Extended

---

### LQ-E09-S01 — Generation du PDF a la soumission

**En tant que** marchand,
**je veux** qu'un PDF recapitulatif de chaque demande de devis soit genere automatiquement,
**afin de** disposer d'un document formel archivable pour chaque demande.

**Priorite** : P3 | **SP** : 8

**Criteres d'acceptation** :
- [ ] Un toggle "Activer la generation PDF" dans les reglages (onglet PDF)
- [ ] Le PDF est genere via la bibliotheque FPDF 1.86 (incluse dans le plugin, pas de Composer)
- [ ] Le PDF est genere a chaque soumission validee (apres verification nonce + sanitisation)
- [ ] La generation PDF ne bloque pas l'envoi de l'email (si FPDF echoue, l'email part quand meme)
- [ ] Le PDF est genere en memoire (pas de fichier temporaire sur disque sauf archivage)

---

### LQ-E09-S02 — Contenu et structure du PDF

**En tant que** marchand,
**je veux** que le PDF contienne toutes les informations utiles de la demande,
**afin de** avoir un document complet et professionnel.

**Priorite** : P3 | **SP** : 5

**Criteres d'acceptation** :
- [ ] En-tete : logo de la boutique (si configure) + nom de la boutique + date
- [ ] Numero de demande auto-incremente : format "LQ-{ANNEE}-{ID}" (ex. : LQ-2026-0042)
- [ ] Section client : nom, email, telephone
- [ ] Section produit : nom du produit, SKU, variation, URL
- [ ] Section message : message integral du demandeur
- [ ] Pied de page : "Document genere automatiquement par LiteQuote" + date/heure
- [ ] Encodage UTF-8 (support des caracteres accentues)

---

### LQ-E09-S03 — Logo de la boutique dans le PDF

**En tant que** marchand,
**je veux** pouvoir ajouter mon logo dans les PDFs generes,
**afin de** personnaliser les documents a l'image de ma marque.

**Priorite** : P3 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Un champ upload media dans les reglages (onglet PDF) permet de selectionner un logo
- [ ] Le logo est affiche en haut a gauche du PDF
- [ ] Formats acceptes : PNG, JPG
- [ ] Si aucun logo n'est configure, l'espace est laisse vide (pas d'erreur)
- [ ] Le logo est redimensionne automatiquement (max 150px de large)

---

### LQ-E09-S04 — Piece jointe PDF dans les emails

**En tant que** marchand,
**je veux** que le PDF soit joint aux emails de notification (admin et client),
**afin de** avoir le recapitulatif directement dans ma boite mail.

**Priorite** : P3 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Le PDF est joint a l'email admin comme piece jointe
- [ ] Le PDF est joint a l'email auto-repondeur client (si active)
- [ ] Le fichier temporaire est supprime apres envoi (sauf si archivage active)
- [ ] Le nom du fichier PDF est : "LQ-{ANNEE}-{ID}.pdf"
- [ ] La piece jointe est transmise via le parametre `$attachments` de `wp_mail()`

---

### LQ-E09-S05 — Archivage et purge des PDFs

**En tant que** marchand,
**je veux** pouvoir archiver les PDFs sur mon serveur et definir une duree de conservation,
**afin de** garder un historique tout en gerant l'espace disque.

**Priorite** : P3 | **SP** : 5

**Criteres d'acceptation** :
- [ ] Un toggle "Archivage local" dans les reglages (onglet PDF)
- [ ] Les PDFs archives sont stockes dans `wp-content/uploads/litequote-quotes/`
- [ ] Nommage : `LQ-{ANNEE}-{ID}.pdf`
- [ ] Un `.htaccess` avec `deny from all` protege le repertoire contre l'acces direct
- [ ] Un champ "Duree de conservation" (en jours) dans les reglages
- [ ] Une tache WP-Cron programmee supprime les PDFs plus anciens que la duree configuree
- [ ] Par defaut : archivage desactive, duree de conservation 90 jours

---

## EPIC 10 — Interface d'administration (Admin)

> Module : `admin/class-litequote-admin.php` + `includes/class-litequote-settings.php`
> Priorite : P1/P2

---

### LQ-E10-S01 — Page de reglages dans le menu WooCommerce

**En tant que** marchand,
**je veux** acceder aux reglages LiteQuote depuis le menu WooCommerce,
**afin de** configurer le plugin depuis un emplacement logique.

**Priorite** : P1 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Un sous-menu "LiteQuote" apparait sous "WooCommerce" dans le menu admin
- [ ] L'acces est reserve aux utilisateurs ayant la capacite `manage_woocommerce`
- [ ] La page utilise l'API Settings de WordPress (`register_setting`, `add_settings_section`, `add_settings_field`)
- [ ] La page est accessible a l'URL : `admin.php?page=litequote-settings`

---

### LQ-E10-S02 — Organisation en onglets

**En tant que** marchand,
**je veux** que les reglages soient organises en onglets clairs,
**afin de** trouver facilement le parametre que je cherche.

**Priorite** : P1 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Onglet "General" : mode de declenchement, mode catalogue
- [ ] Onglet "Bouton" : texte, couleurs, label prix, position
- [ ] Onglet "Emails" : destinataire admin, auto-repondeur, template
- [ ] Onglet "WhatsApp" : numero, mode d'affichage, template message
- [ ] Onglet "PDF" (Extended) : activation, logo, archivage, purge
- [ ] Onglet "Avance" : mode debug, CSS personnalise
- [ ] La navigation entre onglets est sans rechargement de page (JS) ou avec rechargement minimal
- [ ] L'onglet actif est memorise (query string ou localStorage)

---

### LQ-E10-S03 — Onglet General

**En tant que** marchand,
**je veux** configurer le mode de declenchement du plugin,
**afin de** definir quels produits proposent un devis.

**Priorite** : P1 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Select "Mode de declenchement" : "Prix a 0 EUR" / "Checkbox par produit" / "Les deux"
- [ ] Toggle "Mode catalogue global" : activer/desactiver
- [ ] Si mode catalogue actif : champs d'exclusion visibles (categories + IDs produits)
- [ ] Select "Chargement des scripts" : "Automatique (pages WooCommerce seulement)" (unique option MVP)
- [ ] Bouton "Enregistrer les modifications"
- [ ] Message de confirmation apres sauvegarde

---

### LQ-E10-S04 — Onglet Bouton

**En tant que** marchand,
**je veux** configurer l'apparence du bouton de devis,
**afin de** l'adapter a ma charte graphique.

**Priorite** : P1 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Champ texte "Texte du bouton" (defaut : "Demander un devis")
- [ ] Color picker "Couleur de fond" (defaut : #0073aa)
- [ ] Color picker "Couleur du texte" (defaut : #ffffff)
- [ ] Champ texte "Label prix" (defaut : "Prix sur demande")
- [ ] Select "Position du bouton" : "Avant le formulaire" / "Apres le formulaire"

---

### LQ-E10-S05 — Onglet Emails

**En tant que** marchand,
**je veux** configurer les parametres d'email du plugin,
**afin de** personnaliser les notifications.

**Priorite** : P1 (admin) / P2 (auto-repondeur) | **SP** : 3

**Criteres d'acceptation** :
- [ ] Champ email "Destinataire admin" (defaut : email admin WP)
- [ ] Toggle "Auto-repondeur client" : activer/desactiver
- [ ] Si auto-repondeur actif : editeur HTML (textarea) du template avec variables documentees
- [ ] Bouton "Restaurer le template par defaut"
- [ ] Liste des variables affichee sous l'editeur : `{client_name}`, `{product_name}`, `{product_url}`, `{shop_name}`, `{date}`

---

### LQ-E10-S06 — Onglet WhatsApp

**En tant que** marchand,
**je veux** configurer le canal WhatsApp du plugin,
**afin de** recevoir des demandes de devis sur WhatsApp.

**Priorite** : P2 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Champ texte "Numero WhatsApp Business" (placeholder : +33612345678)
- [ ] Select "Mode d'affichage" : "Formulaire uniquement" / "WhatsApp uniquement" / "Les deux"
- [ ] Textarea "Template du message" avec variables documentees
- [ ] Bouton "Restaurer le message par defaut"
- [ ] Si aucun numero n'est saisi, le mode "WhatsApp uniquement" est grise avec un message explicatif

---

### LQ-E10-S07 — Onglet PDF (Extended)

**En tant que** marchand (licence Extended),
**je veux** configurer la generation de PDF,
**afin de** personnaliser les documents generes.

**Priorite** : P3 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Toggle "Activer la generation PDF"
- [ ] Champ upload media "Logo de la boutique"
- [ ] Toggle "Archivage local des PDFs"
- [ ] Champ numerique "Duree de conservation (jours)" (defaut : 90)
- [ ] Si le module PDF n'est pas disponible (licence Pro), l'onglet affiche un message d'upsell

---

### LQ-E10-S08 — Onglet Avance

**En tant que** marchand ou developpeur,
**je veux** acceder a des reglages avances,
**afin de** debugger ou personnaliser finement le plugin.

**Priorite** : P2 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Toggle "Mode debug" (log des soumissions bloquees)
- [ ] Textarea "CSS personnalise" pour surcharges CSS avancees
- [ ] Le CSS personnalise est injecte dans le `<head>` des pages WooCommerce via `wp_add_inline_style()`
- [ ] Le CSS est sanitise (pas de `<script>`, pas de `expression()`)
- [ ] Un message d'avertissement est affiche : "Modifiez le CSS uniquement si vous savez ce que vous faites."

---

## EPIC 11 — Performance & Chargement (Technique)

> Priorite : P1 — MVP
> Transversal

---

### LQ-E11-S01 — Chargement conditionnel des assets

**En tant que** visiteur,
**je veux** que les scripts du plugin ne soient charges que sur les pages ou ils sont necessaires,
**afin de** ne pas ralentir les autres pages du site.

**Priorite** : P1 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Les fichiers JS et CSS sont enregistres via `wp_enqueue_scripts` avec une condition
- [ ] Condition de chargement : `is_product()` OU `is_shop()` OU `is_product_category()`
- [ ] Sur une page standard WordPress (article, page, accueil non-boutique) : aucun script LiteQuote dans le source HTML
- [ ] Verification : inspecter le code source d'une page non-WooCommerce → aucune reference a `litequote`
- [ ] Le JS est charge avec l'attribut `defer`

---

### LQ-E11-S02 — Budget de poids des assets

**En tant que** marchand,
**je veux** que le plugin respecte un budget de poids strict,
**afin de** ne pas impacter les performances de ma boutique.

**Priorite** : P1 | **SP** : 2

**Criteres d'acceptation** :
- [ ] `litequote-modal.js` : < 15 Ko (non minifie)
- [ ] `litequote.css` : < 8 Ko (non minifie)
- [ ] Total JS + CSS : < 25 Ko minifies
- [ ] Poids total du plugin installe : < 150 Ko (hors repertoire FPDF)
- [ ] Aucun fichier de font, d'image ou de bibliotheque externe inclus

---

### LQ-E11-S03 — Zero requete BDD supplementaire hors declenchement

**En tant que** visiteur,
**je veux** que le plugin n'ajoute aucune requete BDD sur les pages ou il n'est pas actif,
**afin de** ne pas degrader le temps de chargement global.

**Priorite** : P1 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Sur une page non-WooCommerce : 0 requete BDD liee a LiteQuote
- [ ] Sur une page produit sans mode devis actif : 0 requete BDD supplementaire (la meta `_litequote_enabled` est deja chargee par WooCommerce)
- [ ] Les options du plugin sont chargees avec autoload = 'yes' (lecture depuis le cache, pas de requete supplementaire)
- [ ] Verification via le plugin Query Monitor

---

### LQ-E11-S04 — Absence d'impact sur le score PageSpeed

**En tant que** marchand,
**je veux** que l'activation du plugin n'impacte pas mon score Google PageSpeed,
**afin de** conserver mon referencement et mes performances.

**Priorite** : P1 | **SP** : 1

**Criteres d'acceptation** :
- [ ] Le JS LiteQuote ne bloque pas le rendu (attribut `defer`)
- [ ] Aucune requete HTTP externe n'est ajoutee par le plugin
- [ ] Le CSS LiteQuote ne provoque pas de layout shift (CLS = 0 impact)
- [ ] Test avant/apres activation : ecart LCP < 50ms

---

## EPIC 12 — Compatibilite & Internationalisation (Technique)

> Priorite : P1 (compat) / P2 (i18n avance)
> Transversal

---

### LQ-E12-S01 — Compatibilite themes majeurs

**En tant que** marchand,
**je veux** que le plugin fonctionne correctement avec les themes WooCommerce les plus populaires,
**afin de** ne pas avoir de problemes d'affichage.

**Priorite** : P1 | **SP** : 5

**Criteres d'acceptation** :
- [ ] Teste et fonctionnel sur Storefront (theme officiel WooCommerce)
- [ ] Teste et fonctionnel sur Astra
- [ ] Teste et fonctionnel sur Divi
- [ ] Teste et fonctionnel sur Flatsome
- [ ] Teste et fonctionnel sur GeneratePress
- [ ] Le bouton de devis s'affiche au bon endroit sur chaque theme
- [ ] La modale s'affiche correctement par-dessus le contenu de chaque theme
- [ ] Aucun conflit CSS visible (z-index, overflow, position)

---

### LQ-E12-S02 — Compatibilite page builders

**En tant que** marchand utilisant Elementor ou WPBakery,
**je veux** que le plugin fonctionne avec les widgets produit de mon page builder,
**afin de** ne pas perdre la fonctionnalite devis sur mes pages personnalisees.

**Priorite** : P2 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Compatibilite Elementor : le widget produit WooCommerce affiche le bouton LiteQuote
- [ ] Compatibilite WPBakery : le shortcode produit affiche le bouton LiteQuote
- [ ] Les hooks WooCommerce utilises par LiteQuote sont bien executes dans le contexte des builders
- [ ] Aucun conflit JS entre LiteQuote et les scripts des builders

---

### LQ-E12-S03 — Support RTL

**En tant que** marchand avec une boutique en arabe ou en hebreu,
**je veux** que le plugin s'affiche correctement en mode RTL,
**afin de** offrir une experience coherente a mes visiteurs.

**Priorite** : P2 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Le CSS utilise des proprietes logiques (`margin-inline-start` au lieu de `margin-left`, etc.)
- [ ] La modale est correctement mirrorisee en RTL
- [ ] Le bouton de fermeture "x" est en haut a gauche en RTL
- [ ] Les champs de formulaire sont alignes a droite en RTL
- [ ] Test : activer un theme RTL et verifier l'affichage complet

---

### LQ-E12-S04 — Internationalisation complete (i18n)

**En tant que** marchand,
**je veux** que tous les textes du plugin soient traduisibles,
**afin de** proposer le plugin dans n'importe quelle langue.

**Priorite** : P1 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Tous les textes visibles (front + admin) utilisent `__()` ou `_e()` avec le text domain `litequote`
- [ ] Le text domain est declare dans le header du fichier principal (`Text Domain: litequote`)
- [ ] Le `Domain Path` est declare : `Domain Path: /languages`
- [ ] Un fichier `.pot` est genere dans `/languages/litequote.pot`
- [ ] Les textes pluriels utilisent `_n()`
- [ ] Le plugin est compatible WPML et Polylang (les strings sont detectees automatiquement)

---

### LQ-E12-S05 — Compatibilite PHP multi-versions

**En tant que** marchand,
**je veux** que le plugin fonctionne sur PHP 8.0 a 8.3,
**afin de** ne pas etre bloque par la version PHP de mon hebergeur.

**Priorite** : P1 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Le code PHP utilise la syntaxe compatible PHP 8.0 minimum (pas de features 8.1+ exclusives pour le core)
- [ ] Aucun `match` expression, aucune `enum`, aucun `readonly` property (PHP 8.1+) dans le code principal
- [ ] Test sans erreur ni deprecation notice sur PHP 8.0, 8.1, 8.2, 8.3
- [ ] Le `Requires PHP` est declare a `8.0` dans le header du plugin

---

## EPIC 13 — Installation & Desinstallation (Technique)

> Priorite : P1 — MVP

---

### LQ-E13-S01 — Activation du plugin

**En tant que** marchand,
**je veux** que l'activation du plugin soit instantanee et sans configuration prealable,
**afin de** pouvoir l'utiliser immediatement apres installation.

**Priorite** : P1 | **SP** : 2

**Criteres d'acceptation** :
- [ ] L'activation enregistre les options par defaut dans la base WP (si elles n'existent pas deja)
- [ ] Aucune table custom n'est creee (le plugin utilise uniquement l'Options API et les post meta)
- [ ] Un hook d'activation `register_activation_hook()` initialise les valeurs par defaut
- [ ] Le plugin verifie la version minimale de WordPress (6.0) et WooCommerce (8.0) a l'activation
- [ ] Si les prerequis ne sont pas remplis : afficher un admin notice et desactiver le plugin proprement
- [ ] Si WooCommerce n'est pas actif : afficher un message clair et ne pas activer

---

### LQ-E13-S02 — Desactivation du plugin

**En tant que** marchand,
**je veux** que la desactivation du plugin restaure le comportement normal de ma boutique,
**afin de** ne pas avoir de residus visuels ou fonctionnels.

**Priorite** : P1 | **SP** : 1

**Criteres d'acceptation** :
- [ ] Les hooks WooCommerce sont automatiquement detaches a la desactivation
- [ ] Tous les prix et boutons panier sont restaures immediatement
- [ ] Les options et meta sont conservees (pour une reactivation future)
- [ ] Aucun script ou style LiteQuote n'est charge apres desactivation

---

### LQ-E13-S03 — Desinstallation complete du plugin

**En tant que** marchand,
**je veux** que la desinstallation supprime toutes les donnees du plugin,
**afin de** ne pas laisser de donnees orphelines dans ma base de donnees.

**Priorite** : P1 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Le fichier `uninstall.php` est execute a la suppression du plugin
- [ ] Toutes les options `litequote_*` sont supprimees de la table `wp_options`
- [ ] Toutes les post meta `_litequote_*` sont supprimees de `wp_postmeta`
- [ ] Le repertoire d'archivage PDF `wp-content/uploads/litequote-quotes/` est supprime (si existant)
- [ ] Les taches WP-Cron planifiees par le plugin sont desinscrites
- [ ] Le fichier `uninstall.php` verifie `defined('WP_UNINSTALL_PLUGIN')` avant execution
- [ ] Aucune erreur PHP n'est generee si les donnees n'existent deja plus

---

## Recapitulatif par priorite

### P1 — MVP (37 stories)

| Epic | Stories | SP total estime |
|---|---|---|
| E01 — Core Detection | 6 | 17 |
| E02 — Bouton | 3 (hors S04) | 7 |
| E03 — Modale Form | 7 | 24 |
| E04 — Email Admin | 4 | 8 |
| E06 — Securite (nonce) | 3 (S01, S02, S05) | 5 |
| E10 — Admin | 4 (S01-S04) | 10 |
| E11 — Performance | 4 | 8 |
| E12 — Compat (base) | 3 (S01, S04, S05) | 10 |
| E13 — Install/Uninstall | 3 | 6 |
| **Total P1** | **37** | **95 SP** |

### P2 — Premium (22 stories)

| Epic | Stories | SP total estime |
|---|---|---|
| E02 — Bouton loop | 1 (S04) | 3 |
| E05 — Auto-repondeur | 4 | 11 |
| E06 — Honeypot | 2 (S03, S04) | 5 |
| E07 — WhatsApp | 5 | 11 |
| E08 — Catalogue | 5 | 14 |
| E10 — Admin (WA, avance) | 3 (S05 partiel, S06, S08) | 7 |
| E12 — Compat (RTL, builders) | 2 (S02, S03) | 5 |
| **Total P2** | **22** | **56 SP** |

### P3 — Extended (6 stories)

| Epic | Stories | SP total estime |
|---|---|---|
| E09 — PDF | 5 | 23 |
| E10 — Admin PDF | 1 (S07) | 2 |
| **Total P3** | **6** | **25 SP** |

---

### Total general

| Priorite | Stories | SP |
|---|---|---|
| P1 — MVP | 37 | 95 |
| P2 — Premium | 22 | 56 |
| P3 — Extended | 6 | 25 |
| **Total** | **65** | **176 SP** |

---

*Fin du document — LiteQuote User Stories v1.0*
*Avril 2026 — Denis — AmazScript / ByteSproutLab*
