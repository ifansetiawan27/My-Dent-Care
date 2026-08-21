#!/usr/bin/env bash
#
# Restore a PostgreSQL backup produced by scripts/backup-database.sh.
#
# Usage:
#   ./scripts/restore-database.sh <archive.dump> [target-database]
#
# This is destructive: it drops and recreates every object in the target
# database. It refuses to run against APP_ENV=production unless
# ALLOW_PRODUCTION_RESTORE=yes is set explicitly.

set -euo pipefail

ARCHIVE="${1:?Usage: restore-database.sh <archive.dump> [target-database]}"

DB_HOST="${DB_HOST:-postgres}"
DB_PORT="${DB_PORT:-5432}"
DB_USERNAME="${DB_USERNAME:?DB_USERNAME is required}"
TARGET_DB="${2:-${DB_DATABASE:?DB_DATABASE is required}}"

if [[ ! -f "${ARCHIVE}" ]]; then
    echo "Archive not found: ${ARCHIVE}" >&2
    exit 1
fi

if [[ "${APP_ENV:-}" == "production" && "${ALLOW_PRODUCTION_RESTORE:-no}" != "yes" ]]; then
    echo "Refusing to restore into production." >&2
    echo "Set ALLOW_PRODUCTION_RESTORE=yes to override." >&2
    exit 1
fi

export PGPASSWORD="${DB_PASSWORD:-}"

echo "==> Verifying archive integrity"
pg_restore --list "${ARCHIVE}" > /dev/null

echo "==> Restoring ${ARCHIVE} into ${TARGET_DB} at ${DB_HOST}:${DB_PORT}"
echo "==> This will overwrite existing data in ${TARGET_DB}"

pg_restore \
    --host="${DB_HOST}" \
    --port="${DB_PORT}" \
    --username="${DB_USERNAME}" \
    --dbname="${TARGET_DB}" \
    --clean \
    --if-exists \
    --no-owner \
    --no-privileges \
    --exit-on-error \
    "${ARCHIVE}"

echo "==> Restore complete"
echo "==> Run 'php artisan migrate --force' to apply migrations newer than the backup"
