# Sprint 3 — Notification & Securite

**Duree** : 1 semaine
**Objectif** : Handler AJAX serveur, envoi email admin, nonce CSRF, sanitisation — la boucle complete fonctionne
**SP total** : 20
**Version** : v0.3
**Depend de** : Sprint 0, Sprint 1, Sprint 2

---

## Stories

### 1. LQ-E06-S01 — Protection nonce CSRF
**SP** : 2 | **Priorite** : P1

En tant que marchand, je veux que chaque soumission de formulaire soit protegee par un nonce WordPress, afin de prevenir les attaques CSRF et les soumissions frauduleuses.

**Criteres d'acceptation** :
- [ ] Un nonce est genere via `wp_create_nonce('litequote_nonce')` et inclus dans le formulaire en champ hidden
- [ ] Le handler AJAX verifie le nonce via `wp_verify_nonce()` avant tout traitement
- [ ] Si le nonce est invalide ou expire : rejet avec code HTTP 403 et message d'erreur
- [ ] Le nonce est renouvele a chaque chargement de page

---

### 2. LQ-E06-S02 — Sanitisation des entrees serveur
**SP** : 2 | **Priorite** : P1

En tant que marchand, je veux que toutes les donnees soumises soient nettoyees cote serveur, afin de proteger mon site contre les injections et le contenu malveillant.

**Criteres d'acceptation** :
- [ ] Nom : `sanitize_text_field()`
- [ ] Email : `sanitize_email()` + `is_email()` pour validation
- [ ] Telephone : `sanitize_text_field()` + regex de validation E.164 basique
- [ ] Message : `wp_kses_post()` (autorise le formatage basique, supprime les scripts)
- [ ] Product ID : `absint()`
- [ ] Toute donnee invalide entraine un rejet avec message d'erreur specifique

---

### 3. LQ-E04-S01 — Envoi de l'email admin a la soumission
**SP** : 3 | **Priorite** : P1

En tant que marchand, je veux recevoir un email immediatement quand un visiteur soumet une demande de devis, afin de pouvoir y repondre rapidement.

**Criteres d'acceptation** :
- [ ] Un email est envoye via `wp_mail()` a chaque soumission validee
- [ ] L'adresse destinataire est celle configuree dans les reglages (defaut : `get_bloginfo('admin_email')`)
- [ ] L'envoi est declenche cote serveur apres validation du nonce et des donnees
- [ ] L'email est envoye meme si l'auto-repondeur client est desactive
- [ ] En cas d'echec d'envoi, l'erreur est loguee (pas de message d'erreur au visiteur)

---

### 4. LQ-E04-S02 — Objet et contenu de l'email admin
**SP** : 3 | **Priorite** : P1

En tant que marchand, je veux que l'email de notification soit clair et structure, afin de comprendre en un coup d'oeil la demande du client.

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

### 5. LQ-E04-S03 — Adresse destinataire configurable
**SP** : 1 | **Priorite** : P1

En tant que marchand, je veux pouvoir configurer l'adresse email de reception des devis, afin de recevoir les demandes sur l'adresse de mon choix.

**Criteres d'acceptation** :
- [ ] Un champ email dans les reglages du plugin (onglet Emails)
- [ ] Valeur par defaut : `get_bloginfo('admin_email')`
- [ ] Le champ est valide avec `sanitize_email()`
- [ ] Si le champ est vide, l'email admin WP est utilise en fallback

---

### 6. LQ-E04-S04 — Header Reply-To dynamique
**SP** : 1 | **Priorite** : P1

En tant que marchand, je veux pouvoir repondre directement a l'email de notification, afin de contacter le client sans copier-coller son adresse.

**Criteres d'acceptation** :
- [ ] Le header `Reply-To` de l'email contient l'adresse email du demandeur
- [ ] Le header `From` utilise le nom de la boutique et l'email WordPress natif
- [ ] Le Reply-To est sanitise via `sanitize_email()`

---

### 7. LQ-E01-S05 — Gestion des produits variables
**SP** : 5 | **Priorite** : P1

En tant que visiteur, je veux que le mode devis fonctionne aussi sur les produits variables, afin de pouvoir demander un devis en precisant ma variation.

**Criteres d'acceptation** :
- [ ] Le selecteur de variations reste visible meme si le produit est en mode devis
- [ ] Le visiteur peut selectionner ses attributs (taille, couleur, etc.) avant de cliquer sur le bouton devis
- [ ] La combinaison d'attributs selectionnee est capturee dynamiquement (ex. : "Couleur : Rouge / Taille : L")
- [ ] Si aucune variation n'est selectionnee, le formulaire s'ouvre quand meme (variation optionnelle)
- [ ] Les variations dont le prix est a 0 EUR sont detectees individuellement si le mode "prix a 0" est actif

---

### 8. LQ-E11-S03 — Zero requete BDD supplementaire hors declenchement
**SP** : 2 | **Priorite** : P1

En tant que visiteur, je veux que le plugin n'ajoute aucune requete BDD sur les pages ou il n'est pas actif, afin de ne pas degrader le temps de chargement global.

**Criteres d'acceptation** :
- [ ] Sur une page non-WooCommerce : 0 requete BDD liee a LiteQuote
- [ ] Sur une page produit sans mode devis actif : 0 requete BDD supplementaire
- [ ] Les options du plugin sont chargees avec autoload = 'yes'
- [ ] Verification via le plugin Query Monitor

---

## Livrable Sprint 3

A la fin de ce sprint, **la boucle complete fonctionne** :
1. Visiteur clique sur "Demander un devis"
2. Modale s'ouvre avec formulaire pre-rempli
3. Visiteur remplit et soumet
4. Nonce verifie, donnees sanitisees
5. Email HTML envoye au marchand
6. Message de succes affiche au visiteur

C'est le **premier MVP fonctionnel** — le plugin est utilisable de bout en bout.

**Bloque** : Sprint 4 (admin), Sprint 5 (WhatsApp/auto-repondeur)
