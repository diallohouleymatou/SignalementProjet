<?php

namespace App\Providers;

use App\Modules\Signalement\Models\Signalement;
use App\Policies\SignalementPolicy;
use Illuminate\Support\Facades\Gate;
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
        // Register policies
        Gate::policy(Signalement::class, SignalementPolicy::class);
        Gate::policy(\App\Modules\Comment\Models\Comment::class, \App\Policies\CommentPolicy::class);
        Gate::policy(\App\Modules\Message\Models\Message::class, \App\Policies\MessagePolicy::class);
        Gate::policy(\App\Modules\Report\Models\Report::class, \App\Policies\ReportPolicy::class);
    }
}
