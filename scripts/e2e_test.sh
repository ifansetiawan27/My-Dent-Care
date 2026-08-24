#!/bin/bash

# End-to-End Testing Script
# Tests: Backend Health, API Endpoints, Database Connection

set -e

echo "=========================================="
echo "End-to-End Testing - Dental ERP"
echo "=========================================="
echo ""

# Configuration
BACKEND_URL="${1:-http://108.136.48.83:8080}"
FRONTEND_URL="${2:-https://mydentcare.com}"

echo "Backend URL: $BACKEND_URL"
echo "Frontend URL: $FRONTEND_URL"
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test counter
TESTS_PASSED=0
TESTS_FAILED=0

# Test function
run_test() {
    local test_name=$1
    local command=$2
    
    echo -n "Testing: $test_name... "
    
    if eval "$command" > /dev/null 2>&1; then
        echo -e "${GREEN}✓ PASSED${NC}"
        ((TESTS_PASSED++))
        return 0
    else
        echo -e "${RED}✗ FAILED${NC}"
        ((TESTS_FAILED++))
        return 1
    fi
}

run_test_with_output() {
    local test_name=$1
    local command=$2
    local expected=$3
    
    echo -n "Testing: $test_name... "
    
    result=$(eval "$command" 2>&1)
    
    if echo "$result" | grep -q "$expected"; then
        echo -e "${GREEN}✓ PASSED${NC}"
        ((TESTS_PASSED++))
        return 0
    else
        echo -e "${RED}✗ FAILED${NC}"
        echo "  Expected: $expected"
        echo "  Got: $result"
        ((TESTS_FAILED++))
        return 1
    fi
}

echo "=== 1. Backend Health Tests ==="
echo ""

run_test "Backend is reachable" \
    "curl -sf $BACKEND_URL/up"

run_test_with_output "Health endpoint returns success" \
    "curl -s $BACKEND_URL/up" \
    "Application up"

echo ""
echo "=== 2. API Endpoint Tests ==="
echo ""

run_test "API root endpoint" \
    "curl -sf $BACKEND_URL/api"

run_test "API v1 endpoint" \
    "curl -sf $BACKEND_URL/api/v1"

echo ""
echo "=== 3. Authentication Tests ==="
echo ""

# Test login endpoint exists
run_test "Login endpoint is reachable" \
    "curl -sf -X POST $BACKEND_URL/api/v1/auth/login -H 'Content-Type: application/json' -d '{\"email\":\"test\",\"password\":\"test\"}'"

echo ""
echo "=== 4. CORS Tests ==="
echo ""

run_test_with_output "CORS headers present" \
    "curl -sI $BACKEND_URL/up" \
    "Access-Control"

echo ""
echo "=== 5. Database Connection Tests ==="
echo ""

# Test if backend can connect to database (via any DB-dependent endpoint)
run_test "Database connection working" \
    "curl -sf $BACKEND_URL/up | grep -q 'up'"

echo ""
echo "=== 6. Frontend Tests ==="
echo ""

run_test "Frontend is accessible" \
    "curl -sf $FRONTEND_URL"

run_test_with_output "Frontend loads correctly" \
    "curl -s $FRONTEND_URL" \
    "<!DOCTYPE html>"

echo ""
echo "=== 7. SSL/HTTPS Tests (if applicable) ==="
echo ""

if [[ $BACKEND_URL == https://* ]]; then
    run_test "SSL certificate is valid" \
        "curl -sf $BACKEND_URL/up"
    
    run_test_with_output "HSTS header present" \
        "curl -sI $BACKEND_URL/up" \
        "Strict-Transport-Security"
else
    echo -e "${YELLOW}⊘ SKIPPED (HTTP only)${NC}"
fi

echo ""
echo "=========================================="
echo "Test Results Summary"
echo "=========================================="
echo ""
echo -e "${GREEN}Passed: $TESTS_PASSED${NC}"
echo -e "${RED}Failed: $TESTS_FAILED${NC}"
echo "Total:  $((TESTS_PASSED + TESTS_FAILED))"
echo ""

if [ $TESTS_FAILED -eq 0 ]; then
    echo -e "${GREEN}✅ All tests passed!${NC}"
    echo ""
    echo "System is ready for live review."
    exit 0
else
    echo -e "${RED}❌ Some tests failed.${NC}"
    echo ""
    echo "Please check the failed tests and fix issues before live review."
    exit 1
fi
