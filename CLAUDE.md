# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

This is a monorepo containing multiple web portals for Alvest. The main applications are:

- **api/** — Symfony PHP backend (core business logic, all other apps consume this)
- **intranet/** — Main GSE web portal (React + TypeScript + Symfony)
- **extranet-new/** — Symfony-based extranet portal
- **evendors/** — Vendor management (Symfony)
- **mobile/** — React mobile app
- **shopfloor/** — Shopfloor management (React)
- **survey/** — Survey application (React)
- **powerbi/** — Power BI embedding service (Node.js/Express/TypeScript)
- **packages/** — Shared PHP packages (translator, twig_helper, php-sdk)

All frontend portals call the **api** service. Infrastructure runs via Docker Compose with HAProxy as the reverse proxy.

## Common Commands

Most day-to-day operations are via the root `Makefile`:

```bash
make install              # Full environment setup
make start                # Start Docker containers
make build                # Build Docker images
make composer-install     # Install PHP dependencies for all apps
make yarn-install         # Install JS dependencies for all apps
```

### Building assets

```bash
make webpack-deploy-gse       # Build intranet (Encore)
make webpack-deploy-api       # Build API email assets
make webpack-deploy-evendors  # Build evendors assets
make webpack-deploy-shop      # Build shopfloor assets
make yarn-build-mobile        # Build mobile React app
```

Per-app dev/watch — all `yarn` commands must be run inside Docker, not locally. Use the Makefile targets which execute in the correct container:

```bash
make webpack-deploy-gse   # instead of yarn build (intranet)
make yarn-build-mobile    # instead of yarn build (mobile)
```

### Testing

```bash
make test                        # Run ALL tests
make test-behat                  # Behat BDD tests
make test-phpunit-api            # API PHPUnit
make test-phpunit-gse            # Intranet PHPUnit
make test-phpcs                  # PHP code style
make test-phpstan                # PHP static analysis
make test-javascript-unit-gse   # Jest tests (intranet)
make test-mocha-gse              # Mocha tests (intranet)
```

Run Jest via Docker (always use this — `yarn` must not be run locally):

```bash
make test-javascript-unit-gse
```

Jest coverage threshold for intranet is **90%** (branches, functions, lines, statements).

### Linting & formatting

All linting and formatting must go through Makefile targets (run in Docker — never run `yarn` directly):

```bash
make yarn-lint-gse              # ESLint check
make yarn-lint-fix-gse          # ESLint auto-fix
make yarn-format-gse            # Prettier format (intranet)
make yarn-baseline-override-gse # Update ESLint baseline
```

### Database

```bash
make db-init        # Initialize databases
make test-init      # Set up test databases
make test-reset     # Reset test environment
```

## Docker Containers

The project uses Docker Compose (project name `tld`). Key containers:

| Container | Role |
|---|---|
| `tld-php-api-1` | PHP-FPM for the Symfony API — run `php`, `phpunit`, `composer` here |
| `tld-api-1` | Nginx for the API |
| `tld-evendors-php-1` | PHP-FPM for the eVendors app |
| `tld-extranet-php-1` | PHP-FPM for the Extranet app |
| `tld-mobile-react-1` | Node/yarn for the mobile app |
| `tld-mariadb-1` | MariaDB database |

Run PHP/Composer/PHPUnit commands inside `tld-php-api-1`:
```sh
docker exec tld-php-api-1 php vendor/bin/phpunit ...
docker exec tld-php-api-1 composer ...
```

## Architecture

### Backend (PHP/Symfony)

- The **api** application is the central data source; all other apps are consumers
- JWT authentication via `LexikJWTAuthenticationBundle`
- Doctrine ORM for database access (MariaDB)
- Shared twig templating helpers via `packages/twig_helper`
- Tests use HauteLook fixtures and a dedicated `api_test` / `tld_test` MariaDB database

### Frontend (JavaScript)

- **intranet** is the most complex frontend: React 18, Redux Toolkit, TypeScript, Material-UI, Highcharts, Webpack Encore. It is embedded in a Symfony app (assets served via Encore).
- **mobile** is a standalone Create React App (React 19) with Redux and Material-UI.
- **shopfloor** and **survey** use older React 16 stacks with Webpack directly.
- **powerbi** is a Node.js/Express TypeScript service built with esbuild.

### Infrastructure

- HAProxy is the single entry point (SSL termination, routing to services)
- Redis sentinel (3 sentinels + master + 2 slaves) for session/cache HA
- Selenium standalone-chromium for Behat browser tests
- CI pipeline (`.gitlab-ci.yml`) runs only the tests affected by changed components

## Translations

All translations live in `packages/translator/translations/` and are organized by locale:

```
packages/translator/translations/
├── en/          # English — YAML format: {domain}.en.yaml
├── fr/          # French  — PO format:   {domain}.fr.po
└── zh-CN/       # Chinese — PO format:   {domain}.zh-CN.po
```

- **English** (source of truth): YAML files, keys use dot notation (e.g. `ai.fields.placeholder`)
- **French / Chinese**: PO files, `msgid` = the dot-notation key, `msgstr` = the translation
- When adding a new key, always update all three locales (en YAML + fr PO + zh-CN PO)
- In React/TypeScript, use `Translator.trans("domain.key", {}, "domain")` (third argument = domain name)
- Available domains include: `ai`, `aircraft_compatibility`, and others — check the folder for the full list

## Documentation

Each application keeps its own `documentation/` folder at its root, holding
markdown docs written by the team:

- `api/documentation/` — AI features (searcher, extractor, summarizer, chat…),
  ION source provider, exports, contract security, mail builders…
- `intranet/documentation/` — autocomplete, tabs, cascading/redirect selects,
  dynamic forms
- `evendors/documentation/` — README + `how-to/` guides

**Always check the relevant `documentation/` folder before working on a feature
area, and read the docs that cover it.** They carry the intent, the conventions
and the flows that the code alone doesn't show — starting from the code only
usually means re-inventing something that already exists and is documented.

When your change makes a doc inaccurate, update the doc in the same commit.

### Feature Documentation attribute

PHP classes and interfaces annotated with `#[Alvest\FeatureDoc\Attribute\FeatureDoc(path: '...')]` point to a markdown file that documents the feature they belong to. The `path` is **relative to the `documentation/` folder of the repo (app/package) the annotated file lives in** — for a class under `api/src/...` it resolves to `api/documentation/<path>`, for one under another app it resolves to that app's own `documentation/` folder. Each repo will eventually have its own.

Whenever you encounter this attribute on a file you are working on (or on a related interface/abstract base class), **read the referenced doc first** — it provides the high-level context, architecture diagrams, and key flows that are not obvious from the code alone. Conversely, when starting work on a feature area, look for `#[FeatureDoc]` on its interfaces or abstract bases to find the relevant documentation entry point.

When updating a feature doc after a feature is removed or replaced, rewrite the affected sections as if the removed feature never existed. Do not leave traces such as "this used to do X", "X is no longer supported", "X has been replaced by Y". The doc describes the current state of the system; git history is the source of truth for what changed.

## Working Style

- For complex or large tasks, always enter Plan mode first to align on the approach before writing any code.
- If the requested logic seems incorrect or there is a better approach, raise it before proceeding — don't implement something that looks wrong without flagging it.

## Developing a New Feature

### 1. Specifications first — before any code

No development starts before the specifications are settled with the developer.
Your job at this stage is **not** to agree and start coding, it is to **stress
the spec**:

- Identify what is **missing**: undefined cases, unspecified behaviour on error,
  permissions, edge cases, impact on existing modules.
- Identify what is **contradictory**: rules that conflict with each other or with
  how the existing system already behaves.
- Identify what is **blocking**: a dependency that doesn't exist, data the API
  doesn't expose, a constraint that makes the request infeasible as stated.
- Ask about anything ambiguous. Do not fill a gap with an assumption — state the
  gap.

Report all of it to the developer and get it clarified **before** writing code.
A spec that survives this pass is what you implement; the rest is guesswork.

### 2. During development

- The developer will change their mind or refine the need mid-way — that is
  normal. When it happens, re-check that the earlier decisions still hold instead
  of stacking the new request on top.
- **Refactor your own code as you go.** If successive changes have turned your
  implementation into something convoluted, redundant or badly placed, clean it
  up yourself rather than piling on. Do not wait to be asked.
- If a change makes the earlier design wrong, say so instead of patching around
  it.

### 3. Prioritise generic, factored code

The first reflex is **reuse and factoring**, not a bespoke implementation:

- Look for what already exists — a base type, a shared component, an existing
  service — and use it.
- When something is needed in more than one place, extract it once, properly
  named and placed, instead of duplicating a variant.
- Ultra-specific, one-off code is the last resort, not the starting point. If you
  are writing it, be able to explain why nothing generic fits.
- Conversely, don't over-abstract: factor what is genuinely shared, not what
  merely looks similar today.

This does not license modifying foundation code to fit a specific need — see the
pillar-file rule in `intranet/CLAUDE.md`.

## Writing Tests

**Tests are written from the requested feature, not from the code that was
written.** Start from the need, the acceptance criteria, the business rules and
the edge cases the ticket or the discussion describes, then express them as test
cases. The implementation is what gets verified — it is never the source of
truth for what to verify.

Concretely:

- Before writing a test, restate what the feature must do and what the
  acceptance criteria are. If they are unclear or missing, ask instead of
  guessing from the implementation.
- Cover the business cases: nominal path, boundaries, invalid input, permissions,
  and the failure modes the feature is supposed to handle — including cases the
  current implementation may not handle yet.
- Test names describe the expected behaviour (`testQuoteCannotBeSubmittedWhenExpired`),
  not the method being called (`testSubmit`).
- Do not read the implementation and paraphrase it into assertions. A test that
  mirrors the code line by line passes for the wrong reason: it locks in the
  current behaviour, bugs included, and breaks on every harmless refactor.
- If a test derived from the acceptance criteria fails, the default assumption is
  that the code is wrong, not the test. Flag it rather than adjusting the
  expectation to match the output.
- Mock only what the test needs to isolate; never mock the behaviour under test.

Coverage numbers (90 % on the intranet) are a floor, not the goal — a fully
covered feature with no assertion on its rules is untested.

## PHP Testing Conventions

- Whenever you modify a PHP file in any app/package of the monorepo (api, extranet-new, evendors, packages, etc.), check whether a corresponding PHPUnit test exists (typically under that app's `tests/` folder mirroring its `src/`). If it does, update it to cover your changes; if it does not and the file contains testable logic, flag it and propose adding one.
- In PHPUnit tests, use `$this->createMock()` for repositories (e.g. `EntityRepository`, `AILogRepository`). Do **not** use Prophecy (`$this->prophesize()`) for repositories — it causes PHPStan to lose the generic type and reports false errors on `reveal()` and method calls.

## Commit Messages

**The entire commit is in English — no exception.** Title, technical body and the
user-facing `Description:` line. This holds even when the source ticket, the
discussion or the request is in another language.

```
<type>(<scope>): <short technical summary>

<technical body: root cause and approach, for developers. Do not list changed files.>

Module: <business module code, e.g. SPR, ESR, CBOM, TASK>
Description: <user-facing, non-technical, max 100 chars>
Refs: <TTS#12345 / SP#123 — only if there is something to reference>

Co-Authored-By: Claude <model name> <noreply@anthropic.com>
```

- `<type>`: `feat`, `fix`, `chore`, `refactor`, `docs`, `test`…
- **`<scope>` is the technical scope, never the module.** It is either a
  component (bundle/service touched) or an application (`intranet`, `api`,
  `shopfloor`, `extranet`, `evendors`, `powerbi`). If the change spans **two or
  more applications**, drop the parentheses entirely: `<type>: <summary>`.
- `Module:` carries the business module code — it belongs on its own line, never
  in the scope. It comes from whatever the work is about, not necessarily from a
  ticket.
- `Description:` goes into the user changelog: one plain sentence explaining what
  changes **for them**. No dev jargon; business terms of the module are fine.
- `Refs:` references a TTS ticket (`TTS#…`) or a Jira issue (`SP#…`). Work often
  starts from a Jira issue, which may or may not be linked to a TTS ticket — and
  some work starts from neither. **`Refs:` is independent of the two lines
  above**: a commit can perfectly have `Module:` and `Description:` with no
  `Refs:`. Only put a reference there when one actually exists; never invent one.
- `Co-Authored-By:` names the model actually used, **without angle brackets
  around the model name** (only the email keeps its `<>`) — otherwise GitLab
  renders it as `&lt;…&gt;`.

### Purely technical commits

`Module:`, `Description:` and `Refs:` are **optional**. Anything filled in
`Module:` + `Description:` is published in the **changelog users can read**, so
they are only there when the commit changes something for them.

Omit the three lines when the commit is purely technical and invisible to users —
dependency bumps, tooling or CI configuration, refactoring with no behaviour
change, code style, internal documentation. Such a commit is just a title, an
optional technical body, and the `Co-Authored-By:` trailer:

```
chore(intranet): bump symfony/ux-live-component to 2.20

Co-Authored-By: Claude <model name> <noreply@anthropic.com>
```

When in doubt, ask yourself whether a user opening the changelog would care. If
not, leave the lines out — an entry that means nothing to them is noise.

## Key Conventions

- Code comments (PHP, JS/TS, YAML, `.env`, etc.) must always be written in English.
- Intranet TypeScript: strict mode enabled, target ES6, JSX `react-jsx`
- PHP: PSR standards enforced by PHP-CS-Fixer; PHPStan static analysis; Rector for refactoring checks
- Do not leave `dump()`, `dd()`, `var_dump()`, or `console.log` in committed code (`make test-dump` checks this)
- Each application has its own `.env` / `.env.local`; never commit `.env.local` files
- PHP: use `sprintf()` instead of `.` for string concatenation
- PHP-CS-Fixer and PHPStan run automatically after any Edit/Write on a `.php` file via the `.claude/hooks/php-check.sh` PostToolUse hook (routes to the `api`, `intranet` or `extranet-new` Docker container based on the file path). No need to invoke them manually.
- Symfony configuration: always prefer **PHP attributes** over YAML when feasible (`#[AsDecorator]`, `#[Autowire]`, `#[AutoconfigureTag]`, `#[AsEventListener]`, `#[AsCommand]`, route `#[Route]`, etc.). Fall back to YAML only when an attribute equivalent does not exist or when the binding must stay outside the class (e.g. third-party services).
- Intranet styling: prefer Bootstrap 5.3 utility classes (e.g. `bg-danger-subtle`, `border`, `rounded`, `p-2`, `text-muted`) over custom CSS/SCSS whenever possible. Only add custom styles when Bootstrap utilities cannot express the intent.
