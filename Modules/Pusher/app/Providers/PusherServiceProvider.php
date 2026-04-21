<?php

namespace Modules\Pusher\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\Pusher\Console\Commands\ImportMdbCommand;

class PusherServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Pusher';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'pusher';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    protected array $commands = [
        ImportMdbCommand::class,
    ];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Define module schedules.
     *
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
