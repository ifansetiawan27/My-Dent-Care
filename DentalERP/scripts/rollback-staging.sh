#!/usr/bin/env bash
#
# Roll staging back to a previous release.
#
# Usage:
#   ./scripts/rollback-staging.sh <git-ref> [database-archive]
#
# Code rollback alone is safe only when the failed deploy ran no destructive
# migration. Pass a database archive to also restore data; see
# docs/Deployment/Rollback.md for how to decide.

set -euo pipefail

cd "$(dirname "$0")/.."

TARGET_REF="${1:?Usage: rollback-staging.sh <git-ref> [database-archive]}"
DB_ARCHIVE="${2:-}"

COMPOSE_FILE="docker/compose.staging.yaml"
ENV_FILE=".env.staging"
COMPOSE=(docker compose -f "${COMPOSE_FILE}" --env-file "${ENV_FILE}")

if ! git rev-parse --verify --quiet "${TARGET_REF}^{commit}" > /dev/null; then
    echo "Unknown git ref: ${TARGET_REF}" >&2
    exit 1
fi

echo "==> Rolling staging back to ${TARGET_REF}"

echo "==> [1/5] Enabling maintenance mode"
"${COMPOSE[@]}" exec -T app php artisan down --retry=60 || true

echo "==> [2/5] Checking out ${TARGET_REF}"
git checkout --detach "${TARGET_REF}"

if [[ -n "${DB_ARCHIVE}" ]]; then
    echo "==> [3/5] Restoring database from ${DB_ARCHIVE}"
    ./scripts/restore-database.sh "${DB_ARCHIVE}"
else
    echo "==> [3/5] Skipping database restore (no archive supplied)"
    echo "    Verify the schema is compatible with ${TARGET_REF}"
fi

echo "==> [4/5] Rebuilding and restarting services"
"${COMPOSE[@]}" build app
"${COMPOSE[@]}" up -d --wait
"${COMPOSE[@]}" exec -T app php artisan config:cache
"${COMPOSE[@]}" exec -T app php artisan route:cache

echo "==> [5/5] Verifying the rolled-back release"
"${COMPOSE[@]}" exec -T app php artisan up
./scripts/smoke-test.sh

echo "==> Rollback to ${TARGET_REF} complete"
