<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        'chamado.updated' => [
            'App\Events\ChamadoEvent@chamadoUpdated',

        ],
        'projeto.updated' => [
            'App\Events\ProjetoEvent@projetoUpdated',

        ],
        'tarefa.updated' => [
            'App\Events\TarefaEvent@tarefaUpdated',

        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

    }
}
