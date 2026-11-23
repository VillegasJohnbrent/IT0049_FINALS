<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_guests_cannot_open_management_or_sales_pages(): void
    {
        $product = $this->product();
        $customer = $this->customer();
        $staff = $this->staff();
        foreach (['/products', '/products/create', '/products/'.$product->id.'/edit',
            '/customers', '/customers/create', '/customers/'.$customer->id.'/edit',
            '/users', '/users/create', '/users/'.$staff->id.'/edit', '/sales', '/sales/create'] as $path) {
            $this->get($path)->assertRedirect('/login');
        }
        foreach (['/products', '/customers', '/users', '/sales'] as $path) {
            $this->post($path, [])->assertRedirect('/login');
        }
        foreach (['/products/'.$product->id, '/customers/'.$customer->id, '/users/'.$staff->id] as $path) {
            $this->put($path, [])->assertRedirect('/login');
            $this->delete($path)->assertRedirect('/login');
        }
        $this->patch('/products/'.$product->id.'/restore')->assertRedirect('/login');
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_staff_can_log_in_and_out(): void
    {
        $staff = $this->staff();
        $this->get('/login')->assertOk()->assertSee('Staff login');
        $this->post('/login', ['email' => $staff->email, 'password' => 'Password123!'])
            ->assertRedirect(route('sales.create'));
        $this->assertAuthenticatedAs($staff);
        $this->get('/sales/create')->assertOk();
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_bad_password_is_rejected(): void
    {
        $staff = $this->staff();
        $this->post('/login', ['email' => $staff->email, 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_throttled_after_five_failures(): void
    {
        $staff = $this->staff();
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $staff->email, 'password' => 'wrong']);
        }
        $this->post('/login', ['email' => $staff->email, 'password' => 'Password123!'])
            ->assertSessionHasErrors('email');
        $this->assertStringContainsString('Too many attempts.', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_deleted_staff_cannot_log_in(): void
    {
        $staff = $this->staff();
        $staff->delete();
        $this->post('/login', ['email' => $staff->email, 'password' => 'Password123!'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_forms_require_csrf_tokens(): void
    {
        $this->app['env'] = 'local';
        try {
            $this->post('/login', ['email' => 'staff@example.com', 'password' => 'Password123!'])->assertStatus(419);
            $this->actingAs($this->staff())->post('/products', [])->assertStatus(419);
            $this->post('/sales', [])->assertStatus(419);
        } finally {
            $this->app['env'] = 'testing';
        }
    }
}
