<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRoleAccess
{
    private const RESTRICTED = [
        'finance' => ['bank-accounts', 'chart-of-accounts', 'expenses', 'expense-categories', 'journal-entries', 'laporan-keuangan'],
        'security' => ['blacklist-entries', 'watch-lists', 'investigation-cases', 'police-reports', 'trust-score-logs', 'gps-alerts', 'gps-commands'],
        'system' => ['users', 'providers'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user?->is_active, 403);

        $segment = $request->segment(2);
        $role = $user->role;

        if (in_array($segment, self::RESTRICTED['finance'], true)) {
            abort_unless(in_array($role, ['super_admin', 'owner', 'manager', 'finance'], true), 403);
        }
        if (in_array($segment, self::RESTRICTED['security'], true)) {
            abort_unless(in_array($role, ['super_admin', 'owner', 'manager', 'admin'], true), 403);
        }
        if (in_array($segment, self::RESTRICTED['system'], true)) {
            abort_unless(in_array($role, ['super_admin', 'owner', 'admin'], true), 403);
        }
        if ($role === 'driver') {
            abort_unless(in_array($segment, [null, 'driver-tracking', 'gps-map', 'deliveries', 'rental-orders'], true), 403);
        }
        if ($role === 'cashier') {
            abort_unless(!in_array($segment, array_merge(self::RESTRICTED['finance'], self::RESTRICTED['security'], self::RESTRICTED['system']), true), 403);
        }

        return $next($request);
    }
}
