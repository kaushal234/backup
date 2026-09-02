# CLAUDE.md — intranet

Rules and context specific to the `intranet/` application. The root `CLAUDE.md`
still applies (Makefile-only commands, Docker, translations, PHP conventions).

## Two applications in one folder

`intranet/` hosts **two distinct codebases**. Always identify which one a task
belongs to before touching anything.

### Legacy (a.k.a. "the legacy intranet")

- Lives in `intranet/legacy/`, routed through `src/LegacyBundle/`.
- The former intranet, still in production and still maintained.
- **No framework**: in-house code, mostly procedural PHP, Smarty-style templates.
- **No tests at all**, no static analysis worth relying on.
- Policy: **maintain, don't grow.** Fix bugs, keep it running, and only make it
  evolve in rare cases when there is no alternative.
- When you do touch it: do the **minimum** required to answer the need. No
  refactoring, no modernization, no drive-by cleanup, no "while I'm here"
  improvements. Any change beyond the strict need must be raised first.

### Intranet (Symfony)

- Lives in `src/AppBundle/`, `src/ApiBundle/`, `templates/`, `assets/`.
- Modern Symfony application, and the target for all new development.
- Data comes from the **api** service (API Platform), not from a local ORM —
  `ApiBundle` and `AppBundle/DataTable/Query/ApiProxyQuery` proxy the calls.
- Pages follow a **CRUD convention**: list / show / new / edit / delete, with
  consistent controller, form, template and data table naming. Follow the shape
  of an existing module rather than inventing a new one.

## Folder hierarchy

Directories mirror the **API's own hierarchy**, and stay at **two levels**:
a *section*, then a *module* inside it.

```
Quality/FirstArticleQualification
Sales/Catalogue
Purchasing/SupplierRanking
```

This applies consistently across `src/AppBundle/Controller/`, `Form/Type/`,
`DataTable/Type/`, `Chart/`, `templates/`… and matches the layout on the API side
(`api/src/Entity/`, `api/src/Dto/`). When adding something, find where the
resource lives in the API and place the intranet code in the same section/module.

Don't invent a third level or a new top-level section without a matching one on
the API.

## Business logic belongs to the API

The intranet is a **presentation layer**. Business rules, computations,
validation of domain invariants and workflow decisions live in the **api**
service.

- Avoid writing business logic in `intranet/`. If you find yourself
  reimplementing a rule here, stop — it belongs to the API, and duplicating it
  means it will drift.
- **Controllers must stay thin.** A controller method performs *the action*:
  read the request, call the API, hand data to the form/template, redirect.
  Methods hundreds of lines long are a defect, not a style preference.
- What legitimately lives here: form types, data table types, chart building,
  templates, presentation-only formatting.

## Frontend policy

### React — frozen

- The React app under `assets/react/` is **legacy in spirit**: no new React
  screens, no new React components.
- Maintain the minimum needed to keep existing screens working.
- If a feature needs a new page, build it with Symfony + Twig, not React.

### Symfony first, JavaScript last

The default answer to a UI need is a **Symfony tool**, not JavaScript:

1. Plain Twig / forms / controllers
2. **TwigComponent** (`src/AppBundle/Twig/Components/`)
3. **LiveComponent** for interactivity that needs server state
4. Only then, a Stimulus controller

JS exists to add **quality of life** to a page, rarely to make it work. If the
page cannot render its content without JS, the design is probably wrong — flag it.

### Stimulus

- New JS goes in `assets/controllers/` as a Stimulus controller (TypeScript).
- Stimulus controllers are **covered by Jest** (`assets/__tests__/`), threshold
  **90 %** — `collectCoverageFrom` targets `assets/controllers/**/*.ts`.
- Stimulus is **not** a 1:1 replacement for React. Do not port React components
  into Stimulus controllers just to move them; only build a controller when there
  is a real interaction need that Symfony cannot cover.
- Controllers must stay **generic and reusable**, like the existing ones:
  autocomplete, form collection, datepicker, cascading select, redirect select…
  See `intranet/documentation/` for the ones already documented.
- **Page-specific JS is to be avoided.** If a controller only makes sense on one
  page, that is a strong signal the logic belongs server-side. Raise it before
  writing it.
- Legacy scripts under `assets/javascript/` are not the pattern to follow; don't
  add new files there.

## Data tables (KreyuDataTableBundle)

List pages use **KreyuDataTableBundle** (`src/AppBundle/DataTable/`).

- Reuse the existing building blocks before writing new ones:
  - Columns: `src/AppBundle/DataTable/Column/Type/`
  - Filters: `src/AppBundle/DataTable/Filter/Type/`
  - Base types: `AbstractGridDataTableType`, `AbstractSimpleDataTableType`
- Data tables query the **api** service, so filtering and sorting happen there,
  not in the intranet.

**Critical:** when you add a filter, or set `'sort' => true` on a column, verify
that the matching filter is actually **enabled on the API side**. Without the
corresponding `#[ApiFilter(...)]` on the API resource
(`api/src/Entity/...` or `api/src/Dto/...`), the filter or the sort is silently
ignored and the table looks like it works while it does not.

- Sorting → `OrderFilter` with the property listed
- Text/exact filtering → `SearchFilter` (`partial` where relevant)
- Dates → `DateFilter`, booleans → `BooleanFilter`
- Free-text search box (`setSearchHandler`) → the API's `q` / `SimpleSearchFilter`

Add the missing `#[ApiFilter]` on the API resource as part of the same change,
and check the property path matches exactly what the data table sends.

## Forms

Form types live in `src/AppBundle/Form/Type/`, following the section/module
hierarchy.

**Autocomplete fields must always go through a dedicated type.** The base
`AppBundle\Form\Type\Common\AutocompleteChoiceType` (TomSelect + remote data) is
**never used directly** when building a form. Every resource gets its own
factored type — `LocationChoiceType`, `PeopleChoiceType`,
`CustomerAutocompleteChoiceType`, `ProductAutocompleteChoiceType`… — that
encapsulates the resource, the template and the options.

- Need an autocomplete on a resource that already has a type? Reuse it.
- Need one on a resource that doesn't? Create the dedicated type next to its
  module, then use it. Don't inline `AutocompleteChoiceType` options in a form.

The same factoring rule applies to any recurring field: extract a named type
rather than repeating option arrays.

## Templates (Twig)

- Favour **template inheritance and reuse** over copy-paste. Extend the existing
  base templates and shared partials before creating a new standalone view.
- Shared building blocks live in `templates/components/`.
- **Tabs**: use `templates/components/Tabs/Tabs.html.twig`. It provides tab
  navigation with AJAX reload via Hotwired Turbo (`<turbo-frame>`), with
  `NavItemList`, `NavItem` and `Content`. The pattern is one route + one
  controller + one view per tab — see `intranet/documentation/tabs.md`.
  Don't rebuild a tab system by hand.

## Charts

Charts are built with **`AppBundle\Chart\ChartBuilder`** (and
`ChartBuilderFactory`), which produces the Highcharts configuration.

- Use the builder API rather than assembling Highcharts arrays by hand in a
  controller or a template.
- Chart providers/builders live under `src/AppBundle/Chart/`, following the same
  section/module hierarchy (e.g. `Chart/Purchasing/SupplierRanking/`).

## Pillar files — do not bend them to a specific need

Some files sit at the foundation of many modules. Typical examples:

- `src/AppBundle/DataTable/Query/ApiProxyQuery.php`
- `src/AppBundle/DataTable/Type/AbstractGridDataTableType.php`,
  `AbstractSimpleDataTableType.php`
- `src/AppBundle/DataTable/Filter/Type/AbstractApiFilterType.php`,
  `Filter/Handler/ApiFilterHandler.php`
- `src/ApiBundle/Client.php`, `src/ApiBundle/Model/ApiData.php`,
  `src/ApiBundle/Hydra/*`
- `src/AppBundle/Form/Type/Common/AutocompleteChoiceType.php`
- `src/AppBundle/Chart/ChartBuilder.php`, `ChartBuilderFactory.php`
- `templates/components/` (Tabs, and the other shared components)
- `src/LegacyBundle/HttpKernel/LegacyHttpKernel.php`
- the generic Stimulus controllers (`autocomplete`, `form_collection`,
  `datepicker`, `cascading_select`…)

The rule: **never modify a pillar file to satisfy one specific need.** These
files already do their job. If one of them cannot do what the current task
requires, that is the signal the task itself is misdesigned — the development
must be rethought, not the foundation patched.

This does not mean they are frozen. They can legitimately evolve, but only as a
**deliberate, self-contained evolution**: a change that makes sense on its own,
for every consumer, independently of the feature that triggered it.

Practically, when you hit this wall:

1. Stop and say so. Do not add a special case, an optional flag, an
   `if ($resource === '…')`, or a new parameter that only one caller uses.
2. Propose both options: rework the feature to fit the existing foundation, or a
   clean evolution of the pillar file.
3. Flag that the second option should be discussed with the lead before being
   implemented.

## Testing

- **PHP unit tests are mandatory** for any code added or modified under `src/`
  (services, form types, data table types, Twig components, helpers…). Tests live
  in `tests/` mirroring `src/`.
- **JS unit tests are mandatory** for any Stimulus controller, in
  `assets/__tests__/`, mirroring `assets/controllers/`.
- **Exception, for now: controllers.** Symfony controllers under
  `src/*/Controller/` are not unit-tested; they are covered by Behat
  (`features/`).
- `legacy/` is excluded from all of this — no tests are expected there.
- Use `$this->createMock()` for repositories and API clients, never Prophecy.

Run them through Docker via the root Makefile:

```bash
make test-phpunit-gse            # PHPUnit
make test-javascript-unit-gse    # Jest
make yarn-lint-gse               # ESLint
make webpack-deploy-gse          # Build assets
```

## Styling

Prefer Bootstrap 5.3 utility classes over custom CSS/SCSS. Only add styles in
`assets/styles/` when utilities genuinely cannot express the intent.
