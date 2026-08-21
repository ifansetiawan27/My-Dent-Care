# Backup and Restore

**Phase:** 29 — Deployment | **Step:** STEP_29.1

DentalERP stores clinical records. Backups are a regulatory and clinical safety
requirement, not an operational convenience.

## Taking a backup

```bash
cd DentalERP
set -a && . ./.env.staging && set +a
./scripts/backup-database.sh ./storage/backups
```

The script writes a compressed `pg_dump` custom-format archive, verifies that
the archive can be listed, and prunes archives older than
`BACKUP_RETENTION_DAYS` (default 14).

Verification is part of the backup, not a separate step. An archive that cannot
be listed cannot be restored, and that must be discovered during the backup
rather than during an incident.

## Restoring a backup

```bash
cd DentalERP
set -a && . ./.env.staging && set +a
./scripts/restore-database.sh ./storage/backups/dentalerp_staging-<timestamp>.dump
```

The restore is destructive: it drops and recreates every object in the target
database. It refuses to run against `APP_ENV=production` unless
`ALLOW_PRODUCTION_RESTORE=yes` is set explicitly.

After restoring, apply any migrations newer than the backup:

```bash
php artisan migrate --force
```

## Recovery objectives

| Objective | Target | Basis |
|---|---|---|
| RPO — Recovery Point Objective | 24 hours (staging), 1 hour (production) | Daily scheduled dump in staging; production requires WAL archiving or a managed point-in-time-recovery service |
| RTO — Recovery Time Objective | 1 hour (staging), 4 hours (production) | Time to provision, restore the archive, reapply migrations, and pass the smoke test |

The staging RPO is met by the scheduled dump alone. **The production RPO of one
hour is not achievable with periodic dumps.** Production requires continuous WAL
archiving or a managed provider that offers point-in-time recovery. This is
recorded as an open item in `docs/Deployment/ProductionChecklist.md`.

## Scheduling

Staging takes a pre-deployment backup automatically during
`scripts/deploy-staging.sh`. For a recurring schedule, invoke
`scripts/backup-database.sh` from cron on the host:

```
0 2 * * * cd /srv/dentalerp/DentalERP && set -a && . ./.env.staging && set +a && ./scripts/backup-database.sh /srv/backups >> /var/log/dentalerp-backup.log 2>&1
```

## Restore drills

A backup that has never been restored is an assumption, not a backup. Restore
the most recent archive into a scratch database at least once per quarter, and
record the result and the elapsed time. If the elapsed time exceeds the RTO, the
RTO is wrong or the procedure is.

## Off-site copies

Local archives do not survive the loss of the host. Replicate archives to
object storage in a separate failure domain from the database, and verify that
the retention policy on that bucket is at least as long as the local one.

## Constraints

- Archives contain patient data. Treat them at the same classification as the
  production database: encrypted at rest, access-controlled, and never copied to
  a developer workstation.
- Never restore a production archive into staging without anonymisation.
