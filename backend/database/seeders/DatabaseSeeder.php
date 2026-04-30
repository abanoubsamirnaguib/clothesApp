<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );

        collect([
            'whatsapp_number' => '201000000000',
            'store_name' => 'NuxtCommerce',
            'currency' => 'USD',
            'currency_symbol' => '$',
        ])->each(fn (string $value, string $key) => Setting::updateOrCreate(['key' => $key], ['value' => $value]));

        $this->call(ProductSeeder::class);
    }
}
