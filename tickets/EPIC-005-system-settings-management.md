# Ticket #005: System Settings Management

## Priority: Medium (Could Have)

## Related SRS Requirements

- **Section 8.2.2:** SYSTEM_SETTING entity (Supporting Entities in ERD)
- **NFR-6.1:** Code shall follow established development standards
- **Section 9.1:** System Constraints

## Current Status

❌ **NOT IMPLEMENTED**

No centralized system settings management:

- No `system_settings` table
- Settings hardcoded in config files or .env
- No UI for administrators to change settings
- No settings versioning or audit trail

## Required Implementation

### 1. Database Layer

Create migration: `create_system_settings_table.php`

```php
Schema::create('system_settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->text('value')->nullable();
    $table->enum('type', [
        'string',
        'text',
        'integer',
        'boolean',
        'json',
        'date',
        'datetime',
        'email',
        'url',
        'file'
    ])->default('string');
    $table->string('group')->default('general'); // general, enrollment, billing, etc.
    $table->text('description')->nullable();
    $table->boolean('is_public')->default(false); // Visible to non-admin users
    $table->json('validation_rules')->nullable(); // Laravel validation rules
    $table->json('options')->nullable(); // For select/radio inputs
    $table->integer('sort_order')->default(0);
    $table->boolean('is_editable')->default(true);
    $table->timestamps();
});
```

Create migration: `create_setting_history_table.php`

```php
Schema::create('setting_history', function (Blueprint $table) {
    $table->id();
    $table->foreignId('setting_id')->constrained('system_settings')->onDelete('cascade');
    $table->text('old_value')->nullable();
    $table->text('new_value')->nullable();
    $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
    $table->timestamp('changed_at');
    $table->string('ip_address')->nullable();
});
```

### 2. Model Layer

Create `app/Models/SystemSetting.php`:

**Key Methods:**

- `static get($key, $default = null)` - Get setting value
- `static set($key, $value)` - Set setting value
- `static has($key)` - Check if setting exists
- `static getByGroup($group)` - Get settings by group
- `cast()` - Cast value to appropriate type
- `validate()` - Validate value against rules
- `logChange()` - Log setting change to history

**Scopes:**

- `scopePublic()` - Get public settings
- `scopeEditable()` - Get editable settings
- `scopeByGroup()` - Filter by group

**Example Implementation:**

```php
class SystemSetting extends Model
{
    public static function get(string $key, $default = null)
    {
        $setting = Cache::remember(
            "setting.{$key}",
            3600,
            fn() => static::where('key', $key)->first()
        );

        return $setting ? $setting->getCastValue() : $default;
    }

    public function getCastValue()
    {
        return match($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'json' => json_decode($this->value, true),
            'date' => Carbon::parse($this->value),
            default => $this->value,
        };
    }
}
```

### 3. Backend Layer

**Controllers:**

- `SuperAdmin/SystemSettingController.php` - Full CRUD and management
- `Api/SystemSettingController.php` - API for frontend

**Routes:**

```php
// Super Admin routes
Route::prefix('super-admin/settings')->name('super-admin.settings.')->group(function () {
    Route::get('/', [SuperAdminSystemSettingController::class, 'index'])->name('index');
    Route::get('/{setting}', 'show')->name('show');
    Route::put('/{setting}', 'update')->name('update');
    Route::post('/bulk-update', 'bulkUpdate')->name('bulk-update');
    Route::get('/{setting}/history', 'history')->name('history');
    Route::post('/{setting}/restore', 'restore')->name('restore');
});

// API routes for shared settings
Route::get('/api/settings/public', [ApiSystemSettingController::class, 'public']);
```

**Form Request Validation:**

```php
class UpdateSystemSettingRequest extends FormRequest
{
    public function rules()
    {
        $setting = $this->route('setting');

        // Use validation rules from the setting's validation_rules JSON
        return [
            'value' => $setting->validation_rules ?? 'nullable',
        ];
    }
}
```

### 4. Setting Groups and Keys

**Enrollment Settings:**

```
enrollment.max_students_per_grade
enrollment.allow_midyear_enrollment
enrollment.require_document_verification
enrollment.auto_approve_returning_students
enrollment.application_fee
enrollment.max_applications_per_guardian
```

**Billing Settings:**

```
billing.currency
billing.payment_methods
billing.late_payment_fee_percentage
billing.discount_early_payment_percentage
billing.payment_due_days
billing.send_payment_reminders
```

**System Settings:**

```
system.school_name
system.school_address
system.school_email
system.school_phone
system.school_logo
system.timezone
system.date_format
system.time_format
system.items_per_page
```

**Security Settings:**

```
security.max_login_attempts
security.lockout_duration
security.session_timeout
security.require_email_verification
security.password_expiry_days
```

**Notification Settings:**

```
notifications.email_enabled
notifications.sms_enabled
notifications.enrollment_approved_template
notifications.enrollment_rejected_template
notifications.payment_reminder_days
```

**Document Settings:**

```
documents.max_file_size
documents.allowed_mime_types
documents.storage_driver
documents.require_verification
```

### 5. Frontend Layer

**Super Admin Pages:**

- `/resources/js/pages/super-admin/settings/index.tsx` - All settings grouped
- `/resources/js/pages/super-admin/settings/group.tsx` - Settings by group
- `/resources/js/pages/super-admin/settings/history.tsx` - Change history

**Components:**

- `SettingCard` - Display and edit single setting
- `SettingGroup` - Group of related settings
- `SettingInput` - Dynamic input based on setting type
- `SettingHistory` - Display change history
- `SettingSearch` - Search settings
- `BulkSettingEditor` - Edit multiple settings at once

**Features:**

- Grouped settings display
- Search and filter settings
- Inline editing
- Bulk update
- Reset to default
- Change history view
- Restore previous values
- Validation feedback
- Unsaved changes warning

### 6. Configuration Cache

**Cache Management:**

```php
// Helper class
class Setting
{
    public static function get($key, $default = null)
    {
        return SystemSetting::get($key, $default);
    }

    public static function set($key, $value)
    {
        SystemSetting::set($key, $value);
        Cache::forget("setting.{$key}");
    }

    public static function clearCache()
    {
        Cache::tags(['settings'])->flush();
    }
}
```

**Artisan Command:**

```php
// php artisan settings:cache
class CacheSettings extends Command
{
    public function handle()
    {
        $settings = SystemSetting::all();

        foreach ($settings as $setting) {
            Cache::put(
                "setting.{$setting->key}",
                $setting->getCastValue(),
                3600
            );
        }
    }
}
```

### 7. Seeders

Create `database/seeders/SystemSettingSeeder.php`:

```php
class SystemSettingSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            [
                'key' => 'system.school_name',
                'value' => 'Christian Bible Heritage Learning Center',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Official school name',
                'is_public' => true,
            ],
            [
                'key' => 'enrollment.max_students_per_grade',
                'value' => '30',
                'type' => 'integer',
                'group' => 'enrollment',
                'description' => 'Maximum students per grade level',
                'validation_rules' => ['integer', 'min:1', 'max:100'],
            ],
            // ... more settings
        ];

        foreach ($settings as $setting) {
            SystemSetting::create($setting);
        }
    }
}
```

### 8. Middleware Integration

Use settings in middleware:

```php
// Check enrollment period based on setting
if (!Setting::get('enrollment.allow_midyear_enrollment', false)) {
    // Validate enrollment is within active period
}

// Apply rate limiting based on setting
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(Setting::get('security.api_rate_limit', 60));
});
```

## Acceptance Criteria

✅ Super Admin can view all system settings grouped by category
✅ Super Admin can edit settings with appropriate input types
✅ Settings are validated based on defined rules
✅ Change history is maintained for all settings
✅ Public settings are available to frontend without authentication
✅ Settings are cached for performance
✅ Bulk update functionality works correctly
✅ Settings can be restored to previous values
✅ Clear documentation for each setting
✅ Search and filter functionality works
✅ Unsaved changes warning is shown

## Testing Requirements

- Unit tests for SystemSetting model methods
- Feature tests for CRUD operations
- Validation tests for different setting types
- Cache tests for performance
- Permission tests for setting access
- History tracking tests
- Integration tests with other modules

## Estimated Effort

**Medium Priority:** 3-4 days

## Dependencies

- Requires cache system (Redis recommended)
- May require file upload for logo/images
- Requires proper permission setup

## Notes

- Consider environment-based setting overrides (.env priority)
- Add export/import functionality for settings
- Consider setting templates for different school types
- Add setting validation preview before saving
- Implement setting backup and restore
- Consider encrypted storage for sensitive settings
- Add API for mobile app to fetch public settings
