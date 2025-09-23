<?php

namespace App\Providers;

use App\Contracts\Services\BillingServiceInterface;
use App\Contracts\Services\DashboardServiceInterface;
use App\Contracts\Services\EnrollmentServiceInterface;
use App\Contracts\Services\StudentServiceInterface;
use App\Models\Enrollment;
use App\Models\Student;
use App\Services\BillingService;
use App\Services\CurrencyService;
use App\Services\DashboardService;
use App\Services\EnrollmentService;
use App\Services\StudentService;
use Illuminate\Support\ServiceProvider;

class ServiceLayerProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register StudentService
        $this->app->bind(StudentServiceInterface::class, function ($app) {
            return new StudentService(new Student);
        });

        // Register EnrollmentService
        $this->app->bind(EnrollmentServiceInterface::class, function ($app) {
            return new EnrollmentService(new Enrollment);
        });

        // Register BillingService
        $this->app->bind(BillingServiceInterface::class, function ($app) {
            return new BillingService(new \App\Models\Invoice);
        });

        // Register DashboardService
        $this->app->bind(DashboardServiceInterface::class, DashboardService::class);

        // Register CurrencyService as singleton
        $this->app->singleton(CurrencyService::class, function ($app) {
            return new CurrencyService;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
