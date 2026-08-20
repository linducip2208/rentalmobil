<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('rental:expire-overdue')->everyFifteenMinutes();
Schedule::command('rental:release-holds')->everyFiveMinutes();
Schedule::command('rental:send-reminders')->dailyAt('20:00');
Schedule::command('rental:escalate-overdue')->hourly();
Schedule::command('seo:indexnow')->dailyAt('02:45');
Schedule::command('notifications:send-pending')->everyFiveMinutes();
Schedule::command('db:backup')->dailyAt('03:00');
Schedule::command('invoices:generate-from-returns')->dailyAt('06:00');
Schedule::command('gps:sync')->everyMinute()->withoutOverlapping();
Schedule::command('gps:monitor-health')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('gps:prune')->dailyAt('03:30')->withoutOverlapping();
Schedule::command('rental:expire-operational-records')->hourly()->withoutOverlapping();
Schedule::command('approvals:escalate-stale')->hourly()->withoutOverlapping();
