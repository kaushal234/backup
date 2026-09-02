# Legacy Portal Translations (DMS & Shopfloor) — How It Works

> **Scope of this document**
> This is a *current-state* reference describing **how translation works today** in the
> legacy portals (DMS and Shopfloor) and how it is set up across our environments.
> It intentionally does **not** describe any incident, root cause, or fix — those will be
> captured in a separate document. The goal here is simply to make a previously
> undocumented part of the system understandable to anyone in the organisation.

---

## 1. Two separate translation systems exist in this codebase

It is important to start here, because the repository contains **two unrelated translation
mechanisms**, and confusing them is the first thing that trips people up.

| | Legacy portals | Modern apps |
|---|---|---|
| **Applies to** | DMS, Shopfloor (and other legacy PHP pages) | intranet, evendors, extranet, API |
| **Technology** | PHP **gettext** (`_()`, `setlocale`, `.mo` files) | Symfony Translation component + Crowdin |
| **Translation source** | `.po` → compiled `.mo` catalogs committed in the repo | `.po`/`.yaml` pulled from Crowdin into `packages/translator` |
| **Depends on the operating system?** | **Yes** — relies on OS-installed locales | No — fully self-contained in PHP |

**This document covers the left-hand column only — the legacy gettext system.**
The modern Symfony/Crowdin pipeline (managed via the `translations-pull` / `translations-push`
Make targets and the `packages/translator` package) is mentioned only for contrast and is out
of scope here.

---

## 2. What "gettext" is, and its moving parts

The legacy portals translate text using **GNU gettext**, exposed in PHP through a small set of
built-in functions. On every request, the application performs the same sequence:

1. **Pick a language** (e.g. `fr`) — see the per-portal rules in Sections 3 and 4.
2. **`setlocale(LC_MESSAGES, '<locale>')`** — activates an operating-system *locale*
   (e.g. `fr_FR.iso88591`). This only succeeds if that locale is installed on the OS.
3. **`bindtextdomain('<domain>', '<path>')`** — tells gettext where the translation catalogs
   for a given "text domain" live on disk.
4. **`textdomain('<domain>')`** — selects which domain subsequent lookups use.
5. **`bind_textdomain_codeset('<domain>', '<charset>')`** — sets the character encoding the
   translated strings are returned in (e.g. ISO-8859-1).
6. **`_('Some text')`** — at render time, every translatable string is wrapped in `_()`
   (an alias of `gettext()`). gettext looks the string up in the active catalog and returns the
   translated version.

### Catalog files: `.po` vs `.mo`

- **`.po`** — the human-editable *source* file containing the original strings (`msgid`) and
  their translations (`msgstr`).
- **`.mo`** — the *compiled, binary* version of a `.po`, which is what gettext actually reads at
  runtime. A `.po` must be compiled into a `.mo` for changes to take effect.

### Where gettext looks for a catalog

gettext combines the bound directory, the active locale, and the text domain into a path:

```
<bound directory>/<locale>/LC_MESSAGES/<domain>.mo
```

For example, DMS in French resolves to:

```
dms/web/locale/fr_FR/LC_MESSAGES/dms.mo
```

---

## 3. DMS — how it works

**Entry point:** `dms/web/index.php`
**Language layer:** `dms/src/App.php` (`App\App`) and `dms/src/Language.php` (`App\Language`)

### Language selection

`App\Language::getLanguage()` decides the language for the request, in this order of priority:

1. The **`lang` query-string parameter** (e.g. `index.php?lang=fr`) — used by the
   `[English] [Français] [中文]` switcher links at the top of every page.
2. The **language stored in the session**, if no query parameter is present.
3. The **browser's `Accept-Language`** header, if neither of the above is set.
4. **Default to English** (`en`) if nothing else applies.

The supported languages are defined in `App\Language::AVAILABLE_LANGUAGES`, keyed by a
two-letter code (`en`, `fr`, `zh`).

### Activating the locale and catalog

Once the language is chosen, the language layer sets the relevant locale environment variable,
calls `setlocale()` with the mapped locale string, and the application binds the gettext domain:

- `bindtextdomain('dms', <dms locale directory>)`
- `bind_textdomain_codeset('dms', <charset>)`
- `textdomain('dms')`

### Translatable strings

Throughout `index.php` and the DMS templates, user-facing text is wrapped in `_()`, e.g.
`_('Home')`, `_('By Number')`, `_('Search')`. These are the strings looked up in the catalog.

### Catalogs

```
dms/web/locale/fr_FR/LC_MESSAGES/dms.mo      (French)
dms/web/locale/zh_CN/LC_MESSAGES/dms.mo      (Chinese)
dms/web/locale/dms.po                         (source)
```

The text domain is **`dms`**.

---

## 4. Shopfloor — how it works

**Entry point:** `shopfloor/shop/autoselect.php`

Shopfloor uses the same gettext mechanism as DMS, but **selects its language differently** and
also layers a second translation system on top for some strings.

### Language selection — driven by location/ERP, not a URL parameter

Shopfloor does **not** honour `?lang=`. Instead, it derives the language from the logged-in
user's **location**, specifically that location's **ERP code** (`$ERP`):

| ERP code | Language | Locale string | Charset |
|---|---|---|---|
| 500, 510, 520, 540, 420, 570 | French | `fr_FR.iso88591` | ISO-8859-1 |
| 600, 610, 620, 640, 650, 660 | Chinese | `zh_CN.gb2312` | GB2312 |
| everything else (default) | English | `en_US` | ISO-8859-1 |

A user can change their active location through the **`changeLocation`** flow
(`autoselect.php?m[0]=changeLocation`), which is permission-gated: a user may only switch to
locations linked to their current one, unless they belong to the `superuser`, `role_CMO`, or
`gg_HR` groups.

### Activating the locale and catalog

In `autoselect.php`, after the ERP-based language is chosen, Shopfloor sets the locale
environment variable, calls `setlocale(LC_ALL, '<locale>')`, and binds its own domain:

- `bindtextdomain('shopfloor', <shopfloor locale directory>)`
- `bind_textdomain_codeset('shopfloor', <charset>)`
- `textdomain('shopfloor')`
- and sets the page `Content-Type` charset to match.

### Catalogs

```
shopfloor/shop/locale/fr_FR/LC_MESSAGES/shopfloor.mo   (French)
shopfloor/shop/locale/zh_CN/LC_MESSAGES/shopfloor.mo   (Chinese)
```

The text domain is **`shopfloor`**.

### Note: Shopfloor uses *both* translation systems

In addition to gettext (`_()`), `autoselect.php` also instantiates the modern **`Translator`**
(the Crowdin-backed `packages/translator`, with YAML/PO loaders) for certain strings. So
Shopfloor is a hybrid: legacy gettext for the bulk of the page, plus the modern translator for
some content. Only the gettext half is described in this document.

---

## 5. Locale & charset summary

The exact locale strings the legacy portals request from the operating system:

| Portal | Language | Locale (passed to `setlocale`) | Charset | Text domain |
|---|---|---|---|---|
| DMS | English | `en_US.iso88591` | ISO-8859-1 | `dms` |
| DMS | French | `fr_FR.iso88591` | ISO-8859-1 | `dms` |
| DMS | Chinese | `zh_CN.gb2312` | GB2312 | `dms` |
| Shopfloor | English | `en_US` | ISO-8859-1 | `shopfloor` |
| Shopfloor | French | `fr_FR.iso88591` | ISO-8859-1 | `shopfloor` |
| Shopfloor | Chinese | `zh_CN.gb2312` | GB2312 | `shopfloor` |

Note these are **legacy, non-UTF-8 locales/encodings** (Latin-1 and GB2312). The translated
output is emitted in those character sets, and the page `Content-Type` is set accordingly.

---

## 6. Where it runs (environments)

The legacy portals run in different ways depending on the environment, which is important
context because the translation system's OS-level dependency (see Section 8) behaves the same
way everywhere but is *provided* differently in each place.

| Environment | Where the legacy portals run | OS / runtime | Provisioned by | Code deployed by |
|---|---|---|---|---|
| **Local / CI** | the **`gse`** Docker container (image `tld-gse`) | Debian (`php:8.3.30-apache`) | the container image — `docker/images/multistage/gse/Dockerfile` | `docker compose` (compose project `tld`) |
| **Staging** | `web-stag-portals-01.tld-america.com` | Ubuntu (no Docker) | Ansible (`provisioning/`) | Deployer (`deploy.php`) |
| **Production** | `web-portals-01.tld-america.com`, `web-portals-02.tld-america.com` | Ubuntu (no Docker) | Ansible (`provisioning/`) | Deployer (`deploy.php`) |

Key points:

- **Local/CI uses Docker; production and staging do not.** Production/staging are classic Ubuntu
  servers, configured by Ansible and updated with code-only deployments.
- In the local container, the `gse` service hosts several legacy and modern apps together
  (DMS, Shopfloor, intranet, admin, extranet, shared includes). On the servers, the
  `web-portals-*` hosts serve the legacy portals.
- **Deployments push code only.** The Deployer run (`deploy.php`) prepares the release, installs
  composer/yarn dependencies, builds assets, symlinks `current`, restarts `php8.3-fpm`, and
  cleans up. It does not modify the operating system itself.
- The PHP runtime on the servers is **PHP 8.3 FPM** (`php8.3-fpm`).

---

## 7. Request lifecycle (end to end)

**DMS, French page request:**

1. Browser requests `…/dms/index.php?lang=fr`.
2. Request reaches the portals host (the `gse` container locally; a `web-portals-*` server in prod),
   routed via HAProxy.
3. `index.php` boots `App\App`; `App\Language` reads `lang=fr` and selects French.
4. The app calls `setlocale(LC_MESSAGES, 'fr_FR.iso88591')` and binds the `dms` text domain to
   `dms/web/locale`.
5. As the page renders, every `_('…')` call is looked up in
   `dms/web/locale/fr_FR/LC_MESSAGES/dms.mo`.
6. The French strings are returned and the page is sent to the browser in ISO-8859-1.

**Shopfloor, French page request:**

1. Browser requests a Shopfloor page (e.g. `…/shop/autoselect.php`).
2. `autoselect.php` determines the user's location and reads its **ERP code**.
3. If the ERP maps to French, it sets language to `fr_FR.iso88591`.
4. It calls `setlocale(LC_ALL, 'fr_FR.iso88591')` and binds the `shopfloor` text domain to
   `shopfloor/shop/locale`.
5. `_('…')` calls are looked up in `shopfloor/shop/locale/fr_FR/LC_MESSAGES/shopfloor.mo`.
6. The page renders in French (GB2312 for Chinese locations).

---

## 8. Behavioural characteristics worth knowing

These are inherent properties of how gettext-based translation works in these portals. They are
described here so the system's behaviour is understood, not as problems to act on.

- **It depends on the operating system having the locale installed.** `setlocale()` can only
  activate a locale (e.g. `fr_FR.iso88591`) if that exact locale exists on the host OS. The
  locale name also determines which catalog directory gettext reads (e.g. `fr_FR/`).
- **Missing translations fall back to the source text silently.** If a particular string has no
  entry in the `.mo`, gettext returns the original English `msgid`. English is effectively the
  source/fallback language, so untranslated strings simply appear in English with no error.
- **The same silent behaviour applies if the locale isn't active.** Because there is no error
  raised, the absence of a translation is not obvious from logs — the page just renders in the
  source language.
- **Encodings are legacy and non-UTF-8.** Output is ISO-8859-1 (French/English) or GB2312
  (Chinese), and the page `Content-Type` is set per request to match.
- **Locale state is established per request, by the PHP process handling it.** Long-running PHP
  workers read and apply the locale at request time.

---

## 9. Key files & locations

| Path | Role |
|---|---|
| `dms/web/index.php` | DMS entry point; renders the menu and pages using `_()` |
| `dms/src/App.php` (`App\App`) | Boots DMS; binds the gettext `dms` text domain |
| `dms/src/Language.php` (`App\Language`) | DMS language selection and `setlocale` call; defines `AVAILABLE_LANGUAGES` |
| `dms/web/locale/` | DMS `.po` source and compiled `.mo` catalogs (`fr_FR`, `zh_CN`) |
| `shopfloor/shop/autoselect.php` | Shopfloor entry point; ERP-based language selection; binds `shopfloor` domain |
| `shopfloor/shop/locale/` | Shopfloor `.mo` catalogs (`fr_FR`, `zh_CN`) |
| `docker/images/multistage/gse/Dockerfile` | Builds the local/CI `gse` container image |
| `compose.yml` / `compose.override.yml` | Local Docker stack definition (compose project `tld`) |
| `deploy.php` | Deployer config — production/staging hosts and deployment steps |
| `provisioning/` | Ansible roles that configure the production/staging servers |

---

## 10. Glossary

- **gettext** — A widely used translation system. Programs mark strings with `_()` / `gettext()`,
  and gettext substitutes the translation for the active language at runtime.
- **locale** — An OS-level setting identifying a language, region, and character encoding
  (e.g. `fr_FR.iso88591` = French / France / Latin-1). Must be installed on the OS to be usable.
- **charset / charmap** — The character encoding (e.g. ISO-8859-1, GB2312) used to represent text.
- **text domain** — A named namespace for a set of translations, so multiple apps (e.g. `dms` and
  `shopfloor`) can keep their catalogs separate.
- **`.po` file** — Editable translation source (original strings and their translations).
- **`.mo` file** — Compiled binary form of a `.po`, read by gettext at runtime.
- **`LC_MESSAGES`** — The locale category that governs translated program messages.
- **`setlocale`** — The function that activates a locale for the current process.
- **`bindtextdomain` / `textdomain`** — Functions that tell gettext where a domain's catalogs are
  and which domain to use.
- **ERP code** — In Shopfloor, a numeric code tied to a user's location; it determines the
  Shopfloor language.

---

*This document describes current behaviour only. Remediation and incident history are documented
separately.*
