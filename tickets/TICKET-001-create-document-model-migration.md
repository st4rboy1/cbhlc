# Ticket #001: Create Document Model and Migration

**Epic:** [EPIC-001 Document Management System](./EPIC-001-document-management-system.md)

**Type:** Story
**Priority:** Critical
**Estimated Effort:** 0.5 day
**Status:** ✅ Completed
**Assignee:** Claude

## Description

Create the database migration and Eloquent model for the Document entity to support file uploads for enrollment applications.

## Acceptance Criteria

- [ ] Migration created: `create_documents_table.php`
- [ ] Document model created with relationships
- [ ] Model includes casts for `upload_date`, `verified_at`
- [ ] Relationships defined: `student()`, `verifiedBy()`
- [ ] Enum for `document_type` includes: birth_certificate, report_card, form_138, good_moral_certificate, other
- [ ] Enum for `verification_status` includes: pending, verified, rejected
- [ ] Migration can be run and rolled back successfully

## Implementation Details

### Migration Schema

```php
Schema::create('documents', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained()->onDelete('cascade');
    $table->enum('document_type', [
        'birth_certificate',
        'report_card',
        'form_138',
        'good_moral_certificate',
        'other'
    ]);
    $table->string('original_filename');
    $table->string('stored_filename');
    $table->string('file_path', 500);
    $table->integer('file_size'); // in bytes
    $table->string('mime_type', 100);
    $table->timestamp('upload_date');
    $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
    $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
    $table->timestamp('verified_at')->nullable();
    $table->text('verification_notes')->nullable();
    $table->timestamps();
});
```

### Model Relationships

- `belongsTo(Student::class)`
- `belongsTo(User::class, 'verified_by')`

## Testing Requirements

- [ ] Migration runs successfully
- [ ] Migration rollback works
- [ ] Model can be instantiated
- [ ] Relationships work correctly
- [ ] Enums cast properly

## Dependencies

None

## Notes

- Store file_size in bytes for consistency
- verification_notes allows registrar to add rejection reasons
