# Translation

LiteQuote is fully translatable. All user-facing strings use WordPress i18n functions.

## Included Languages

| Language | File | Status |
|---|---|---|
| English | Built-in (default) | Complete |
| French (fr_FR) | `languages/litequote-fr_FR.po` | Complete |
| Spanish (es_ES) | `languages/litequote-es_ES.po` | Complete |
| Arabic (ar) | `languages/litequote-ar.po` | Complete |
| German (de_DE) | `languages/litequote-de_DE.po` | Complete |
| Italian (it_IT) | `languages/litequote-it_IT.po` | Complete |
| Portuguese - Brazil (pt_BR) | `languages/litequote-pt_BR.po` | Complete |

## How WordPress Translation Works

1. LiteQuote's default language is **English**
2. When WordPress is set to another language (e.g., French), it looks for a `.mo` file in `languages/`
3. If found, all `__()` and `_e()` strings are automatically translated
4. If not found, English is displayed

## Adding a New Translation

### Using Poedit (desktop app)

1. Download [Poedit](https://poedit.net/) (free)
2. Open `languages/litequote.pot`
3. Create a new translation (e.g., Spanish)
4. Translate all strings
5. Save as `litequote-es_ES.po` (Poedit auto-generates the `.mo` file)
6. Place both files in the `languages/` folder

### Using Loco Translate (WordPress plugin)

1. Install and activate [Loco Translate](https://wordpress.org/plugins/loco-translate/)
2. Go to **Loco Translate > Plugins > LiteQuote**
3. Click **New Language**
4. Select your language and translate in the browser
5. Save -- the `.po` and `.mo` files are created automatically

### Using WordPress.org (community)

If LiteQuote is published on WordPress.org, translations can be contributed via [translate.wordpress.org](https://translate.wordpress.org/).

## Translation Functions Used

| Function | Usage |
|---|---|
| `__()` | Returns translated string |
| `_e()` | Echoes translated string |
| `esc_html__()` | Returns escaped translated string |
| `esc_html_e()` | Echoes escaped translated string |
| `esc_attr__()` | Returns attribute-safe translated string |
| `_n()` | Handles plural forms |
| `_n_noop()` | Registers plural strings for later translation |

## Text Domain

- **Text domain**: `litequote`
- **Domain path**: `/languages`
- Declared in the plugin header:

```php
Text Domain: litequote
Domain Path: /languages
```

## WPML / Polylang Compatibility

LiteQuote is compatible with WPML and Polylang. All strings are automatically detected by these plugins because we use standard WordPress i18n functions with the `litequote` text domain.

## Translatable Settings

The **button text** and **price label** are stored as options (not translated via `.po`). The merchant types them in their language directly in the settings. This is by design -- these are customizable per store, not per language.

For multilingual stores using WPML, use WPML's String Translation to translate these options.
