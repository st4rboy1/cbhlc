# Missing Features from GUARDIAN_USER_JOURNEY.md

This directory contains tickets for features referenced in `GUARDIAN_USER_JOURNEY.md` but not yet implemented in the codebase.

## Summary

After analyzing the user journey documentation and comparing it with the actual implementation, the following features are missing:

## Tickets Overview

| # | Ticket | Priority | Effort | Status |
|---|--------|----------|--------|--------|
| 001 | [Backend PDF Generation for Invoices](001-backend-pdf-generation.md) | Medium | 4-6h | Not Started |
| 002 | [Enrollment Certificate Download](002-enrollment-certificate-download.md) | High | 6-8h | Not Started |
| 003 | [Enrollment List Filtering & DataTable](003-enrollment-list-filtering-datatable.md) | High | 8-10h | Not Started |
| 004 | [Request More Info Functionality](004-request-more-info-functionality.md) | Medium | 10-12h | Not Started |
| 005 | [Payment History Report PDF](005-payment-history-report-pdf.md) | Medium | 4-6h | Not Started |
| 006 | [Official Payment Receipts PDF](006-official-payment-receipts-pdf.md) | Medium | 6-8h | Not Started |
| 007 | [Automated Payment Reminders](007-automated-payment-reminders.md) | Low | 12-16h | Not Started |

**Total Estimated Effort:** 50-68 hours

## Priority Breakdown

### High Priority (16-18 hours)
- **002 - Enrollment Certificate Download**: Official certificates for enrolled students
- **003 - Enrollment List Filtering**: Essential for usability with multiple children/years

### Medium Priority (34-44 hours)
- **001 - Backend PDF Generation**: Professional PDF generation for invoices
- **004 - Request More Info**: Admin can request additional information instead of rejecting
- **005 - Payment History Report**: Downloadable payment history for records
- **006 - Official Payment Receipts**: Individual receipts for each payment

### Low Priority (12-16 hours)
- **007 - Automated Payment Reminders**: Automated reminder emails for payments

## Dependencies

```
001 (Backend PDF)
 ├── 002 (Enrollment Certificate) - depends on PDF generation
 ├── 005 (Payment History) - depends on PDF generation
 └── 006 (Official Receipts) - depends on PDF generation

003 (Filtering) - independent
004 (Request Info) - independent
007 (Reminders) - independent
```

## Recommended Implementation Order

1. **Week 1**: Ticket #001 (Backend PDF Generation) - 4-6 hours
2. **Week 1-2**: Ticket #003 (Enrollment List Filtering) - 8-10 hours
3. **Week 2**: Ticket #002 (Enrollment Certificate) - 6-8 hours
4. **Week 3**: Ticket #005 (Payment History Report) - 4-6 hours
5. **Week 3-4**: Ticket #006 (Official Receipts) - 6-8 hours
6. **Week 4-5**: Ticket #004 (Request More Info) - 10-12 hours
7. **Week 6+**: Ticket #007 (Payment Reminders) - 12-16 hours

## What's Already Implemented ✅

- ✅ Invoice routes and viewing
- ✅ Print invoice functionality
- ✅ Basic PDF download (browser print-to-PDF)
- ✅ Email notifications (submitted, approved, rejected)
- ✅ Payment status tracking
- ✅ Guardian dashboard
- ✅ Student CRUD with all fields
- ✅ Document upload and management
- ✅ Enrollment application workflow
- ✅ Admin approval/rejection process

---

**Last Updated:** January 2025
**Total Tickets:** 7
