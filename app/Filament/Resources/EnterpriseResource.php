<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

abstract class EnterpriseResource extends Resource
{
    public static function canViewAny(): bool
    {
        return static::allows('view_any');
    }

    public static function canView(Model $record): bool
    {
        return static::allows('view');
    }

    public static function canCreate(): bool
    {
        return static::allows('create');
    }

    public static function canEdit(Model $record): bool
    {
        return static::allows('update');
    }

    public static function canDelete(Model $record): bool
    {
        return static::allows('delete');
    }

    public static function canDeleteAny(): bool
    {
        return static::allows('delete');
    }

    public static function canForceDelete(Model $record): bool
    {
        return static::allows('force_delete');
    }

    public static function canForceDeleteAny(): bool
    {
        return static::allows('force_delete');
    }

    public static function canRestore(Model $record): bool
    {
        return static::allows('restore');
    }

    public static function canRestoreAny(): bool
    {
        return static::allows('restore');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function permissionName(string $action): string
    {
        $model = class_basename(static::getModel());

        return $action.'.'.Str::snake($model);
    }

    protected static function allows(string $action): bool
    {
        $user = auth()->user();

        if (! $user?->is_active) {
            return false;
        }

        if (in_array($user->role, ['super_admin', 'owner'], true) || $user->hasAnyRole(['Super Admin', 'Owner'])) {
            return true;
        }

        if ($user->roles()->exists()) {
            return $user->can(static::permissionName($action));
        }

        return static::legacyRoleAllows((string) $user->role, $action);
    }

    protected static function legacyRoleAllows(string $role, string $action): bool
    {
        if ($role === 'driver') {
            return $action === 'view' || $action === 'view_any';
        }

        if ($role === 'mechanic') {
            $allowed = ['Vehicle', 'MaintenanceLog', 'MaintenancePrediction', 'ServiceSchedule', 'SparePart'];

            return in_array(class_basename(static::getModel()), $allowed, true)
                && in_array($action, ['view_any', 'view', 'create', 'update'], true);
        }

        if ($role === 'finance') {
            $allowed = ['BankAccount', 'BankStatementImport', 'BankStatementLine', 'ChartOfAccount', 'Expense', 'ExpenseCategory', 'Invoice', 'JournalEntry', 'Payment', 'PaymentMethod', 'PaymentTransaction'];

            return in_array(class_basename(static::getModel()), $allowed, true)
                && $action !== 'force_delete';
        }

        if ($role === 'cashier') {
            return in_array(class_basename(static::getModel()), ['Customer', 'Invoice', 'Payment', 'PaymentMethod', 'RentalOrder'], true)
                && in_array($action, ['view_any', 'view', 'create', 'update'], true);
        }

        return in_array($role, ['admin', 'manager'], true) && $action !== 'force_delete';
    }
}
