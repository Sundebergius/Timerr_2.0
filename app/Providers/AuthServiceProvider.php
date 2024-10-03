<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Project;
use App\Models\Client;
use App\Models\Product;
use App\Models\Event;
use App\Policies\ProjectPolicy;
use App\Policies\ClientPolicy;
use App\Policies\ProductPolicy;
use App\Policies\EventPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Project::class => ProjectPolicy::class,
        \App\Models\Client::class => ClientPolicy::class,
        \App\Models\Product::class => ProductPolicy::class,
        \App\Models\Event::class => EventPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
