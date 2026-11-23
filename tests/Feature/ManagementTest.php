<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->staff());
        Storage::fake('public');
    }

    private function image(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('photo.png', file_get_contents(database_path('seeders/images/notebook.png')));
    }

    public function test_all_management_views_render(): void
    {
        $product = $this->product();
        $customer = $this->customer();
        $staff = $this->staff();
        foreach (['/products', '/products?archived=1', '/products/create', '/products/'.$product->id.'/edit',
            '/customers', '/customers/create', '/customers/'.$customer->id.'/edit',
            '/users', '/users/create', '/users/'.$staff->id.'/edit', '/sales', '/sales/create'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_product_crud_and_image_replacement(): void
    {
        $this->post('/products', [
            'name' => 'Pen', 'price' => '15.25', 'stock_quantity' => 8, 'image' => $this->image(),
        ])->assertRedirect(route('products.index'));
        $product = Product::firstOrFail();
        $oldImage = $product->image;
        Storage::disk('public')->assertExists($oldImage);
        $this->get('/products')->assertSee('Pen')->assertSee('/storage/'.$oldImage, false);

        $this->put('/products/'.$product->id, [
            'name' => 'Blue Pen', 'price' => '17.00', 'stock_quantity' => 12,
            'original_stock_quantity' => 8, 'image' => $this->image(),
        ])->assertSessionHasNoErrors();
        $product->refresh();
        $this->assertSame('Blue Pen', $product->name);
        $this->assertSame(12, $product->stock_quantity);
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($product->image);

        $this->delete('/products/'.$product->id)->assertSessionHasNoErrors();
        $this->assertSoftDeleted($product);
        $this->get('/products?archived=1')->assertSee('Blue Pen');
        $this->patch('/products/'.$product->id.'/restore')->assertSessionHasNoErrors();
        $this->assertNotNull(Product::find($product->id));
    }

    public function test_stale_product_form_cannot_overwrite_new_stock(): void
    {
        $product = $this->product(['stock_quantity' => 8]);
        $this->put('/products/'.$product->id, [
            'name' => 'Changed', 'price' => '15.00', 'stock_quantity' => 20, 'original_stock_quantity' => 10,
            'image' => $this->image(),
        ])->assertSessionHasErrors('stock_quantity');
        $this->assertSame(8, $product->fresh()->stock_quantity);
        $this->assertSame('Notebook', $product->fresh()->name);
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    public function test_invalid_product_values_are_rejected(): void
    {
        $this->post('/products', ['name' => '', 'price' => '-1', 'stock_quantity' => -1])
            ->assertSessionHasErrors(['name', 'price', 'stock_quantity']);
        $this->post('/products', ['name' => 'Pen', 'price' => '1.999', 'stock_quantity' => '2.5'])
            ->assertSessionHasErrors(['price', 'stock_quantity']);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_unsafe_and_oversized_images_are_rejected(): void
    {
        foreach ([
            UploadedFile::fake()->createWithContent('image.png', '<?php echo "bad";'),
            UploadedFile::fake()->createWithContent('image.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
            $this->image()->size(2049),
        ] as $file) {
            $this->post('/products', ['name' => 'Pen', 'price' => '12.00', 'stock_quantity' => 2, 'image' => $file])
                ->assertSessionHasErrors('image');
        }
        $this->assertDatabaseCount('products', 0);
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    public function test_customer_crud_and_optional_phone(): void
    {
        $this->post('/customers', ['full_name' => 'Maria Cruz', 'email' => 'MARIA@example.com'])
            ->assertRedirect(route('customers.index'));
        $customer = Customer::firstOrFail();
        $this->assertSame('maria@example.com', $customer->email);
        $this->assertNull($customer->phone);
        $this->get('/customers')->assertSee('Maria Cruz');
        $this->put('/customers/'.$customer->id, [
            'full_name' => 'Maria Santos', 'email' => 'maria@example.com', 'phone' => '+63 917 123 4567',
        ])->assertSessionHasNoErrors();
        $this->assertSame('Maria Santos', $customer->fresh()->full_name);
        $this->delete('/customers/'.$customer->id)->assertSessionHasNoErrors();
        $this->assertSoftDeleted($customer);
    }

    public function test_customer_email_and_phone_validation(): void
    {
        $customer = $this->customer();
        $this->post('/customers', ['full_name' => 'Another', 'email' => $customer->email])
            ->assertSessionHasErrors('email');
        $this->post('/customers', ['full_name' => '', 'email' => 'invalid', 'phone' => 'letters'])
            ->assertSessionHasErrors(['full_name', 'email', 'phone']);
    }

    public function test_staff_crud_hashes_password_and_stores_avatar(): void
    {
        $this->post('/users', [
            'full_name' => 'New Staff', 'email' => 'new@example.com', 'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!', 'avatar' => $this->image(),
        ])->assertRedirect(route('users.index'));
        $staff = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('NewPassword123!', $staff->password));
        $this->assertNotSame('NewPassword123!', $staff->password);
        $this->assertArrayNotHasKey('password', $staff->toArray());
        Storage::disk('public')->assertExists($staff->avatar);
        $this->get('/users')->assertSee('New Staff')->assertSee('/storage/'.$staff->avatar, false)->assertDontSee($staff->password);
        $hash = $staff->password;
        $this->put('/users/'.$staff->id, [
            'full_name' => 'Updated Staff', 'email' => 'new@example.com', 'password' => '', 'password_confirmation' => '',
        ])->assertSessionHasNoErrors();
        $this->assertSame($hash, $staff->fresh()->password);
        $this->delete('/users/'.$staff->id)->assertSessionHasNoErrors();
        $this->assertSoftDeleted($staff);
    }

    public function test_staff_password_change_and_avatar_replacement(): void
    {
        $staff = $this->staff();
        Storage::disk('public')->put('avatars/old.png', 'old');
        $staff->update(['avatar' => 'avatars/old.png']);
        $this->put('/users/'.$staff->id, [
            'full_name' => $staff->full_name, 'email' => $staff->email,
            'password' => 'Changed123!', 'password_confirmation' => 'Changed123!', 'avatar' => $this->image(),
        ])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('Changed123!', $staff->fresh()->password));
        Storage::disk('public')->assertMissing('avatars/old.png');
        Storage::disk('public')->assertExists($staff->fresh()->avatar);
    }

    public function test_staff_validation_and_self_deletion_guard(): void
    {
        $current = auth()->user();
        $this->post('/users', [
            'full_name' => 'Bad Staff', 'email' => $current->email,
            'password' => 'short', 'password_confirmation' => 'different',
            'avatar' => UploadedFile::fake()->createWithContent('avatar.png', 'not an image'),
        ])->assertSessionHasErrors(['email', 'password', 'avatar']);
        $this->delete('/users/'.$current->id)->assertSessionHasErrors('user');
        $this->assertNotNull(User::find($current->id));
    }

    public function test_saved_text_is_escaped_in_views(): void
    {
        $this->product(['name' => '<script>alert(1)</script>']);
        $this->get('/products')->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_pagination_renders(): void
    {
        for ($i = 0; $i < 13; $i++) {
            $this->product(['name' => 'Item '.$i]);
        }
        $this->get('/products')->assertOk()->assertSee('Page 1 of 2');
        $this->get('/products?page=2')->assertOk()->assertSee('Page 2 of 2');
    }
}
