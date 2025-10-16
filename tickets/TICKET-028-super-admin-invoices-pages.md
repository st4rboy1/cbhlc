# TICKET-028: Create Super Admin Invoices CRUD Pages

**Type:** Story
**Priority:** High
**Estimated Effort:** 1.5 days
**Status:** 🔴 To Do
**Epic:** Frontend Completion

## Description

Create all missing frontend pages for Super Admin Invoices CRUD operations. Backend routes and controllers exist, but React pages are missing.

## Missing Pages

1. **Index Page** - `/super-admin/invoices`
2. **Create Page** - `/super-admin/invoices/create`
3. **Show Page** - `/super-admin/invoices/{invoice}`
4. **Edit Page** - `/super-admin/invoices/{invoice}/edit`

## Existing Backend

**Controller:** `App\Http\Controllers\SuperAdmin\InvoiceController`
**Routes:** All CRUD routes exist
**Model:** `App\Models\Invoice`
**Related Models:** `InvoiceItem`, `Payment`

**Note:** Shared invoice pages exist at:
- `/home/tony/Desktop/cbhlc/resources/js/pages/invoices/` (index, show)

## Acceptance Criteria

- [ ] Index page displays all invoices in a table
- [ ] Index page has "Create" button
- [ ] Index page has filters (status, student, date range)
- [ ] Create page has form with dynamic line items
- [ ] Create page calculates totals automatically
- [ ] Create page validates input before submission
- [ ] Show page displays invoice details and line items
- [ ] Show page displays payment history
- [ ] Show page has print/download button
- [ ] Show page has Edit and Delete buttons
- [ ] Edit page has pre-filled form with line items
- [ ] Edit page recalculates totals on changes
- [ ] Edit page validates input before submission
- [ ] All pages use shadcn/ui components
- [ ] All pages are responsive
- [ ] TypeScript types are proper
- [ ] Breadcrumbs are implemented
- [ ] Success/error toast notifications work

## Implementation Details

### Directory Structure

```
resources/js/pages/super-admin/invoices/
├── index.tsx          # List all invoices
├── create.tsx         # Create new invoice
├── show.tsx           # View invoice details
└── edit.tsx           # Edit existing invoice
```

### Fields Required

Based on `Invoice` model:
- `invoice_number` (auto-generated, unique)
- `enrollment_id` (foreign key to enrollments)
- `student_id` (foreign key to students, via enrollment)
- `guardian_id` (foreign key to guardians, via enrollment)
- `issue_date` (required)
- `due_date` (required)
- `subtotal` (calculated from line items)
- `discount_amount` (optional)
- `tax_amount` (optional)
- `total_amount` (calculated)
- `amount_paid` (sum of payments)
- `balance_due` (calculated)
- `status` (enum: draft, pending, paid, overdue, cancelled)
- `notes` (optional)

**Invoice Items:**
- `description` (required)
- `quantity` (required, default 1)
- `unit_price` (required)
- `amount` (calculated: quantity * unit_price)

### Component Structure

### Index Page
```tsx
- Heading with title and description
- Stats cards (total invoices, paid, pending, overdue, total revenue)
- Filters:
  - Status (dropdown)
  - Student (search select)
  - Date range (date picker)
- Data table with columns:
  - Invoice Number
  - Student Name
  - Issue Date
  - Due Date
  - Total Amount
  - Amount Paid
  - Balance Due
  - Status (badge with color)
  - Actions (View, Edit, Delete)
- Pagination
- Create button
- Export button (CSV/PDF)
```

### Create/Edit Page
```tsx
- Heading
- Form with sections:
  - Invoice Information
    - Student/Enrollment (select)
    - Issue Date, Due Date
  - Line Items (dynamic array)
    - Description
    - Quantity
    - Unit Price
    - Amount (calculated)
    - Add/Remove item buttons
  - Calculations
    - Subtotal (sum of line items)
    - Discount Amount (optional)
    - Tax Amount (optional)
    - Total Amount (calculated)
  - Additional Information
    - Status
    - Notes
- Submit/Cancel buttons
- Form validation
- Real-time total calculations
```

### Show Page
```tsx
- Heading with Invoice Number
- Invoice details card:
  - Student and guardian information
  - Issue date, due date
  - Invoice number
- Line items table:
  - Description, Quantity, Unit Price, Amount
  - Subtotal, Discount, Tax, Total
- Financial summary:
  - Total Amount
  - Amount Paid (from payments)
  - Balance Due
  - Status badge
- Payment history table:
  - Date, Amount, Method, Reference
  - Link to payment details
- Notes section
- Action buttons:
  - Edit, Delete
  - Record Payment (link to create payment)
  - Print/Download PDF
  - Send Email
  - Back
- Audit log section
```

## Testing Requirements

- [ ] Test all CRUD operations work correctly
- [ ] Test line item dynamic add/remove
- [ ] Test total calculations are correct
- [ ] Test validation errors display properly
- [ ] Test filters work correctly
- [ ] Test status badges display correct colors
- [ ] Test payment history displays correctly
- [ ] Test responsive design on mobile
- [ ] Test TypeScript compilation
- [ ] Test breadcrumb navigation
- [ ] Test toast notifications

## Dependencies

- [TICKET-025](./TICKET-025-fix-navigation-issues.md) - Should add navigation link after this is done
- [TICKET-029](./TICKET-029-super-admin-payments-pages.md) - Payments pages (for payment history links)

## Notes

- Shared invoice pages exist but are read-only
- Super Admin version needs full CRUD capabilities
- Consider adding invoice templates (future enhancement)
- Consider adding email sending capability (future enhancement)
- May need to add navigation link in Super Admin sidebar after completion
- Invoice number should be auto-generated in backend
- Payment history should link to individual payment records
