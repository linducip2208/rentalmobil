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
Schedule::command('maintenance:predict')->dailyAt('01:30')->withoutOverlapping();
Schedule::command('subscriptions:bill')->dailyAt('05:00')->withoutOverlapping();
Schedule::command('vehicles:remind-documents')->dailyAt('07:30')->withoutOverlapping();
Schedule::command('marketing:generate-vouchers')->dailyAt('06:30')->withoutOverlapping();
Schedule::command('marketing:request-reviews')->dailyAt('10:00')->withoutOverlapping();
Schedule::command('finance:remind-overdue')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('report:daily-owner')->dailyAt('07:00')->withoutOverlapping();
Schedule::command('webhooks:retry-failed')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('pricing:forecast-demand')->dailyAt('01:00')->withoutOverlapping();
Schedule::command('gps:detect-fuel-anomalies')->dailyAt('02:00')->withoutOverlapping();
Schedule::command('drivers:generate-scorecards')->monthlyOn(1, '03:00')->withoutOverlapping();
Schedule::command('finance:auto-refund-deposits')->dailyAt('09:00')->withoutOverlapping();
Schedule::command('finance:project-cashflow')->dailyAt('04:30')->withoutOverlapping();
Schedule::command('maintenance:draft-purchase-orders')->dailyAt('06:45')->withoutOverlapping();
Schedule::command('marketing:recover-abandoned')->hourly()->withoutOverlapping();
Schedule::command('risk:scan-fraud-patterns')->dailyAt('03:15')->withoutOverlapping();
Schedule::command('investors:distribute')->monthlyOn(2, '06:00')->withoutOverlapping();
Schedule::command('finance:run-depreciation')->monthlyOn(1, '02:30')->withoutOverlapping();
