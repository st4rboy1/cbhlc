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
            ->has('values')
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
        $response = $this->actingAs($this->superAdmin)
            ->put('/super-admin/school-information', [
                'school_name' => 'Updated School Name',
                'school_email' => 'updated@school.edu',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'School information updated successfully.');

        $this->assertDatabaseHas('school_information', [
            'key' => 'school_name',
            'value' => 'Updated School Name',
        ]);

        $this->assertDatabaseHas('school_information', [
            'key' => 'school_email',
            'value' => 'updated@school.edu',
        ]);
    }

    public function test_administrator_cannot_update_school_information(): void
    {
        $response = $this->actingAs($this->administrator)
            ->put('/super-admin/school-information', [
                'school_name' => 'Admin Updated Name',
            ]);

        $response->assertForbidden();
    }

    public function test_registrar_cannot_update_school_information(): void
    {
        $response = $this->actingAs($this->registrar)
            ->put('/super-admin/school-information', [
                'school_name' => 'Unauthorized Update',
            ]);

        $response->assertForbidden();
    }

    public function test_update_validates_email_format(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->put('/super-admin/school-information', [
                'school_email' => 'invalid-email',
            ]);

        $response->assertSessionHasErrors('school_email');
    }

    public function test_update_validates_url_format(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->put('/super-admin/school-information', [
                'facebook_url' => 'not-a-url',
            ]);

        $response->assertSessionHasErrors('facebook_url');
    }

    public function test_update_allows_null_values(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->put('/super-admin/school-information', [
                'facebook_url' => null,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('school_information', [
            'key' => 'facebook_url',
            'value' => null,
        ]);
    }

    public function test_update_allows_empty_string_values(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->put('/super-admin/school-information', [
                'instagram_url' => '',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Empty string may be stored as null by database, which is acceptable
        $updated = SchoolInformation::where('key', 'instagram_url')->first();
        $this->assertTrue($updated->value === '' || $updated->value === null);
    }

    public function test_values_are_returned_as_key_value_pairs(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get('/super-admin/school-information');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('values.school_name')
            ->has('values.school_email')
            ->has('values.school_phone')
        );
    }

    public function test_can_update_multiple_fields_at_once(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->put('/super-admin/school-information', [
                'school_name' => 'Updated School Name',
                'school_email' => 'updated@school.edu',
                'school_phone' => '(02) 9999-8888',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('school_information', [
            'key' => 'school_name',
            'value' => 'Updated School Name',
        ]);

        $this->assertDatabaseHas('school_information', [
            'key' => 'school_email',
            'value' => 'updated@school.edu',
        ]);

        $this->assertDatabaseHas('school_information', [
            'key' => 'school_phone',
            'value' => '(02) 9999-8888',
        ]);
    }
}
