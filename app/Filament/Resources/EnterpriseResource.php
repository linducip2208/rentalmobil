<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

abstract class EnterpriseResource extends Resource
{
    /** @var array<string, string> */
    private const NAVIGATION_GROUPS = [
        'Quotation' => 'Rental', 'Booking' => 'Rental', 'RentalOrder' => 'Rental',
        'BookingWaitlist' => 'Rental', 'RentalExtension' => 'Rental', 'Delivery' => 'Rental', 'HandoverRecord' => 'Rental',
        'ReturnRecord' => 'Rental', 'Contract' => 'Rental', 'Subscription' => 'Rental',
        'TripPermit' => 'Rental', 'Addon' => 'Rental',
        'Vehicle' => 'Fleet', 'Driver' => 'Fleet', 'Brand' => 'Fleet', 'Category' => 'Fleet',
        'Location' => 'Fleet', 'FuelLog' => 'Fleet', 'KmLog' => 'Fleet', 'Transfer' => 'Fleet',
        'Customer' => 'Customers', 'CorporateAccount' => 'Customers', 'CustomerDocument' => 'Customers',
        'TrustScoreLog' => 'Customers', 'BlacklistEntry' => 'Customers', 'WatchList' => 'Customers',
        'InvestigationCase' => 'Customers', 'RiskAssessment' => 'Customers', 'RiskRule' => 'Customers',
        'GpsTracker' => 'GPS & Monitoring', 'GpsLog' => 'GPS & Monitoring', 'GpsAlert' => 'GPS & Monitoring',
        'GpsGeofence' => 'GPS & Monitoring', 'GpsCommand' => 'GPS & Monitoring',
        'GpsIntegration' => 'GPS & Monitoring', 'DriverBehaviorEvent' => 'GPS & Monitoring',
        'MaintenanceLog' => 'Maintenance', 'ServiceSchedule' => 'Maintenance',
        'VehicleInspection' => 'Maintenance', 'MaintenancePrediction' => 'Maintenance',
        'DamageReport' => 'Maintenance', 'InsurancePolicy' => 'Maintenance',
        'Invoice' => 'Finance', 'Payment' => 'Finance', 'Expense' => 'Finance',
        'BankAccount' => 'Finance', 'JournalEntry' => 'Finance', 'ChartOfAccount' => 'Finance',
        'AccountingPeriod' => 'Finance', 'SupplierInvoice' => 'Finance', 'BankStatementImport' => 'Finance',
        'BankStatementLine' => 'Finance', 'PaymentTransaction' => 'Finance', 'PaymentMethod' => 'Finance',
        'Supplier' => 'Procurement & Inventory', 'PurchaseRequisition' => 'Procurement & Inventory',
        'SparePartPurchaseOrder' => 'Procurement & Inventory', 'GoodsReceipt' => 'Procurement & Inventory',
        'Warehouse' => 'Procurement & Inventory', 'InventoryStock' => 'Procurement & Inventory',
        'StockMovement' => 'Procurement & Inventory', 'StockTransfer' => 'Procurement & Inventory',
        'SparePart' => 'Procurement & Inventory',
        'Page' => 'CMS & Marketing', 'BlogPost' => 'CMS & Marketing', 'Media' => 'CMS & Marketing',
        'Menu' => 'CMS & Marketing', 'Faq' => 'CMS & Marketing', 'Testimonial' => 'CMS & Marketing',
        'PromoVoucher' => 'CMS & Marketing', 'SeasonPeriod' => 'CMS & Marketing',
        'User' => 'Settings', 'NotificationTemplate' => 'Settings', 'NotificationQueue' => 'Settings',
        'Provider' => 'Settings',
    ];

    /** Resources kept in the compact sidebar. Every other resource remains routable and searchable. */
    private const PRIMARY_NAVIGATION = [
        'Quotation', 'Booking', 'RentalOrder', 'RentalExtension', 'HandoverRecord', 'ReturnRecord',
        'Vehicle', 'Driver', 'Category',
        'Customer', 'CorporateAccount', 'BlacklistEntry',
        'GpsTracker', 'GpsAlert',
        'MaintenanceLog', 'ServiceSchedule', 'SparePart',
        'Invoice', 'Payment', 'Expense', 'BankAccount', 'JournalEntry',
        'Supplier', 'PurchaseRequisition', 'SparePartPurchaseOrder', 'GoodsReceipt',
        'InventoryStock',
        'Page', 'BlogPost', 'Media', 'Menu', 'User', 'Location', 'NotificationTemplate',
    ];

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
        return static::canViewAny()
            && in_array(class_basename(static::getModel()), self::PRIMARY_NAVIGATION, true);
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return self::NAVIGATION_GROUPS[class_basename(static::getModel())]
            ?? parent::getNavigationGroup();
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

        // Navigation authorization is evaluated once for every registered
        // resource. Loading the relationship once avoids an EXISTS query for
        // every sidebar item (there are dozens of enterprise resources).
        if ($user->loadMissing('roles')->roles->isNotEmpty()) {
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
