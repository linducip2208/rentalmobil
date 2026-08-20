<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerLogin;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerLoginFactory extends Factory
{
    protected $model = CustomerLogin::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'email' => fake()->unique()->safeEmail(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'remember_token' => null,
        ];
    }
}
