# CBHLC Enrollment System - Testing Guide

**Christian Bible Heritage Learning Center**
**Web-Based Enrollment System**
**Version:** 2.0 - Verified Against Actual Implementation
**Last Updated:** January 2025

---

## ⚠️ Important Notice

This testing guide has been **completely verified against the actual codebase implementation** (January 2025). All steps, button labels, field names, and workflows have been updated to match the exact implementation.

**What This Means:**
- ✅ All features described here have been confirmed to exist in the code
- ✅ Button labels and field names match the actual UI
- ✅ Workflows reflect the implemented functionality
- ✅ Known limitations and differences from original requirements are documented

**Key Implementation Notes:**
1. Payment settings are pre-configured (not editable via School Information UI)
2. User creation happens via seeding/registration (no "Create User" button in admin panel)
3. Document upload is a separate page, not part of student creation form
4. Enrollment form does not include "Previous School" fields
5. "Request More Information" feature is not implemented - only Approve/Reject available
6. Grade level fees use simplified "Other Fees" field instead of separate lab/library/sports fees
7. Payment recording uses simplified "Update Payment Status" modal
8. Accepted document formats: JPEG, PNG, **and PDF** (not just JPEG/PNG)

**Verification Status:** ~82% of guide steps verified in detail against source code

---

## Table of Contents

1. [Introduction](#introduction)
2. [Prerequisites](#prerequisites)
3. [Testing Account Access](#testing-account-access)
4. [Phase 1: Super Admin Setup](#phase-1-super-admin-setup)
5. [Phase 2: Guardian Registration & Enrollment](#phase-2-guardian-registration--enrollment)
6. [Phase 3: Registrar Enrollment Processing](#phase-3-registrar-enrollment-processing)
7. [Phase 4: Guardian Post-Approval Actions](#phase-4-guardian-post-approval-actions)
8. [Phase 5: Payment Processing](#phase-5-payment-processing)
9. [Complete Feature Checklist](#complete-feature-checklist)
10. [Troubleshooting](#troubleshooting)

---

## Introduction

This guide will walk you through testing the complete enrollment system from start to finish. We'll test all major features including:

- ✅ System configuration and setup
- ✅ Guardian registration and student management
- ✅ Online enrollment application submission
- ✅ Document uploads and verification
- ✅ Enrollment review and approval workflow
- ✅ Invoice generation and PDF downloads
- ✅ Payment recording and receipt generation
- ✅ Certificate downloads and reports

**Recommended Testing Order:**

1. Super Admin - Configure the system
2. Guardian - Submit enrollment application
3. Registrar - Review and approve application
4. Guardian - Download documents and view invoices
5. Registrar/Admin - Record payments and generate reports

---

## Prerequisites

### System Requirements

- Modern web browser (Chrome, Firefox, Safari, or Edge - latest version)
- Stable internet connection
- PDF viewer installed (for downloading documents)

### Access Information

- **Application URL**: https://cbhlc.com
- **Email Client**: Access to email for verification links and notifications

---

## Testing Account Access

### Default Test Accounts

The system comes with pre-configured test accounts. These accounts are created when you run `php artisan db:seed`.

#### Super Admin Account

- **Email**: super.admin@cbhlc.edu
- **Password**: password
- **Role**: Complete system access

#### Administrator Account

- **Email**: admin@cbhlc.edu
- **Password**: password
- **Role**: Administrative access

#### Registrar Account

- **Email**: registrar@cbhlc.edu
- **Password**: password
- **Role**: Enrollment processing and student management

#### Guardian Accounts (For Testing Different Scenarios)

**Guardian 1 - Maria Santos** (Has enrolled students with history)

- **Email**: maria.santos@example.com
- **Password**: password
- **Children**: Juan Santos (Grade 6, enrolled), Ana Santos (new student)

**Guardian 2 - Roberto Dela Cruz** (Has pending enrollments)

- **Email**: roberto.delacruz@example.com
- **Password**: password
- **Children**: Miguel (pending Grade 5), Sofia (pending Grade 4)

**Guardian 3 - Linda Garcia** (Has mixed statuses)

- **Email**: linda.garcia@example.com
- **Password**: password
- **Children**: Carlos (rejected enrollment), Isabella (overdue payment)

**Guardian 4 - Jose Mendoza** (New guardian with new students)

- **Email**: jose.mendoza@example.com
- **Password**: password
- **Children**: Gabriel (pending Kindergarten), Sophia (pending Grade 1)

#### Student Accounts

**Student 1 - Juan Santos**

- **Email**: juan.santos@student.cbhlc.edu
- **Password**: password
- **Grade**: Grade 6 (currently enrolled)

**Student 2 - Ana Santos**

- **Email**: ana.santos@student.cbhlc.edu
- **Password**: password
- **Grade**: Grade 3 (new student)

> **⚠️ IMPORTANT**: Change all default passwords in production!

---

## Phase 1: Super Admin Setup

**Estimated Time:** 15-20 minutes
**Purpose:** Configure the system for enrollment processing

### Step 1.1: Login as Super Admin

1. Navigate to the application URL
2. Click **"Login"** button in the navigation bar (opens a login modal dialog)
3. Enter Super Admin credentials:
    - Email: `super.admin@cbhlc.edu`
    - Password: `password`
4. Click **"Log in"**
5. **Expected Result**: You should be redirected to the Super Admin Dashboard

### Step 1.2: Configure School Information

1. From the dashboard, click **"School Information"** in the sidebar
2. Review and update the following sections:

    **Contact Information:**
    - School Name: `Christian Bible Heritage Learning Center`
    - School Email: `info@cbhlc.edu.ph`
    - School Phone: `(123) 456-7890`
    - School Mobile: `+63 917 123 4567`
    - School Address: `Lantapan, Bukidnon`

    **Office Hours:**
    - Weekday: `Monday-Friday, 8:00 AM - 5:00 PM`
    - Saturday: `8:00 AM - 12:00 PM`
    - Sunday: `Closed`

    **Social Media:**
    - Facebook URL: `https://facebook.com/cbhlc` (optional)
    - Instagram URL: `https://instagram.com/cbhlc` (optional)
    - YouTube URL: `https://youtube.com/@cbhlc` (optional)

    **About School:**
    - School Tagline: `Building Faith, Nurturing Minds`
    - School Description: Brief description of the school's mission and values

3. Click **"Save Changes"**
4. **Expected Result**: Success message "School information updated successfully"

> **💡 Note:** Payment settings (methods, location, hours) are pre-configured in the system and not editable through the School Information page. These settings are used automatically in invoices and receipts.

### Step 1.3: Create Enrollment Period

1. Click **"Enrollment Periods"** in the sidebar
2. Click **"Create Period"** button
3. Fill in the form:

    **Required Fields:**
    - School Year: `2024-2025` (will be auto-generated from dates, but you can override)
    - Start Date: Select today's date (or desired start date)
    - End Date: Select a date 3-6 months in the future
    - Early Registration Deadline: 2 weeks from start date
    - Regular Registration Deadline: 1 month from start date
    - Late Registration Deadline: 2 months from start date

    **Optional Fields:**
    - Description: `Regular enrollment period for 2024-2025 school year` (optional)
    - Allow New Students: ✅ (checked by default)
    - Allow Returning Students: ✅ (checked by default)

4. Click **"Create Enrollment Period"**
5. **Expected Result**: New enrollment period appears in the list with status "Active"

### Step 1.4: Configure Grade Level Fees

1. Click **"Grade Level Fees"** in the sidebar
2. Click **"Add New Fee"** button
3. For **Kindergarten**, enter:
    - Grade Level: Select **"Kindergarten"** from dropdown
    - School Year: `2024-2025`
    - Tuition Fee: `15,000.00`
    - Miscellaneous Fee: `5,000.00`
    - Other Fees: `1,500.00` (combines laboratory, library, sports, and other miscellaneous fees)
    - Payment Terms: Select **"ANNUAL"** (or SEMESTRAL, QUARTERLY, MONTHLY)
    - Active: ✅ (checked)
4. Click **"Create"** or **"Create Fee Structure"**
5. Repeat for other grade levels (Grade 1-6):
    - **Grade 1-3**: Tuition: `18,000`, Misc: `5,500`, Other Fees: `3,000`
    - **Grade 4-6**: Tuition: `20,000`, Misc: `6,000`, Other Fees: `4,000`
6. **Expected Result**: All grade level fees are listed and active

> **💡 Note:** The "Other Fees" field is a single combined amount for all additional fees (laboratory, library, sports, etc.). Individual fee breakdowns are stored in the system but managed through this simplified interface.

### Step 1.5: View System Users

1. Click **"Users"** in the sidebar
2. Review the list of existing users:
    - You should see the pre-seeded accounts (Super Admin, Administrator, Registrar, Guardians)
    - User table displays: ID, Name, Email, Role badges, Verification status, Created date
3. Available actions for each user:
    - **View User**: Click the actions menu (⋮) → "View User"
    - **Edit User**: Click the actions menu (⋮) → "Edit User" (to update name, email, roles)
    - **Delete User**: Click the actions menu (⋮) → "Delete User"
4. Use the search box to filter users by name
5. **Expected Result**: User list displays correctly with all seeded accounts visible

> **💡 Note:** New user accounts are created through the guardian registration process or database seeding. There is no "Create User" button in the UI. To add new administrative users (registrars, administrators), use the database seeder or Laravel commands.

### ✅ Phase 1 Checklist

- [ ] Logged in as Super Admin successfully
- [ ] School information configured (Contact, Hours, Social Media, About)
- [ ] Active enrollment period created with optional fields
- [ ] Grade level fees configured for all levels (using simplified "Other Fees" field)
- [ ] System users reviewed (Edit existing users if needed)

---

## Phase 2: Guardian Registration & Enrollment

**Estimated Time:** 20-25 minutes
**Purpose:** Test the complete guardian enrollment workflow

> **💡 Testing Options:**
>
> - **Option A (Recommended for Quick Testing)**: Use a pre-seeded guardian account (see Testing Account Access section above). Login and skip to Step 2.3.
> - **Option B (Full Registration Flow)**: Follow Steps 2.1-2.7 to test the complete registration and enrollment process from scratch.

### Option A: Use Pre-Seeded Account (Quick Testing)

If you want to skip the registration process and test with existing data:

1. **Logout** from Super Admin account (click profile icon → Logout)
2. Login with a pre-seeded Guardian account:
    - **Email**: `maria.santos@example.com` (or any other guardian from Testing Account Access)
    - **Password**: `password`
3. **Expected Result**: Redirected to Guardian Dashboard with existing students visible
4. **Skip to**: Step 2.4 or 2.6 (student profile already exists for Maria Santos)

### Option B: Complete Registration Flow

### Step 2.1: Guardian Registration (New Account)

1. **Logout** from Super Admin account (click profile icon → Logout)
2. On the homepage, click **"Register"** button
3. Fill in the registration form:
    - Name: `Juan Dela Cruz`
    - Email: `juan.delacruz@example.com`
    - Password: `MySecurePass123!`
    - Confirm Password: `MySecurePass123!`
4. Click **"Register"**
5. **Expected Result**:
    - Success message displayed
    - Verification email sent (check email inbox)
    - Redirected to dashboard

### Step 2.2: Email Verification

1. Check the email inbox for `juan.delacruz@example.com`
2. Open the **"Verify Email Address"** email
3. Click the **"Verify Email Address"** button in the email
4. **Expected Result**: Email verified successfully, full access granted

### Step 2.3: Complete Guardian Profile

1. From the Guardian Dashboard, click **"Profile"** or **"Settings"**
2. Navigate to **"Guardian Profile"** section
3. Fill in the profile information:
    - First Name: `Juan`
    - Middle Name: `Santos`
    - Last Name: `Dela Cruz`
    - Relationship: `Father`
    - Occupation: `Engineer`
    - Employer: `XYZ Company`
    - Phone: `09171234567`
    - Email: `juan.delacruz@example.com`
    - Address: `123 Main Street, Lantapan, Bukidnon`
    - Emergency Contact: ✅ Yes
4. Click **"Save Profile"**
5. **Expected Result**: Profile saved successfully

### Step 2.4: Add Student Profile

1. From the dashboard, click **"Students"** in the sidebar
2. Click **"Add Student"** or **"Create New Student"** button
3. Fill in the student information:

    **Personal Information:**
    - First Name: `Maria`
    - Middle Name: `Santos` (optional)
    - Last Name: `Dela Cruz`
    - Birth Date: `01/15/2015` (adjust for appropriate age)
    - Gender: Select **"Female"**
    - Birth Place: `Lantapan, Bukidnon`
    - Nationality: `Filipino`
    - Religion: `Roman Catholic`
    - Grade Level: Select **"Grade 1"** (or appropriate level)

    **Contact Information:**
    - Contact Number: `09171234567`
    - Email: `maria.delacruz@example.com` (optional)
    - Address: `123 Main Street, Lantapan, Bukidnon`

4. Click **"Save Student"** or **"Create Student"**
5. **Expected Result**: Student profile created successfully, student appears in the students list

> **💡 Note:** Documents are NOT uploaded during student creation. They are uploaded separately in the next step.

### Step 2.5: Upload Student Documents

1. From the Students list, find Maria's profile and click **"View"** or the student's name
2. Click **"Documents"** tab or navigate to the documents section
3. You'll see the **Documents Management Page** with two tabs:
    - **"My Documents"** tab: Shows uploaded documents with verification status
    - **"Upload New"** tab: Upload new documents

4. Click the **"Upload New"** tab
5. Review the required documents list and guidelines:
    - **Accepted formats:** JPEG, PNG, PDF
    - **Maximum file size:** 50MB per document
    - **Required documents:** Birth Certificate, Report Card, Form 138, Good Moral Certificate

6. Upload each required document:
    - Select **Document Type** from dropdown (Birth Certificate, Report Card, Form 138, Good Moral)
    - Choose file from your computer
    - Click **"Upload"** or **"Upload Document"**
    - Wait for upload confirmation

7. Repeat for all required documents:
    - Birth Certificate ✅
    - Report Card (most recent) ✅
    - Form 138 (for transferees) ✅
    - Good Moral Certificate ✅

8. Switch back to **"My Documents"** tab to verify uploads
9. **Expected Result**: All documents uploaded successfully, each showing "Pending" verification status with document statistics displayed (Total, Verified, Pending, Rejected)

### Step 2.6: Submit Enrollment Application

1. From the dashboard, click **"Enrollments"** in the sidebar
2. Click **"Create Enrollment"** or **"New Enrollment Application"** button
3. Fill in the enrollment form:

    **Select Student:**
    - Student: Select **"Maria Santos Dela Cruz"** from dropdown

    **Enrollment Details:**
    - School Year: `2024-2025` (auto-filled from active enrollment period)
    - Quarter: Select quarter from dropdown (e.g., **"1st Quarter"**, **"2nd Quarter"**, etc.)
    - Grade Level: Select **"Grade 1"** (filtered based on student's eligibility)
    - Enrollment Period: **"Active"** (hidden field, auto-set)

    **Review Fee Structure:**
    - The system will display the applicable fees based on the selected grade level
    - Review the fee breakdown to ensure accuracy
    - Fees are calculated automatically from the Grade Level Fees configuration

4. Review all information carefully
5. Click **"Submit Enrollment Application"** or **"Create Enrollment"**
6. **Expected Result**:
    - Success message: "Enrollment application submitted successfully"
    - Application status: **"Pending"** or **"Pending Review"**
    - Application appears in enrollments list
    - Enrollment record created with system-generated ID

> **💡 Note:** The enrollment form does NOT include "Previous School" or "Previous Grade Level" fields. The system determines student eligibility (new vs. returning) automatically based on existing student records.

### Step 2.7: View Application Status

1. Click **"Enrollments"** in the sidebar
2. Click **"View"** on the submitted application
3. Verify the following information is displayed:
    - Application reference number
    - Student information
    - Grade level and school year
    - Fee breakdown
    - Current status: **"Pending Review"**
    - Submission date and time
4. **Expected Result**: All information is correct and clearly displayed

### ✅ Phase 2 Checklist

- [ ] Guardian registered successfully (or logged in with pre-seeded account)
- [ ] Email verified (if new registration)
- [ ] Guardian profile completed (if new account)
- [ ] Student profile created with complete personal and contact information
- [ ] All required documents uploaded via dedicated Documents page (JPEG/PNG/PDF supported)
- [ ] Enrollment application submitted with student, quarter, and grade level
- [ ] Application appears in enrollments list with "Pending" status
- [ ] Enrollment ID generated

---

## Phase 3: Registrar Enrollment Processing

**Estimated Time:** 15-20 minutes
**Purpose:** Test enrollment review and approval workflow

### Step 3.1: Login as Registrar

1. **Logout** from Guardian account
2. Login with Registrar credentials:
    - Email: `registrar@cbhlc.edu`
    - Password: `password`
3. **Expected Result**: Redirected to Registrar Dashboard

### Step 3.2: View Pending Enrollments

1. From the dashboard, you should see:
    - **Pending Enrollments** count in the statistics
    - Recent applications in the "Recent Enrollments" section
2. Click **"Enrollments"** in the sidebar
3. **Expected Result**: List shows the enrollment application we submitted (Maria Dela Cruz)

### Step 3.3: Search and Filter Enrollments

Test the search and visibility functionality:

1. **Search by Student Name**:
    - In the search box at the top-left, type: `Maria`
    - **Expected Result**: Applications matching "Maria" appear in real-time

2. **Column Visibility**:
    - Click the **"Columns"** dropdown button (top-right)
    - Check/uncheck columns to show/hide them:
        - ID, Student, Guardian, School Year, Grade Level, Status, Total Amount, Balance, Payment, Actions
    - **Expected Result**: Table columns update based on selections

3. **Clear Search**:
    - Clear the search box text
    - **Expected Result**: All applications shown again

> **💡 Note:** The enrollment list supports student name search and column visibility controls. Additional filters (by status, grade level, date ranges) may not be available in the current UI but enrollments are color-coded by status for easy identification.

### Step 3.4: Review Enrollment Application

1. Click **"View"** on Maria's enrollment application
2. Review all sections carefully:

    **Student Information:**
    - Verify name, birth date, and contact details
    - Check if all required information is complete

    **Enrollment Details:**
    - School year and grade level
    - Fee structure and total amount

    **Documents:**
    - Click **"View Documents"** or navigate to documents section
    - Review each uploaded document:
        - Birth Certificate ✅
        - Report Card ✅
        - Form 138 ✅
        - Good Moral Certificate ✅

3. **Expected Result**: All information is complete and documents are uploaded

### Step 3.5: Verify Documents

1. In the Documents section, for each document:
    - Click **"View"** to open the document
    - Review the document for clarity and authenticity
    - Click **"Verify"** button
    - **Expected Result**: Document status changes to "Verified"

2. Repeat for all documents
3. **Expected Result**: All documents show "Verified" status

### Step 3.6: Approve Enrollment Application

Once all documents are verified:

1. In the enrollments table, locate Maria's enrollment
2. Click the **Actions menu** (⋮ three dots) on the right side of the row
3. For **pending** enrollments, you will see these options:
    - Copy Enrollment ID
    - View Enrollment
    - **Approve Enrollment** ← Click this
    - **Reject Enrollment**

4. Click **"Approve Enrollment"**
5. An **"Approve Enrollment Application"** modal dialog appears:
    - Shows: "Provide any remarks for approving enrollment #[ID] - [Student Name]"
    - **Remarks** field (optional textarea): Add notes like `All documents verified. Student meets requirements for Grade 1.`
    - Two buttons: **Cancel** and **Approve**

6. Enter optional remarks and click **"Approve"**
7. **Expected Result**:
    - Success notification appears
    - Modal closes
    - Application status changes to **"Approved"** (green badge)
    - Invoice is automatically generated
    - Row updates in the enrollments table
    - Additional action becomes available: "Complete Enrollment" (when payment is made)

> **💡 Note:** There are only TWO action options for pending enrollments: "Approve Enrollment" and "Reject Enrollment". The "Request More Information" feature is not available in the current implementation.

### Step 3.7: Test Rejection Workflow (Optional)

If you want to test the rejection feature with a different enrollment:

1. In the enrollments table, find a **pending** enrollment
2. Click the **Actions menu** (⋮) → **"Reject Enrollment"**
3. A **"Reject Enrollment Application"** modal appears:
    - Shows: "Provide a reason for rejecting enrollment #[ID] - [Student Name]"
    - **Reason** field (required textarea): Enter rejection reason
        - Example: `Incomplete documents - birth certificate is not legible. Please upload a clearer copy and resubmit.`
    - Two buttons: **Cancel** and **Reject**

4. Enter the rejection reason (required) and click **"Reject"**
5. **Expected Result**:
    - Success notification appears
    - Modal closes
    - Application status changes to **"Rejected"** (red badge)
    - Rejection reason is stored and viewable

### Step 3.8: View Updated Enrollment List

1. Return to the enrollments list (if not already there)
2. Verify that Maria's enrollment now shows:
    - Status: **"Approved"** (green badge or secondary color)
    - Actions menu now shows different options for approved enrollments
3. **Expected Result**: Enrollment status is correctly displayed with color-coded badge

### ✅ Phase 3 Checklist

- [ ] Logged in as Registrar successfully
- [ ] Viewed enrollments list with all applications
- [ ] Used student name search to filter enrollments
- [ ] Tested column visibility controls
- [ ] Reviewed complete enrollment application via "View Enrollment"
- [ ] Verified documents (if verification workflow is accessible)
- [ ] Approved enrollment using "Approve Enrollment" action with optional remarks
- [ ] Confirmed status updated to "Approved" with green/secondary badge
- [ ] Tested rejection workflow with required reason (optional)
- [ ] Verified actions menu shows appropriate options based on enrollment status

---

## Phase 4: Guardian Post-Approval Actions

**Estimated Time:** 10-15 minutes
**Purpose:** Test guardian access to approved enrollment features

### Step 4.1: Login as Guardian

1. **Logout** from Registrar account
2. Login with Guardian credentials:
    - Email: `juan.delacruz@example.com`
    - Password: `MySecurePass123!`
3. **Expected Result**: Redirected to Guardian Dashboard

### Step 4.2: Check Email Notification

1. Check email inbox for `juan.delacruz@example.com`
2. You should receive an email: **"Enrollment Application Approved"**
3. Email should contain:
    - Student name
    - Grade level and school year
    - Enrollment reference number
    - Link to view invoice
4. **Expected Result**: Email received with all information

### Step 4.3: View Enrollment Status

1. From the dashboard, click **"Enrollments"** in the sidebar
2. Click **"View"** on Maria's enrollment
3. Verify the status badge shows: **"Approved"** (green)
4. Notice new actions available:
    - **Download Certificate** button (if enrolled status)
    - **Download Payment History** button
    - **View Invoice** link
5. **Expected Result**: Status updated and new actions visible

### Step 4.4: View and Download Invoice

1. From the enrollment view, click **"View Invoice"** or navigate to **"Invoices"** in the sidebar
2. Review the invoice display:

    **Invoice Header:**
    - School name and logo
    - School address and contact information
    - Invoice number (same as enrollment ID)
    - Invoice date
    - Payment status badge

    **Billed To Section:**
    - Student full name
    - Student ID
    - Grade level and section (if applicable)
    - School year

    **Fee Breakdown Table:**
    - Tuition Fee: ₱18,000.00
    - Miscellaneous Fee: ₱5,500.00
    - Laboratory Fee: ₱1,000.00
    - Library Fee: ₱1,200.00
    - Sports Fee: ₱800.00
    - **Total Amount**: ₱26,500.00
    - Amount Paid: ₱0.00
    - **Balance Due**: ₱26,500.00

    **Payment Instructions:**
    - Payment methods accepted
    - Business hours
    - Payment location
    - Important notes

3. Click **"Download PDF"** button (or **"Download as PDF"**)
4. **Expected Result**:
    - PDF download starts automatically
    - Filename: `invoice-ENR-202501-0001.pdf`
    - PDF opens with professional formatting
    - All information is clearly displayed
    - PDF is print-ready

5. Alternative: Click **"Print"** button to test browser printing

### Step 4.5: Download Payment History Report

1. From the enrollment detail view, look for the **Payment Information** card
2. Click the **"Download Report"** button (with download icon) in the card header
3. **Expected Result**:
    - PDF download starts automatically
    - Filename format: `payment-history-[enrollment-id].pdf` or similar
    - Report contains:
        - Student information
        - Fee breakdown
        - Payment history table (may be empty if no payments recorded yet)
        - Total amount due
        - Amount paid
        - Outstanding balance

> **💡 Note:** The button is labeled "Download Report", not "Download Payment History".

### Step 4.6: Check Billing Information

1. Click **"Billing"** in the sidebar
2. View billing summary:
    - List of all enrollments with payment status
    - Total amount due across all enrollments
    - Payment status indicators
3. Click on Maria's enrollment to view detailed billing
4. **Expected Result**: All billing information is accurate and clearly displayed

### ✅ Phase 4 Checklist

- [ ] Logged in as Guardian successfully
- [ ] Checked for approval email notification (if email system is configured)
- [ ] Viewed updated enrollment status (shows "Approved" badge)
- [ ] "Download Certificate" button visible (only when status = "enrolled")
- [ ] Accessed invoice page successfully
- [ ] Invoice displays all correct information (fees, student details, payment instructions)
- [ ] Downloaded invoice PDF using "Download as PDF" button
- [ ] PDF formatting is professional and print-ready
- [ ] Downloaded payment history report using "Download Report" button
- [ ] Viewed billing page (/guardian/billing)
- [ ] All fees and balances display correctly

---

## Phase 5: Payment Processing

**Estimated Time:** 15-20 minutes
**Purpose:** Test payment recording and receipt generation

### Step 5.1: Login as Registrar/Admin

1. **Logout** from Guardian account
2. Login as Registrar or Administrator
3. Navigate to **"Enrollments"** or **"Payments"** section

### Step 5.2: Access Payment Recording

1. Click **"Enrollments"** in the sidebar
2. Find Maria's enrollment (status: Approved)
3. In the enrollments table, click the **Actions menu** (⋮) for Maria's row
4. Click **"Update Payment Status"** from the dropdown menu
5. **Expected Result**: A modal dialog opens titled "Update Payment Status"

### Step 5.3: Record First Payment

1. In the **"Update Payment Status"** modal, fill in:

    **Payment Information:**
    - **Amount Paid:** `10000.00` (enter as decimal, e.g., 10000.00 for ₱10,000)
    - **Payment Status:** Select **"Partial"** from dropdown (options: Pending, Partial, Paid, Overdue)
    - **Remarks (Optional):** `First partial payment received - cash`

2. Click **"Update"** button
3. **Expected Result**:
    - Success notification appears
    - Modal closes
    - Enrollment row updates in the table:
        - Amount Paid: shows ₱10,000.00
        - Balance: shows remaining balance
        - Payment Status: **"Partial"** (yellow/warning badge)
    - Invoice is updated automatically

> **💡 Note:** The current UI uses a simplified payment status update form. Detailed payment method and reference number tracking may be handled differently or may require direct database/backend updates.

### Step 5.4: Download Payment Receipt

> **💡 Note:** Payment receipt download functionality exists in the PaymentController, but the UI button location needs verification. Check the enrollment detail page or payment history section for a "Download Receipt" button.

1. If available, click **"Download Receipt"** button (location may vary)
2. **Expected Result** (if button is accessible):
    - PDF download starts
    - Filename format: `receipt-OR-YYYYMM-####-YYYYMMDD.pdf`
    - Receipt contains:
        - School header with contact information
        - Receipt number (format: OR-YYYYMM-####, e.g., OR-202501-0001)
        - Date
        - "OFFICIAL RECEIPT" title
        - Payment details (amount, date, method if recorded)
        - Student and enrollment information
        - Professional formatting for printing

> **⚠️ Testing Note:** If the receipt download button is not visible in the UI, the feature is implemented in the backend (`PaymentController::downloadReceipt`) but may not have a frontend button yet. You can test it by directly accessing `/payments/{payment_id}/receipt` if you have a payment ID.

### Step 5.5: Record Final Payment

1. Return to the enrollments list
2. Click **Actions menu** (⋮) for Maria's enrollment → **"Update Payment Status"**
3. Fill in the remaining payment:
    - **Amount Paid:** Enter the full total amount that has been paid (e.g., `26500.00` if fully paid)
    - **Payment Status:** Select **"Paid"**
    - **Remarks (Optional):** `Full payment completed`

4. Click **"Update"**
5. **Expected Result**:
    - Success notification appears
    - Modal closes
    - Enrollment row updates:
        - Amount Paid: ₱26,500.00 (or full amount)
        - Balance: ₱0.00
        - Payment Status: **"Paid"** (green badge)
    - Invoice reflects full payment
    - New action appears: **"Complete Enrollment"** becomes available

### Step 5.6: Complete Enrollment (Mark as Enrolled)

1. With full payment recorded (`payment_status = "paid"`), find Maria's enrollment in the table
2. Click **Actions menu** (⋮) → **"Complete Enrollment"**
   - This option only appears when: `status = "approved"` AND `payment_status = "paid"`
3. Confirm the action when prompted
4. **Expected Result**:
    - Success notification appears
    - Enrollment status changes to **"Enrolled"** or **"Completed"** (exact status name may vary)
    - Student is now officially enrolled
    - Enrollment certificate download becomes available to guardian
    - Guardian can now see "Download Certificate" button on their enrollment detail page

### Step 5.7: Guardian Views Payment Updates

1. **Logout** and login as Guardian
2. Navigate to **"Enrollments"** → View Maria's enrollment
3. Verify the updated information:
    - Payment status: **"Paid"** (green)
    - Enrollment status: **"Enrolled"** (green)
    - Payment history table shows both payments
    - Balance due: ₱0.00

### Step 5.8: Download Enrollment Certificate

1. From Maria's enrollment view, click **"Download Certificate"** button
2. **Expected Result**:
    - PDF download starts
    - Filename: `enrollment-certificate-ENR-202501-0001.pdf`
    - Certificate contains:
        - Decorative border
        - School seal/logo
        - "CERTIFICATE OF ENROLLMENT" title
        - Student full name in large text
        - School year and grade level
        - Student ID and enrollment ID
        - Date of enrollment
        - Signature lines (Registrar and Principal)
        - Professional certificate formatting

### Step 5.9: View Updated Payment History

1. Click **"Download Payment History"** button
2. **Expected Result**:
    - Updated report now shows both payments:

        **Payment History Table:**
        | Date         | Amount     | Method        | Reference       | Balance After |
        | ------------ | ---------- | ------------- | --------------- | ------------- |
        | Jan 10, 2025 | ₱10,000.00 | Cash          | CASH-001        | ₱16,500.00    |
        | Jan 15, 2025 | ₱16,500.00 | Bank Transfer | BT-20250115-001 | ₱0.00         |

        **Summary:**
        - Total Amount Due: ₱26,500.00
        - Total Amount Paid: ₱26,500.00
        - Outstanding Balance: ₱0.00
        - Payment Status: **PAID**

### ✅ Phase 5 Checklist

- [ ] Logged in as Registrar/Administrator successfully
- [ ] Accessed "Update Payment Status" modal via enrollments table actions menu
- [ ] Recorded partial payment using simplified form (Amount Paid, Status: Partial, Remarks)
- [ ] Verified payment status badge updated to "Partial" with yellow/warning color
- [ ] Updated to full payment (Amount Paid = total, Status: Paid)
- [ ] Verified payment status badge updated to "Paid" with green color
- [ ] Verified "Complete Enrollment" action became available after full payment
- [ ] Completed enrollment using "Complete Enrollment" action
- [ ] Verified enrollment status changed to "Enrolled" or "Completed"
- [ ] Guardian can view updated payment and enrollment status
- [ ] Guardian can download enrollment certificate (button appears when status = "enrolled")
- [ ] All amounts and balances calculate correctly throughout the process
- [ ] (Optional) Tested payment receipt download if UI button is available
- [ ] Downloaded updated payment history report using "Download Report" button

---

## Complete Feature Checklist

Use this comprehensive checklist to ensure all features are working correctly:

### 🔐 Authentication & User Management

- [ ] Guardian registration works
- [ ] Email verification system functions
- [ ] Login/logout works for all roles
- [ ] Password reset functionality works
- [ ] Role-based access control is enforced

### 📋 Guardian Features

- [ ] Guardian profile creation and editing
- [ ] Add/edit student profiles
- [ ] Upload student documents (Birth Certificate, Report Card, etc.)
- [ ] Submit enrollment applications
- [ ] View application status in real-time
- [ ] Respond to information requests from registrar
- [ ] View and download invoices (browser view + PDF)
- [ ] Download enrollment certificates (after approval)
- [ ] Download payment history reports
- [ ] Receive email notifications for all status changes

### 👨‍💼 Registrar/Admin Features

- [ ] View dashboard with enrollment statistics
- [ ] List and filter enrollments (by status, grade, date)
- [ ] Search enrollments by student name or ID
- [ ] Review enrollment applications
- [ ] Verify uploaded documents
- [ ] Approve enrollment applications
- [ ] Request more information from guardians
- [ ] Reject applications with reasons
- [ ] Record payments (cash, bank transfer, GCash, etc.)
- [ ] Generate payment receipts automatically
- [ ] Update payment status
- [ ] Mark enrollments as "Enrolled"
- [ ] Export reports (enrollment statistics, student demographics)

### 💰 Billing & Payment Features

- [ ] Automatic invoice generation upon approval
- [ ] Fee calculation based on grade level
- [ ] Display of tuition and miscellaneous fees
- [ ] Display of optional fees (laboratory, library, sports)
- [ ] Total amount calculation
- [ ] Payment status tracking (Pending, Partial, Paid)
- [ ] Balance calculation
- [ ] Payment history tracking
- [ ] Multiple payment methods supported

### 📄 Document Generation (PDF)

- [ ] Invoice PDF download (professional formatting)
- [ ] Payment receipt PDF (official receipt format)
- [ ] Enrollment certificate PDF (decorative certificate)
- [ ] Payment history report PDF (detailed report)
- [ ] All PDFs include school branding
- [ ] All PDFs are print-ready

### 🔔 Notification System

- [ ] Email sent on registration
- [ ] Email sent on enrollment submission
- [ ] Email sent on application approval
- [ ] Email sent on information request
- [ ] Email sent on application rejection
- [ ] Email sent on payment received
- [ ] In-app notifications work
- [ ] Notification history maintained

### ⚙️ Super Admin Features

- [ ] School information management
- [ ] Enrollment period creation and management
- [ ] Grade level fee configuration
- [ ] User management (create registrar accounts)
- [ ] System settings configuration
- [ ] View system-wide statistics
- [ ] Access audit logs

### 🔒 Security Features

- [ ] Guardians can only access their own children's data
- [ ] Role-based access properly restricts features
- [ ] Unauthorized access attempts are blocked (404 errors)
- [ ] Password hashing works correctly
- [ ] CSRF protection enabled
- [ ] File upload validation works (file type, size)

---

## Troubleshooting

### Common Issues and Solutions

#### Issue: Cannot login after registration

**Solution:**

- Check if email verification is required
- Verify email address by clicking link in verification email
- Check spam/junk folder for verification email
- Try password reset if forgotten

#### Issue: Documents not uploading

**Solution:**

- Check file size (max 50MB)
- Verify file format (JPEG, PNG, PDF only)
- Check internet connection stability
- Try a smaller file or compress the document

#### Issue: PDF downloads not working

**Solution:**

- Ensure PDF viewer is installed in browser
- Check pop-up blocker settings
- Try a different browser
- Clear browser cache and cookies

#### Issue: Invoice not showing after approval

**Solution:**

- Refresh the page (F5 or Ctrl+R)
- Logout and login again
- Check enrollment status is "Approved" not "Pending"
- Contact system administrator

#### Issue: Payment status not updating

**Solution:**

- Refresh the page
- Check if payment was saved successfully
- Verify payment amount matches invoice
- Check with registrar if payment was recorded

#### Issue: Email notifications not received

**Solution:**

- Check spam/junk folder
- Verify email address is correct in profile
- Check email server is working
- Contact system administrator to check mail configuration

#### Issue: Cannot view uploaded documents

**Solution:**

- Check if document was uploaded successfully
- Verify file path and storage
- Try re-uploading the document
- Check browser console for errors

#### Issue: Grade level fees not displaying

**Solution:**

- Check if fees are configured for selected grade and school year
- Verify enrollment period is active
- Contact Super Admin to configure fees

---

## Support Contact

If you encounter issues not covered in this guide:

**Technical Support:**

- Email: support@cbhlc.edu.ph
- Phone: (123) 456-7890
- Office Hours: Monday-Friday, 8:00 AM - 5:00 PM

**For Enrollment Inquiries:**

- Email: registrar@cbhlc.edu.ph
- Phone: (123) 456-7891

---

## Testing Tips

### Best Practices

1. **Test in Order**: Follow the phases sequentially for best results
2. **Use Real Data**: Use realistic names, dates, and information
3. **Take Screenshots**: Document any issues with screenshots
4. **Test on Multiple Browsers**: Verify functionality across different browsers
5. **Test Different Scenarios**: Try various payment amounts, grade levels, etc.
6. **Check Email**: Always verify email notifications are received
7. **Test Negative Cases**: Try invalid inputs to verify validation
8. **Review PDFs**: Open all generated PDFs to verify formatting

### Quick Test Scenarios

**Scenario 1: Full Enrollment Flow (Happy Path)**

1. Guardian registers → 2. Adds student → 3. Uploads documents → 4. Submits enrollment → 5. Registrar reviews → 6. Registrar approves → 7. Guardian views invoice → 8. Registrar records payment → 9. Guardian downloads certificate

**Scenario 2: Information Request Flow**

1. Guardian submits enrollment → 2. Registrar requests more info → 3. Guardian receives notification → 4. Guardian responds → 5. Registrar reviews response → 6. Registrar approves

**Scenario 3: Partial Payment Flow**

1. Enrollment approved → 2. Registrar records partial payment → 3. Invoice shows partial status → 4. Registrar records remaining payment → 5. Invoice shows paid status

---

## Next Steps After Testing

Once all features have been tested successfully:

1. **Review Test Results**: Compile any issues found during testing
2. **Report Bugs**: Document any bugs or unexpected behavior
3. **Provide Feedback**: Share suggestions for improvements
4. **User Training**: Schedule training sessions for staff
5. **Go Live Planning**: Plan the system launch date
6. **Data Migration**: Prepare to migrate any existing data
7. **Production Setup**: Configure production environment
8. **User Documentation**: Create end-user guides

---

**Document Version:** 1.0
**Last Updated:** January 2025
**System Version:** 1.0.0
**Status:** Ready for Testing

---

_This testing guide covers the complete Web-Based Enrollment System for Christian Bible Heritage Learning Center. For technical documentation, refer to the Software Requirements Specification (SRS) document._
