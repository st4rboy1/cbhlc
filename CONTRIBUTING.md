# Contributing to cbhlc

Thanks for contributing! This document explains how to open issues, create PRs, run tests locally, and the project's basic workflow.

## Issues

- Before opening a new issue, search existing issues and the `tickets/` folder.
- Use clear titles and include:
    - Summary of the problem or feature
    - Acceptance criteria
    - Steps to reproduce (for bugs)
    - Screenshots or logs when relevant
- Labels
    - Use or request: `priority::critical|high|medium|low`, `area::backend|frontend|infra|ux`, `effort::XS|S|M|L|XL`, `status::needs-triage|in-progress|blocked|ready-for-review`.

## Pull Requests

- Create PRs against `main` using a feature branch (e.g., `feature/documents-upload`).
- Link the related issue in the PR description (e.g., `Closes #123`).
- Include a short changelog line and acceptance criteria.
- Add tests for new logic (unit + feature). The PR should not reduce overall test coverage.
- Review process: at least one backend and one frontend reviewer for full-stack changes.

## Local development

- Requirements: PHP 8.\*, Composer, Node 18+, npm or pnpm, and a local DB.
- Setup (example):

```bash
cp .env.example .env
composer install --no-interaction
npm install
php artisan key:generate
php artisan migrate --seed
npm run dev   # starts Vite dev server
php artisan serve
```

- Run tests

```bash
# PHPUnit / Pest
vendor/bin/pest
# or
composer test
```

## Database Migrations and Seeders

- Add migrations under `database/migrations` and use descriptive names.
- Include schema changes and test migrations locally before opening PRs.
- If adding settings or roles, provide seeders under `database/seeders`.

## File Uploads & Security

- Validate file types and sizes on server-side.
- Use Laravel Storage (signed URLs for private files) and avoid storing user uploads directly in `public/` unless intended.

## Coding Standards

- PHP: follow existing Pint / phpstan settings. Run `vendor/bin/pint` and `composer phpstan` where configured.
- JavaScript/TypeScript: run `npm run lint` and follow `tsconfig.json` rules.

## CI / Tests

- CI runs unit and feature tests, static analysis, and frontend build/lint checks.
- PRs must pass CI before merging.

## Templates and Automation

- Use ISSUE and PR templates when provided by the repo.
- If you're unsure how to label an issue, request `status::needs-triage` and maintainers will triage.

## Code of Conduct

Be respectful and constructive. Follow the project's code of conduct if present.

Thank you for improving cbhlc — contributions are welcome and appreciated!
