# Project Roadmap

This roadmap summarizes the next milestones and priorities for the cbhlc project.

## Milestones

### Milestone 1 — Core Data & Security (2–5 days)

- Implement Enrollment Periods (EPIC-002): migrations, model, validation, scheduled status updates.
- Implement Document Management (EPIC-001): migrations, Document model, secure storage, basic upload and verification.
- Implement System Settings (EPIC-005): settings table, model, caching and basic admin UI.

Why: these features are foundational and will unblock enrollment flows, reporting, and secure file handling.

---

### Milestone 2 — Verification & Notifications (3–6 days)

- Document verification workflows (registrar-side verification/reject).
- Notification improvements and scheduled reminders (EPIC-006).
- Finish audit logging verification and retention (EPIC-007).

Why: completes core user flows and improves trust and observability.

---

### Milestone 3 — Reporting & Financials (4–8 days)

- Core reporting controllers and endpoints (enrollment stats, demographics, class roster).
- Export functionality (PDF/Excel/CSV).
- Basic frontend dashboards and charts.

Why: reporting depends on stable, audited data and provides high value to stakeholders.

---

### Milestone 4 — Admin UX & Communication (4–7 days)

- Permission management UI (EPIC-008) and role seeding.
- Communication/inquiry system and announcements (EPIC-004).
- Polishing, performance tuning, scheduled reports, and optional features.

Why: improves admin productivity and communication channels.

---

## Quick Wins

- Add missing `public/favicon.svg` to fix console 404s.
- Add `system.school_name` seeder to improve branding.
- Create `migration-presence` check in CI and add pre-push hooks improvements (TICKET-023).

## How to use this roadmap

- Each EPIC is split into smaller GitHub issues and labeled with `priority::`, `area::`, and `effort::` labels.
- Use GitHub Milestones to track progress across sprints.
- Prioritize Milestone 1 items in the next sprint.
