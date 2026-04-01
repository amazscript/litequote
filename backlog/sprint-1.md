# Sprint 1 — Core Detection & Bouton

**Duree** : 1 semaine
**Objectif** : Le coeur du plugin — detecter les produits "devis", masquer prix/panier, afficher le bouton LiteQuote
**SP total** : 22
**Version** : v0.1
**Depend de** : Sprint 0

---

## Stories

### 1. LQ-E01-S01 — Declenchement par case a cocher produit
**SP** : 3 | **Priorite** : P1

En tant que marchand, je veux pouvoir cocher une option "Prix sur demande" dans la fiche produit admin, afin de choisir individuellement quels produits proposent un devis au lieu d'un achat.

**Criteres d'acceptation** :
- [ ] Une meta box ou un champ checkbox apparait dans l'onglet "General" de la fiche produit WooCommerce admin
- [ ] Le champ est labellise "Prix sur demande" (translatable)
- [ ] La valeur est stockee en post meta : `_litequote_enabled` (yes/no)
- [ ] Le champ est visible pour les produits simples et variables
- [ ] La valeur par defaut est "no" (non coche)
- [ ] La meta est sauvegardee via le hook `woocommerce_process_product_meta`

---

### 2. LQ-E01-S02 — Declenchement automatique par prix a zero
**SP** : 2 | **Priorite** : P1

En tant que marchand, je veux que les produits dont le prix est a 0 EUR activent automatiquement le mode devis, afin de ne pas avoir a cocher manuellement chaque produit sans prix.

**Criteres d'acceptation** :
- [ ] Si le prix du produit est vide ou egal a 0, le mode devis s'active automatiquement
- [ ] Ce comportement est activable/desactivable dans les reglages generaux du plugin
- [ ] Le mode "prix a 0" et le mode "checkbox" peuvent fonctionner simultanement (option "Les deux" dans les reglages)
- [ ] Un produit variable dont toutes les variations sont a 0 EUR est aussi detecte

---

### 3. LQ-E01-S03 — Masquage du prix affiche
**SP** : 2 | **Priorite** : P1

En tant que visiteur, je veux voir un label personnalise ("Prix sur demande") a la place du prix, afin de comprendre que le tarif est disponible sur devis.

**Criteres d'acceptation** :
- [ ] Le prix natif WooCommerce est masque via le filtre `woocommerce_get_price_html`
- [ ] Un label configurable est affiche a la place (defaut : "Prix sur demande")
- [ ] Le label est translatable via `__()` avec le text domain `litequote`
- [ ] Le masquage fonctionne sur la fiche produit, la page boutique et les pages de categorie
- [ ] Le label respecte le style du theme actif (pas de style force)
- [ ] Le prix reste visible dans l'admin WooCommerce (back-office)

---

### 4. LQ-E01-S04 — Masquage du bouton Ajouter au panier
**SP** : 3 | **Priorite** : P1

En tant que visiteur, je veux que le bouton "Ajouter au panier" soit masque pour les produits en mode devis, afin de ne pas pouvoir acheter un produit dont le prix est sur demande.

**Criteres d'acceptation** :
- [ ] Le filtre `woocommerce_is_purchasable` retourne `false` pour les produits en mode devis
- [ ] Le bouton natif "Ajouter au panier" disparait de la fiche produit
- [ ] Le bouton disparait aussi des pages boutique et categorie (boutons loop)
- [ ] Le champ quantite est egalement masque
- [ ] Les produits en mode devis ne sont pas ajoutables au panier via URL directe (`?add-to-cart=ID`)
- [ ] Les autres produits de la boutique ne sont pas affectes

---

### 5. LQ-E01-S06 — Coexistence avec les produits normaux
**SP** : 2 | **Priorite** : P1

En tant que marchand, je veux que seuls les produits marques "devis" soient affectes par le plugin, afin de conserver le fonctionnement normal de ma boutique pour les autres produits.

**Criteres d'acceptation** :
- [ ] Un produit sans la meta `_litequote_enabled` et avec un prix > 0 conserve son prix et son bouton panier
- [ ] Les pages panier, commande et mon-compte ne sont pas impactees
- [ ] Les produits groupes et externes non marques restent inchanges
- [ ] Le plugin ne modifie aucune table WooCommerce native
- [ ] Aucun conflit avec le panier existant (un client peut avoir des produits normaux en panier tout en demandant un devis)

---

### 6. LQ-E02-S01 — Affichage du bouton de devis
**SP** : 3 | **Priorite** : P1

En tant que visiteur, je veux voir un bouton "Demander un devis" sur la fiche d'un produit en mode devis, afin de pouvoir initier ma demande de prix.

**Criteres d'acceptation** :
- [ ] Un bouton apparait a la place du bouton "Ajouter au panier" sur les produits en mode devis
- [ ] Le texte par defaut est "Demander un devis" (configurable dans les reglages)
- [ ] Le bouton est rendu via un hook WooCommerce configurable (avant ou apres le formulaire d'ajout)
- [ ] Le bouton contient un attribut `data-product-id` avec l'ID du produit
- [ ] Le bouton contient un attribut `data-product-name` avec le nom du produit
- [ ] Le bouton est de type `button` (pas un lien, pas un submit)

---

### 7. LQ-E02-S02 — Personnalisation visuelle du bouton
**SP** : 2 | **Priorite** : P1

En tant que marchand, je veux pouvoir modifier la couleur et le texte du bouton de devis, afin de l'adapter a la charte graphique de ma boutique.

**Criteres d'acceptation** :
- [ ] Le texte du bouton est modifiable via un champ texte dans les reglages
- [ ] La couleur de fond est modifiable via un champ hexadecimal (color picker)
- [ ] La couleur du texte est modifiable via un champ hexadecimal (color picker)
- [ ] Les couleurs sont appliquees via des variables CSS inline (`--litequote-btn-bg`, `--litequote-btn-color`)
- [ ] Un apercu en temps reel n'est pas requis mais les changements sont visibles apres sauvegarde
- [ ] Les valeurs par defaut sont harmonieuses (ex. : fond bleu #0073aa, texte blanc #ffffff)

---

## Livrable Sprint 1

A la fin de ce sprint, sur un produit marque "Prix sur demande" :
- Le prix est masque et remplace par un label configurable
- Le bouton panier est masque
- Un bouton "Demander un devis" personnalisable est affiche
- Les autres produits ne sont pas affectes
- Le plugin ne charge rien sur les pages non-WooCommerce

**Bloque** : Sprint 2 (la modale a besoin du bouton pour s'ouvrir)
