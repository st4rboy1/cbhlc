<?php

namespace App\Providers;

use App\Events\EnrollmentCreated;
use App\Events\StudentCreated;
use App\Listeners\LogAuthenticationActivity;
use App\Listeners\NotifyRegistrarOfNewEnrollment;
use App\Listeners\NotifyRegistrarOfNewStudent;
use App\Listeners\NotifySuperAdminOfNewUser;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register authentication event listeners
        Event::listen(Login::class, [LogAuthenticationActivity::class, 'handleLogin']);
        Event::listen(Logout::class, [LogAuthenticationActivity::class, 'handleLogout']);
        Event::listen(Failed::class, [LogAuthenticationActivity::class, 'handleFailed']);

        // Register notification event listeners
        Event::listen(Registered::class, NotifySuperAdminOfNewUser::class);
        Event::listen(StudentCreated::class, NotifyRegistrarOfNewStudent::class);
        Event::listen(EnrollmentCreated::class, NotifyRegistrarOfNewEnrollment::class);

        // Super admin has access to everything
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('super_admin')) {
                return true;
            }
        });

        // Define gates for each model and action
        $this->defineUserGates();
        $this->defineStudentGates();
        $this->defineEnrollmentGates();
        $this->defineGuardianGates();
        $this->defineInvoiceGates();
        $this->definePaymentGates();
        $this->defineGradeLevelFeeGates();

        // Configure rate limiters
        $this->configureRateLimiters();
    }

    /**
     * Configure rate limiters for the application.
     */
    protected function configureRateLimiters(): void
    {
        RateLimiter::for('document-uploads', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Too many upload attempts. Please try again later.',
                    ], 429);
                });
        });
    }

    /**
     * Define gates for User model
     */
    protected function defineUserGates(): void
    {
        Gate::define('viewAny-user', function (User $user) {
            return $user->hasAnyRole(['administrator', 'registrar']);
        });

        Gate::define('view-user', function (User $user, User $model) {
            return $user->hasAnyRole(['administrator', 'registrar']) || $user->id === $model->id;
        });

        Gate::define('create-user', function (User $user) {
            return $user->hasRole('administrator');
        });

        Gate::define('update-user', function (User $user, User $model) {
            return $user->hasRole('administrator') || $user->id === $model->id;
        });

        Gate::define('delete-user', function (User $user, User $model) {
            return $user->hasRole('administrator') && $user->id !== $model->id;
        });
    }

    /**
     * Define gates for Student model
     */
    protected function defineStudentGates(): void
    {
        Gate::define('viewAny-student', function (User $user) {
            return $user->hasAnyRole(['administrator', 'registrar']);
        });

        Gate::define('view-student', function (User $user, $student) {
            if ($user->hasAnyRole(['administrator', 'registrar'])) {
                return true;
            }

            // Guardians can view their own children
            if ($user->hasRole('guardian') && $user->guardian) {
                return $user->guardian->children->contains('id', $student->id);
            }

            // Students can view their own record
            if ($user->hasRole('student') && $user->student) {
                return $user->student->id === $student->id;
            }

            return false;
        });

        Gate::define('create-student', function (User $user) {
            return $user->hasAnyRole(['administrator', 'registrar', 'guardian']);
        });

        Gate::define('update-student', function (User $user, $student) {
            if ($user->hasAnyRole(['administrator', 'registrar'])) {
                return true;
            }

            // Guardians can update their own children
            if ($user->hasRole('guardian') && $user->guardian) {
                return $user->guardian->children->contains('id', $student->id);
            }

            return false;
        });

        Gate::define('delete-student', function (User $user, $student) {
            return $user->hasAnyRole(['administrator', 'registrar']);
        });
    }

    /**
     * Define gates for Enrollment model
     */
    protected function defineEnrollmentGates(): void
    {
        Gate::define('viewAny-enrollment', function (User $user) {
            return $user->hasAnyRole(['administrator', 'registrar']);
        });

        Gate::define('view-enrollment', function (User $user, $enrollment) {
            if ($user->hasAnyRole(['administrator', 'registrar'])) {
                return true;
            }

            // Guardians can view their children's enrollments
            if ($user->hasRole('guardian') && $user->guardian) {
                return $enrollment->guardian_id === $user->guardian->id;
            }

            return false;
        });

        Gate::define('create-enrollment', function (User $user) {
            return $user->hasAnyRole(['administrator', 'registrar', 'guardian']);
        });

        Gate::define('update-enrollment', function (User $user, $enrollment) {
            if ($user->hasAnyRole(['administrator', 'registrar'])) {
                return true;
            }

            // Guardians can update pending enrollments only
            if ($user->hasRole('guardian') && $user->guardian) {
                return $enrollment->guardian_id === $user->guardian->id &&
                       $enrollment->status === 'pending';
            }

            return false;
        });

        Gate::define('delete-enrollment', function (User $user, $enrollment) {
            return $user->hasAnyRole(['administrator', 'registrar']);
        });

        Gate::define('approve-enrollment', function (User $user) {
            return $user->hasAnyRole(['administrator', 'registrar']);
        });

        Gate::define('reject-enrollment', function (User $user) {
            return $user->hasAnyRole(['administrator', 'registrar']);
        });
    }

    /**
     * Define gates for Guardian model
     */
    protected function defineGuardianGates(): void
    {
        Gate::define('viewAny-guardian', function (User $user) {
            return $user->hasAnyRole(['administrator', 'registrar']);
        });

        Gate::define('view-guardian', function (User $user, $guardian) {
            if ($user->hasAnyRole(['administrator', 'registrar'])) {
                return true;
            }

            // Guardians can view their own record
            if ($user->hasRole('guardian') && $user->guardian) {
                return $user->guardian->id === $guardian->id;
            }

            return false;
        });

        Gate::define('create-guardian', function (User $user) {
            return $user->hasAnyRole(['administrator', 'registrar']);
        });

        Gate::define('update-guardian', function (User $user, $guardian) {
            if ($user->hasAnyRole(['administrator', 'registrar'])) {
                return true;
            }

            // Guardians can update their own record
            if ($user->hasRole('guardian') && $user->guardian) {
                return $user->guardian->id === $guardian->id;
            }

            return false;
        });

        Gate::define('delete-guardian', function (User $user, $guardian) {
            return $user->hasAnyRole(['administrator', 'registrar']);
        });
    }

    /**
     * Define gates for Invoice model
     */
    protected function defineInvoiceGates(): void
    {
        Gate::define('viewAny-invoice', function (User $user) {
            return $user->hasAnyRole(['administrator', 'registrar']);
        });

        Gate::define('view-invoice', function (User $user, $invoice) {
            if ($user->hasAnyRole(['administrator', 'registrar'])) {
                return true;
            }

            // Guardians can view their enrollments' invoices
            if ($user->hasRole('guardian') && $user->guardian && $invoice->enrollment) {
                return $invoice->enrollment->guardian_id === $user->guardian->id;
            }

            return false;
        });

        Gate::define('create-invoice', function (User $user) {
            return $user->hasAnyRole(['administrator', 'registrar']);
        });

        Gate::define('update-invoice', function (User $user, $invoice) {
            return $user->hasAnyRole(['administrator', 'registrar']);
        });

        Gate::define('delete-invoice', function (User $user, $invoice) {
            return $user->hasRole('administrator');
        });
    }

    /**
     * Define gates for Payment model
     */
    protected function definePaymentGates(): void
    {
        Gate::define('viewAny-payment', function (User $user) {
            return $user->hasAnyRole(['administrator', 'registrar']);
        });

        Gate::define('view-payment', function (User $user, $payment) {
            if ($user->hasAnyRole(['administrator', 'registrar'])) {
                return true;
            }

            // Guardians can view their payments
            if ($user->hasRole('guardian') && $user->guardian && $payment->invoice && $payment->invoice->enrollment) {
                return $payment->invoice->enrollment->guardian_id === $user->guardian->id;
            }

            return false;
        });

        Gate::define('create-payment', function (User $user) {
            return $user->hasAnyRole(['administrator', 'registrar']);
        });

        Gate::define('update-payment', function (User $user, $payment) {
            return $user->hasAnyRole(['administrator', 'registrar']);
        });

        Gate::define('delete-payment', function (User $user, $payment) {
            return $user->hasRole('administrator');
        });
    }

    /**
     * Define gates for GradeLevelFee model
     */
    protected function defineGradeLevelFeeGates(): void
    {
        Gate::define('viewAny-gradeLevelFee', function (User $user) {
            return true; // Everyone can view fees
        });

        Gate::define('view-gradeLevelFee', function (User $user, $fee) {
            return true; // Everyone can view fees
        });

        Gate::define('create-gradeLevelFee', function (User $user) {
            return $user->hasRole('administrator');
        });

        Gate::define('update-gradeLevelFee', function (User $user, $fee) {
            return $user->hasRole('administrator');
        });

        Gate::define('delete-gradeLevelFee', function (User $user, $fee) {
            return $user->hasRole(['super_admin', 'registrar']);
        });

        Gate::define('deleteAny-gradeLevelFee', function (User $user) {
            return $user->hasRole(['super_admin', 'registrar']);
        });
    }
}
