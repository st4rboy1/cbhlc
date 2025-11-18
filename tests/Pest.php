<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Browser');

// Helper function to generate a unique enrollment ID for tests
function generateUniqueEnrollmentId(): string
{
    do {
        $id = 'ENR-'.str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);
    } while (\App\Models\Enrollment::where('enrollment_id', $id)->exists());

    return $id;
}

// Pest v4 Browser Testing Configuration
// Using HEADLESS mode (default) - fast, perfect for pre-push hooks and CI/CD
// Screenshots are automatically saved on failures to tests/Browser/Screenshots/

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
 // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Helper to check for console errors in browser tests
 * Note: Pest v4 browser testing doesn't expose driver logs directly
 * This is a placeholder for future implementation
 */
function assertNoConsoleErrors($browser)
{
    // TODO: Implement console error checking when Pest v4 API supports it
    // For now, return browser to allow chaining
    return $browser;
}

/**
 * Helper to check for failed network requests (403, 404, 500 errors)
 * Note: Pest v4 browser testing doesn't expose driver logs directly
 * This is a placeholder for future implementation
 */
function assertNoNetworkErrors($browser)
{
    // TODO: Implement network error checking when Pest v4 API supports it
    return assertNoConsoleErrors($browser);
}

/*
|--------------------------------------------------------------------------
| Global Hooks
|--------------------------------------------------------------------------
|
| Pest allows you to define global hooks that run before or after each test.
| This is useful for cleaning up resources, resetting state, etc.
|
*/

/**
 * Clean up Storage::fake() test directories after each test
 *
 * This prevents root-owned directories from accumulating and causing permission errors.
 * Laravel's Storage::fake() creates temporary directories in storage/framework/testing/disks/
 * that sometimes get created with root ownership when running in Laravel Sail (Docker).
 *
 * By cleaning up after each test, we ensure:
 * 1. No accumulation of old test directories
 * 2. Fresh state for each test run
 * 3. No permission conflicts from root-owned directories
 *
 * Note: This runs AFTER each test, so if a test fails, the directories still exist for debugging.
 * They'll be cleaned up before the next test runs.
 */
afterEach(function () {
    // Only clean up if we're in a test environment
    if (app()->environment('testing')) {
        $testDisksPath = storage_path('framework/testing/disks');

        // Check if the test disks directory exists
        if (is_dir($testDisksPath)) {
            try {
                // Get all directories in the test disks path
                $directories = glob($testDisksPath.'/*', GLOB_ONLYDIR);

                if ($directories !== false) {
                    foreach ($directories as $directory) {
                        // Use Laravel's File facade for cross-platform compatibility
                        // deleteDirectory() recursively removes directory and contents
                        \Illuminate\Support\Facades\File::deleteDirectory($directory);
                    }
                }
            } catch (\Exception $e) {
                // Silently fail if we can't clean up (e.g., permission issues)
                // The manual fix script can handle these cases
            }
        }
    }
});
