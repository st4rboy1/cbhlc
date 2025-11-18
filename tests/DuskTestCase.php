<?php

namespace Tests;

use App\Models\User;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => \Database\Seeders\RolesAndPermissionsSeeder::class]);
        $this->app->register(\Illuminate\Hashing\HashServiceProvider::class);

        // Create super admin user
        $this->superAdmin = \App\Models\User::factory()->superAdmin()->create([
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
        ]);

        // Create necessary records
        $this->schoolYear = \App\Models\SchoolYear::factory()->create();
        $this->enrollmentPeriod = \App\Models\EnrollmentPeriod::factory()->create([
            'school_year_id' => $this->schoolYear->id,
        ]);
        $this->gradeLevelFee = \App\Models\GradeLevelFee::factory()->create([
            'enrollment_period_id' => $this->enrollmentPeriod->id,
        ]);
        $this->guardian = \App\Models\Guardian::factory()->create();
        $this->student = \App\Models\Student::factory()->create();
        $this->enrollment = \App\Models\Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'school_year_id' => $this->schoolYear->id,
        ]);
        $this->document = \App\Models\Document::factory()->create([
            'student_id' => $this->student->id,
        ]);
        $this->invoice = \App\Models\Invoice::factory()->create([
            'enrollment_id' => $this->enrollment->id,
        ]);
        $this->payment = \App\Models\Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
        ]);
        $this->receipt = \App\Models\Receipt::factory()->create([
            'payment_id' => $this->payment->id,
        ]);
        $this->user = \App\Models\User::factory()->create();
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                // '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}
