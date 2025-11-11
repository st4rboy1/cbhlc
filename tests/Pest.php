<?php

pest()->extend(Tests\DuskTestCase::class)
//  ->use(Illuminate\Foundation\Testing\DatabaseMigrations::class)
    ->in('Browser');

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
 */
function assertNoConsoleErrors($browser)
{
    $consoleLogs = $browser->driver->manage()->getLog('browser');

    $errors = collect($consoleLogs)->filter(function ($log) {
        return $log['level'] === 'SEVERE' ||
               (isset($log['message']) && str_contains($log['message'], '403')) ||
               (isset($log['message']) && str_contains($log['message'], '404')) ||
               (isset($log['message']) && str_contains($log['message'], '500'));
    });

    if ($errors->isNotEmpty()) {
        $errorMessages = $errors->map(fn ($log) => $log['message'])->join("\n");
        throw new \Exception("Console errors detected:\n".$errorMessages);
    }

    return $browser;
}

/**
 * Helper to check for failed network requests (403, 404, 500 errors)
 */
function assertNoNetworkErrors($browser)
{
    // This will be caught by console errors check since XHR errors appear in console
    return assertNoConsoleErrors($browser);
}
