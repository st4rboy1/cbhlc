# TICKET-024: Remove Duplicate GitHub Actions Test Workflow

**Priority**: Medium
**Estimated Effort**: 30 minutes
**Status**: In Progress
**Branch**: `fix/remove-duplicate-test-workflow`

## Problem Statement

The project currently has two GitHub Actions workflow files that both run tests, causing tests to run twice for every pull request to `main`:

1. **ci.yml** - Main CI pipeline with quality checks and tests
2. **tests.yml** - Duplicate test runner with MySQL

This duplication:
- Wastes CI/CD resources and time
- Creates confusion about which workflow is authoritative
- Shows duplicate test results in PR checks
- Increases GitHub Actions minutes usage unnecessarily

## Current Situation

### Workflow 1: `.github/workflows/ci.yml`
**Triggers:**
- Pull requests to main/develop
- Pushes to develop/feature/*

**Features:**
- Parallel execution (quality-checks + tests jobs)
- SQLite in-memory database (faster)
- PCOV coverage driver (faster than xdebug)
- Comprehensive caching
- Code quality checks (Pint, PHPStan, Composer audit)

### Workflow 2: `.github/workflows/tests.yml`
**Triggers:**
- Pull requests to main/develop
- Pushes to main/develop

**Features:**
- MySQL 8.0 service container
- Xdebug coverage driver (slower)
- Less comprehensive than ci.yml

## Root Cause

When a pull request is created targeting `main` branch:
- Both `ci.yml` and `tests.yml` trigger (both have `pull_request: branches: [main]`)
- Tests run twice: once with SQLite (ci.yml) and once with MySQL (tests.yml)
- Results in duplicate "Tests" and "CI" check statuses in PR

## Proposed Solution

**Remove `.github/workflows/tests.yml`** and keep only `ci.yml` because:

### Advantages of ci.yml:
✅ More comprehensive (includes quality checks)
✅ Faster execution (SQLite + PCOV vs MySQL + xdebug)
✅ Better structured (parallel jobs)
✅ Better caching strategy
✅ Already used as primary CI pipeline

### Why tests.yml is redundant:
- SQLite is sufficient for CI testing (matches local development with `DB_CONNECTION=sqlite`)
- MySQL testing can be done locally if needed
- The application uses Eloquent ORM which abstracts database differences
- No MySQL-specific features being tested

## Implementation Plan

### Step 1: Delete Redundant Workflow
```bash
rm .github/workflows/tests.yml
```

### Step 2: Verify ci.yml Coverage
Ensure ci.yml still has:
- ✅ PHP syntax check
- ✅ Code style (Pint)
- ✅ Static analysis (PHPStan)
- ✅ Security audit (Composer)
- ✅ Asset building (Vite)
- ✅ Test suite with coverage

### Step 3: Commit and Test
- Commit the change
- Create PR
- Verify only one test workflow runs
- Confirm all checks pass

## Expected Results

**Before:**
- PR triggers both `ci.yml` and `tests.yml`
- GitHub Actions shows: "Tests" + "Continuous Integration / quality-checks" + "Continuous Integration / tests"
- Total CI time: ~10-15 minutes (both workflows)

**After:**
- PR triggers only `ci.yml`
- GitHub Actions shows: "Continuous Integration / quality-checks" + "Continuous Integration / tests"
- Total CI time: ~5-8 minutes (single workflow)
- **50% reduction in CI time and resource usage**

## Acceptance Criteria

- [x] `.github/workflows/tests.yml` is deleted
- [ ] Pull requests trigger only `ci.yml` workflow
- [ ] All existing CI checks still pass
- [ ] No duplicate test runs in PR checks
- [ ] GitHub Actions shows only one test job per PR
- [ ] Documentation updated if needed

## Testing Strategy

1. **Create PR with this change**
2. **Verify GitHub Actions tab** shows only "Continuous Integration" workflow
3. **Check PR checks section** - should show:
   - Continuous Integration / quality-checks
   - Continuous Integration / tests
   - linter / quality (from lint.yml - keep this)
4. **Confirm no "ci / tests" job** (from old tests.yml)

## Files to Modify

- ✅ Delete: `.github/workflows/tests.yml`

## Related Workflows (Keep These)

- ✅ `.github/workflows/ci.yml` - Main CI pipeline (KEEP)
- ✅ `.github/workflows/lint.yml` - Code formatting checks (KEEP)

## Performance Impact

- **CI minutes saved per PR**: ~5-7 minutes
- **Resource efficiency**: 50% reduction in redundant test execution
- **Developer experience**: Clearer, single source of truth for CI results

## References

- [GitHub Actions Best Practices](https://docs.github.com/en/actions/learn-github-actions/best-practices)
- [Avoiding Duplicate Workflows](https://docs.github.com/en/actions/using-workflows/events-that-trigger-workflows#avoiding-duplicate-workflows)

## Related Tickets

- TICKET-023: Refactor Pre-Push Hooks (local CI improvements)
- This ticket complements local CI improvements with GitHub Actions optimization

## Notes

- If MySQL-specific testing becomes necessary in the future, consider:
  - Adding MySQL service to ci.yml as optional job
  - Running MySQL tests only on main/develop branches
  - Creating separate workflow for database-specific integration tests
