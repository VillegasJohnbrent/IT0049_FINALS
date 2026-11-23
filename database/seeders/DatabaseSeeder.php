<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $email = strtolower((string) config('seed.email'));
        $password = config('seed.password');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || ! is_string($password) || strlen($password) < 8 || strlen($password) > 72) {
            throw new RuntimeException('Set SEED_STAFF_EMAIL and an 8–72 character SEED_STAFF_PASSWORD in .env.');
        }
        if (app()->isProduction() && $password === 'ChangeMe123!') {
            throw new RuntimeException('Set a private seed password before production seeding.');
        }

        User::withTrashed()->firstOrCreate(['email' => $email], [
            'full_name' => config('seed.name'),
            'password' => Hash::make($password),
        ]);

        if (! app()->environment('local', 'testing')) {
            return;
        }

        $products = [
            ['name' => 'Notebook', 'price' => '45.00', 'stock_quantity' => 20, 'image' => 'notebook.png'],
            ['name' => 'Ballpen', 'price' => '12.50', 'stock_quantity' => 50, 'image' => 'ballpen.png'],
            ['name' => 'Bottled Water', 'price' => '20.00', 'stock_quantity' => 3, 'image' => 'water.png'],
        ];
        foreach ($products as $data) {
            $filename = $data['image'];
            $data['image'] = 'products/'.$filename;
            if (! Storage::disk('public')->exists($data['image'])) {
                Storage::disk('public')->put($data['image'], file_get_contents(database_path('seeders/images/'.$filename)));
            }
            Product::withTrashed()->firstOrCreate(['name' => $data['name']], $data);
        }

        Customer::withTrashed()->firstOrCreate(['email' => 'customer@example.com'], [
            'full_name' => 'Sample Customer', 'phone' => null,
        ]);
    }
}
