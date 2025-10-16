#!/bin/bash

# Exit on any error
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Log directory
LOG_DIR="storage/pre-push-logs"

# Function to get current branch name in snake_case
get_branch_name() {
    git branch --show-current | tr '/' '_' | tr '-' '_'
}

# Function to log command output and manage log rotation
log_command() {
    local command_name=$1
    local branch=$(get_branch_name)
    local timestamp=$(date +%Y_%m_%d_%H_%M_%S)
    local log_file="${LOG_DIR}/${command_name}_${branch}_${timestamp}.log"

    # Create log directory if it doesn't exist
    mkdir -p "$LOG_DIR"

    # Run command and capture output (shift to get actual command)
    shift
    if "$@" > "$log_file" 2>&1; then
        # Keep only latest 5 logs for this command
        ls -t "${LOG_DIR}/${command_name}_"*.log 2>/dev/null | tail -n +6 | xargs rm -f 2>/dev/null || true
        return 0
    else
        # Keep only latest 5 logs for this command
        ls -t "${LOG_DIR}/${command_name}_"*.log 2>/dev/null | tail -n +6 | xargs rm -f 2>/dev/null || true
        echo "$log_file"
        return 1
    fi
}

# Function to run a check with timing
run_check() {
    local check_name=$1
    local command_name=$2
    shift 2

    local start_time=$(date +%s.%N)

    if log_file=$(log_command "$command_name" "$@"); then
        local end_time=$(date +%s.%N)
        local duration=$(awk "BEGIN {printf \"%.1f\", $end_time - $start_time}")
        printf "  ${GREEN}✅ %s: PASSED${NC} (${duration}s)\n" "$check_name"
        return 0
    else
        local end_time=$(date +%s.%N)
        local duration=$(awk "BEGIN {printf \"%.1f\", $end_time - $start_time}")
        printf "  ${RED}❌ %s: FAILED${NC} (${duration}s)\n" "$check_name"
        echo "     See output in: $log_file"
        return 1
    fi
}

echo "🔍 Running local CI/CD checks..."
echo "================================"

# PHP Syntax Check
echo "✓ Checking PHP syntax..."
if ! run_check "PHP syntax" "php_syntax" bash -c 'find . -path "./vendor" -prune -o -path "./storage" -prune -o -name "*.php" -print 2>/dev/null | xargs -n1 php -l'; then
    exit 1
fi

# Laravel Pint (Code Style)
echo "✓ Checking code style with Pint..."
if ! run_check "Code style" "pint" ./vendor/bin/pint --test; then
    echo "     Run './vendor/bin/pint' to fix"
    exit 1
fi

# PHPStan (Static Analysis)
echo "✓ Running static analysis with PHPStan..."
if [ -f vendor/bin/phpstan ]; then
    if ! run_check "Static analysis" "phpstan" ./vendor/bin/phpstan analyse --memory-limit=512M; then
        echo "     Run './vendor/bin/phpstan analyse' to see issues"
        exit 1
    fi
else
    echo "  ${YELLOW}⚠️  Static analysis: SKIPPED${NC} (PHPStan not installed)"
fi

# Composer Audit
echo "✓ Checking for security vulnerabilities..."
if ! run_check "Security audit" "composer_audit" composer audit; then
    echo "     ${YELLOW}⚠️  Security vulnerabilities found${NC}"
    echo "     Run 'composer audit' for details"
    # Don't exit on security audit failures, just warn
fi

# JavaScript/TypeScript checks
echo "✓ Checking TypeScript..."
if ! run_check "TypeScript" "typescript" npm run types; then
    echo "     Run 'npm run types' to see issues"
    exit 1
fi

echo "✓ Checking Prettier formatting..."
if ! run_check "Prettier" "prettier" npm run format:check; then
    echo "     Run 'npm run format' to fix"
    exit 1
fi

# Vite Build (to catch Inertia component path mismatches)
echo "✓ Building assets with Vite..."
if ! run_check "Vite build" "vite" npm run build; then
    echo "     Run 'npm run build' to see issues"
    echo "     Common issue: Inertia component paths must use kebab-case"
    echo "     (e.g., 'settings/notifications' not 'Settings/Notifications')"
    exit 1
fi

echo ""
echo "================================"
echo "✅ All checks passed!"
echo ""
