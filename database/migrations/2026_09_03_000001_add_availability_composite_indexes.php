<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Composite availability indexes for the hot period-overlap queries used by
 * the AvailabilityEngine (bookings/orders), the storefront catalog (vehicles),
 * and the vehicle gallery (photos). Plain single-column indexes already exist;
 * these composites match the WHERE + range shape of the availability queries.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['vehicle_id', 'status', 'start_date', 'end_date'], 'bookings_availability_idx');
        });

        Schema::table('rental_orders', function (Blueprint $table) {
            $table->index(['vehicle_id', 'status', 'start_date', 'end_date'], 'rental_orders_availability_idx');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->index(['is_active', 'status', 'category_id', 'daily_rate'], 'vehicles_catalog_idx');
            $table->index(['location_id', 'is_active'], 'vehicles_location_active_idx');
        });

        Schema::table('vehicle_photos', function (Blueprint $table) {
            $table->index(['vehicle_id', 'sort_order', 'is_primary'], 'vehicle_photos_gallery_idx');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_availability_idx');
        });

        Schema::table('rental_orders', function (Blueprint $table) {
            $table->dropIndex('rental_orders_availability_idx');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex('vehicles_catalog_idx');
            $table->dropIndex('vehicles_location_active_idx');
        });

        Schema::table('vehicle_photos', function (Blueprint $table) {
            $table->dropIndex('vehicle_photos_gallery_idx');
        });
    }
};
