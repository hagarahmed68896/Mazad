<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class OtpAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test registration redirects to verify-otp and does not log the user in immediately.
     */
    public function test_registration_requires_otp(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '512345678',
            'password' => 'password123',
            'account_type' => 'individual',
        ]);

        $response->assertRedirect(route('verify-otp'));
        $this->assertFalse(Auth::check());
        $this->assertEquals(session('is_registering'), true);
        $this->assertNotNull(session('pending_user_id'));
    }

    /**
     * Test normal user login bypasses OTP and logs user in directly.
     */
    public function test_normal_login_bypasses_otp(): void
    {
        $user = User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'phone' => '512345678',
            'password' => bcrypt('password123'),
            'account_type' => 'individual',
        ]);

        $response = $this->post(route('login.post'), [
            'phone' => '512345678',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertTrue(Auth::check());
        $this->assertEquals(Auth::id(), $user->id);
        $this->assertNull(session('pending_user_id'));
    }

    /**
     * Test admin login redirects to admin.verify-otp.
     */
    public function test_admin_login_requires_otp(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@mazad.com',
            'phone' => '587654321',
            'password' => bcrypt('password123'),
            'account_type' => 'individual',
        ]);

        $response = $this->post(route('admin.post-login'), [
            'phone' => '587654321',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.verify-otp'));
        $this->assertFalse(Auth::guard('admin')->check());
        $this->assertEquals(session('pending_user_id'), $admin->id);
    }

    /**
     * Test my-sales page loads successfully.
     */
    public function test_my_sales_page_loads_successfully(): void
    {
        $user = User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'phone' => '512345678',
            'password' => bcrypt('password123'),
            'account_type' => 'individual',
        ]);

        $response = $this->actingAs($user)->get(route('sales.my'));
        $response->assertStatus(200);
    }

    /**
     * Test my-sales page loads successfully when category or buyer are null/missing.
     */
    public function test_my_sales_page_loads_with_null_relations(): void
    {
        $user = User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'phone' => '512345678',
            'password' => bcrypt('password123'),
            'account_type' => 'individual',
        ]);

        $category = \App\Models\Category::create([
            'name' => 'Test Category',
            'image' => 'placeholder.png',
        ]);

        $ad = \App\Models\Ad::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'city_id' => null,
            'title' => 'Test Ad',
            'type' => 'direct_purchase',
            'price' => 1000,
            'status' => 'active',
        ]);

        $buyer = User::create([
            'name' => 'Buyer User',
            'email' => 'buyer@example.com',
            'phone' => '598765432',
            'password' => bcrypt('password123'),
            'account_type' => 'individual',
        ]);

        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        $order = \App\Models\Order::create([
            'order_number' => 'TEST1234',
            'ad_id' => $ad->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $user->id,
            'final_price' => 1000,
            'status' => 'paid',
        ]);

        // Delete to simulate missing relations
        $category->delete();
        $buyer->delete();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        $response = $this->actingAs($user)->get(route('sales.my'));
        $response->assertStatus(200);
    }
}
