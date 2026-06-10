<?php

namespace App\Providers;

use App\Domains\Client\Models\Client;
use App\Domains\Client\Policies\ClientPolicy;
use App\Domains\FileUpload\Models\WorkOrderFile;
use App\Domains\FileUpload\Policies\WorkOrderFilePolicy;
use App\Domains\History\Models\History;
use App\Domains\History\Observers\TicketObserver;
use App\Domains\History\Observers\WorkOrderFileObserver;
use App\Domains\History\Observers\WorkOrderObserver;
use App\Domains\History\Policies\HistoryPolicy;
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
    public function register(): void {}

    public function boot(): void
    {
        // Policies
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Machine::class, MachinePolicy::class);
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(WorkOrder::class, WorkOrderPolicy::class);
        Gate::policy(WorkOrderFile::class, WorkOrderFilePolicy::class);
        Gate::policy(History::class, HistoryPolicy::class);

        // Observers (RN-030 – RN-033)
        Ticket::observe(TicketObserver::class);
        WorkOrder::observe(WorkOrderObserver::class);
        WorkOrderFile::observe(WorkOrderFileObserver::class);
    }
}
