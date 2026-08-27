<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Location;
use App\Models\PurchaseRequisition;
use App\Models\SparePart;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class EnterpriseProcurementSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@rentalmobil.test')->firstOrFail();
        $category = Category::firstOrCreate(['slug' => 'suku-cadang'], ['name' => 'Suku Cadang', 'is_active' => true]);
        foreach (Location::all() as $index => $location) {
            Warehouse::firstOrCreate(['code' => 'GD-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)], ['location_id' => $location->id, 'name' => 'Gudang '.$location->name, 'address' => $location->address, 'is_active' => true]);
        }
        $supplier = Supplier::firstOrCreate(['code' => 'SUP-NUSANTARA'], ['name' => 'PT Suku Cadang Nusantara', 'contact_person' => 'Andi Pratama', 'phone' => '021-555-8800', 'email' => 'sales@sukucadang.test', 'address' => 'Jakarta, Indonesia', 'tax_number' => '00.000.000.0-000.000', 'payment_terms_days' => 30, 'credit_limit' => 100000000, 'rating' => 4.75, 'is_active' => true]);
        $part = SparePart::firstOrCreate(['part_number' => 'OLI-5W30'], ['name' => 'Oli Mesin 5W-30', 'category_id' => $category->id, 'unit_price' => 125000, 'stock' => 0, 'min_stock' => 12, 'supplier_name' => $supplier->name, 'supplier_phone' => $supplier->phone]);
        $warehouse = Warehouse::orderBy('id')->first();
        if ($warehouse) {
            $pr = PurchaseRequisition::firstOrCreate(['requisition_number' => 'PR-DEMO-001'], ['location_id' => $warehouse->location_id, 'warehouse_id' => $warehouse->id, 'requested_by' => $user->id, 'department' => 'Workshop', 'request_date' => now(), 'required_date' => now()->addDays(7), 'priority' => 'high', 'status' => 'pending_approval', 'estimated_total' => 3000000, 'notes' => 'Pengadaan stok servis berkala']);
            $pr->items()->firstOrCreate(['spare_part_id' => $part->id], ['quantity' => 24, 'estimated_unit_price' => 125000, 'estimated_total' => 3000000]);
        }
    }
}
