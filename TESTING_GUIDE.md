# CBHLC Enrollment System - Testing Guide

**Christian Bible Heritage Learning Center**
**Web-Based Enrollment System**
**Version:** 1.0
**Last Updated:** January 2025

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

- **Application URL**: https://your-domain.com (or http://128.199.161.224 for staging)
- **Email Client**: Access to email for verification links and notifications

---

## Testing Account Access

### Default Test Accounts

The system comes with pre-configured test accounts. If you need to create new accounts, follow the registration process.

#### Super Admin Account

- **Email**: superadmin@cbhlc.test
- **Password**: password
- **Role**: Complete system access

#### Registrar Account

- **Email**: registrar@cbhlc.test
- **Password**: password
- **Role**: Enrollment processing and student management

#### Guardian Account (Example)

- **Email**: guardian@example.com
- **Password**: password
- **Role**: Submit enrollments, view invoices

> **Note**: Change all default passwords in production!

---

## Phase 1: Super Admin Setup

**Estimated Time:** 15-20 minutes
**Purpose:** Configure the system for enrollment processing

### Step 1.1: Login as Super Admin

1. Navigate to the application URL
2. Click **"Login"** button in the navigation bar
3. Enter Super Admin credentials:
    - Email: `superadmin@cbhlc.test`
    - Password: `password`
4. Click **"Sign In"**
5. **Expected Result**: You should be redirected to the Super Admin Dashboard

### Step 1.2: Configure School Information

1. From the dashboard, click **"School Information"** in the sidebar
2. Review and update the following fields:
    - School Name: `Christian Bible Heritage Learning Center`
    - School Address: `Lantapan, Bukidnon`
    - School Phone: `(123) 456-7890`
    - School Email: `info@cbhlc.edu.ph`
3. Scroll down to **Payment Information** section:
    - Payment Methods: `Cash, Bank Transfer, GCash`
    - Payment Location: `School Cashier Office`
    - Payment Hours: `Monday-Friday, 8:00 AM - 5:00 PM`
    - Payment Note: `Please bring a valid ID when making payments`
4. Click **"Save Changes"**
5. **Expected Result**: Success message "School information updated successfully"

### Step 1.3: Create Enrollment Period

1. Click **"Enrollment Periods"** in the sidebar
2. Click **"Create Enrollment Period"** button
3. Fill in the form:
    - School Year: `2024-2025`
    - Start Date: Select today's date (or desired start date)
    - End Date: Select a date 3-6 months in the future
    - Early Registration Deadline: 2 weeks from start date
    - Regular Registration Deadline: 1 month from start date
    - Late Registration Deadline: 2 months from start date
4. Click **"Create Enrollment Period"**
5. **Expected Result**: New enrollment period appears in the list with status "Active"

### Step 1.4: Configure Grade Level Fees

1. Click **"Grade Level Fees"** in the sidebar
2. Click **"Create Grade Level Fee"** button
3. For **Kindergarten**, enter:
    - Grade Level: `Kindergarten`
    - School Year: `2024-2025`
    - Tuition Fee: `15,000.00`
    - Miscellaneous Fee: `5,000.00`
    - Laboratory Fee: `0.00`
    - Library Fee: `1,000.00`
    - Sports Fee: `500.00`
    - Status: Active ✅
4. Click **"Create"**
5. Repeat for other grade levels (Grade 1-6):
    - **Grade 1-3**: Tuition: `18,000`, Misc: `5,500`, Lab: `1,000`, Library: `1,200`, Sports: `800`
    - **Grade 4-6**: Tuition: `20,000`, Misc: `6,000`, Lab: `1,500`, Library: `1,500`, Sports: `1,000`
6. **Expected Result**: All grade level fees are listed and active

### Step 1.5: Create Registrar Account (Optional)

If you need additional registrar accounts:

1. Click **"Users"** in the sidebar
2. Click **"Create User"** button
3. Fill in the form:
    - Name: `Maria Santos`
    - Email: `msantos@cbhlc.edu.ph`
    - Role: Select **"Registrar"**
    - Password: `TempPassword123!`
4. Click **"Create User"**
5. **Expected Result**: New registrar account created successfully

### ✅ Phase 1 Checklist

- [ ] Logged in as Super Admin successfully
- [ ] School information configured
- [ ] Active enrollment period created
- [ ] Grade level fees configured for all levels
- [ ] Registrar accounts created (if needed)

---

## Phase 2: Guardian Registration & Enrollment

**Estimated Time:** 20-25 minutes
**Purpose:** Test the complete guardian enrollment workflow

### Step 2.1: Guardian Registration

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
    - Middle Name: `Santos`
    - Last Name: `Dela Cruz`
    - Birth Date: `01/15/2015` (adjust for appropriate age)
    - Gender: `Female`
    - Birth Place: `Lantapan, Bukidnon`
    - Nationality: `Filipino`
    - Religion: `Roman Catholic`

    **Contact Information:**
    - Address: `123 Main Street, Lantapan, Bukidnon`
    - Phone: `09171234567`
    - Email: `maria.delacruz@example.com` (optional)

4. Click **"Save Student"** or **"Create Student"**
5. **Expected Result**: Student profile created, student appears in the list

### Step 2.5: Upload Student Documents

1. From the Students list, click **"View"** or **"Documents"** for Maria
2. Click **"Upload Document"** button
3. Upload **Birth Certificate**:
    - Document Type: Select **"Birth Certificate"**
    - File: Choose a PDF or image file (max 50MB)
    - Click **"Upload"**
4. Repeat for other required documents:
    - Report Card (previous school year)
    - Form 138 (if transferee)
    - Good Moral Certificate
5. **Expected Result**: All documents uploaded successfully, status shows "Pending Verification"

### Step 2.6: Submit Enrollment Application

1. From the dashboard, click **"Enrollments"** in the sidebar
2. Click **"Create Enrollment"** or **"New Enrollment Application"** button
3. Fill in the enrollment form:

    **Select Student:**
    - Student: Select **"Maria Santos Dela Cruz"**

    **Enrollment Details:**
    - School Year: `2024-2025`
    - Grade Level: Select **"Grade 1"** (or appropriate level)
    - Previous School: `ABC Elementary School` (if transferee)
    - Previous Grade Level: `Kindergarten` (if applicable)

    **Confirm Fee Structure:**
    - Review the displayed fees (auto-calculated from grade level fees)
    - Tuition Fee: ₱18,000.00
    - Miscellaneous Fee: ₱5,500.00
    - Laboratory Fee: ₱1,000.00
    - Library Fee: ₱1,200.00
    - Sports Fee: ₱800.00
    - **Total**: ₱26,500.00

4. Review the information carefully
5. Check the box: **"I confirm that all information provided is accurate"**
6. Click **"Submit Enrollment Application"**
7. **Expected Result**:
    - Success message: "Enrollment application submitted successfully"
    - Application status: **"Pending Review"**
    - Email notification sent
    - Application appears in enrollments list with reference number (e.g., `ENR-202501-0001`)

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

- [ ] Guardian registered successfully
- [ ] Email verified
- [ ] Guardian profile completed
- [ ] Student profile created with complete information
- [ ] All required documents uploaded
- [ ] Enrollment application submitted successfully
- [ ] Application appears in enrollments list
- [ ] Application reference number generated
- [ ] Email notification received

---

## Phase 3: Registrar Enrollment Processing

**Estimated Time:** 15-20 minutes
**Purpose:** Test enrollment review and approval workflow

### Step 3.1: Login as Registrar

1. **Logout** from Guardian account
2. Login with Registrar credentials:
    - Email: `registrar@cbhlc.test`
    - Password: `password`
3. **Expected Result**: Redirected to Registrar Dashboard

### Step 3.2: View Pending Enrollments

1. From the dashboard, you should see:
    - **Pending Enrollments** count in the statistics
    - Recent applications in the "Recent Enrollments" section
2. Click **"Enrollments"** in the sidebar
3. **Expected Result**: List shows the enrollment application we submitted (Maria Dela Cruz)

### Step 3.3: Filter and Search Enrollments

Test the filtering functionality:

1. **Filter by Status**:
    - Click the **"Status"** dropdown
    - Select **"Pending"**
    - **Expected Result**: Only pending applications are shown

2. **Search by Student Name**:
    - In the search box, type: `Maria`
    - **Expected Result**: Application for Maria Dela Cruz appears

3. **Filter by Grade Level**:
    - Click **"Grade Level"** dropdown
    - Select **"Grade 1"**
    - **Expected Result**: Only Grade 1 applications shown

4. **Clear Filters**:
    - Click **"Clear Filters"** or reset button
    - **Expected Result**: All applications shown again

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

1. Go back to the enrollment application view
2. Scroll down to the **Actions** section
3. You should see three options:
    - **Approve** (green button)
    - **Request More Information** (yellow button)
    - **Reject** (red button)

4. Click **"Approve"** button
5. A confirmation dialog appears:
    - Review the student and fee information
    - Optionally add approval notes: `All documents verified. Student meets all requirements for Grade 1.`
6. Click **"Confirm Approval"**
7. **Expected Result**:
    - Success message: "Enrollment approved successfully"
    - Application status changes to **"Approved"**
    - Invoice is automatically generated
    - Email notification sent to guardian
    - Application now shows enrollment ID (e.g., `ENR-202501-0001`)

### Step 3.7: Test "Request More Information" Feature (Optional)

If you want to test this feature, create another enrollment application and:

1. View the application
2. Click **"Request More Information"** button
3. Fill in the request form:
    - Information Needed: `Please upload a clearer copy of the birth certificate`
    - Additional Notes: `The current document is difficult to read`
4. Click **"Submit Request"**
5. **Expected Result**:
    - Application status: **"Information Requested"**
    - Guardian receives email notification
    - Guardian can respond to the request from their dashboard

### Step 3.8: View Approved Enrollments

1. Click **"Enrollments"** in the sidebar
2. Filter by status: **"Approved"**
3. **Expected Result**: Maria's enrollment appears with "Approved" status

### ✅ Phase 3 Checklist

- [ ] Logged in as Registrar successfully
- [ ] Viewed pending enrollments
- [ ] Tested filtering and search functionality
- [ ] Reviewed complete enrollment application
- [ ] Verified all uploaded documents
- [ ] Approved enrollment successfully
- [ ] Invoice generated automatically
- [ ] Email notification sent to guardian
- [ ] Application status updated correctly

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

1. From the enrollment view, click **"Download Payment History"** button
2. **Expected Result**:
    - PDF download starts
    - Filename: `payment-history-ENR-202501-0001.pdf`
    - Report shows:
        - Student information
        - Fee breakdown
        - Payment history table (empty if no payments yet)
        - Total amount due
        - Amount paid: ₱0.00
        - Outstanding balance: ₱26,500.00

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
- [ ] Received approval email notification
- [ ] Viewed updated enrollment status
- [ ] Accessed invoice successfully
- [ ] Invoice displays all correct information
- [ ] Downloaded invoice PDF successfully
- [ ] PDF formatting is professional and clear
- [ ] Downloaded payment history report
- [ ] Viewed billing information
- [ ] All fees and balances are correct

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
3. Click **"View"** on the enrollment
4. Look for **"Record Payment"** button or **"Add Payment"** option
5. Click the payment button

### Step 5.3: Record First Payment

1. Fill in the payment form:

    **Payment Information:**
    - Amount: `10,000.00` (partial payment)
    - Payment Date: Select today's date
    - Payment Method: Select **"Cash"**
    - Reference Number: `CASH-001` (optional for cash payments)
    - Notes: `First installment payment`

2. Click **"Record Payment"** or **"Submit"**
3. **Expected Result**:
    - Success message displayed
    - Payment recorded successfully
    - Receipt number generated (e.g., `RCP-202501-0001`)
    - Invoice updated automatically:
        - Amount Paid: ₱10,000.00
        - Balance: ₱16,500.00
        - Payment Status: **"Partial"** (yellow badge)

### Step 5.4: Download Payment Receipt

1. After recording payment, click **"Download Receipt"** button
2. **Expected Result**:
    - PDF download starts
    - Filename: `receipt-RCP-202501-0001.pdf`
    - Receipt contains:
        - School header with logo
        - Receipt number and date
        - "OFFICIAL RECEIPT" title
        - Received from: Juan Santos Dela Cruz
        - Student name and details
        - Amount paid: ₱10,000.00
        - Payment method and reference
        - Received by: [Registrar/Admin name]
        - Professional formatting

### Step 5.5: Record Second Payment

1. Go back to Maria's enrollment
2. Click **"Record Payment"** again
3. Fill in the second payment:
    - Amount: `16,500.00` (remaining balance)
    - Payment Date: Select a date (can be future date for scheduled payment)
    - Payment Method: Select **"Bank Transfer"**
    - Reference Number: `BT-20250115-001`
    - Notes: `Full payment - bank transfer`
4. Click **"Record Payment"**
5. **Expected Result**:
    - Payment recorded
    - New receipt generated
    - Invoice updated:
        - Amount Paid: ₱26,500.00
        - Balance: ₱0.00
        - Payment Status: **"Paid"** (green badge)

### Step 5.6: Update Enrollment to "Enrolled" Status

1. With full payment recorded, the enrollment can be moved to "Enrolled" status
2. Click **"Complete Enrollment"** or **"Mark as Enrolled"** button
3. Confirm the action
4. **Expected Result**:
    - Enrollment status: **"Enrolled"**
    - Student is now officially enrolled
    - Enrollment certificate becomes available

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
        | Date | Amount | Method | Reference | Balance After |
        |------|--------|--------|-----------|---------------|
        | Jan 10, 2025 | ₱10,000.00 | Cash | CASH-001 | ₱16,500.00 |
        | Jan 15, 2025 | ₱16,500.00 | Bank Transfer | BT-20250115-001 | ₱0.00 |

        **Summary:**
        - Total Amount Due: ₱26,500.00
        - Total Amount Paid: ₱26,500.00
        - Outstanding Balance: ₱0.00
        - Payment Status: **PAID**

### ✅ Phase 5 Checklist

- [ ] Logged in as Registrar/Admin
- [ ] Recorded first payment successfully
- [ ] Payment receipt generated automatically
- [ ] Downloaded payment receipt PDF
- [ ] Receipt contains all correct information
- [ ] Recorded second payment (full payment)
- [ ] Invoice updated to "Paid" status
- [ ] Enrollment status updated to "Enrolled"
- [ ] Guardian can view updated payment status
- [ ] Downloaded enrollment certificate
- [ ] Certificate formatting is professional
- [ ] Downloaded updated payment history report
- [ ] All payments are recorded correctly

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
