<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('check-due', function (Schedule $schedule) {
    $this->call('tasks:check-due');
})->purpose('Check due tasks and send notifications if necessary')->everyMinute();
