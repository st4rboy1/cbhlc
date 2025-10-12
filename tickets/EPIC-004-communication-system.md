# Ticket #004: Communication and Announcement System

## Priority: Low (Won't Have Initially)

## Related SRS Requirements

- **FR-8.1:** System shall provide inquiry form for parent-school communication
- **FR-8.2:** System shall display school contact information
- **FR-8.3:** System shall show school office hours
- **FR-8.4:** System shall support announcement broadcasting
- **FR-8.5:** System shall maintain communication history
- **Section 3.8:** Communication Module

## Current Status

❌ **NOT IMPLEMENTED**

No communication system exists:

- No inquiry/contact form
- No announcement system
- No communication history
- School contact info may be hardcoded or missing

## Required Implementation

### 1. Database Layer

#### Announcements Table

Create migration: `create_announcements_table.php`

```php
Schema::create('announcements', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('content');
    $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
    $table->enum('target_audience', ['all', 'guardians', 'students', 'staff'])->default('all');
    $table->json('target_grade_levels')->nullable(); // Specific grades
    $table->boolean('is_published')->default(false);
    $table->timestamp('published_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
    $table->boolean('send_email')->default(false);
    $table->boolean('send_notification')->default(true);
    $table->timestamps();
    $table->softDeletes();
});
```

#### Inquiries Table

Create migration: `create_inquiries_table.php`

```php
Schema::create('inquiries', function (Blueprint $table) {
    $table->id();
    $table->string('inquiry_number')->unique();
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
    $table->string('name'); // For non-authenticated users
    $table->string('email');
    $table->string('phone')->nullable();
    $table->enum('category', [
        'enrollment',
        'billing',
        'technical',
        'general',
        'complaint',
        'suggestion'
    ]);
    $table->string('subject');
    $table->text('message');
    $table->enum('status', ['new', 'in_progress', 'resolved', 'closed'])->default('new');
    $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
    $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
    $table->timestamp('resolved_at')->nullable();
    $table->timestamps();
});
```

#### Inquiry Responses Table

```php
Schema::create('inquiry_responses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('inquiry_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->text('message');
    $table->boolean('is_internal_note')->default(false);
    $table->timestamps();
});
```

#### School Information Table

```php
Schema::create('school_information', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->text('value')->nullable();
    $table->string('type')->default('text'); // text, email, phone, time, json
    $table->string('group')->default('general'); // contact, hours, social, etc.
    $table->string('label');
    $table->text('description')->nullable();
    $table->timestamps();
});
```

### 2. Model Layer

**Models to Create:**

- `App\Models\Announcement`
- `App\Models\Inquiry`
- `App\Models\InquiryResponse`
- `App\Models\SchoolInformation`

**Key Methods:**

**Announcement Model:**

- `publish()` - Publish announcement
- `unpublish()` - Unpublish announcement
- `isActive()` - Check if currently active
- `scopeActive()` - Get active announcements
- `scopeForAudience()` - Filter by audience
- `sendNotifications()` - Send to target users

**Inquiry Model:**

- `assign()` - Assign to staff member
- `resolve()` - Mark as resolved
- `addResponse()` - Add response
- `generateInquiryNumber()` - Generate unique number

### 3. Backend Layer

**Controllers:**

#### SuperAdmin/AnnouncementController

- Full CRUD for announcements
- Publish/unpublish
- Schedule announcements
- Target specific audiences

#### Registrar/InquiryController

- View all inquiries
- Assign inquiries
- Respond to inquiries
- Mark as resolved

#### Guardian/InquiryController

- Create inquiry
- View own inquiries
- Add follow-up messages

#### Public/ContactController

- Display contact information
- Submit inquiry (guest or authenticated)

#### SuperAdmin/SchoolInformationController

- Manage school information
- Update contact details
- Set office hours

**Routes:**

```php
// Super Admin - Announcements
Route::resource('super-admin.announcements', SuperAdminAnnouncementController::class);
Route::post('/super-admin/announcements/{announcement}/publish', 'publish');
Route::post('/super-admin/announcements/{announcement}/unpublish', 'unpublish');

// Registrar - Inquiries
Route::resource('registrar.inquiries', RegistrarInquiryController::class)->only(['index', 'show']);
Route::post('/registrar/inquiries/{inquiry}/assign', 'assign');
Route::post('/registrar/inquiries/{inquiry}/respond', 'respond');
Route::post('/registrar/inquiries/{inquiry}/resolve', 'resolve');

// Guardian - Inquiries
Route::resource('guardian.inquiries', GuardianInquiryController::class);

// Public
Route::get('/contact', [PublicContactController::class, 'index'])->name('contact');
Route::post('/contact', [PublicContactController::class, 'submit'])->name('contact.submit');
```

### 4. Frontend Layer

**Public Pages:**

- `/resources/js/pages/public/contact.tsx` - Contact page with form and info
- Display school contact information
- Display office hours
- Inquiry submission form

**Guardian Pages:**

- `/resources/js/pages/guardian/inquiries/index.tsx` - List own inquiries
- `/resources/js/pages/guardian/inquiries/create.tsx` - New inquiry form
- `/resources/js/pages/guardian/inquiries/show.tsx` - View inquiry and responses

**Registrar Pages:**

- `/resources/js/pages/registrar/inquiries/index.tsx` - All inquiries
- `/resources/js/pages/registrar/inquiries/show.tsx` - Inquiry details with response form

**Super Admin Pages:**

- `/resources/js/pages/super-admin/announcements/index.tsx` - Announcements list
- `/resources/js/pages/super-admin/announcements/create.tsx` - Create announcement
- `/resources/js/pages/super-admin/announcements/edit.tsx` - Edit announcement
- `/resources/js/pages/super-admin/school-information/index.tsx` - Manage school info

**Dashboard Components:**

- `AnnouncementBanner` - Display active announcements
- `AnnouncementCarousel` - Rotating announcements
- `InquiryStatusWidget` - Show inquiry status
- `ContactInfoWidget` - Quick contact information

**Shared Components:**

- `AnnouncementCard` - Announcement display
- `InquiryForm` - Inquiry submission form
- `InquiryThread` - Message thread display
- `ContactInformation` - School contact details
- `OfficeHours` - Office hours display

### 5. Notification Integration

**Email Notifications:**

- New inquiry submitted (to staff)
- Inquiry assigned (to staff member)
- Inquiry response (to guardian)
- New announcement published (optional)

**In-App Notifications:**

- New announcements
- Inquiry status updates
- Responses received

**Implementation:**

- Use Laravel Notifications
- Queue email sending
- Store notifications in database

### 6. Features

**Announcement Features:**

- Rich text editor for content
- Schedule future publication
- Set expiration dates
- Target specific audiences
- Attach files/images
- Priority levels
- Email distribution
- Pin important announcements

**Inquiry Features:**

- Category-based routing
- Auto-assignment based on category
- Priority levels
- Status tracking
- Internal notes (staff only)
- File attachments
- Response templates
- SLA tracking
- Satisfaction rating

**Contact Information:**

- Multiple phone numbers
- Multiple email addresses
- Office hours per day
- Holiday schedules
- Social media links
- Location map integration
- Emergency contact info

## Acceptance Criteria

✅ Super Admin can create and manage announcements
✅ Announcements are displayed on appropriate dashboards
✅ Users can submit inquiries via contact form
✅ Registrar can view, assign, and respond to inquiries
✅ Guardians can track their inquiry status
✅ School contact information is displayed prominently
✅ Office hours are clearly shown
✅ Email notifications sent for inquiries and responses
✅ Announcement expiration works correctly
✅ Inquiry history is maintained
✅ File attachments work for inquiries

## Testing Requirements

- Unit tests for Announcement and Inquiry models
- Feature tests for CRUD operations
- Email notification tests
- Permission tests for different roles
- UI tests for forms and displays
- Integration tests for notification system

## Estimated Effort

**Low Priority:** 5-7 days

## Dependencies

- Requires notification system
- Requires email configuration
- May require rich text editor package (Tiptap or similar)
- May require file upload for inquiry attachments

## Implementation Phases

**Phase 1: School Information (1 day)**

- School information table and UI
- Contact page display

**Phase 2: Inquiry System (3 days)**

- Database and models
- Controllers and routes
- Frontend pages and forms
- Email notifications

**Phase 3: Announcements (2-3 days)**

- Database and models
- Admin interface
- Display on dashboards
- Notification integration

**Phase 4: Enhancements (1 day)**

- File attachments
- Rich text editor
- Advanced features
- Testing

## Notes

- Consider SMS notifications for urgent announcements
- Add announcement templates
- Implement inquiry escalation rules
- Add FAQ system to reduce inquiries
- Consider chatbot integration for common questions
- Ensure GDPR/DPA compliance for communication data
- Add communication analytics and reports
