#!/usr/bin/env bash
#
# Deploy DentalERP to the staging environment.
#
# Usage:
#   ./scripts/deploy-staging.sh
#
# Takes a database backup first so scripts/rollback-staging.sh always has a
# known-good restore point, then builds the image, migrates, and smoke-tests.
# Any failed step aborts the deployment.

set -euo pipefail

cd "$(dirname "$0")/.."

COMPOSE_FILE="docker/compose.staging.yaml"
ENV_FILE=".env.staging"
COMPOSE=(docker compose -f "${COMPOSE_FILE}" --env-file "${ENV_FILE}")

if [[ ! -f "${ENV_FILE}" ]]; then
    echo "Missing ${ENV_FILE}. Copy .env.staging.example and fill in secrets." >&2
    exit 1
fi

RELEASE="$(git rev-parse --short HEAD)"
echo "==> Deploying ${RELEASE} to staging"

echo "==> [1/7] Backing up the database"
if "${COMPOSE[@]}" ps --status running --services 2>/dev/null | grep -qx postgres; then
    "${COMPOSE[@]}" exec -T postgres sh -c \
        'pg_dump -U "$POSTGRES_USER" -d "$POSTGRES_DB" -Fc -Z9 --no-owner --no-privileges \
            > "/backup/pre-deploy-$(date -u +%Y%m%dT%H%M%SZ).dump"'
    echo "    backup written to the postgres backup volume"
else
    echo "    postgres is not running yet; skipping pre-deploy backup"
fi

echo "==> [2/7] Recording the current release for rollback"
git rev-parse HEAD > storage/app/.last-deployed-release || true

echo "==> [3/7] Building the application image"
"${COMPOSE[@]}" build app

echo "==> [4/7] Starting services"
"${COMPOSE[@]}" up -d --wait

echo "==> [5/7] Running migrations"
"${COMPOSE[@]}" exec -T app php artisan migrate --force

echo "==> [6/7] Caching configuration and routes"
"${COMPOSE[@]}" exec -T app php artisan config:cache
"${COMPOSE[@]}" exec -T app php artisan route:cache
"${COMPOSE[@]}" exec -T app php artisan event:cache

echo "==> [7/7] Running smoke tests"
./scripts/smoke-test.sh

echo "==> Deployment of ${RELEASE} complete"
