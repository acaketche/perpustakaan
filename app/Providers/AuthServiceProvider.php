<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\DigitalBook;
use App\Policies\DigitalBookPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        DigitalBook::class => DigitalBookPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define additional gates if needed
        Gate::define('admin-access', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('dosen-access', function ($user) {
            return $user->isDosen() || $user->isAdmin();
        });
    }
}
