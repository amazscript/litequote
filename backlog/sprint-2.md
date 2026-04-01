# Sprint 2 — Form Modale de devis

**Duree** : 1 semaine
**Objectif** : Creer la popup modale AJAX avec formulaire, validation front et soumission — le coeur de l'experience utilisateur
**SP total** : 24
**Version** : v0.2
**Depend de** : Sprint 0, Sprint 1

---

## Stories

### 1. LQ-E03-S01 — Ouverture de la modale
**SP** : 5 | **Priorite** : P1

En tant que visiteur, je veux qu'une fenetre modale s'ouvre quand je clique sur le bouton "Demander un devis", afin de remplir ma demande sans quitter la page produit.

**Criteres d'acceptation** :
- [ ] Le clic sur le bouton ouvre une modale en overlay centree
- [ ] L'ouverture est animee en CSS3 (opacity + transform, transition 200ms)
- [ ] Un overlay semi-transparent couvre le fond de page
- [ ] Le scroll de la page de fond est desactive (body overflow:hidden)
- [ ] La modale est generee en JavaScript vanilla ES6+ (zero jQuery)
- [ ] Le HTML de la modale est injecte dans le DOM au chargement de la page (pas de requete AJAX pour le template)

---

### 2. LQ-E03-S02 — Fermeture de la modale
**SP** : 2 | **Priorite** : P1

En tant que visiteur, je veux pouvoir fermer la modale de plusieurs facons, afin de ne pas me sentir bloque dans le formulaire.

**Criteres d'acceptation** :
- [ ] Un bouton "x" (close) en haut a droite ferme la modale
- [ ] Un clic sur l'overlay (fond sombre) ferme la modale
- [ ] La touche Echap ferme la modale
- [ ] La fermeture est animee (inverse de l'ouverture, 200ms)
- [ ] Le scroll de la page est restaure apres fermeture
- [ ] Le focus retourne sur le bouton de devis apres fermeture

---

### 3. LQ-E03-S03 — Accessibilite de la modale (a11y)
**SP** : 3 | **Priorite** : P1

En tant que visiteur utilisant un lecteur d'ecran ou la navigation clavier, je veux que la modale soit pleinement accessible, afin de pouvoir demander un devis sans barriere.

**Criteres d'acceptation** :
- [ ] La modale possede l'attribut `role="dialog"`
- [ ] L'attribut `aria-modal="true"` est present
- [ ] Un `aria-labelledby` pointe vers le titre de la modale
- [ ] Le focus est automatiquement place sur le premier champ a l'ouverture
- [ ] Un focus trap empeche la tabulation de sortir de la modale tant qu'elle est ouverte
- [ ] Le bouton de fermeture est focusable et possede un `aria-label="Fermer"`

---

### 4. LQ-E03-S04 — Champs du formulaire
**SP** : 3 | **Priorite** : P1

En tant que visiteur, je veux remplir un formulaire simple avec mes coordonnees et mon message, afin de soumettre ma demande de devis rapidement.

**Criteres d'acceptation** :
- [ ] Champ "Nom complet" : type text, required, placeholder "Votre nom"
- [ ] Champ "Email" : type email, required, placeholder "votre@email.com"
- [ ] Champ "Telephone" : type tel, optionnel, placeholder "+33 6 12 34 56 78"
- [ ] Champ "Message" : textarea, pre-rempli avec le contexte produit
- [ ] Tous les labels sont translatable via `__()` avec le text domain `litequote`
- [ ] Les champs required sont marques visuellement (asterisque ou bordure)
- [ ] L'ordre de tabulation est logique (nom → email → telephone → message → envoyer)

---

### 5. LQ-E03-S05 — Pre-remplissage contextuel du message
**SP** : 3 | **Priorite** : P1

En tant que visiteur, je veux que le message soit pre-rempli avec les informations du produit, afin de gagner du temps et que le marchand sache immediatement de quel produit il s'agit.

**Criteres d'acceptation** :
- [ ] Le textarea "Message" contient par defaut : "Bonjour, je souhaite un devis pour : {nom_produit} — Ref. {SKU}"
- [ ] Si le produit n'a pas de SKU, la mention "Ref." est omise
- [ ] Pour un produit variable, la variation selectionnee est ajoutee : "Variante : Couleur: Rouge / Taille: L"
- [ ] Si la variation change apres ouverture de la modale, le message est mis a jour dynamiquement
- [ ] Le visiteur peut modifier librement le message pre-rempli
- [ ] Les donnees produit sont injectees via des attributs `data-*` sur le bouton, lues par le JS

---

### 6. LQ-E03-S06 — Validation front-end du formulaire
**SP** : 3 | **Priorite** : P1

En tant que visiteur, je veux etre averti si mes informations sont incorrectes avant l'envoi, afin de corriger mes erreurs sans attendre une reponse serveur.

**Criteres d'acceptation** :
- [ ] Le nom est valide si non vide (apres trim)
- [ ] L'email est valide selon le format RFC 5322 (regex cote client)
- [ ] Le telephone, s'il est rempli, est valide selon un format international basique
- [ ] Les messages d'erreur sont affiches sous chaque champ invalide (pas d'alert())
- [ ] Les messages d'erreur sont en rouge et translatable
- [ ] Le focus est place sur le premier champ en erreur
- [ ] Le bouton "Envoyer" est desactive pendant la soumission (anti double-clic)

---

### 7. LQ-E03-S07 — Soumission AJAX du formulaire
**SP** : 5 | **Priorite** : P1

En tant que visiteur, je veux que ma demande soit envoyee sans rechargement de page, afin d'avoir une experience fluide et rapide.

**Criteres d'acceptation** :
- [ ] La soumission est faite en AJAX via `fetch()` vers `admin-ajax.php`
- [ ] L'action AJAX est `litequote_submit_quote`
- [ ] Le nonce WordPress est inclus dans la requete
- [ ] Un loader/spinner est affiche pendant l'envoi
- [ ] En cas de succes : message de confirmation affiche dans la modale, puis fermeture auto apres 3 secondes
- [ ] En cas d'erreur serveur : message d'erreur affiche dans la modale (sans fermeture)
- [ ] Le message de succes par defaut : "Merci ! Votre demande de devis a ete envoyee. Nous vous repondrons dans les plus brefs delais."
- [ ] Le formulaire est reinitialise apres un envoi reussi
- [ ] Donnees envoyees : nom, email, telephone, message, product_id, product_name, sku, variation, nonce

---

## Livrable Sprint 2

A la fin de ce sprint :
- Le clic sur le bouton ouvre une modale accessible et animee
- Le formulaire est pre-rempli avec les infos du produit
- La validation front empeche les soumissions invalides
- La soumission AJAX fonctionne (handler serveur = Sprint 3)
- La modale se ferme de 3 facons (x, overlay, Echap)

**Note** : Le handler AJAX serveur (traitement + envoi email) sera code au Sprint 3. Ce sprint se concentre sur le front-end.

**Bloque** : Sprint 3 (le handler serveur traite la soumission)
