#!/usr/bin/env bash
#
# Create a compressed, verified PostgreSQL backup.
#
# Usage:
#   ./scripts/backup-database.sh [output-directory]
#
# Reads connection settings from the environment (DB_HOST, DB_PORT, DB_DATABASE,
# DB_USERNAME, DB_PASSWORD). Source the relevant .env before running.
#
# Produces the custom pg_dump format (-Fc), which restore-database.sh consumes
# and which supports selective and parallel restore.

set -euo pipefail

OUTPUT_DIR="${1:-./storage/backups}"

DB_HOST="${DB_HOST:-postgres}"
DB_PORT="${DB_PORT:-5432}"
DB_DATABASE="${DB_DATABASE:?DB_DATABASE is required}"
DB_USERNAME="${DB_USERNAME:?DB_USERNAME is required}"

RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-14}"

TIMESTAMP="$(date -u +%Y%m%dT%H%M%SZ)"
ARCHIVE="${OUTPUT_DIR}/${DB_DATABASE}-${TIMESTAMP}.dump"

mkdir -p "${OUTPUT_DIR}"

export PGPASSWORD="${DB_PASSWORD:-}"

echo "==> Backing up ${DB_DATABASE} from ${DB_HOST}:${DB_PORT}"

pg_dump \
    --host="${DB_HOST}" \
    --port="${DB_PORT}" \
    --username="${DB_USERNAME}" \
    --dbname="${DB_DATABASE}" \
    --format=custom \
    --compress=9 \
    --no-owner \
    --no-privileges \
    --file="${ARCHIVE}"

# A dump that cannot be listed cannot be restored. Fail now rather than during
# an incident.
echo "==> Verifying archive integrity"
pg_restore --list "${ARCHIVE}" > /dev/null

SIZE="$(du -h "${ARCHIVE}" | cut -f1)"
echo "==> Backup complete: ${ARCHIVE} (${SIZE})"

echo "==> Pruning backups older than ${RETENTION_DAYS} days"
find "${OUTPUT_DIR}" -name "${DB_DATABASE}-*.dump" -type f -mtime "+${RETENTION_DAYS}" -delete

echo "==> Done"
