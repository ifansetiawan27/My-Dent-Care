# Rollback Procedure

**Phase:** 29 — Deployment | **Step:** STEP_29.1

A rollback restores the previous known-good release. The decision that matters
is whether the failed deployment changed the database, because code can be
reverted freely while data cannot.

## Decide first: was the migration destructive?

| Migration in the failed release | Rollback action |
|---|---|
| None | Revert code only |
| Additive only (new table, new nullable column, new index) | Revert code only; the unused schema is harmless |
| Destructive (dropped or renamed column, dropped table, backfill that overwrote data) | Revert code **and** restore the database |

Reverting code without restoring data is the faster and safer default. Restore
the database only when the schema the old code expects no longer exists, or
when data was corrupted.

## Rolling back

```bash
cd DentalERP

# Code only
./scripts/rollback-staging.sh <previous-git-sha>

# Code and data
./scripts/rollback-staging.sh <previous-git-sha> /backup/pre-deploy-<timestamp>.dump
```

The script enables maintenance mode, checks out the target ref, optionally
restores the database, rebuilds and restarts the services, re-caches
configuration, disables maintenance mode, and finally runs the smoke test. If
the smoke test fails, the rollback itself has failed and must be escalated.

## Finding the rollback target

The release that was running before the current deployment is recorded during
deployment:

```bash
cat storage/app/.last-deployed-release
```

Pre-deployment database backups are written into the postgres backup volume as
`/backup/pre-deploy-<timestamp>.dump`:

```bash
docker compose -f docker/compose.staging.yaml --env-file .env.staging \
    exec postgres ls -lh /backup
```

## After rolling back

1. Confirm the smoke test passed.
2. Confirm the queue worker and scheduler restarted cleanly.
3. Record what failed, and why, before attempting to redeploy.
4. Add a regression test that reproduces the failure. A rollback without a test
   invites the same incident again.

## Constraints

- `scripts/restore-database.sh` refuses to run against `APP_ENV=production`
  unless `ALLOW_PRODUCTION_RESTORE=yes` is set explicitly.
- A restore is destructive: it drops and recreates every object in the target
  database.
- Migrations newer than the backup must be reapplied with
  `php artisan migrate --force` after a restore.
