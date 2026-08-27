<?php

namespace Tests\Feature;

use App\Filament\Resources\AccountingPeriodResource;
use App\Filament\Resources\MediaResource;
use App\Filament\Resources\MenuResource;
use App\Filament\Resources\PageResource;
use App\Filament\Resources\PurchaseRequisitionResource;
use App\Models\Location;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EnterpriseRoleScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_cms_editor_has_cms_access_without_accounting_access(): void
    {
        $role = Role::create(['name' => 'CMS Editor', 'guard_name' => 'web']);
        $role->syncPermissions(collect(['page', 'media', 'menu'])->flatMap(fn ($model) => collect(['view_any', 'view', 'create'])->map(fn ($action) => Permission::findOrCreate("{$action}.{$model}", 'web'))));
        $user = User::create(['name' => 'CMS Editor Test', 'email' => 'cms-'.Str::random(6).'@test.local', 'password' => Hash::make('password'), 'role' => 'cms_editor', 'is_active' => true]);
        $user->assignRole('CMS Editor');
        $this->actingAs($user);
        $this->assertTrue(PageResource::canViewAny());
        $this->assertTrue(PageResource::canCreate());
        $this->assertTrue(MediaResource::canViewAny());
        $this->assertTrue(MenuResource::canViewAny());
        $this->assertFalse(AccountingPeriodResource::canViewAny());
        $this->assertFalse(PurchaseRequisitionResource::canViewAny());
    }

    public function test_procurement_role_can_submit_pr_but_not_access_accounting_period(): void
    {
        $role = Role::create(['name' => 'Procurement', 'guard_name' => 'web']);
        $role->syncPermissions([Permission::findOrCreate('view_any.purchase_requisition', 'web'), Permission::findOrCreate('submit.purchase_requisition', 'web')]);
        $user = User::create(['name' => 'Procurement Test', 'email' => 'proc-'.Str::random(6).'@test.local', 'password' => Hash::make('password'), 'role' => 'procurement', 'is_active' => true]);
        $user->assignRole('Procurement');
        $this->actingAs($user);
        $this->assertTrue(PurchaseRequisitionResource::canViewAny());
        $this->assertTrue($user->can('submit.purchase_requisition'));
        $this->assertFalse(AccountingPeriodResource::canViewAny());
    }

    public function test_supplier_invoices_are_isolated_by_branch_for_non_global_users(): void
    {
        $suffix = Str::lower(Str::random(6));
        $a = Location::create(['name' => 'A '.$suffix, 'slug' => 'a-'.$suffix, 'is_active' => true]);
        $b = Location::create(['name' => 'B '.$suffix, 'slug' => 'b-'.$suffix, 'is_active' => true]);
        $supplier = Supplier::create(['code' => 'S-'.$suffix, 'name' => 'Supplier', 'is_active' => true]);
        $user = User::create(['name' => 'Branch User', 'email' => 'branch-'.$suffix.'@test.local', 'password' => Hash::make('password'), 'role' => 'finance', 'location_id' => $a->id, 'is_active' => true]);
        $own = SupplierInvoice::create(['supplier_id' => $supplier->id, 'location_id' => $a->id, 'invoice_date' => now(), 'due_date' => now(), 'total' => 100, 'status' => 'draft']);
        SupplierInvoice::withoutGlobalScopes()->create(['supplier_id' => $supplier->id, 'location_id' => $b->id, 'invoice_date' => now(), 'due_date' => now(), 'total' => 200, 'status' => 'draft']);
        $this->actingAs($user);
        $this->assertSame([$own->id], SupplierInvoice::pluck('id')->all());
    }
}
