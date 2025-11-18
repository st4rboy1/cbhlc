# CBHLC Enrollment System - Models Architecture

**Date:** October 28, 2025
**Total Models:** 18

---

## Core Models Overview

### 1. **User** (`app/Models/User.php`)

**Purpose:** Authentication and account management for all system users

**Key Features:**

- Extends Laravel's `Authenticatable`
- Implements `MustVerifyEmail` for email verification
- Uses Spatie Permission (`HasRoles` trait) for role-based access
- Activity logging via Spatie Activitylog
- Notification preferences management

**Relationships:**

- `hasOne` Guardian (for guardian users)
- `hasOne` Student (for student users)
- `hasMany` NotificationPreferences

**Key Methods:**

- `getDashboardRoute()` - Returns role-specific dashboard route
- `shouldReceiveNotification()` - Checks notification preferences
- `sendEmailVerificationNotification()` - Custom email verification

**Roles:** super_admin, administrator, registrar, guardian, student

---

### 2. **Student** (`app/Models/Student.php`)

**Purpose:** Stores student information and academic records

**Key Attributes:**

- `student_id` - Unique student identifier (e.g., CBHLC20250001)
- Personal info: first_name, middle_name, last_name, birthdate, gender
- Contact: address, contact_number, email
- Academic: grade_level, section
- Additional: birth_place, nationality, religion

**Relationships:**

- `belongsTo` User (optional - if student has login)
- `belongsTo` Guardian (primary guardian)
- `belongsToMany` Guardians (through GuardianStudent pivot)
- `hasMany` Enrollments
- `hasMany` Documents

**Key Features:**

- Activity logging for all changes
- Full name cast using FullNameCast
- GradeLevel enum cast
- Age calculation from birthdate

---

### 3. **Guardian** (`app/Models/Guardian.php`)

**Purpose:** Parent/Guardian information managing student accounts

**Key Attributes:**

- Personal info: first_name, middle_name, last_name
- Contact: contact_number, address
- Professional: occupation, employer
- Emergency contact: emergency_contact_name, emergency_contact_phone, emergency_contact_relationship

**Note:** Guardian email is stored in the linked User model, not in the guardians table.

**Relationships:**

- `belongsTo` User (linked to user account)
- `belongsToMany` Students (through GuardianStudent pivot)
- `hasMany` Enrollments (for their children)

**Key Features:**

- Activity logging
- Can manage multiple students
- Primary contact designation

---

### 4. **GuardianStudent** (`app/Models/GuardianStudent.php`)

**Purpose:** Pivot table managing many-to-many relationship between Guardians and Students

**Key Attributes:**

- `guardian_id`
- `student_id`
- `relationship_type` (father, mother, legal_guardian, etc.)
- `is_primary_contact` - Designates primary guardian

**Purpose:**

- Allows students to have multiple guardians
- Tracks relationship type
- Designates primary contact for communications

---

### 5. **Enrollment** (`app/Models/Enrollment.php`)

**Purpose:** Tracks student enrollment applications for school years

**Key Attributes:**

- `enrollment_id` - Unique identifier (e.g., ENR-202501-0001)
- `student_id`, `guardian_id` (FK to users), `school_year_id`, `school_year`, `enrollment_period_id`
- `grade_level` - Grade enrolling in
- `section`, `adviser` - Class assignment
- `quarter` - Quarter of entry
- `type` - Enum: new, continuing, returnee, transferee (default: 'new')
- `previous_school` - For transfers
- `payment_plan` - Enum: annual, semestral, monthly (default: 'monthly')
- `status` - Enum: pending, approved, rejected, enrolled (default: 'pending')
- Financial (all stored in cents):
    - `tuition_fee_cents`, `miscellaneous_fee_cents`, `laboratory_fee_cents`, `library_fee_cents`, `sports_fee_cents`
    - `total_amount_cents`, `discount_cents`, `net_amount_cents`
    - `invoice_id`, `payment_reference`
- Payment tracking (amounts in cents):
    - `payment_status` - Enum: pending, partial, paid, overdue (default: 'pending')
    - `amount_paid_cents`, `balance_cents`, `payment_due_date`
- Workflow timestamps:
    - `approved_at`, `rejected_at`, `ready_for_payment_at`, `paid_at`
    - `approved_by` (FK to users)
- Information request workflow:
    - `info_requested`, `info_request_message`, `info_request_date`, `info_requested_by`
    - `info_response_message`, `info_response_date`
- `remarks` - Additional notes/rejection reason

**Note:** All monetary amounts are stored in cents using the money gem pattern for precision.

**Relationships:**

- `belongsTo` Student
- `belongsTo` Guardian (through users table)
- `belongsTo` SchoolYear
- `belongsTo` EnrollmentPeriod
- `belongsTo` User (approved_by)
- `belongsTo` User (info_requested_by)
- `belongsTo` Invoice
- `hasMany` Invoices
- `hasManyThrough` Payments (through Invoices)

**Key Features:**

- Activity logging
- Enum casts for status fields
- Money gem pattern for all financial fields (\_cents suffix)
- Fee calculations
- Comprehensive approval workflow tracking
- Information request/response workflow
- Multiple enrollment types support
- Flexible payment plans

---

### 6. **EnrollmentPeriod** (`app/Models/EnrollmentPeriod.php`)

**Purpose:** Defines enrollment windows and registration deadlines

**Key Attributes:**

- `school_year` - String format (e.g., "2025-2026")
- `start_date`, `end_date`
- `early_registration_deadline`
- `regular_registration_deadline`
- `late_registration_deadline`
- `status` - Enum: upcoming, active, closed (default: 'upcoming')
- `description` - Period description
- `allow_new_students` - Boolean (default: true)
- `allow_returning_students` - Boolean (default: true)

**Note:** Uses `school_year` string instead of foreign key relationship. Status field replaces `is_active` boolean.

**Relationships:**

- `hasMany` Enrollments
- `hasMany` GradeLevelFees

**Key Features:**

- Status-based enrollment control
- Date-based deadline management
- Separate flags for new vs returning students
- `isOpen()` method checks if accepting enrollments
- `getDaysRemaining()` calculates time left

---

### 7. **SchoolYear** (`app/Models/SchoolYear.php`)

**Purpose:** Academic year configuration

**Key Attributes:**

- `name` - e.g., "2024-2025"
- `start_year`, `end_year` - Integer years
- `start_date`, `end_date` - Full dates
- `status` - Enum: upcoming, current, completed
- `is_active` - Boolean for current year

**Relationships:**

- `hasMany` EnrollmentPeriods
- `hasMany` Enrollments
- `hasMany` GradeLevelFees

**Key Features:**

- Only one active school year at a time
- Auto-generates name from years

---

### 8. **GradeLevelFee** (`app/Models/GradeLevelFee.php`)

**Purpose:** Fee structure per grade level per school year

**Key Attributes:**

- `grade_level` - Enum
- `school_year` - String format (e.g., "2025-2026")
- Financial (all stored in cents):
    - `tuition_fee_cents`
    - `registration_fee_cents`
    - `miscellaneous_fee_cents`
    - `laboratory_fee_cents`
    - `library_fee_cents`
    - `sports_fee_cents`
- `is_active` - Boolean (default: true)

**Note:** All monetary amounts use the money gem pattern (\_cents suffix). Total should be calculated via accessor method, not stored. Uses `school_year` string instead of `enrollment_period_id`.

**Relationships:**

- Can be related to EnrollmentPeriod through school_year string matching

**Key Features:**

- Money gem pattern for all financial fields
- Comprehensive fee breakdown by type
- Unique constraint per grade_level + school_year
- Total should be calculated dynamically via accessor
- Activity logging

---

### 9. **Invoice** (`app/Models/Invoice.php`)

**Purpose:** Billing documents for enrollments

**Key Attributes:**

- `invoice_number` - Unique identifier
- `enrollment_id` (FK)
- `total_amount` - Decimal(10,2)
- `paid_amount` - Decimal(10,2), default 0
- `due_date` - Date (nullable)
- `paid_at` - Timestamp (nullable)
- `status` - Enum: draft, sent, partially_paid, paid, overdue, cancelled (default: 'draft')
- `notes` - Text (nullable)

**Note:** Invoice date uses `created_at` timestamp. Balance is calculated as `total_amount - paid_amount`. No `subtotal` or `tax_amount` fields - system uses simple total.

**Relationships:**

- `belongsTo` Enrollment
- `hasMany` InvoiceItems
- `hasMany` Payments
- `hasMany` Receipts

**Key Features:**

- Auto-generates invoice numbers
- Decimal precision for monetary values
- Tracks payment status including partially_paid
- `paid_at` timestamp tracking
- PDF generation capability
- Balance calculated dynamically

---

### 10. **InvoiceItem** (`app/Models/InvoiceItem.php`)

**Purpose:** Line items in invoices (tuition, misc fees, etc.)

**Key Attributes:**

- `invoice_id`
- `description` - Fee description
- `quantity` - Usually 1
- `unit_price`
- `amount` - quantity \* unit_price

**Relationships:**

- `belongsTo` Invoice

**Key Features:**

- Simple line item tracking
- Amount calculation
- Decimal precision for monetary values

---

### 11. **Payment** (`app/Models/Payment.php`)

**Purpose:** Records payments made toward enrollments

**Key Attributes:**

- `invoice_id` (FK)
- `amount` - Decimal(10,2)
- `payment_method` - Enum: cash, bank_transfer, gcash
- `payment_date` - Date
- `reference_number` - External reference (bank ref, etc.) - nullable
- `receipt_number` - Generated receipt number - nullable, unique
- `notes` - Text (nullable)
- `processed_by` - User ID who recorded payment (FK, nullable)

**Note:** No separate `payment_reference`, `enrollment_id`, or `payment_status` fields. Enrollment accessed through Invoice relationship. Payment status is implicit (all records are completed payments).

**Relationships:**

- `belongsTo` Invoice
- `hasOneThrough` Enrollment (through Invoice)
- `belongsTo` User (processedBy)
- `hasOne` Receipt

**Key Features:**

- Activity logging
- Multiple payment methods supported
- Tracks who processed payment
- Links to receipt number
- Decimal precision for amount
- All payments are completed (no pending status)

---

### 12. **Receipt** (`app/Models/Receipt.php`)

**Purpose:** Official payment receipts (OR - Official Receipt)

**Key Attributes:**

- `receipt_number` - Format: OR-YYYYMM-#### (e.g., OR-202501-0001)
- `payment_id`, `invoice_id`
- `amount`
- `receipt_date`
- `received_by` - User ID
- `notes`

**Relationships:**

- `belongsTo` Payment
- `belongsTo` Invoice
- `belongsTo` User (receivedBy)

**Key Features:**

- Auto-generates sequential receipt numbers
- PDF generation capability
- Official receipt format for accounting

---

### 13. **Document** (`app/Models/Document.php`)

**Purpose:** Uploaded student documents (birth certificates, report cards, etc.)

**Key Attributes:**

- `student_id` (FK)
- `document_type` - Enum: birth_certificate, report_card, form_138, good_moral, other
- `original_filename`, `stored_filename`
- `file_path` - Storage path
- `file_size` - Unsigned big integer (bytes), `mime_type`
- `upload_date` - Timestamp (default: current)
- `verification_status` - Enum: pending, verified, rejected (default: 'pending')
- `verified_by` - User ID (FK, nullable)
- `verified_at` - Timestamp (nullable)
- `rejection_reason` - Text (nullable)
- `deleted_at` - Soft deletes timestamp

**Note:** Uses Laravel soft deletes for document recovery. Upload date defaults to current timestamp.

**Relationships:**

- `belongsTo` Student
- `belongsTo` User (verifier)

**Key Features:**

- Secure file storage
- Verification workflow
- Multiple document types per student
- Activity logging
- Soft deletes for recovery
- Comprehensive indexes for performance

---

### 14. **SchoolInformation** (`app/Models/SchoolInformation.php`)

**Purpose:** School contact information and settings

**Key Attributes:**

- Contact: school_name, school_email, school_phone, school_mobile, school_address
- Hours: weekday_hours, saturday_hours, sunday_hours
- Social: facebook_url, instagram_url, youtube_url
- About: school_tagline, school_description
- Payment: payment_methods, payment_location, payment_hours

**Relationships:**

- Singleton pattern (only one record)

**Key Features:**

- Used in invoices, receipts, public pages
- Centralized school information management

---

### 15. **Setting** (`app/Models/Setting.php`)

**Purpose:** Key-value configuration storage

**Key Attributes:**

- `key` - Setting identifier
- `value` - Setting value (can be JSON)
- `description` - What the setting does
- `type` - data type (string, boolean, json, etc.)

**Key Features:**

- Flexible configuration system
- Cached for performance
- Type casting support

---

### 16. **NotificationPreference** (`app/Models/NotificationPreference.php`)

**Purpose:** User notification channel preferences

**Key Attributes:**

- `user_id`
- `notification_type` - Type of notification
- `email_enabled` - Boolean
- `database_enabled` - Boolean

**Relationships:**

- `belongsTo` User

**Key Features:**

- Per-notification-type preferences
- Multi-channel control (email, database)
- Default preferences created on user creation

**Available Types:**

- enrollment_submitted
- enrollment_approved
- enrollment_rejected
- payment_received
- document_verified
- document_rejected

---

### 17. **PaymentReminder** (`app/Models/PaymentReminder.php`)

**Purpose:** Tracks payment reminder emails sent

**Key Attributes:**

- `enrollment_id`
- `sent_at` - When reminder was sent
- `email_opened_at` - Email tracking (if implemented)

**Relationships:**

- `belongsTo` Enrollment

**Key Features:**

- Prevents duplicate reminders
- Email tracking capability
- No timestamps (uses sent_at)

---

### 18. **EmailVerificationEvent** (`app/Models/EmailVerificationEvent.php`)

**Purpose:** Tracks email verification attempts and success

**Key Attributes:**

- `user_id`
- `email`
- `verified_at`
- `ip_address`
- `user_agent`

**Relationships:**

- `belongsTo` User

**Key Features:**

- Security tracking
- Verification audit trail
- IP and browser tracking

---

## Model Relationships Summary

### User → Guardian → Students → Enrollments → Invoices → Payments → Receipts

**Flow:**

1. User registers and creates Guardian profile
2. Guardian adds Students
3. Guardian submits Enrollments for Students
4. Registrar reviews and approves Enrollments
5. System generates Invoices for approved Enrollments
6. Payments are recorded against Invoices
7. Receipts are generated for Payments

### Supporting Models:

- **SchoolYear & EnrollmentPeriod** - Define when enrollments can happen
- **GradeLevelFee** - Determines how much to charge
- **Document** - Required documents for enrollment
- **SchoolInformation & Setting** - System configuration

---

## Key Design Patterns

### 1. **Activity Logging (Spatie Activitylog)**

Used by: User, Student, Guardian, Enrollment, Document, GradeLevelFee

Provides audit trail for all changes to critical models.

### 2. **Enum Casts**

Used extensively for type safety:

- EnrollmentStatus, PaymentStatus
- GradeLevel
- DocumentType
- Quarter, RelationshipType

### 3. **Observer Pattern**

- EnrollmentObserver: Handles enrollment workflow (ID generation, fee calculation, notifications)
- StudentObserver: Generates student IDs, activity logging

### 4. **Soft Deletes**

Not currently implemented but recommended for:

- Students
- Guardians
- Enrollments (for data retention)

### 5. **Pivot Models**

- GuardianStudent: Rich pivot with relationship_type and is_primary_contact

---

## Database Normalization

The system follows **3NF (Third Normal Form)**:

- No repeating groups
- All non-key attributes depend on the primary key
- No transitive dependencies

**Example:** Fees are stored in GradeLevelFee table, not duplicated in each Enrollment record.

---

## Missing Models (Potential Future Additions)

Based on typical school systems, these models might be needed:

1. **Subject** - For curriculum management
2. **Teacher** - Faculty management
3. **Class/Section** - Class assignments
4. **Grade** - Academic grades/marks
5. **Attendance** - Attendance tracking
6. **Announcement** - School announcements
7. **Calendar/Event** - School events

---

This architecture provides a solid foundation for the enrollment system with clear separation of concerns and good relationship modeling.
