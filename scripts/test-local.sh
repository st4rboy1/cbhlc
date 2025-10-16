#!/bin/bash

# Exit on any error
set -e

echo "🔍 Running local CI/CD checks..."
echo "================================"

# PHP Syntax Check
echo "✓ Checking PHP syntax..."
find . -path "./vendor" -prune -o -path "./storage" -prune -o -name "*.php" -print 2>/dev/null | xargs -n1 php -l > /dev/null
echo "  PHP syntax: ✅ PASSED"

# Laravel Pint (Code Style)
echo "✓ Checking code style with Pint..."
if ./vendor/bin/pint --test > /dev/null 2>&1; then
    echo "  Code style: ✅ PASSED"
else
    echo "  Code style: ❌ FAILED"
    echo "  Run './vendor/bin/pint' to fix"
    exit 1
fi

# PHPStan (Static Analysis)
echo "✓ Running static analysis with PHPStan..."
if [ -f vendor/bin/phpstan ]; then
    if ./vendor/bin/phpstan analyse --memory-limit=2G > /dev/null 2>&1; then
        echo "  Static analysis: ✅ PASSED"
    else
        echo "  Static analysis: ❌ FAILED"
        echo "  Run './vendor/bin/phpstan analyse' to see issues"
        exit 1
    fi
else
    echo "  Static analysis: ⚠️  SKIPPED (PHPStan not installed)"
fi

# Composer Audit
echo "✓ Checking for security vulnerabilities..."
if composer audit 2>/dev/null | grep -q "No security vulnerability"; then
    echo "  Security audit: ✅ PASSED"
else
    vulnerabilities=$(composer audit 2>/dev/null | grep -c "advisories" || true)
    if [ "$vulnerabilities" -gt 0 ]; then
        echo "  Security audit: ⚠️  WARNING ($vulnerabilities vulnerabilities found)"
    else
        echo "  Security audit: ✅ PASSED"
    fi
fi

# JavaScript/TypeScript checks
echo "✓ Checking TypeScript..."
if npm run types > /dev/null 2>&1; then
    echo "  TypeScript: ✅ PASSED"
else
    echo "  TypeScript: ❌ FAILED"
    echo "  Run 'npm run types' to see issues"
    exit 1
fi

echo "✓ Checking Prettier formatting..."
if npm run format:check > /dev/null 2>&1; then
    echo "  Prettier: ✅ PASSED"
else
    echo "  Prettier: ❌ FAILED"
    echo "  Run 'npm run format' to fix"
    exit 1
fi

# Vite Build (to catch Inertia component path mismatches)
echo "✓ Building assets with Vite..."
if npm run build > /dev/null 2>&1; then
    echo "  Vite build: ✅ PASSED"
else
    echo "  Vite build: ❌ FAILED"
    echo "  Run 'npm run build' to see issues"
    echo "  Common issue: Inertia component paths must use kebab-case (e.g., 'settings/notifications' not 'Settings/Notifications')"
    exit 1
fi

# Tests (optional - takes longer)
if [ "$1" == "--with-tests" ]; then
    echo "✓ Running tests..."
    if ./vendor/bin/sail pest > /dev/null 2>&1; then
        echo "  Tests: ✅ PASSED"
    else
        echo "  Tests: ❌ FAILED"
        echo "  Run './vendor/bin/pest' to see failures"
        exit 1
    fi
else
    echo "  Tests: ⚠️  SKIPPED (use --with-tests to run)"
fi

echo ""
echo "================================"
echo "✅ All checks passed! Ready to push."
echo ""
echo "Tip: This script runs automatically on git push."
echo "To run tests too: ./scripts/test-local.sh --with-tests"
