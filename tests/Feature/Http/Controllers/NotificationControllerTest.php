<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use App\Notifications\TestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->user = User::factory()->create();
        $this->user->assignRole('guardian');
    }

    public function test_authenticated_user_can_access_notifications_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('notifications'));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Notifications/Index', false)
                ->has('notifications')
                ->has('notifications.data')
                ->where('filter', 'all')
            );
    }

    public function test_guest_cannot_access_notifications_page(): void
    {
        $response = $this->get(route('notifications'));

        $response->assertRedirect(route('login'));
    }

    public function test_can_get_paginated_notifications(): void
    {
        // Create 5 notifications for the user
        for ($i = 0; $i < 5; $i++) {
            $this->user->notify(new TestNotification('Test notification '.$i));
        }

        $response = $this->actingAs($this->user)
            ->getJson(route('api.notifications.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'type', 'data', 'read_at', 'created_at'],
                ],
                'current_page',
                'last_page',
                'per_page',
                'total',
            ])
            ->assertJsonCount(5, 'data');
    }

    public function test_can_filter_unread_notifications(): void
    {
        // Create 3 unread and 2 read notifications
        for ($i = 0; $i < 3; $i++) {
            $this->user->notify(new TestNotification('Unread notification '.$i));
        }

        for ($i = 0; $i < 2; $i++) {
            $this->user->notify(new TestNotification('Read notification '.$i));
        }

        // Mark the last 2 notifications as read
        $this->user->notifications()->latest()->take(2)->get()->each(fn ($notification) => $notification->markAsRead());

        $response = $this->actingAs($this->user)
            ->getJson(route('api.notifications.index', ['filter' => 'unread']));

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_filter_read_notifications(): void
    {
        // Create 3 unread and 2 read notifications
        for ($i = 0; $i < 3; $i++) {
            $this->user->notify(new TestNotification('Unread notification '.$i));
        }

        for ($i = 0; $i < 2; $i++) {
            $this->user->notify(new TestNotification('Read notification '.$i));
        }

        // Mark all as read
        $this->user->unreadNotifications->each(fn ($notification) => $notification->markAsRead());

        $response = $this->actingAs($this->user)
            ->getJson(route('api.notifications.index', ['filter' => 'read']));

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    public function test_can_limit_notifications_for_dropdown(): void
    {
        // Create 10 notifications
        for ($i = 0; $i < 10; $i++) {
            $this->user->notify(new TestNotification('Test notification '.$i));
        }

        $response = $this->actingAs($this->user)
            ->getJson(route('api.notifications.index', ['limit' => 5]));

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    public function test_can_get_unread_count(): void
    {
        // Create 3 unread notifications
        for ($i = 0; $i < 3; $i++) {
            $this->user->notify(new TestNotification('Unread notification '.$i));
        }

        $response = $this->actingAs($this->user)
            ->getJson(route('api.notifications.unread-count'));

        $response->assertOk()
            ->assertJson(['count' => 3]);
    }

    public function test_can_mark_notification_as_read(): void
    {
        $this->user->notify(new TestNotification('Test notification'));

        $notification = $this->user->unreadNotifications()->first();

        $response = $this->actingAs($this->user)
            ->postJson(route('api.notifications.mark-read', $notification->id));

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_cannot_mark_another_users_notification_as_read(): void
    {
        $anotherUser = User::factory()->create();
        $anotherUser->assignRole('guardian');
        $anotherUser->notify(new TestNotification('Test notification'));

        $notification = $anotherUser->unreadNotifications()->first();

        $response = $this->actingAs($this->user)
            ->postJson(route('api.notifications.mark-read', $notification->id));

        $response->assertNotFound();
    }

    public function test_can_mark_all_notifications_as_read(): void
    {
        // Create 3 unread notifications
        for ($i = 0; $i < 3; $i++) {
            $this->user->notify(new TestNotification('Unread notification '.$i));
        }

        $this->assertEquals(3, $this->user->unreadNotifications()->count());

        $response = $this->actingAs($this->user)
            ->postJson(route('api.notifications.mark-all-read'));

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertEquals(0, $this->user->unreadNotifications()->count());
    }

    public function test_can_delete_notification(): void
    {
        $this->user->notify(new TestNotification('Test notification'));

        $notification = $this->user->notifications()->first();

        $response = $this->actingAs($this->user)
            ->deleteJson(route('api.notifications.destroy', $notification->id));

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertNull($this->user->notifications()->find($notification->id));
    }

    public function test_cannot_delete_another_users_notification(): void
    {
        $anotherUser = User::factory()->create();
        $anotherUser->assignRole('guardian');
        $anotherUser->notify(new TestNotification('Test notification'));

        $notification = $anotherUser->notifications()->first();

        $response = $this->actingAs($this->user)
            ->deleteJson(route('api.notifications.destroy', $notification->id));

        $response->assertNotFound();
    }

    public function test_can_delete_all_notifications(): void
    {
        // Create 5 notifications
        for ($i = 0; $i < 5; $i++) {
            $this->user->notify(new TestNotification('Test notification '.$i));
        }

        $this->assertEquals(5, $this->user->notifications()->count());

        $response = $this->actingAs($this->user)
            ->deleteJson(route('api.notifications.destroy-all'));

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertEquals(0, $this->user->notifications()->count());
    }

    public function test_guest_cannot_access_notification_api(): void
    {
        $response = $this->getJson(route('api.notifications.index'));
        $response->assertUnauthorized();

        $response = $this->getJson(route('api.notifications.unread-count'));
        $response->assertUnauthorized();

        $response = $this->postJson(route('api.notifications.mark-read', 'fake-id'));
        $response->assertUnauthorized();

        $response = $this->postJson(route('api.notifications.mark-all-read'));
        $response->assertUnauthorized();

        $response = $this->deleteJson(route('api.notifications.destroy', 'fake-id'));
        $response->assertUnauthorized();

        $response = $this->deleteJson(route('api.notifications.destroy-all'));
        $response->assertUnauthorized();
    }

    public function test_notifications_page_respects_filter_parameter(): void
    {
        // Create notifications
        for ($i = 0; $i < 2; $i++) {
            $this->user->notify(new TestNotification('Unread notification '.$i));
        }

        $response = $this->actingAs($this->user)->get(route('notifications', ['filter' => 'unread']));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Notifications/Index', false)
                ->where('filter', 'unread')
            );
    }
}
