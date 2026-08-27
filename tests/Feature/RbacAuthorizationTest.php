<?php

namespace Tests\Feature;

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\VehicleResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RbacAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_navigation_and_resource_actions_follow_granular_permissions(): void
    {
        $user = User::findOrFail(1);
        $user->update(['role' => 'read_only']);

        $role = Role::create(['name' => 'Fleet Limited', 'guard_name' => 'web']);
        $viewAny = Permission::create(['name' => 'view_any.vehicle', 'guard_name' => 'web']);
        $view = Permission::create(['name' => 'view.vehicle', 'guard_name' => 'web']);
        $role->syncPermissions([$viewAny, $view]);
        $user->syncRoles([$role]);

        $this->actingAs($user);

        $this->assertTrue(VehicleResource::shouldRegisterNavigation());
        $this->assertTrue(VehicleResource::canViewAny());
        $this->assertFalse(VehicleResource::canCreate());
        $this->assertFalse(CategoryResource::shouldRegisterNavigation());
    }
}
