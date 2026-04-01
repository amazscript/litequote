# Sprint 4 — Admin Interface reglages

**Duree** : 1 semaine
**Objectif** : Page d'administration complete avec onglets — le marchand peut tout configurer sans toucher au code
**SP total** : 22
**Version** : v1.0-rc (release candidate MVP)
**Depend de** : Sprint 0, Sprint 1, Sprint 3

---

## Stories

### 1. LQ-E10-S01 — Page de reglages dans le menu WooCommerce
**SP** : 3 | **Priorite** : P1

En tant que marchand, je veux acceder aux reglages LiteQuote depuis le menu WooCommerce, afin de configurer le plugin depuis un emplacement logique.

**Criteres d'acceptation** :
- [ ] Un sous-menu "LiteQuote" apparait sous "WooCommerce" dans le menu admin
- [ ] L'acces est reserve aux utilisateurs ayant la capacite `manage_woocommerce`
- [ ] La page utilise l'API Settings de WordPress (`register_setting`, `add_settings_section`, `add_settings_field`)
- [ ] La page est accessible a l'URL : `admin.php?page=litequote-settings`

---

### 2. LQ-E10-S02 — Organisation en onglets
**SP** : 3 | **Priorite** : P1

En tant que marchand, je veux que les reglages soient organises en onglets clairs, afin de trouver facilement le parametre que je cherche.

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

### 3. LQ-E10-S03 — Onglet General
**SP** : 2 | **Priorite** : P1

En tant que marchand, je veux configurer le mode de declenchement du plugin, afin de definir quels produits proposent un devis.

**Criteres d'acceptation** :
- [ ] Select "Mode de declenchement" : "Prix a 0 EUR" / "Checkbox par produit" / "Les deux"
- [ ] Toggle "Mode catalogue global" : activer/desactiver
- [ ] Si mode catalogue actif : champs d'exclusion visibles (categories + IDs produits)
- [ ] Select "Chargement des scripts" : "Automatique (pages WooCommerce seulement)"
- [ ] Bouton "Enregistrer les modifications"
- [ ] Message de confirmation apres sauvegarde

---

### 4. LQ-E10-S04 — Onglet Bouton
**SP** : 2 | **Priorite** : P1

En tant que marchand, je veux configurer l'apparence du bouton de devis, afin de l'adapter a ma charte graphique.

**Criteres d'acceptation** :
- [ ] Champ texte "Texte du bouton" (defaut : "Demander un devis")
- [ ] Color picker "Couleur de fond" (defaut : #0073aa)
- [ ] Color picker "Couleur du texte" (defaut : #ffffff)
- [ ] Champ texte "Label prix" (defaut : "Prix sur demande")
- [ ] Select "Position du bouton" : "Avant le formulaire" / "Apres le formulaire"

---

### 5. LQ-E10-S05 — Onglet Emails
**SP** : 3 | **Priorite** : P1 (admin) / P2 (auto-repondeur)

En tant que marchand, je veux configurer les parametres d'email du plugin, afin de personnaliser les notifications.

**Criteres d'acceptation** :
- [ ] Champ email "Destinataire admin" (defaut : email admin WP)
- [ ] Toggle "Auto-repondeur client" : activer/desactiver
- [ ] Si auto-repondeur actif : editeur HTML (textarea) du template avec variables documentees
- [ ] Bouton "Restaurer le template par defaut"
- [ ] Liste des variables affichee sous l'editeur : `{client_name}`, `{product_name}`, `{product_url}`, `{shop_name}`, `{date}`

---

### 6. LQ-E02-S03 — Position configurable du bouton
**SP** : 2 | **Priorite** : P1

En tant que marchand, je veux choisir ou placer le bouton de devis sur la fiche produit, afin de l'integrer au mieux dans le design de mon theme.

**Criteres d'acceptation** :
- [ ] Un reglage permet de choisir entre "Avant le formulaire d'ajout au panier" et "Apres le formulaire d'ajout au panier"
- [ ] Le hook utilise change en consequence (`woocommerce_before_add_to_cart_form` ou `woocommerce_after_add_to_cart_form`)
- [ ] La valeur par defaut est "Apres le formulaire"
- [ ] Le changement de position est effectif sans purge de cache

---

### 7. LQ-E11-S02 — Budget de poids des assets
**SP** : 2 | **Priorite** : P1

En tant que marchand, je veux que le plugin respecte un budget de poids strict, afin de ne pas impacter les performances de ma boutique.

**Criteres d'acceptation** :
- [ ] `litequote-modal.js` : < 15 Ko (non minifie)
- [ ] `litequote.css` : < 8 Ko (non minifie)
- [ ] Total JS + CSS : < 25 Ko minifies
- [ ] Poids total du plugin installe : < 150 Ko (hors repertoire FPDF)
- [ ] Aucun fichier de font, d'image ou de bibliotheque externe inclus

---

## Livrable Sprint 4

A la fin de ce sprint :
- Le marchand peut configurer TOUT le plugin depuis WooCommerce > LiteQuote
- Les onglets General, Bouton, Emails sont fonctionnels
- Les onglets WhatsApp, PDF, Avance sont presents mais inactifs (fonctionnalites Sprint 5-7)
- Le budget de poids est respecte
- **→ RELEASE v1.0 MVP possible** (Core + Form + Email + Nonce + Admin)

**Bloque** : Sprint 5 (les reglages WhatsApp/Email sont en place)
