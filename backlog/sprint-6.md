# Sprint 6 — Mode Catalogue & Avance

**Duree** : 1 semaine
**Objectif** : Mode catalogue global (vitrine), honeypot anti-spam, CSS custom, bouton loop, onglet avance
**SP total** : 23
**Version** : v1.2
**Depend de** : Sprint 1, Sprint 4

---

## Stories

### 1. LQ-E08-S01 — Activation du mode catalogue
**SP** : 3 | **Priorite** : P2

En tant que marchand, je veux pouvoir activer un mode catalogue global sur toute ma boutique, afin de transformer mon site en vitrine sans possibilite d'achat direct.

**Criteres d'acceptation** :
- [ ] Un toggle on/off dans les reglages generaux : "Activer le mode catalogue pour toute la boutique"
- [ ] Quand active : TOUS les prix et boutons "Ajouter au panier" sont masques sur la boutique
- [ ] Le bouton LiteQuote remplace le bouton panier sur CHAQUE produit
- [ ] Le mode catalogue prend le dessus sur la meta individuelle `_litequote_enabled`
- [ ] La page panier et la page commande affichent un message ou redirigent vers la boutique

---

### 2. LQ-E08-S02 — Exclusion de produits ou categories
**SP** : 5 | **Priorite** : P2

En tant que marchand, je veux pouvoir exclure certains produits ou categories du mode catalogue, afin de conserver la vente directe sur une partie de mon catalogue.

**Criteres d'acceptation** :
- [ ] Un champ multi-select dans les reglages permet de selectionner des categories a exclure
- [ ] Un champ texte permet de saisir des IDs de produits a exclure (separes par des virgules)
- [ ] Les produits/categories exclus conservent leur prix et leur bouton panier
- [ ] L'exclusion fonctionne sur les pages boutique, categorie et fiches produit
- [ ] Les exclusions sont stockees dans les options WP

---

### 3. LQ-E08-S03 — Compatibilite avec tous les types de produits
**SP** : 3 | **Priorite** : P2

En tant que marchand, je veux que le mode catalogue fonctionne avec tous les types de produits WooCommerce, afin de couvrir l'ensemble de mon catalogue.

**Criteres d'acceptation** :
- [ ] Fonctionne avec les produits simples
- [ ] Fonctionne avec les produits variables (selecteur de variation visible, bouton panier masque)
- [ ] Fonctionne avec les produits groupes
- [ ] Fonctionne avec les produits externes/affilies (le bouton externe est remplace)
- [ ] Aucun type de produit ne provoque d'erreur PHP

---

### 4. LQ-E08-S04 — Masquage des elements panier en mode catalogue
**SP** : 2 | **Priorite** : P2

En tant que visiteur, je veux que le panier soit coherent avec le mode catalogue, afin de ne pas voir d'elements confus.

**Criteres d'acceptation** :
- [ ] Le widget mini-cart est masque quand le mode catalogue est actif et aucune exclusion n'est definie
- [ ] Si des exclusions existent (produits achetables), le mini-cart reste visible
- [ ] La page panier affiche un message : "Cette boutique fonctionne sur devis."
- [ ] Le message est translatable

---

### 5. LQ-E08-S05 — Indication visuelle du mode catalogue en admin
**SP** : 1 | **Priorite** : P2

En tant que marchand, je veux voir clairement quand le mode catalogue est actif, afin de ne pas oublier que ma boutique est en mode vitrine.

**Criteres d'acceptation** :
- [ ] Un avis admin (notice) est affiche en haut des pages WooCommerce admin
- [ ] Le message : "LiteQuote : le mode catalogue est actif. Tous les prix et boutons d'achat sont masques."
- [ ] Le notice est de type "info" (bleu) et fermable (dismissible)
- [ ] Le notice n'apparait que pour les utilisateurs ayant la capacite `manage_woocommerce`

---

### 6. LQ-E06-S03 — Honeypot anti-spam
**SP** : 3 | **Priorite** : P2

En tant que marchand, je veux un systeme anti-spam invisible qui bloque les robots sans gener les humains, afin de ne pas recevoir de demandes de devis spam.

**Criteres d'acceptation** :
- [ ] Un champ input supplementaire est ajoute au formulaire
- [ ] Le champ est invisible pour les humains : `display:none` + `tabindex="-1"`
- [ ] Le `name` du champ est genere aleatoirement a chaque chargement de page
- [ ] Si le champ est rempli a la soumission → rejet silencieux (HTTP 200, pas de message d'erreur visible)
- [ ] Aucun email n'est envoye en cas de rejet honeypot
- [ ] Le champ n'a pas de label visible ni de `aria-label`

---

### 7. LQ-E02-S04 — Bouton sur les pages boutique et categorie (loop)
**SP** : 3 | **Priorite** : P2

En tant que visiteur, je veux voir le bouton de devis aussi sur les pages liste (boutique, categorie), afin de pouvoir identifier visuellement les produits en mode devis.

**Criteres d'acceptation** :
- [ ] Les produits en mode devis affichent le bouton LiteQuote dans la boucle produit (loop)
- [ ] Le bouton remplace le bouton "Ajouter au panier" natif dans la loop
- [ ] Le clic sur le bouton en loop redirige vers la fiche produit (pas d'ouverture modale depuis la loop)
- [ ] Le style du bouton en loop est coherent avec celui de la fiche produit

---

### 8. LQ-E10-S08 — Onglet Avance
**SP** : 2 | **Priorite** : P2

En tant que marchand ou developpeur, je veux acceder a des reglages avances, afin de debugger ou personnaliser finement le plugin.

**Criteres d'acceptation** :
- [ ] Toggle "Mode debug" (log des soumissions bloquees)
- [ ] Textarea "CSS personnalise" pour surcharges CSS avancees
- [ ] Le CSS personnalise est injecte dans le `<head>` via `wp_add_inline_style()`
- [ ] Le CSS est sanitise (pas de `<script>`, pas de `expression()`)
- [ ] Un message d'avertissement : "Modifiez le CSS uniquement si vous savez ce que vous faites."

---

## Livrable Sprint 6

A la fin de ce sprint :
- Le mode catalogue transforme toute la boutique en vitrine
- Les exclusions par categorie/produit fonctionnent
- Le honeypot bloque les bots sans CAPTCHA
- Le bouton LiteQuote apparait dans les listings produit
- L'onglet Avance est fonctionnel
- **→ RELEASE v1.2** (MVP + WhatsApp + Auto-repondeur + Catalogue + Honeypot)
