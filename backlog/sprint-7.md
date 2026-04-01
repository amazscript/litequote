# Sprint 7 — Generation PDF (Extended)

**Duree** : 1 semaine
**Objectif** : Module PDF complet — generation, contenu, logo, pieces jointes email, archivage et purge WP-Cron
**SP total** : 25
**Version** : v1.5
**Depend de** : Sprint 3 (email), Sprint 4 (admin)

---

## Stories

### 1. LQ-E09-S01 — Generation du PDF a la soumission
**SP** : 8 | **Priorite** : P3

En tant que marchand, je veux qu'un PDF recapitulatif de chaque demande de devis soit genere automatiquement, afin de disposer d'un document formel archivable.

**Criteres d'acceptation** :
- [ ] Un toggle "Activer la generation PDF" dans les reglages (onglet PDF)
- [ ] Le PDF est genere via la bibliotheque FPDF 1.86 (incluse dans le plugin, pas de Composer)
- [ ] Le PDF est genere a chaque soumission validee (apres verification nonce + sanitisation)
- [ ] La generation PDF ne bloque pas l'envoi de l'email (si FPDF echoue, l'email part quand meme)
- [ ] Le PDF est genere en memoire (pas de fichier temporaire sur disque sauf archivage)

---

### 2. LQ-E09-S02 — Contenu et structure du PDF
**SP** : 5 | **Priorite** : P3

En tant que marchand, je veux que le PDF contienne toutes les informations utiles de la demande, afin d'avoir un document complet et professionnel.

**Criteres d'acceptation** :
- [ ] En-tete : logo de la boutique (si configure) + nom de la boutique + date
- [ ] Numero de demande auto-incremente : format "LQ-{ANNEE}-{ID}" (ex. : LQ-2026-0042)
- [ ] Section client : nom, email, telephone
- [ ] Section produit : nom du produit, SKU, variation, URL
- [ ] Section message : message integral du demandeur
- [ ] Pied de page : "Document genere automatiquement par LiteQuote" + date/heure
- [ ] Encodage UTF-8 (support des caracteres accentues)

---

### 3. LQ-E09-S03 — Logo de la boutique dans le PDF
**SP** : 2 | **Priorite** : P3

En tant que marchand, je veux pouvoir ajouter mon logo dans les PDFs generes, afin de personnaliser les documents a l'image de ma marque.

**Criteres d'acceptation** :
- [ ] Un champ upload media dans les reglages (onglet PDF) permet de selectionner un logo
- [ ] Le logo est affiche en haut a gauche du PDF
- [ ] Formats acceptes : PNG, JPG
- [ ] Si aucun logo n'est configure, l'espace est laisse vide (pas d'erreur)
- [ ] Le logo est redimensionne automatiquement (max 150px de large)

---

### 4. LQ-E09-S04 — Piece jointe PDF dans les emails
**SP** : 3 | **Priorite** : P3

En tant que marchand, je veux que le PDF soit joint aux emails de notification, afin d'avoir le recapitulatif directement dans ma boite mail.

**Criteres d'acceptation** :
- [ ] Le PDF est joint a l'email admin comme piece jointe
- [ ] Le PDF est joint a l'email auto-repondeur client (si active)
- [ ] Le fichier temporaire est supprime apres envoi (sauf si archivage active)
- [ ] Le nom du fichier PDF : "LQ-{ANNEE}-{ID}.pdf"
- [ ] La piece jointe est transmise via le parametre `$attachments` de `wp_mail()`

---

### 5. LQ-E09-S05 — Archivage et purge des PDFs
**SP** : 5 | **Priorite** : P3

En tant que marchand, je veux pouvoir archiver les PDFs sur mon serveur et definir une duree de conservation, afin de garder un historique tout en gerant l'espace disque.

**Criteres d'acceptation** :
- [ ] Un toggle "Archivage local" dans les reglages (onglet PDF)
- [ ] Les PDFs archives sont stockes dans `wp-content/uploads/litequote-quotes/`
- [ ] Nommage : `LQ-{ANNEE}-{ID}.pdf`
- [ ] Un `.htaccess` avec `deny from all` protege le repertoire contre l'acces direct
- [ ] Un champ "Duree de conservation (jours)" dans les reglages
- [ ] Une tache WP-Cron programmee supprime les PDFs plus anciens que la duree configuree
- [ ] Par defaut : archivage desactive, duree de conservation 90 jours

---

## Onglet admin PDF (LQ-E10-S07)

L'onglet PDF des reglages est active et fonctionnel a ce sprint :
- [ ] Toggle "Activer la generation PDF"
- [ ] Champ upload media "Logo de la boutique"
- [ ] Toggle "Archivage local des PDFs"
- [ ] Champ numerique "Duree de conservation (jours)" (defaut : 90)
- [ ] Si le module PDF n'est pas disponible (licence Pro), l'onglet affiche un message d'upsell

---

## Livrable Sprint 7

A la fin de ce sprint :
- Les PDFs sont generes automatiquement a chaque demande de devis
- Le PDF est joint aux emails admin et client
- L'archivage local avec purge automatique fonctionne
- L'onglet PDF admin est pleinement fonctionnel
- **→ RELEASE v1.5** apres Sprint 8 (polish)
