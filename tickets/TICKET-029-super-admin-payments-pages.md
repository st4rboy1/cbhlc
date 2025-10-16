# TICKET-029: Create Super Admin Payments CRUD Pages

**Type:** Story
**Priority:** High
**Estimated Effort:** 1 day
**Status:** 🔴 To Do
**Epic:** Frontend Completion

## Description

Create all missing frontend pages for Super Admin Payments CRUD operations. Backend routes and controllers exist, but React pages are missing.

## Missing Pages

1. **Index Page** - `/super-admin/payments`
2. **Create Page** - `/super-admin/payments/create`
3. **Show Page** - `/super-admin/payments/{payment}`
4. **Edit Page** - `/super-admin/payments/{payment}/edit`

## Existing Backend

**Controller:** `App\Http\Controllers\SuperAdmin\PaymentController`
**Routes:** All CRUD routes exist + refund action
**Model:** `App\Models\Payment`
**Related Models:** `Invoice`, `Student`, `Guardian`

**Special Action:**
- `POST /super-admin/payments/{payment}/refund` - Process refund

## Acceptance Criteria

- [ ] Index page displays all payments in a table
- [ ] Index page has "Record Payment" button
- [ ] Index page has filters (status, payment method, date range, student)
- [ ] Create page has form to record new payment
- [ ] Create page auto-calculates amount from invoice balance
- [ ] Create page validates input before submission
- [ ] Show page displays payment details and related invoice
- [ ] Show page displays refund information if applicable
- [ ] Show page has Refund button (if not already refunded)
- [ ] Show page has Edit and Delete buttons
- [ ] Edit page has pre-filled form
- [ ] Edit page validates input before submission
- [ ] Refund modal with confirmation and reason
- [ ] All pages use shadcn/ui components
- [ ] All pages are responsive
- [ ] TypeScript types are proper
- [ ] Breadcrumbs are implemented
- [ ] Success/error toast notifications work

## Implementation Details

### Directory Structure

```
resources/js/pages/super-admin/payments/
├── index.tsx          # List all payments
├── create.tsx         # Record new payment
├── show.tsx           # View payment details
└── edit.tsx           # Edit existing payment
```

### Fields Required

Based on `Payment` model:
- `invoice_id` (foreign key to invoices, required)
- `student_id` (foreign key to students, via invoice)
- `payment_date` (required, default today)
- `amount` (required, must be > 0)
- `payment_method` (enum: cash, check, bank_transfer, credit_card, gcash, paymaya, online)
- `reference_number` (optional, for online payments)
- `notes` (optional)
- `status` (enum: pending, completed, failed, refunded)
- `refunded_at` (timestamp, null if not refunded)
- `refund_reason` (text, null if not refunded)
- `refund_amount` (decimal, null if not refunded)

### Component Structure

### Index Page
```tsx
- Heading with title and description
- Stats cards:
  - Total Payments
  - Total Amount Collected
  - Pending Payments
  - Refunded Payments
- Filters:
  - Status (dropdown)
  - Payment Method (dropdown)
  - Student (search select)
  - Date range (date picker)
- Data table with columns:
  - Payment Date
  - Invoice Number (link)
  - Student Name
  - Amount
  - Payment Method (badge)
  - Status (badge with color)
  - Actions (View, Edit, Refund, Delete)
- Pagination
- Record Payment button
- Export button (CSV/PDF)
```

### Create Page (Record Payment)
```tsx
- Heading: "Record Payment"
- Form with sections:
  - Invoice Selection
    - Student/Invoice (searchable select)
    - Display: Invoice number, balance due
  - Payment Information
    - Payment Date (date picker, default today)
    - Amount (number input, max = invoice balance)
    - Payment Method (select dropdown)
    - Reference Number (text input, conditional)
  - Additional Information
    - Notes (textarea)
- Amount validation:
  - Must be > 0
  - Should not exceed invoice balance (warning)
- Submit/Cancel buttons
- Form validation
```

### Show Page
```tsx
- Heading with Payment ID
- Payment details card:
  - Payment Date
  - Amount
  - Payment Method
  - Reference Number
  - Status badge
  - Notes
- Related invoice section:
  - Invoice Number (link)
  - Student Name (link)
  - Guardian Name (link)
  - Invoice Total
  - Previous Balance
  - This Payment
  - New Balance
- Refund information (if refunded):
  - Refunded At
  - Refund Amount
  - Refund Reason
  - Refunded By (user)
- Action buttons:
  - Edit (if not refunded)
  - Refund (if not refunded)
  - Delete
  - Print Receipt
  - Back
- Audit log section
```

### Edit Page
```tsx
- Similar to Create page but:
  - Pre-filled with existing data
  - Invoice is read-only (can't change)
  - Can update: date, amount, method, reference, notes
  - Cannot edit if refunded
```

### Refund Modal
```tsx
- Confirmation dialog with:
  - Warning message
  - Original payment details
  - Refund amount (input, max = payment amount)
  - Refund reason (textarea, required)
  - Confirm/Cancel buttons
- After refund:
  - Update payment status to 'refunded'
  - Update invoice balance
  - Show success toast
  - Redirect to payment show page
```

## Testing Requirements

- [ ] Test all CRUD operations work correctly
- [ ] Test payment recording updates invoice balance
- [ ] Test amount validation (cannot exceed invoice balance)
- [ ] Test refund process works correctly
- [ ] Test refund updates payment and invoice
- [ ] Test filters work correctly
- [ ] Test status badges display correct colors
- [ ] Test related invoice information displays
- [ ] Test responsive design on mobile
- [ ] Test TypeScript compilation
- [ ] Test breadcrumb navigation
- [ ] Test toast notifications

## Dependencies

- [TICKET-025](./TICKET-025-fix-navigation-issues.md) - Should add navigation link after this is done
- [TICKET-028](./TICKET-028-super-admin-invoices-pages.md) - Invoice pages (for invoice links)

## Notes

- Payment recording should update invoice balance automatically
- Refunded payments should not be editable
- Consider adding receipt generation (future enhancement)
- Consider adding batch payment import (future enhancement)
- May need to add navigation link in Super Admin sidebar after completion
- Ensure proper authorization for refund action (super admin only)
- Payment method badges should have different colors
- Status transitions: pending → completed or failed
- Once completed, can only be refunded (not edited)
