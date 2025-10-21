<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\SchoolInformation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SchoolInformationTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $administrator;

    private User $registrar;

    private User $guardian;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles directly
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::create(['name' => 'administrator', 'guard_name' => 'web']);
        Role::create(['name' => 'registrar', 'guard_name' => 'web']);
        Role::create(['name' => 'guardian', 'guard_name' => 'web']);

        // Create users with different roles
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');

        $this->administrator = User::factory()->create();
        $this->administrator->assignRole('administrator');

        $this->registrar = User::factory()->create();
        $this->registrar->assignRole('registrar');

        $this->guardian = User::factory()->create();
        $this->guardian->assignRole('guardian');

        // Seed school information data
        $this->seed(\Database\Seeders\SchoolInformationSeeder::class);
    }

    public function test_super_admin_can_access_school_information_page(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get('/super-admin/school-information');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('super-admin/school-information/index')
            ->has('information')
            ->has('information.contact')
            ->has('information.hours')
            ->has('information.social')
            ->has('information.about')
        );
    }

    public function test_administrator_cannot_access_school_information_page(): void
    {
        $response = $this->actingAs($this->administrator)
            ->get('/super-admin/school-information');

        $response->assertForbidden();
    }

    public function test_registrar_cannot_access_school_information_page(): void
    {
        $response = $this->actingAs($this->registrar)
            ->get('/super-admin/school-information');

        $response->assertForbidden();
    }

    public function test_guardian_cannot_access_school_information_page(): void
    {
        $response = $this->actingAs($this->guardian)
            ->get('/super-admin/school-information');

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_school_information_page(): void
    {
        $response = $this->get('/super-admin/school-information');

        $response->assertRedirect('/login');
    }

    public function test_super_admin_can_update_school_information(): void
    {
        $schoolName = SchoolInformation::where('key', 'school_name')->first();
        $schoolEmail = SchoolInformation::where('key', 'school_email')->first();

        $response = $this->actingAs($this->superAdmin)
            ->put('/super-admin/school-information', [
                'updates' => [
                    [
                        'id' => $schoolName->id,
                        'value' => 'Updated School Name',
                    ],
                    [
                        'id' => $schoolEmail->id,
                        'value' => 'updated@school.edu',
                    ],
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'School information updated successfully.');

        $this->assertDatabaseHas('school_information', [
            'id' => $schoolName->id,
            'value' => 'Updated School Name',
        ]);

        $this->assertDatabaseHas('school_information', [
            'id' => $schoolEmail->id,
            'value' => 'updated@school.edu',
        ]);
    }

    public function test_administrator_cannot_update_school_information(): void
    {
        $schoolName = SchoolInformation::where('key', 'school_name')->first();

        $response = $this->actingAs($this->administrator)
            ->put('/super-admin/school-information', [
                'updates' => [
                    [
                        'id' => $schoolName->id,
                        'value' => 'Admin Updated Name',
                    ],
                ],
            ]);

        $response->assertForbidden();
    }

    public function test_registrar_cannot_update_school_information(): void
    {
        $schoolName = SchoolInformation::where('key', 'school_name')->first();

        $response = $this->actingAs($this->registrar)
            ->put('/super-admin/school-information', [
                'updates' => [
                    [
                        'id' => $schoolName->id,
                        'value' => 'Unauthorized Update',
                    ],
                ],
            ]);

        $response->assertForbidden();
    }

    public function test_update_requires_valid_updates_array(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->put('/super-admin/school-information', [
                'updates' => 'invalid',
            ]);

        $response->assertSessionHasErrors('updates');
    }

    public function test_update_requires_valid_school_information_id(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->put('/super-admin/school-information', [
                'updates' => [
                    [
                        'id' => 99999, // Non-existent ID
                        'value' => 'Test Value',
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('updates.0.id');
    }

    public function test_update_allows_null_values(): void
    {
        $socialMedia = SchoolInformation::where('key', 'facebook_url')->first();

        $response = $this->actingAs($this->superAdmin)
            ->put('/super-admin/school-information', [
                'updates' => [
                    [
                        'id' => $socialMedia->id,
                        'value' => null,
                    ],
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('school_information', [
            'id' => $socialMedia->id,
            'value' => null,
        ]);
    }

    public function test_update_allows_empty_string_values(): void
    {
        $socialMedia = SchoolInformation::where('key', 'instagram_url')->first();

        $response = $this->actingAs($this->superAdmin)
            ->put('/super-admin/school-information', [
                'updates' => [
                    [
                        'id' => $socialMedia->id,
                        'value' => '',
                    ],
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Empty string may be stored as null by database, which is acceptable
        $updated = SchoolInformation::find($socialMedia->id);
        $this->assertTrue($updated->value === '' || $updated->value === null);
    }

    public function test_school_information_is_grouped_correctly(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get('/super-admin/school-information');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('information.contact.0.group', 'contact')
            ->where('information.hours.0.group', 'hours')
            ->where('information.social.0.group', 'social')
            ->where('information.about.0.group', 'about')
        );
    }

    public function test_can_update_multiple_fields_at_once(): void
    {
        $info = SchoolInformation::whereIn('key', ['school_name', 'school_email', 'school_phone'])->get();

        $response = $this->actingAs($this->superAdmin)
            ->put('/super-admin/school-information', [
                'updates' => $info->map(fn ($item) => [
                    'id' => $item->id,
                    'value' => 'Updated '.$item->key,
                ])->toArray(),
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        foreach ($info as $item) {
            $this->assertDatabaseHas('school_information', [
                'id' => $item->id,
                'value' => 'Updated '.$item->key,
            ]);
        }
    }
}
