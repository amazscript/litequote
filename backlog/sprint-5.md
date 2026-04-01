# Sprint 5 — WhatsApp & Auto-repondeur

**Duree** : 1 semaine
**Objectif** : Canal WhatsApp Business (differenciateur cle) + email auto-repondeur client + honeypot anti-spam
**SP total** : 22
**Version** : v1.1
**Depend de** : Sprint 3, Sprint 4

---

## Stories

### 1. LQ-E07-S01 — Configuration du numero WhatsApp
**SP** : 2 | **Priorite** : P2

En tant que marchand, je veux renseigner mon numero WhatsApp Business dans les reglages du plugin, afin de recevoir les demandes de devis directement sur WhatsApp.

**Criteres d'acceptation** :
- [ ] Un champ texte dans l'onglet "WhatsApp" des reglages
- [ ] Placeholder : "+33612345678"
- [ ] Le numero est stocke au format international (avec indicatif pays)
- [ ] Validation : le numero ne contient que des chiffres et le prefixe "+"
- [ ] Le numero est sanitise et stocke via l'Options API

---

### 2. LQ-E07-S02 — Mode d'affichage WhatsApp configurable
**SP** : 3 | **Priorite** : P2

En tant que marchand, je veux choisir comment le canal WhatsApp est propose aux visiteurs, afin d'adapter l'experience a ma strategie de communication.

**Criteres d'acceptation** :
- [ ] Un reglage propose 3 modes : "Formulaire uniquement" / "WhatsApp uniquement" / "Les deux"
- [ ] Mode "Formulaire uniquement" : comportement standard (modale)
- [ ] Mode "WhatsApp uniquement" : le clic sur le bouton ouvre directement WhatsApp (pas de modale)
- [ ] Mode "Les deux" : la modale affiche le formulaire + un bouton secondaire "Contacter via WhatsApp"
- [ ] La valeur par defaut est "Formulaire uniquement"
- [ ] Si aucun numero WhatsApp n'est configure, le mode "WhatsApp uniquement" est desactive avec un avertissement

---

### 3. LQ-E07-S03 — Construction du lien WhatsApp
**SP** : 3 | **Priorite** : P2

En tant que visiteur, je veux etre redirige vers WhatsApp avec un message pre-rempli contenant les infos du produit, afin de contacter le marchand en un clic.

**Criteres d'acceptation** :
- [ ] L'URL est construite au format : `https://wa.me/{numero}?text={message_encode}`
- [ ] Le message par defaut : "Bonjour ! Je suis interesse par le produit {nom} (Ref. {SKU}). Pourriez-vous me faire parvenir votre meilleur prix ? {URL}"
- [ ] Le message est encode en UTF-8 via `encodeURIComponent()` cote JS
- [ ] Si le produit est variable, la variation selectionnee est incluse
- [ ] Si le produit n'a pas de SKU, la mention "Ref." est omise
- [ ] Le lien ouvre un nouvel onglet (`target="_blank"` + `rel="noopener noreferrer"`)

---

### 4. LQ-E07-S04 — Template du message WhatsApp personnalisable
**SP** : 2 | **Priorite** : P2

En tant que marchand, je veux pouvoir personnaliser le message WhatsApp pre-rempli, afin d'adapter le ton et le contenu a ma clientele.

**Criteres d'acceptation** :
- [ ] Un champ textarea dans les reglages (onglet WhatsApp)
- [ ] Variables disponibles : `{product_name}`, `{sku}`, `{product_url}`, `{variation}`
- [ ] Un message par defaut est fourni et restaurable
- [ ] Le message est sanitise via `sanitize_textarea_field()`

---

### 5. LQ-E07-S05 — Compatibilite mobile et desktop WhatsApp
**SP** : 1 | **Priorite** : P2

En tant que visiteur, je veux que le lien WhatsApp fonctionne sur mobile et sur desktop, afin de contacter le marchand quel que soit mon appareil.

**Criteres d'acceptation** :
- [ ] Sur mobile : le lien ouvre l'application WhatsApp native (iOS/Android)
- [ ] Sur desktop : le lien ouvre WhatsApp Web dans le navigateur
- [ ] L'URL `https://wa.me/` est utilisee (schema officiel Meta, compatible partout)
- [ ] Aucune API WhatsApp privee n'est utilisee

---

### 6. LQ-E05-S01 — Activation de l'auto-repondeur
**SP** : 1 | **Priorite** : P2

En tant que marchand, je veux pouvoir activer ou desactiver l'envoi d'un email de confirmation au client, afin de choisir si mes clients recoivent un accuse de reception.

**Criteres d'acceptation** :
- [ ] Un toggle on/off dans les reglages (onglet Emails)
- [ ] Par defaut : desactive
- [ ] Si active, un email est envoye au client a chaque soumission validee
- [ ] L'auto-repondeur fonctionne independamment de l'email admin

---

### 7. LQ-E05-S02 — Contenu de l'email client
**SP** : 3 | **Priorite** : P2

En tant que client, je veux recevoir un email de confirmation apres ma demande de devis, afin de savoir que ma demande a bien ete prise en compte.

**Criteres d'acceptation** :
- [ ] L'email contient : nom du client, nom du produit, lien vers le produit, nom de la boutique, date
- [ ] Le ton est professionnel et rassurant (ex. : "Nous avons bien recu votre demande...")
- [ ] Le content-type est `text/html`
- [ ] Toutes les variables sont echappees

---

### 8. LQ-E05-S03 — Template email personnalisable
**SP** : 5 | **Priorite** : P2

En tant que marchand, je veux pouvoir personnaliser le contenu de l'email envoye au client, afin d'adapter le message a ma marque.

**Criteres d'acceptation** :
- [ ] Un editeur HTML (textarea) dans les reglages (onglet Emails)
- [ ] Variables disponibles : `{client_name}`, `{product_name}`, `{product_url}`, `{shop_name}`, `{date}`
- [ ] Un template par defaut est fourni et restaurable en un clic
- [ ] Le contenu est sanitise via `wp_kses_post()` avant sauvegarde
- [ ] Les variables sont documentees dans l'interface

---

## Onglet admin WhatsApp (LQ-E10-S06)

L'onglet WhatsApp des reglages est active et fonctionnel a ce sprint :
- [ ] Champ texte "Numero WhatsApp Business"
- [ ] Select "Mode d'affichage" : 3 options
- [ ] Textarea "Template du message" avec variables
- [ ] Bouton "Restaurer le message par defaut"
- [ ] Si aucun numero → mode WhatsApp grise + message explicatif

---

## Livrable Sprint 5

A la fin de ce sprint :
- Le marchand peut activer WhatsApp comme canal de devis
- Le visiteur peut contacter via WhatsApp en 1 clic avec message pre-rempli
- L'auto-repondeur email est configurable et fonctionnel
- Les onglets Emails et WhatsApp sont pleinement fonctionnels
- **→ RELEASE v1.1** (MVP + WhatsApp + Auto-repondeur)
