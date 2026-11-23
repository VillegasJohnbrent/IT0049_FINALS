<?php

namespace Tests;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function staff(): User
    {
        return User::create([
            'full_name' => 'Test Staff',
            'email' => 'staff'.User::withTrashed()->count().'@example.com',
            'password' => Hash::make('Password123!'),
        ]);
    }

    protected function product(array $data = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Notebook', 'price' => '12.50', 'stock_quantity' => 10,
        ], $data));
    }

    protected function customer(): Customer
    {
        return Customer::create([
            'full_name' => 'Test Customer',
            'email' => 'customer'.Customer::withTrashed()->count().'@example.com',
            'phone' => null,
        ]);
    }
}
