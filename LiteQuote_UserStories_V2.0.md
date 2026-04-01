# LiteQuote for WooCommerce — User Stories v2.0

**Reference** : LITEQUOTE-WOO-2025
**Version** : v2.0
**Base** : CDC v1.2 + Roadmap v2.0
**Date** : Avril 2026
**Auteur** : Denis — AmazScript / ByteSproutLab

---

## Contexte v2.0

La v1.5 permet aux clients de **demander** un devis. Le marchand recoit un email et repond manuellement.

La v2.0 transforme LiteQuote en un **vrai outil de gestion de devis** :
- Le marchand voit toutes les demandes dans un dashboard admin
- Il tape un prix, clique "Envoyer le devis" → PDF pro genere et envoye automatiquement
- Il peut suivre le statut de chaque devis (en attente, envoye, accepte, refuse)
- Il peut exporter les demandes en CSV

---

## Nouveaux Epics v2.0

| Epic | Nom | Module | Stories |
|---|---|---|---|
| E14 | Stockage des demandes (CPT) | Quote CPT | 5 |
| E15 | Dashboard admin des demandes | Quote Admin | 6 |
| E16 | Formulaire de reponse (devis avec prix) | Quote Reply | 5 |
| E17 | PDF devis pro (avec prix) | Quote PDF | 4 |
| E18 | Export CSV | Export | 2 |
| E19 | Statistiques | Stats | 3 |
| **Total v2.0** | | | **25** |

---

## EPIC 14 — Stockage des demandes (Custom Post Type)

> Module : `includes/class-litequote-quote-cpt.php`
> Priorite : P4 — v2.0

---

### LQ-E14-S01 — Creation du Custom Post Type litequote_quote

**En tant que** developpeur,
**je veux** que chaque demande de devis soit stockee en base comme un Custom Post Type,
**afin de** pouvoir les lister, filtrer et gerer depuis l'admin WordPress.

**Priorite** : P4 | **SP** : 5

**Criteres d'acceptation** :
- [ ] Un CPT `litequote_quote` est enregistre via `register_post_type()`
- [ ] Le CPT n'est pas public (pas de page front-end)
- [ ] Le CPT supporte `title` et `custom-fields`
- [ ] Le titre auto-genere est la reference : "LQ-2026-0001"
- [ ] Les meta du CPT stockent : name, company, email, phone, quantity, message, product_id, product_name, sku, variation, status
- [ ] Le CPT est visible uniquement pour les users ayant `manage_woocommerce`
- [ ] Les anciens devis (pre-v2.0) ne sont pas concernes (email only)

---

### LQ-E14-S02 — Sauvegarde automatique des demandes

**En tant que** marchand,
**je veux** que chaque nouvelle demande de devis soit automatiquement sauvegardee en base,
**afin de** ne plus dependre uniquement de l'email pour retrouver mes demandes.

**Priorite** : P4 | **SP** : 3

**Criteres d'acceptation** :
- [ ] A chaque soumission validee, un post `litequote_quote` est cree automatiquement
- [ ] Le post a le statut `lq-pending` (en attente)
- [ ] Toutes les donnees du formulaire sont stockees en post meta
- [ ] La reference LQ-YYYY-NNNN est auto-incrementee
- [ ] L'email admin continue d'etre envoye en parallele
- [ ] Le PDF (si active) est lie au post en post meta (`_litequote_pdf_path`)

---

### LQ-E14-S03 — Statuts personnalises des demandes

**En tant que** marchand,
**je veux** suivre l'etat de chaque demande de devis,
**afin de** savoir lesquelles sont en attente, envoyees, acceptees ou refusees.

**Priorite** : P4 | **SP** : 3

**Criteres d'acceptation** :
- [ ] 4 statuts personnalises enregistres via `register_post_status()` :
  - `lq-pending` — En attente (nouveau)
  - `lq-quoted` — Devis envoye
  - `lq-accepted` — Accepte
  - `lq-rejected` — Refuse
- [ ] Le statut par defaut est `lq-pending`
- [ ] Le statut est modifiable depuis le dashboard admin
- [ ] Un code couleur visuel distingue chaque statut dans la liste

---

### LQ-E14-S04 — Meta box produit lie

**En tant que** marchand,
**je veux** voir les informations du produit lie directement dans la demande de devis,
**afin de** avoir tout le contexte sans naviguer ailleurs.

**Priorite** : P4 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Une meta box affiche le nom du produit, le SKU, la variation et un lien vers la fiche produit
- [ ] Si le produit a une image, elle est affichee en miniature
- [ ] Le lien ouvre la fiche produit admin dans un nouvel onglet

---

### LQ-E14-S05 — Meta box client

**En tant que** marchand,
**je veux** voir les coordonnees du client directement dans la demande de devis,
**afin de** pouvoir le contacter rapidement.

**Priorite** : P4 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Une meta box affiche : nom, entreprise, email (lien mailto), telephone (lien tel)
- [ ] Un bouton "Repondre par email" ouvre un mailto pre-rempli
- [ ] Un bouton "Contacter sur WhatsApp" (si numero configure) ouvre wa.me

---

## EPIC 15 — Dashboard admin des demandes

> Module : `admin/class-litequote-quotes-list.php`
> Priorite : P4 — v2.0

---

### LQ-E15-S01 — Page de liste des demandes

**En tant que** marchand,
**je veux** voir la liste de toutes les demandes de devis dans l'admin WordPress,
**afin de** avoir une vue d'ensemble et gerer mes devis.

**Priorite** : P4 | **SP** : 5

**Criteres d'acceptation** :
- [ ] Un sous-menu "Quotes" (Devis) apparait sous WooCommerce
- [ ] La page affiche un tableau avec colonnes : Reference, Client, Produit, Date, Statut, Actions
- [ ] Le tableau utilise `WP_List_Table` pour la pagination et le tri
- [ ] Le tri par defaut est par date decroissante (plus recentes en premier)
- [ ] Chaque ligne a un lien "Voir" et "Repondre"

---

### LQ-E15-S02 — Filtrage par statut

**En tant que** marchand,
**je veux** filtrer les demandes par statut (en attente, envoye, accepte, refuse),
**afin de** me concentrer sur les demandes qui necessitent une action.

**Priorite** : P4 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Des onglets de filtre en haut du tableau : Toutes | En attente (X) | Devis envoye (X) | Accepte (X) | Refuse (X)
- [ ] Le nombre entre parentheses indique le compte pour chaque statut
- [ ] Le filtre "En attente" est selectionne par defaut
- [ ] Le filtre fonctionne sans rechargement complet (query string)

---

### LQ-E15-S03 — Recherche de demandes

**En tant que** marchand,
**je veux** rechercher une demande par nom de client, email ou reference,
**afin de** retrouver rapidement une demande specifique.

**Priorite** : P4 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Un champ de recherche en haut du tableau
- [ ] La recherche porte sur : reference (LQ-XXXX), nom du client, email, nom du produit
- [ ] Les resultats s'affichent dans le meme tableau avec pagination

---

### LQ-E15-S04 — Actions en masse

**En tant que** marchand,
**je veux** changer le statut de plusieurs demandes en une seule action,
**afin de** gerer mes devis plus efficacement.

**Priorite** : P4 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Des checkboxes sur chaque ligne du tableau
- [ ] Un menu deroulant "Actions groupees" : Marquer comme accepte, Marquer comme refuse, Supprimer
- [ ] Confirmation avant suppression
- [ ] Le compteur de statuts se met a jour apres l'action

---

### LQ-E15-S05 — Compteur de demandes en attente dans le menu

**En tant que** marchand,
**je veux** voir le nombre de demandes en attente directement dans le menu WordPress,
**afin de** savoir en un coup d'oeil si j'ai des devis a traiter.

**Priorite** : P4 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Un badge rouge avec le nombre de demandes `lq-pending` apparait a cote du menu "Quotes"
- [ ] Le badge disparait quand il n'y a plus de demandes en attente
- [ ] Le badge se met a jour a chaque chargement de page admin

---

### LQ-E15-S06 — Page detail d'une demande

**En tant que** marchand,
**je veux** voir le detail complet d'une demande sur une page dediee,
**afin de** avoir tout le contexte avant de repondre.

**Priorite** : P4 | **SP** : 3

**Criteres d'acceptation** :
- [ ] La page affiche : reference, date, statut, meta box client, meta box produit, message
- [ ] Le PDF de la demande (si genere) est telechargeable
- [ ] Un historique des actions est visible (date de creation, date d'envoi du devis, etc.)
- [ ] Le bouton "Repondre avec un devis" est bien visible en haut

---

## EPIC 16 — Formulaire de reponse (devis avec prix)

> Module : `admin/class-litequote-quote-reply.php`
> Priorite : P4 — v2.0

---

### LQ-E16-S01 — Formulaire de reponse au devis

**En tant que** marchand,
**je veux** un formulaire simple pour repondre a une demande de devis avec un prix,
**afin de** generer et envoyer un devis professionnel en quelques clics.

**Priorite** : P4 | **SP** : 5

**Criteres d'acceptation** :
- [ ] Le formulaire apparait sur la page detail de la demande (meta box ou section)
- [ ] Champs du formulaire :
  - Prix unitaire (champ numerique, obligatoire)
  - Quantite (pre-remplie avec la quantite demandee par le client)
  - Remise (%, optionnel)
  - Total (calcule automatiquement : prix × quantite - remise)
  - Conditions / notes (textarea, optionnel)
  - Validite du devis (nombre de jours, defaut 30)
- [ ] Le calcul du total est en temps reel (JavaScript)
- [ ] Le formulaire est pre-rempli si le marchand a deja repondu (modification possible)

---

### LQ-E16-S02 — Sauvegarde du devis en brouillon

**En tant que** marchand,
**je veux** sauvegarder un devis en brouillon sans l'envoyer,
**afin de** pouvoir y revenir plus tard avant de l'envoyer au client.

**Priorite** : P4 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Un bouton "Sauvegarder le brouillon" enregistre les donnees sans envoyer
- [ ] Les donnees du devis sont stockees en post meta sur le CPT `litequote_quote`
- [ ] Le statut reste `lq-pending`
- [ ] Le formulaire est pre-rempli au rechargement

---

### LQ-E16-S03 — Envoi du devis en un clic

**En tant que** marchand,
**je veux** envoyer le devis au client en un seul clic,
**afin de** gagner du temps et offrir une experience professionnelle.

**Priorite** : P4 | **SP** : 5

**Criteres d'acceptation** :
- [ ] Un bouton "Generer & Envoyer le devis" effectue en une seule action :
  1. Sauvegarde les donnees du devis
  2. Genere le PDF devis pro (avec prix)
  3. Envoie l'email au client avec le PDF en piece jointe
  4. Met a jour le statut en `lq-quoted`
- [ ] Un message de confirmation s'affiche : "Devis LQ-2026-0042 envoye a client@email.com"
- [ ] L'historique de la demande est mis a jour avec la date d'envoi
- [ ] Le marchand peut re-envoyer le devis (si modification)

---

### LQ-E16-S04 — Email de devis au client

**En tant que** client,
**je veux** recevoir un email professionnel avec le devis du marchand,
**afin de** pouvoir le consulter, l'accepter ou le refuser.

**Priorite** : P4 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Objet : "[Nom boutique] Votre devis — LQ-2026-0042"
- [ ] Corps HTML structure : header boutique, recapitulatif produit, tableau de prix (unitaire, quantite, remise, total), conditions, validite
- [ ] Le PDF devis est joint en piece jointe
- [ ] Le ton est professionnel et personnalisable via le template email
- [ ] Le Reply-To est l'email du marchand

---

### LQ-E16-S05 — Devise et format de prix

**En tant que** marchand,
**je veux** que le devis utilise la devise et le format de prix configures dans WooCommerce,
**afin de** avoir un devis coherent avec ma boutique.

**Priorite** : P4 | **SP** : 2

**Criteres d'acceptation** :
- [ ] La devise utilisee est celle de WooCommerce (`get_woocommerce_currency()`)
- [ ] Le format du prix respecte les reglages WooCommerce (separateur decimal, position du symbole)
- [ ] Le symbole de devise est affiche dans le formulaire admin et dans le PDF

---

## EPIC 17 — PDF devis pro (avec prix)

> Module : `includes/class-litequote-pdf.php` (extension)
> Priorite : P4 — v2.0

---

### LQ-E17-S01 — PDF devis avec tableau de prix

**En tant que** marchand,
**je veux** que le PDF de devis contienne un tableau de prix professionnel,
**afin de** envoyer un document formel et credible.

**Priorite** : P4 | **SP** : 8

**Criteres d'acceptation** :
- [ ] Le PDF contient :
  - Header : logo + nom de la boutique + coordonnees
  - Reference du devis + date d'emission
  - Section client : nom, entreprise, email, telephone
  - Tableau de prix : description produit, prix unitaire, quantite, remise, total HT
  - Sous-total, remise, total TTC
  - Conditions et notes du marchand
  - Validite du devis ("Ce devis est valable X jours")
  - Pied de page : mentions legales, "Genere par LiteQuote"
- [ ] Le PDF est format A4, professionnel, avec le logo du marchand
- [ ] Les prix sont formates selon les reglages WooCommerce

---

### LQ-E17-S02 — Numero de devis unique

**En tant que** marchand,
**je veux** que chaque devis envoye ait un numero unique et sequentiel,
**afin de** suivre mes devis et respecter les obligations comptables.

**Priorite** : P4 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Le numero de devis suit le format : `LQ-YYYY-NNNN` (ex: LQ-2026-0042)
- [ ] Le compteur est auto-incremente et ne se reinitialise pas
- [ ] Le numero est affiche dans le PDF, l'email et le dashboard admin
- [ ] Deux devis ne peuvent jamais avoir le meme numero

---

### LQ-E17-S03 — Mentions legales configurables

**En tant que** marchand,
**je veux** pouvoir ajouter mes mentions legales dans le PDF de devis,
**afin de** respecter les obligations legales de mon pays.

**Priorite** : P4 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Un champ textarea dans les reglages pour les mentions legales
- [ ] Les mentions sont affichees en pied de page du PDF
- [ ] Valeur par defaut : vide (pas de mentions)
- [ ] Exemples fournis dans la description : "TVA non applicable, art. 293 B du CGI" etc.

---

### LQ-E17-S04 — Coordonnees du marchand dans le PDF

**En tant que** marchand,
**je veux** que mes coordonnees apparaissent dans le header du devis,
**afin de** que le client sache qui emet le devis.

**Priorite** : P4 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Les coordonnees sont lues depuis les reglages WooCommerce (adresse de la boutique)
- [ ] Affichage : nom de la boutique, adresse, email, telephone
- [ ] Si un logo est configure (onglet PDF), il est affiche a cote des coordonnees
- [ ] Fallback : si pas d'adresse WooCommerce, affiche juste le nom du site

---

## EPIC 18 — Export CSV

> Module : `admin/class-litequote-export.php`
> Priorite : P4 — v2.0

---

### LQ-E18-S01 — Export CSV des demandes

**En tant que** marchand,
**je veux** exporter la liste de mes demandes de devis en CSV,
**afin de** analyser mes donnees dans Excel ou les importer dans un CRM.

**Priorite** : P4 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Un bouton "Exporter en CSV" en haut du dashboard des demandes
- [ ] Le CSV contient : reference, date, statut, nom, entreprise, email, telephone, produit, SKU, quantite, message, prix (si devis envoye), total
- [ ] L'export respecte les filtres actifs (statut, recherche)
- [ ] Le fichier est nomme : `litequote-quotes-YYYY-MM-DD.csv`
- [ ] Encodage UTF-8 avec BOM pour compatibilite Excel

---

### LQ-E18-S02 — Export PDF individuel depuis le dashboard

**En tant que** marchand,
**je veux** telecharger le PDF d'un devis directement depuis le dashboard,
**afin de** l'imprimer ou l'archiver localement.

**Priorite** : P4 | **SP** : 2

**Criteres d'acceptation** :
- [ ] Un bouton "Telecharger PDF" sur chaque ligne du tableau et sur la page detail
- [ ] Si le PDF n'existe pas encore, il est genere a la volee
- [ ] Le fichier telecharge est nomme : `LQ-YYYY-NNNN.pdf`

---

## EPIC 19 — Statistiques

> Module : `admin/class-litequote-stats.php`
> Priorite : P4 — v2.0

---

### LQ-E19-S01 — Widget dashboard WordPress

**En tant que** marchand,
**je veux** voir un resume de mes devis sur le dashboard WordPress,
**afin de** avoir une vue rapide sans naviguer dans les menus.

**Priorite** : P4 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Un widget sur le dashboard WP affiche : nombre de demandes ce mois, en attente, devis envoyes, taux de conversion
- [ ] Les chiffres sont cliquables et menent vers le dashboard des demandes filtre
- [ ] Le widget est visible uniquement pour les users avec `manage_woocommerce`

---

### LQ-E19-S02 — Page statistiques

**En tant que** marchand,
**je veux** voir des statistiques detaillees de mes demandes de devis,
**afin de** comprendre mes performances et optimiser mon processus.

**Priorite** : P4 | **SP** : 5

**Criteres d'acceptation** :
- [ ] Une page "Statistiques" dans le menu LiteQuote
- [ ] Metriques affichees :
  - Nombre total de demandes (ce mois, ce trimestre, cette annee)
  - Nombre de devis envoyes
  - Taux de conversion (devis envoyes → acceptes)
  - Produits les plus demandes (top 5)
  - Temps moyen de reponse
- [ ] Graphique simple (barres ou lignes) pour l'evolution mensuelle
- [ ] Pas de librairie JS externe — graphiques en CSS ou canvas natif

---

### LQ-E19-S03 — Notification de demandes non traitees

**En tant que** marchand,
**je veux** recevoir un rappel quand des demandes sont en attente depuis trop longtemps,
**afin de** ne pas oublier de repondre a mes clients.

**Priorite** : P4 | **SP** : 3

**Criteres d'acceptation** :
- [ ] Un email de rappel est envoye au marchand si des demandes sont en `lq-pending` depuis plus de 48h
- [ ] Le rappel est envoye une seule fois par demande
- [ ] Le rappel est desactivable dans les reglages
- [ ] L'email contient la liste des demandes en attente avec liens directs vers le dashboard

---

## Recapitulatif v2.0

| Epic | Stories | SP total |
|---|---|---|
| E14 — CPT Quotes | 5 | 15 |
| E15 — Dashboard admin | 6 | 18 |
| E16 — Formulaire devis avec prix | 5 | 17 |
| E17 — PDF devis pro | 4 | 14 |
| E18 — Export CSV | 2 | 5 |
| E19 — Statistiques | 3 | 11 |
| **Total v2.0** | **25** | **80 SP** |

---

## Total general (v1.0 → v2.0)

| Version | Stories | SP |
|---|---|---|
| v1.0-1.5 (fait) | 65 + bonus | ~192 |
| v2.0 (a faire) | 25 | 80 |
| **Total** | **90** | **~272 SP** |

---

*Fin du document — LiteQuote User Stories v2.0*
*Avril 2026 — Denis — AmazScript / ByteSproutLab*
