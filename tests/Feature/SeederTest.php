<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeederTest extends TestCase
{
    public function test_seeding_is_repeatable_without_resetting_stock_or_password(): void
    {
        Storage::fake('public');
        config(['seed.email' => 'seed@example.com', 'seed.password' => 'SeedPassword123!', 'seed.name' => 'Seed Staff']);
        $this->seed();
        $product = Product::firstOrFail();
        $product->update(['stock_quantity' => 1]);
        $staff = User::firstOrFail();
        $staff->update(['password' => Hash::make('Changed123!')]);
        $this->seed();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('products', 3);
        $this->assertDatabaseCount('customers', 1);
        $this->assertSame(1, $product->fresh()->stock_quantity);
        $this->assertTrue(Hash::check('Changed123!', $staff->fresh()->password));
        foreach (Product::all() as $item) {
            Storage::disk('public')->assertExists($item->image);
        }
    }
}
