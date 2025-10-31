<?php

use Illuminate\Support\Facades\Artisan;
use Spatie\Activitylog\Models\Activity;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Clean Audit Logs Command', function () {

    test('command deletes logs older than specified days', function () {
        // Create recent logs (within 90 days)
        Activity::create([
            'log_name' => 'default',
            'description' => 'Recent activity',
            'created_at' => now()->subDays(30),
        ]);

        Activity::create([
            'log_name' => 'default',
            'description' => 'Recent activity 2',
            'created_at' => now()->subDays(60),
        ]);

        // Create old logs (older than 90 days)
        Activity::create([
            'log_name' => 'default',
            'description' => 'Old activity',
            'created_at' => now()->subDays(100),
        ]);

        Activity::create([
            'log_name' => 'default',
            'description' => 'Old activity 2',
            'created_at' => now()->subDays(120),
        ]);

        expect(Activity::count())->toBe(4);

        // Run command
        Artisan::call('audit-logs:clean', ['--days' => 90]);

        // Should delete 2 old logs, keep 2 recent logs
        expect(Activity::count())->toBe(2);
    })->group('audit-logs', 'command');

    test('command with dry-run does not delete logs', function () {
        // Create old logs
        Activity::create([
            'log_name' => 'default',
            'description' => 'Old activity',
            'created_at' => now()->subDays(100),
        ]);

        Activity::create([
            'log_name' => 'default',
            'description' => 'Old activity 2',
            'created_at' => now()->subDays(120),
        ]);

        expect(Activity::count())->toBe(2);

        // Run command with dry-run
        Artisan::call('audit-logs:clean', ['--days' => 90, '--dry-run' => true]);

        // Should not delete any logs
        expect(Activity::count())->toBe(2);
    })->group('audit-logs', 'command');

    test('command returns success when no logs to delete', function () {
        // Create only recent logs
        Activity::create([
            'log_name' => 'default',
            'description' => 'Recent activity',
            'created_at' => now()->subDays(30),
        ]);

        $exitCode = Artisan::call('audit-logs:clean', ['--days' => 90]);

        expect($exitCode)->toBe(0);
    })->group('audit-logs', 'command');

    test('command returns failure for invalid days parameter', function () {
        $exitCode = Artisan::call('audit-logs:clean', ['--days' => 0]);

        expect($exitCode)->toBe(1);

        $exitCode = Artisan::call('audit-logs:clean', ['--days' => -10]);

        expect($exitCode)->toBe(1);
    })->group('audit-logs', 'command');

    test('command uses default 90 days when no parameter provided', function () {
        // Create old logs (older than 90 days)
        Activity::create([
            'log_name' => 'default',
            'description' => 'Old activity',
            'created_at' => now()->subDays(100),
        ]);

        // Create recent log
        Activity::create([
            'log_name' => 'default',
            'description' => 'Recent activity',
            'created_at' => now()->subDays(30),
        ]);

        expect(Activity::count())->toBe(2);

        // Run command without days parameter (should use default 90)
        Artisan::call('audit-logs:clean');

        expect(Activity::count())->toBe(1);
    })->group('audit-logs', 'command');

    test('command can delete logs with custom retention period', function () {
        // Create logs at different ages
        Activity::create([
            'log_name' => 'default',
            'description' => 'Activity 15 days old',
            'created_at' => now()->subDays(15),
        ]);

        Activity::create([
            'log_name' => 'default',
            'description' => 'Activity 40 days old',
            'created_at' => now()->subDays(40),
        ]);

        Activity::create([
            'log_name' => 'default',
            'description' => 'Activity 100 days old',
            'created_at' => now()->subDays(100),
        ]);

        expect(Activity::count())->toBe(3);

        // Delete logs older than 30 days
        Artisan::call('audit-logs:clean', ['--days' => 30]);

        // Should keep 1 log (15 days old), delete 2 logs (40 and 100 days old)
        expect(Activity::count())->toBe(1);
    })->group('audit-logs', 'command');

    test('command output shows correct counts', function () {
        // Create old logs
        Activity::create([
            'log_name' => 'default',
            'description' => 'Old activity 1',
            'created_at' => now()->subDays(100),
        ]);

        Activity::create([
            'log_name' => 'default',
            'description' => 'Old activity 2',
            'created_at' => now()->subDays(120),
        ]);

        Activity::create([
            'log_name' => 'default',
            'description' => 'Old activity 3',
            'created_at' => now()->subDays(150),
        ]);

        Artisan::call('audit-logs:clean', ['--days' => 90]);

        $output = Artisan::output();

        expect($output)->toContain('Successfully deleted 3 audit log(s)');
    })->group('audit-logs', 'command');
});
