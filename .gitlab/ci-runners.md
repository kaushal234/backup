# CI Runner Architecture

## Why two runner servers

The CI infrastructure uses two dedicated GitLab runner servers:

- **web-gitlabrunner-01** — handles deploy and provisioning jobs exclusively
- **web-gitlabrunner-02** — handles all other CI workloads (tests, builds, lint, etc.)

The key constraint driving this split: **only runner-01 holds the SSH keys** required to connect to production and staging servers. Runner-02 has no access to those servers. Deploy and provisioning jobs must therefore always run on runner-01.

Rather than maintaining SSH keys on both servers (a security and operational risk), runner-02 is kept free of any deployment credentials and used purely for CI workload.

## How pipeline routing works

Since Docker images are built locally and consumed on the same machine (no Docker registry is used), builds and tests must always run on the same server. A build on runner-02 followed by a test on runner-01 would fail because the image would not be found.

To guarantee this, pipeline routing is driven by **CI/CD variables set in the `workflow:` block**. Each pipeline type resolves to a specific set of runner tags, which map to one server only:

```yaml
# MR pipeline          → runner-02  (SHELL_TAG: shell-02, DOCKER_TAG: docker-02, ...)
# main branch push     → runner-01  (SHELL_TAG: shell-01, DOCKER_TAG: docker-01, ...)
# any git tag          → runner-01  (staging/*, provisioning/*, release/*, ...)
# scheduled pipeline   → runner-02
```

Jobs use `tags: [$DOCKER_TAG]`, `tags: [$SHELL_TAG]`, `tags: [$SELENIUM_TAG]`, or `tags: [$GATE_TAG]` instead of hardcoded tag names. The workflow variables transparently route the entire pipeline to the correct server.

## Runner tags reference

### Variable tags (machine-dependent)

| Variable | runner-01 value | runner-02 value | Used by |
|---|---|---|---|
| `$SHELL_TAG` | `shell-01` | `shell-02` | `docker-build-*`, `api-database-init`, `docker-cleanup` |
| `$DOCKER_TAG` | `docker-01` | `docker-02` | All test, lint, and inspection jobs |
| `$SELENIUM_TAG` | `selenium-01` | `selenium-02` | `intranet-e2e` |
| `$GATE_TAG` | `gate-01` | `gate-02` | `pipeline-gate` |

### Fixed tags (always runner-01)

These tags are hardcoded — they are never variable — because the jobs they serve always require SSH access.

| Tag | Used by |
|---|---|
| `deploy` | `deploy_staging`, `deploy_prod` and their unlock/rollback variants |
| `provisioning` | All Ansible provisioning jobs, `run_api_command`, `run_api_staging_command` |
| `provisioning-shell` | `docker-build-provisioning` (must run on runner-01 so the image is available to provisioning jobs) |

## Runner configuration (GitLab UI)

Tags are configured via **GitLab Admin → CI/CD → Runners → Edit runner**. "Run untagged jobs" must be disabled on all runners.

### web-gitlabrunner-01

| Runner ID | Executor | Tags | Limit |
|---|---|---|---|
| 36, 38, 40 | shell | `shell-01`, `provisioning-shell` | — |
| 37, 39 | docker | `docker-01`, `provisioning` | — |
| 41 | docker | `gate-01` | 2 |
| 42 | docker | `deploy` | 1 |
| 43 | docker | `selenium-01` | 1 |
| 44 | docker | `cypress-01` | 1 |

### web-gitlabrunner-02

| Runner ID | Executor | Tags | Limit |
|---|---|---|---|
| 45 | docker | `selenium-02` | 1 |
| 46, 47 | docker | `docker-02` | — |
| 48, 49 | shell | `shell-02` | — |
| 50 | docker | `cypress-02` | 1 |
| 51 | docker | `gate-02` | 2 |

## Pipeline concurrency gate

A `pipeline-gate` job runs in the `.pre` stage (before all other stages) on a dedicated gate runner (`gate-01` or `gate-02`). It limits concurrent pipelines to **2 per server**.

The gate runner has `limit = 2` in `config.toml`. This means at most 2 gate jobs run simultaneously per server. Any additional pipeline queues in GitLab's own queue and consumes no runner slot while waiting.

Once a gate job starts, it polls the GitLab API every 30 seconds and counts how many peer pipelines (same server) with a **lower pipeline ID** are currently running. Pipeline IDs are monotonically increasing, so lower ID = arrived earlier. When fewer than 2 peers are ahead, the gate releases and the rest of the pipeline proceeds.

The gate mirrors the workflow routing rules to identify peers correctly:

- **runner-02 peers**: pipelines with `source == merge_request_event` or `source == schedule`
- **runner-01 peers**: pipelines triggered by a git tag, or a push to the default branch (excluding scheduled pipelines which share the same `ref` but run on runner-02)

Authentication uses `$CI_JOB_TOKEN` with the `JOB-TOKEN` header — no manual token configuration required.

## Protected tag patterns

For deploy and provisioning pipelines to reach runner-01, the corresponding git tag patterns must be declared as **protected tags** in GitLab → Settings → Repository → Protected Tags:

| Pattern | Triggers |
|---|---|
| `staging/*` | `deploy_staging` |
| `release/*` | `deploy_prod` |
| `provisioning/*` | Provisioning ansible jobs |

## Docker disk cleanup

Both servers run in the **America/New_York** timezone. Cron times below are local to that timezone.

### Nightly cleanup — 19:30 every day

```
30 19 * * * bash -c "docker volume rm \$(docker volume ls -q --filter name=runner-) 2>/dev/null || true; docker images --format '{{.Repository}}:{{.Tag}}' | grep '^alvestfr/' | xargs -r docker rmi -f 2>/dev/null; docker image prune -f && docker container prune -f && docker volume prune -f && docker builder prune --filter 'until=72h' -af" >> /var/log/docker-cleanup.log 2>&1
```

| Step | What it removes | What it keeps |
|---|---|---|
| `docker volume rm --filter name=runner-` | Runner cache volumes (named, created per concurrent slot) | — |
| `grep alvestfr/ \| docker rmi` | SHA-tagged pipeline images (`alvestfr/api:abc123…`) | Base images (php, node, nginx…) |
| `docker image prune -f` | Dangling (untagged) image layers | All named images |
| `docker container prune -f` | Stopped containers | Running containers |
| `docker volume prune -f` | Anonymous volumes | Named volumes |
| `docker builder prune --filter 'until=72h' -af` | Build cache older than 3 days | Last 3 days of build cache |

**Why `docker volume rm` instead of just `docker volume prune`:** Runner cache volumes are named (e.g. `runner-TOKEN-cache-HASH`). Docker versions before 23.0 silently skip named volumes in `docker volume prune`. Explicit `docker volume rm` removes them by name regardless of Docker version.

**Why `until=72h` for build cache:** Keeps 3 days of cache so builds benefit from recent layer reuse (e.g. Thursday's build reuses Wednesday's cache). Using `until=168h` (7 days) was ineffective because all cache is typically less than 7 days old. `docker system prune -af --volumes` is intentionally avoided to prevent wiping build cache and causing a cold start.

### Weekly cleanup — Sunday 08:00

```
0 8 * * 0   docker image prune -af >> /var/log/docker-cleanup.log 2>&1
```

Removes all unused named base images (php, node, selenium/standalone-chromium, etc.) that accumulate over the week. Build cache is intentionally left untouched so Monday builds are not cold.

Runs at **08:00 New York = 14:00 UTC = 16:00 Europe/Paris**, which is **2h17m before** the scheduled pipeline (`16 17 * * 7` Europe/Paris) that re-warms all caches.

### Full crontab (both servers)

```
30 19 * * * bash -c "docker volume rm \$(docker volume ls -q --filter name=runner-) 2>/dev/null || true; docker images --format '{{.Repository}}:{{.Tag}}' | grep '^alvestfr/' | xargs -r docker rmi -f 2>/dev/null; docker image prune -f && docker container prune -f && docker volume prune -f && docker builder prune --filter 'until=72h' -af" >> /var/log/docker-cleanup.log 2>&1
0 8 * * 0   docker image prune -af >> /var/log/docker-cleanup.log 2>&1
0 22 1 * * find /usr/local/share/.cache/yarn/v6 -mindepth 1 -maxdepth 1 -mtime +150 -type d -exec rm -rf {} \;
```

Output is appended to `/var/log/docker-cleanup.log` on each server.
