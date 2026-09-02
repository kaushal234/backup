# Context — API Behat tests

## Execution (always inside Docker)

The API container is `docker compose -p tld exec php-api` (= `$(API)` in the root Makefile).

```bash
# Run a specific suite (most common while developing)
docker compose -p tld exec php-api vendor/bin/behat -f progress --strict --suite=module

# A single file / scenario being written
docker compose -p tld exec php-api vendor/bin/behat features/module/module.feature
docker compose -p tld exec php-api vendor/bin/behat features/module/module.feature:42   # scenario line
```

### Preparing the test database: `test-reset` then `test-refresh`

```bash
make test-reset     # cache-clear + test-init-legacy + test-init: reloads ALL fixtures (slow)
make test-refresh   # restores the last test-reset snapshot (fast), between runs
```

Workflow:

1. **`make test-reset`** first. It clears the cache and reloads the full set of
   fixtures (legacy + API). This is the one that picks up **newly created** fixtures
   and schema changes.
2. Once a `test-reset` has been done, chain the runs with **`make test-refresh`**
   (restores the snapshot, much faster).

⚠️ **`test-refresh` pitfalls** (never a substitute for `test-reset`):
- It **does not reload** fixtures created/modified **after** the last `test-reset`
  (it only restores the snapshot taken at that point).
- It **does not clear the cache**.

So: as soon as you **add a fixture, modify a `fixtures/*.yaml` file or change the
schema**, run **`make test-reset`** again — otherwise the test fails on missing data
or a stale cache.

Full suites are grouped under `make test-behat-api-1..6`, `test-behat-api-baan`,
`test-behat-api-legacy` (see the root `Makefile`).

Each feature belongs to a **suite** declared in `api/behat.yaml` (`suites:` key).
A new feature in a folder not yet covered requires adding its `path` to an existing
suite or creating a new one, with the right list of contexts.

## Test layout

```
api/
  features/<domain>/<resource>.feature
  behat.yaml                                  # suites + contexts declaration
  tests/fixtures/json/<domain>/<resource>/
    schemas/<resource>.json                   # JSON Schema (draft-07) to validate the response
    schemas/<resources>.json                  # collection schema
    dummies/post.json                         # POST request body  (legacy pattern — avoid, see below)
    dummies/put.json                          # PUT request body   (legacy pattern — avoid, see below)
  fixtures/*.yaml                             # Hautelook fixtures (test data)
```

Convention: one JSON fixtures folder per resource, `schemas/` subfolder for response
validation. Request bodies go **inline in the test**, not in `dummies/` (see below).

## Project conventions

- **Always** add the `Accept: application/ld+json` header (and `Content-type` for
  POST/PUT/PATCH).
- Systematically test **access control**: a portal scenario
  (`should only be available for intranet user`) + a 403 / 200-201 pair for each
  mutation protected by a voter.
- Validate the response **structure** with a JSON Schema (`should be valid according
  to the schema ...`) on top of targeted value assertions.
- **Test the existence of ALL filters** declared on the entity (see "Testing filters"
  below).
- Project business status codes: `403` permission, `409` conflict (duplicate name),
  `422` business rule (`hydra:description` carries the message).
- **Request bodies always inline in the test** (`with body: """ ... """`), never via
  a `dummies/*.json` file. Some existing tests still use dummies: do not reproduce
  that pattern, put the body directly in the scenario.
- **After every POST/PUT, assert ALL the JSON nodes** of the resource to confirm the
  outcome: that everything was created/modified as expected in the nominal case, and
  — depending on the case under test — that the relevant fields were **not**
  created/modified (ignored field, insufficient right on a field, server-recomputed
  value, etc.). Use `the JSON nodes should be equal to:` (table) to cover all fields
  at once.

## Testing filters (mandatory)

For every exposed resource, **all declared filters** must be tested: existence and
type. Pattern (see existing features):

```gherkin
  Scenario: Filters are declared on resource
    Given the class "App\Entity\<Domain>\<Resource>" is exposed on the API
    Then the filter "order[name]" should be available and its type should be "string"
    And the filter "name" should be available and its type should be "string"
    And the filter "status" should be available and its type should be "string"
    # ... one line per declared filter (order[...], SearchFilter, DateFilter, BooleanFilter, etc.)
```

For ION the equivalent step is `the ION filter :property should be available and its
type should be :type`. A `query parameter` (non-filter) is tested with
`the query parameter :property should be available`.

## Choosing the right test user

Users live in `fixtures/users.yaml`, but **the username alone is not enough** to pick
who "has the right" or "does not". The permission model is:

- `fixtures/users.yaml` — the users (`user-basic@`, `user-superuser@`, `user-buyer@`,
  `user-hr@`, ...).
- `fixtures/acls.yaml` — links each **user → group(s)** (`Acl` entities, sometimes
  restricted by `location`). This is a user's group membership.
- `fixtures/features.yaml` — links each **feature flag → group(s)** that grant it.

**To pick a user who HAS the right:** find the feature/group granting it in
`features.yaml`, then find a user member of that group in `acls.yaml`.
**For a user who does NOT have the right:** pick a user absent from those groups
(often `user-basic@tld.fr`).

Common landmarks: `user-basic@tld.fr` (group `ACL_AUTH_INTRANET` only → low
privileges), `user-superuser@tld.fr` (group `SUPERUSER` → most rights). Always check
`acls.yaml`/`features.yaml` for the specific rights rather than guessing from the name.

## DSL — project custom steps

Beyond standard Behatch/Mink (`I send a ... request`, `the JSON node ... should be
equal to ...`), the project provides (source: `tests/Behat/Context/`):

**Auth**
- `I authenticate as the :portal user :username` — e.g. `intranet user "user-basic@tld.fr"`
- `I authenticate as the authorized application :application` — e.g. `"link"` (machine-to-machine)
- `The JWT token node :node should be equal to :value` / `should not exist` / `should contain :n element(s)`

**JSON response (extras)**
- `the JSON node :node should be superior/inferior to the number :n`
- `the JSON node :node should be empty` / `should contain today's date` / `should be newer than 1 minute ago`
- `the JSON node :node should not be equal to the string/number ...`
- `one JSON array element at node :node should contain :element in property :key`
- `the response should be an error stating :desc (with status code :code)`

**Filters / API exposure**
- `the filter :property should be available and its type should be :type`
- `the query parameter :property should be available`
- `the class :class is exposed on the API`
- `the resource :resource should only be available for :portals user(s)`

**Legacy database** (sync to the old DB)
- `a new row has been inserted in the legacy table :table`
- `the column :column from the :table legacy table has been inserted/updated with string/integer/number :value`
- `:count rows have been deleted in the legacy table :table`
- `:n insert queries has been executed on the (legacy) table :table`

**ION** (ERP integration)
- `a total of :n request(s) has been sent to ION` / `no requests have been sent to ION`
- `the ION filter :property should be available and its type should be :type`

**Async emails**
- `an email should have been sent asynchronously with subject (matching pattern) :x`
- `this asynchronous email should be sent to/cc/bcc :x` / `should contain :content` / `should have an attachment matching :pattern`
- `:n email(s) should have been sent asynchronously` / `no email should have been sent asynchronously`

**Message bus / activity logs**
- `a message of class :class should have been sent in the bus` / `no message of class :class ...`
- `a(n) :operation log should have been inserted on resource :resource ...`
- `a comment should have been inserted on resource :resource with message :message by :username`

**File exports (xlsx/csv)**
- `the :format file headers are:` (table) / `the :format file should have :n lines` / `:n columns`
- `the :format cell :range should be equal to :expected`

**SQL query count (perf / N+1)**
- `I reset the query count` then `:n database queries must have been executed`
  (variants `less than :n` / `against :connection connection`)

**Files**
- `I send a :method request to :url with file :key :file`
- `there should be (no) file matching :pattern in upload directory`
- `@resetFileTable` tag on scenarios that manipulate files.