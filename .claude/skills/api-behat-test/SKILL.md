---
name: api-behat-test
description: >-
  Write and run functional Behat tests for the API (API Platform).
  Use whenever an endpoint, filter, voter, export or legacy/ION sync is added or
  modified under `api/` and the behavior must be covered by a `.feature` test.
  Covers the fixtures layout, the project's custom steps (JSON-LD DSL, legacy table,
  ION, JWT, async emails, message bus, xlsx exports) and the Docker command to run a suite.
---

# API Behat tests

Functional HTTP tests for the API Platform API. A `.feature` sends real requests
against the test database (Hautelook fixtures) and asserts on the JSON-LD response,
the legacy database, ION, emails, etc.

**Key rule: Behat always runs inside Docker, never locally.**

```bash
docker compose -p tld exec php-api vendor/bin/behat features/<domain>/<resource>.feature --strict
```

Read the references as needed:

- **[references/context.md](references/context.md)** — execution (Docker, suites,
  database reset/refresh), fixtures layout, project conventions (access control,
  409/422 codes, choosing the right test user via ACL/features) and the custom-step
  DSL (auth/JWT, legacy table, ION, async emails, message bus, xlsx exports, SQL query count).
- **[references/skeleton.md](references/skeleton.md)** — ready-to-copy feature
  skeleton with the standard scenarios (portal access, GET + JSON Schema, 403/201,
  filters, business errors) and the done checklist.
