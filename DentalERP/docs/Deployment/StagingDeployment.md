# Staging Deployment Procedure

**Phase:** 29 — Deployment | **Step:** STEP_29.1

Staging is the last environment before production. It runs the same image,
the same migrations, and the same process topology as production, so that a
deployment failure surfaces here rather than in front of clinics.

## Topology

`docker/compose.staging.yaml` defines five services:

| Service | Purpose | Notes |
|---|---|---|
| `app` | Serves HTTP | Built image, not a bind mount |
| `queue` | Runs `queue:work` | Audit and notification jobs |
| `scheduler` | Runs `schedule:work` | Subscription lifecycle jobs |
| `postgres` | PostgreSQL 17 | Persistent volume, health-checked |
| `redis` | Cache, session, queue | AOF persistence enabled |

The application image is built from `docker/Dockerfile.staging`, which copies
the source into the image rather than mounting it. The artifact that passes
verification is therefore the exact artifact that runs.

## Prerequisites

1. `.env.staging` exists, created from `.env.staging.example`.
   It is git-ignored and must never be committed.
2. `APP_KEY` is set. Generate one with `php artisan key:generate --show`.
3. `APP_DEBUG=false`. Staging must not leak stack traces.
4. Database credentials, S3 credentials, and Midtrans **sandbox** keys are
   populated from the secret manager.
5. TLS is terminated in front of the application before enabling
   `SECURITY_HSTS_ENABLED`.

## Deploying

```bash
cd DentalERP
./scripts/deploy-staging.sh
```

The script performs seven steps and aborts on the first failure:

1. Backs up the database, so a rollback point always exists.
2. Records the current release SHA for rollback.
3. Builds the application image.
4. Starts services and waits for health checks.
5. Runs `migrate --force`.
6. Caches configuration, routes, and events.
7. Runs `scripts/smoke-test.sh`.

## Verifying

`scripts/smoke-test.sh` runs automatically at the end of a deployment and can
also be run on its own:

```bash
./scripts/smoke-test.sh https://staging.example.com
```

It asserts that the health endpoint responds, that protected endpoints reject
anonymous access with 401 rather than erroring, that unknown routes return 404,
and that the security headers are present. It exits non-zero on any failure.

Beyond the automated checks, confirm manually that:

- The queue worker is processing jobs: `docker compose -f docker/compose.staging.yaml logs queue`
- The scheduler is running: `docker compose -f docker/compose.staging.yaml logs scheduler`
- File uploads reach the configured S3 bucket.
- Log records are written to `storage/logs` and to the `system_logs` table.

## Configuration cache

`config:cache` is applied during deployment. Any change to `.env.staging`
requires re-running the deployment, or at minimum:

```bash
docker compose -f docker/compose.staging.yaml --env-file .env.staging \
    exec app php artisan config:cache
```

A cached configuration ignores subsequent `.env` edits. This is the most common
cause of "the setting did not take effect" reports.

## Constraints

- Staging must never point at the production database.
- Staging must use Midtrans sandbox credentials only.
- Staging data is disposable and may be reset at any time.
