<?php

namespace Database\Seeders;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    private const ACTIONS = [
        'view_any', 'view', 'create', 'update', 'delete', 'restore',
        'force_delete', 'export', 'approve', 'reject', 'submit', 'cancel', 'post', 'close', 'reopen',
    ];

    private const ROLES = [
        'Super Admin', 'Owner', 'Director', 'Branch Manager', 'Rental Manager',
        'Reservation Staff', 'Fleet Staff', 'Dispatcher', 'Finance Manager',
        'Finance Staff', 'Accountant', 'Procurement', 'Warehouse', 'Mechanic',
        'Driver', 'Marketing', 'CMS Editor', 'Auditor', 'Read Only',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $resources = Filament::getPanel('admin')->getResources();
        $permissions = collect($resources)
            ->map(fn (string $resource): string => Str::snake(class_basename($resource::getModel())))
            ->unique()
            ->flatMap(fn (string $model) => collect(self::ACTIONS)->map(fn (string $action) => "{$action}.{$model}"))
            ->merge(['view_any.system_setting', 'update.system_setting'])
            ->unique()
            ->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));

        $roles = collect(self::ROLES)->mapWithKeys(
            fn (string $name) => [$name => Role::findOrCreate($name, 'web')]
        );

        $all = Permission::query()->pluck('name')->all();
        $read = array_values(array_filter($all, fn (string $name) => Str::startsWith($name, ['view_any.', 'view.'])));
        $operational = array_values(array_filter($all, fn (string $name) => ! Str::startsWith($name, 'force_delete.')));

        $roles['Super Admin']->syncPermissions($all);
        $roles['Owner']->syncPermissions($all);
        $roles['Director']->syncPermissions(array_merge($read, $this->actions($all, ['export', 'approve', 'close'])));
        $roles['Branch Manager']->syncPermissions($operational);
        $roles['Rental Manager']->syncPermissions($this->forModels($all, [
            'Booking', 'BookingWaitlist', 'Quotation', 'RentalOrder', 'RentalExtension',
            'Delivery', 'HandoverRecord', 'ReturnRecord', 'Customer', 'CustomerDocument',
            'Vehicle', 'Driver', 'Invoice', 'Payment', 'Deposit',
        ], ['view_any', 'view', 'create', 'update', 'export', 'approve', 'cancel', 'close']));
        $roles['Reservation Staff']->syncPermissions($this->forModels($all, ['Booking', 'BookingWaitlist', 'Quotation', 'Customer', 'Vehicle'], ['view_any', 'view', 'create', 'update']));
        $roles['Fleet Staff']->syncPermissions($this->forModels($all, ['Vehicle', 'Category', 'Brand', 'VehicleInspection', 'FuelLog', 'KmLog', 'InsurancePolicy', 'ServiceSchedule', 'MaintenanceLog'], ['view_any', 'view', 'create', 'update', 'export']));
        $roles['Dispatcher']->syncPermissions($this->forModels($all, ['RentalOrder', 'Delivery', 'Driver', 'GpsTracker', 'GpsAlert', 'GpsLog'], ['view_any', 'view', 'create', 'update']));
        $roles['Finance Manager']->syncPermissions($this->financePermissions($all, ['view_any', 'view', 'create', 'update', 'export', 'approve', 'post', 'close']));
        $roles['Finance Staff']->syncPermissions($this->financePermissions($all, ['view_any', 'view', 'create', 'update', 'export']));
        $roles['Accountant']->syncPermissions($this->financePermissions($all, ['view_any', 'view', 'create', 'update', 'export', 'post', 'close']));
        $roles['Procurement']->syncPermissions($this->forModels($all, ['Supplier', 'PurchaseRequisition', 'SparePart', 'SparePartPurchaseOrder', 'GoodsReceipt', 'SupplierInvoice', 'Warehouse', 'InventoryStock', 'StockMovement'], ['view_any', 'view', 'create', 'update', 'export', 'submit', 'approve', 'reject', 'cancel']));
        $roles['Warehouse']->syncPermissions($this->forModels($all, ['SparePart', 'SparePartPurchaseOrder', 'GoodsReceipt', 'Warehouse', 'InventoryStock', 'StockMovement', 'StockTransfer'], ['view_any', 'view', 'create', 'update', 'export', 'submit', 'approve']));
        $roles['Mechanic']->syncPermissions($this->forModels($all, ['Vehicle', 'ServiceSchedule', 'MaintenanceLog', 'MaintenancePrediction', 'SparePart', 'VehicleInspection'], ['view_any', 'view', 'create', 'update']));
        $roles['Driver']->syncPermissions($this->forModels($all, ['RentalOrder', 'Delivery', 'Vehicle', 'GpsAlert'], ['view_any', 'view', 'update']));
        $roles['Marketing']->syncPermissions($this->forModels($all, ['Page', 'Media', 'Menu', 'PromoVoucher', 'BlogPost', 'Faq', 'Testimonial', 'Customer'], ['view_any', 'view', 'create', 'update', 'export']));
        $roles['CMS Editor']->syncPermissions($this->forModels($all, ['Page', 'Media', 'Menu', 'BlogPost', 'Faq', 'Testimonial'], ['view_any', 'view', 'create', 'update', 'delete']));
        $roles['Auditor']->syncPermissions(array_merge($read, $this->actions($all, ['export'])));
        $roles['Read Only']->syncPermissions($read);

        $legacyMap = [
            'super_admin' => 'Super Admin', 'owner' => 'Owner', 'manager' => 'Branch Manager',
            'admin' => 'Rental Manager', 'finance' => 'Finance Staff', 'cashier' => 'Reservation Staff',
            'mechanic' => 'Mechanic', 'driver' => 'Driver',
        ];

        User::query()->each(function (User $user) use ($legacyMap): void {
            if ($role = $legacyMap[$user->role] ?? null) {
                $user->syncRoles([$role]);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** @param list<string> $permissions @param list<string> $actions */
    private function actions(array $permissions, array $actions): array
    {
        return array_values(array_filter($permissions, fn (string $name) => Str::startsWith($name, array_map(fn (string $action) => "{$action}.", $actions))));
    }

    /** @param list<string> $permissions @param list<string> $models @param list<string> $actions */
    private function forModels(array $permissions, array $models, array $actions): array
    {
        $models = array_map(fn (string $model) => Str::snake($model), $models);

        return array_values(array_filter($permissions, function (string $permission) use ($models, $actions): bool {
            [$action, $model] = explode('.', $permission, 2);

            return in_array($action, $actions, true) && in_array($model, $models, true);
        }));
    }

    /** @param list<string> $permissions @param list<string> $actions */
    private function financePermissions(array $permissions, array $actions): array
    {
        return $this->forModels($permissions, [
            'Invoice', 'Payment', 'PaymentMethod', 'PaymentTransaction', 'Deposit', 'Expense',
            'ExpenseCategory', 'BankAccount', 'BankStatementImport', 'BankStatementLine',
            'ChartOfAccount', 'JournalEntry', 'AccountingPeriod', 'SupplierInvoice', 'SupplierPayment',
        ], $actions);
    }
}
