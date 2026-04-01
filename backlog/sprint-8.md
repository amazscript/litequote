# Sprint 8 — Compatibilite & Polish final

**Duree** : 1 semaine
**Objectif** : Tests themes/builders, RTL, PHP multi-versions, debug log, email logo, performance finale — pret pour la vente
**SP total** : 20
**Version** : v1.5-final
**Depend de** : Tous les sprints precedents

---

## Stories

### 1. LQ-E12-S01 — Compatibilite themes majeurs
**SP** : 5 | **Priorite** : P1

En tant que marchand, je veux que le plugin fonctionne correctement avec les themes WooCommerce les plus populaires, afin de ne pas avoir de problemes d'affichage.

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

### 2. LQ-E12-S02 — Compatibilite page builders
**SP** : 3 | **Priorite** : P2

En tant que marchand utilisant Elementor ou WPBakery, je veux que le plugin fonctionne avec les widgets produit de mon page builder, afin de ne pas perdre la fonctionnalite devis.

**Criteres d'acceptation** :
- [ ] Compatibilite Elementor : le widget produit WooCommerce affiche le bouton LiteQuote
- [ ] Compatibilite WPBakery : le shortcode produit affiche le bouton LiteQuote
- [ ] Les hooks WooCommerce utilises par LiteQuote sont bien executes dans le contexte des builders
- [ ] Aucun conflit JS entre LiteQuote et les scripts des builders

---

### 3. LQ-E12-S03 — Support RTL
**SP** : 2 | **Priorite** : P2

En tant que marchand avec une boutique en arabe ou en hebreu, je veux que le plugin s'affiche correctement en mode RTL, afin d'offrir une experience coherente.

**Criteres d'acceptation** :
- [ ] Le CSS utilise des proprietes logiques (`margin-inline-start` au lieu de `margin-left`, etc.)
- [ ] La modale est correctement mirrorisee en RTL
- [ ] Le bouton de fermeture "x" est en haut a gauche en RTL
- [ ] Les champs de formulaire sont alignes a droite en RTL
- [ ] Test : activer un theme RTL et verifier l'affichage complet

---

### 4. LQ-E12-S05 — Compatibilite PHP multi-versions
**SP** : 2 | **Priorite** : P1

En tant que marchand, je veux que le plugin fonctionne sur PHP 8.0 a 8.3, afin de ne pas etre bloque par la version PHP de mon hebergeur.

**Criteres d'acceptation** :
- [ ] Le code PHP utilise la syntaxe compatible PHP 8.0 minimum
- [ ] Aucun `match` expression, aucune `enum`, aucun `readonly` property dans le code principal
- [ ] Test sans erreur ni deprecation notice sur PHP 8.0, 8.1, 8.2, 8.3
- [ ] Le `Requires PHP` est declare a `8.0` dans le header du plugin

---

### 5. LQ-E11-S04 — Absence d'impact sur le score PageSpeed
**SP** : 1 | **Priorite** : P1

En tant que marchand, je veux que l'activation du plugin n'impacte pas mon score Google PageSpeed, afin de conserver mon referencement.

**Criteres d'acceptation** :
- [ ] Le JS LiteQuote ne bloque pas le rendu (attribut `defer`)
- [ ] Aucune requete HTTP externe n'est ajoutee par le plugin
- [ ] Le CSS LiteQuote ne provoque pas de layout shift (CLS = 0 impact)
- [ ] Test avant/apres activation : ecart LCP < 50ms

---

### 6. LQ-E06-S04 — Log des tentatives bloquees
**SP** : 2 | **Priorite** : P2

En tant que marchand, je veux pouvoir consulter un log des soumissions bloquees par le honeypot, afin de verifier que l'anti-spam fonctionne.

**Criteres d'acceptation** :
- [ ] Un toggle "Mode debug" dans les reglages avances active le logging
- [ ] Les tentatives bloquees sont loguees via `error_log()` avec : date, IP (anonymisee), champ honeypot rempli
- [ ] Le log n'est actif que si le mode debug est active
- [ ] Aucune donnee personnelle complete n'est loguee (IP tronquee, pas d'email)

---

### 7. LQ-E05-S04 — Email client avec logo de la boutique
**SP** : 2 | **Priorite** : P2

En tant que marchand, je veux que l'email client affiche le logo de ma boutique, afin de renforcer l'image de marque.

**Criteres d'acceptation** :
- [ ] Le template par defaut inclut un emplacement pour le logo en en-tete
- [ ] Le logo utilise est celui configure dans les Reglages WooCommerce (ou l'identite du site WP)
- [ ] Si aucun logo n'est configure, l'espace est simplement omis
- [ ] Le logo est affiche via une balise `<img>` avec l'URL absolue

---

### 8. Generation du fichier .pot de traduction
**SP** : 1 | **Priorite** : P1

Tache technique : generer le fichier POT final avec toutes les chaines traduisibles.

**Criteres d'acceptation** :
- [ ] Le fichier `languages/litequote.pot` est genere via WP-CLI ou un outil equivalent
- [ ] Toutes les chaines `__()`, `_e()`, `_n()`, `esc_html__()`, `esc_attr__()` sont extraites
- [ ] Le fichier est valide et importable dans Poedit/Loco Translate
- [ ] Le header du fichier POT contient les bonnes metadonnees (nom du plugin, version, auteur)

---

## Checklist de release finale

Avant la mise en vente, verifier :

- [ ] **T01** : Produit "Prix sur demande" → prix masque, bouton LiteQuote visible
- [ ] **T02** : Clic bouton → modale s'ouvre, focus premier champ
- [ ] **T03** : Soumission valide → email admin recu, succes affiche
- [ ] **T04** : Email invalide → erreur front, pas d'envoi
- [ ] **T05** : Honeypot rempli → rejet silencieux
- [ ] **T06** : Produit variable → variation dans le message
- [ ] **T07** : Mode catalogue → tous prix/boutons masques
- [ ] **T08** : WhatsApp → ouvre WA avec message pre-rempli
- [ ] **T09** : Page non-WooCommerce → 0 script charge
- [ ] **T10** : Poids JS+CSS < 25 Ko minifies
- [ ] **T11** : Mobile 375px → modale plein ecran, lisible
- [ ] **T12** : Desinstallation → toutes options supprimees

---

## Livrable Sprint 8

A la fin de ce sprint :
- Le plugin est teste sur 5 themes + 2 builders
- Le support RTL et PHP 8.0-8.3 est valide
- Le mode debug est fonctionnel
- Le fichier .pot est genere
- Les 12 criteres de recette sont valides
- **→ RELEASE v1.5-final — PRET POUR LA VENTE**

---

## Livrables finaux par tier

### Tier 1 — Basic Snippet (9 EUR)
- [ ] `snippet-litequote-basic.php`
- [ ] `README.txt`

### Tier 2 — Pro Plugin (24 EUR)
- [ ] `litequote-pro.zip`
- [ ] `documentation-litequote-pro.pdf` (avec section SMTP en premier)
- [ ] `changelog.txt`
- [ ] `license.txt`

### Tier 3 — Master Extended (49 EUR)
- [ ] Tout le Tier 2
- [ ] Module PDF active
- [ ] `template-email-admin.html`
- [ ] `template-email-client.html`
- [ ] Licence multi-sites
