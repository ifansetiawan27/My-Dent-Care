#!/usr/bin/env bash
#
# Post-deployment smoke test.
#
# Usage:
#   ./scripts/smoke-test.sh [base-url]
#
# Verifies that the deployed application is actually serving traffic: the health
# endpoint responds, protected endpoints reject anonymous access rather than
# erroring, and the security headers are present. Exits non-zero on any failure
# so deploy-staging.sh can abort.

set -euo pipefail

BASE_URL="${1:-${SMOKE_BASE_URL:-http://localhost:8080}}"
FAILURES=0

check_status() {
    local path="$1"
    local expected="$2"
    local description="$3"
    local actual

    # curl already writes 000 when the connection fails, so suppress its exit
    # status rather than appending a second placeholder.
    actual="$(curl -s -o /dev/null -w '%{http_code}' --max-time 15 "${BASE_URL}${path}" || true)"

    if [[ "${actual}" == "${expected}" ]]; then
        printf '  PASS  %-46s %s\n' "${description}" "${actual}"
    else
        printf '  FAIL  %-46s expected %s, got %s\n' "${description}" "${expected}" "${actual}"
        FAILURES=$((FAILURES + 1))
    fi
}

check_header() {
    local path="$1"
    local header="$2"
    local description="$3"

    if curl -s -I --max-time 15 "${BASE_URL}${path}" | grep -qi "^${header}:"; then
        printf '  PASS  %-46s present\n' "${description}"
    else
        printf '  FAIL  %-46s missing\n' "${description}"
        FAILURES=$((FAILURES + 1))
    fi
}

echo "==> Smoke testing ${BASE_URL}"

echo "--- Availability"
check_status "/up" 200 "health endpoint"

echo "--- Authentication boundary"
check_status "/api/v1/branches" 401 "branches rejects anonymous access"
check_status "/api/v1/ai-queries" 401 "ai queries rejects anonymous access"

echo "--- Routing"
check_status "/api/v1/this-route-does-not-exist" 404 "unknown route returns 404"

echo "--- Security headers"
check_header "/up" "X-Content-Type-Options" "X-Content-Type-Options"
check_header "/up" "X-Frame-Options" "X-Frame-Options"
check_header "/up" "Referrer-Policy" "Referrer-Policy"

if [[ "${FAILURES}" -gt 0 ]]; then
    echo "==> Smoke test FAILED (${FAILURES} check(s) failed)"
    exit 1
fi

echo "==> Smoke test PASSED"
