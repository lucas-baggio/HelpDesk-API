<?php

namespace App\Providers;

use App\Domains\Client\Models\Client;
use App\Domains\Client\Policies\ClientPolicy;
use App\Domains\Machine\Models\Machine;
use App\Domains\Machine\Policies\MachinePolicy;
use App\Domains\Ticket\Models\Ticket;
use App\Domains\Ticket\Policies\TicketPolicy;
use App\Domains\User\Models\User;
use App\Domains\User\Policies\UserPolicy;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Domains\WorkOrder\Policies\WorkOrderPolicy;
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
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Machine::class, MachinePolicy::class);
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(WorkOrder::class, WorkOrderPolicy::class);
    }
}
