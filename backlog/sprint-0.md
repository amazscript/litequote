# Sprint 0 — Setup & Fondations

**Duree** : 1 semaine
**Objectif** : Creer la structure du plugin, le fichier principal, l'autoloader et les fondations securite/installation
**SP total** : 14
**Version** : —

---

## Stories

### 1. LQ-E13-S01 — Activation du plugin
**SP** : 2 | **Priorite** : P1

En tant que marchand, je veux que l'activation du plugin soit instantanee et sans configuration prealable, afin de pouvoir l'utiliser immediatement apres installation.

**Criteres d'acceptation** :
- [ ] L'activation enregistre les options par defaut dans la base WP (si elles n'existent pas deja)
- [ ] Aucune table custom n'est creee (le plugin utilise uniquement l'Options API et les post meta)
- [ ] Un hook d'activation `register_activation_hook()` initialise les valeurs par defaut
- [ ] Le plugin verifie la version minimale de WordPress (6.0) et WooCommerce (8.0) a l'activation
- [ ] Si les prerequis ne sont pas remplis : afficher un admin notice et desactiver le plugin proprement
- [ ] Si WooCommerce n'est pas actif : afficher un message clair et ne pas activer

---

### 2. LQ-E13-S02 — Desactivation du plugin
**SP** : 1 | **Priorite** : P1

En tant que marchand, je veux que la desactivation du plugin restaure le comportement normal de ma boutique, afin de ne pas avoir de residus visuels ou fonctionnels.

**Criteres d'acceptation** :
- [ ] Les hooks WooCommerce sont automatiquement detaches a la desactivation
- [ ] Tous les prix et boutons panier sont restaures immediatement
- [ ] Les options et meta sont conservees (pour une reactivation future)
- [ ] Aucun script ou style LiteQuote n'est charge apres desactivation

---

### 3. LQ-E13-S03 — Desinstallation complete du plugin
**SP** : 3 | **Priorite** : P1

En tant que marchand, je veux que la desinstallation supprime toutes les donnees du plugin, afin de ne pas laisser de donnees orphelines dans ma base de donnees.

**Criteres d'acceptation** :
- [ ] Le fichier `uninstall.php` est execute a la suppression du plugin
- [ ] Toutes les options `litequote_*` sont supprimees de la table `wp_options`
- [ ] Toutes les post meta `_litequote_*` sont supprimees de `wp_postmeta`
- [ ] Le repertoire d'archivage PDF `wp-content/uploads/litequote-quotes/` est supprime (si existant)
- [ ] Les taches WP-Cron planifiees par le plugin sont desinscrites
- [ ] Le fichier `uninstall.php` verifie `defined('WP_UNINSTALL_PLUGIN')` avant execution
- [ ] Aucune erreur PHP n'est generee si les donnees n'existent deja plus

---

### 4. LQ-E06-S05 — Protection contre l'acces direct aux fichiers
**SP** : 1 | **Priorite** : P1

En tant que developpeur, je veux que chaque fichier PHP du plugin soit protege contre l'acces direct, afin de prevenir toute execution de code hors du contexte WordPress.

**Criteres d'acceptation** :
- [ ] Chaque fichier PHP du plugin commence par `if (!defined('ABSPATH')) exit;`
- [ ] Le repertoire du plugin contient un fichier `index.php` vide (ou avec juste le guard ABSPATH)
- [ ] Les sous-repertoires (`includes/`, `admin/`, `assets/`) contiennent aussi un `index.php` vide

---

### 5. LQ-E12-S04 — Internationalisation complete (i18n)
**SP** : 3 | **Priorite** : P1

En tant que marchand, je veux que tous les textes du plugin soient traduisibles, afin de proposer le plugin dans n'importe quelle langue.

**Criteres d'acceptation** :
- [ ] Tous les textes visibles (front + admin) utilisent `__()` ou `_e()` avec le text domain `litequote`
- [ ] Le text domain est declare dans le header du fichier principal (`Text Domain: litequote`)
- [ ] Le `Domain Path` est declare : `Domain Path: /languages`
- [ ] Un fichier `.pot` est genere dans `/languages/litequote.pot`
- [ ] Les textes pluriels utilisent `_n()`
- [ ] Le plugin est compatible WPML et Polylang (les strings sont detectees automatiquement)

---

### 6. LQ-E11-S01 — Chargement conditionnel des assets
**SP** : 3 | **Priorite** : P1

En tant que visiteur, je veux que les scripts du plugin ne soient charges que sur les pages ou ils sont necessaires, afin de ne pas ralentir les autres pages du site.

**Criteres d'acceptation** :
- [ ] Les fichiers JS et CSS sont enregistres via `wp_enqueue_scripts` avec une condition
- [ ] Condition de chargement : `is_product()` OU `is_shop()` OU `is_product_category()`
- [ ] Sur une page standard WordPress (article, page, accueil non-boutique) : aucun script LiteQuote dans le source HTML
- [ ] Verification : inspecter le code source d'une page non-WooCommerce → aucune reference a `litequote`
- [ ] Le JS est charge avec l'attribut `defer`

---

## Taches techniques Sprint 0 (hors US)

- [ ] Creer la structure de repertoires du plugin (`litequote/`, `includes/`, `admin/`, `assets/js/`, `assets/css/`, `languages/`)
- [ ] Creer `litequote.php` avec le header WordPress standard et l'autoloader
- [ ] Creer les fichiers `index.php` de protection dans chaque repertoire
- [ ] Initialiser le depot Git + `.gitignore`
- [ ] Creer un environnement de dev WordPress local (WP 6.5 + WooCommerce 8.8 + PHP 8.2)

---

## Dependances

Ce sprint n'a aucune dependance. Tous les sprints suivants dependent de celui-ci.

**Bloque** : Sprint 1, Sprint 2, Sprint 3, Sprint 4
