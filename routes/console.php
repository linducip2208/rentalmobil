<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('orders:escalate-overdue')->hourly();
Schedule::command('notifications:send-pending')->everyFiveMinutes();
Schedule::command('notifications:send-reminders')->dailyAt('08:00');
Schedule::command('db:backup')->dailyAt('02:00');
Schedule::command('invoices:generate-from-returns')->dailyAt('06:00');
