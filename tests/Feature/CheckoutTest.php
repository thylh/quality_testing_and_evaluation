<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_opening_checkout(): void
    {
        $this->get(route('checkout'))->assertRedirect(route('login'));
    }

    public function test_checkout_redirects_to_cart_when_the_cart_is_empty(): void
    {
        $user = $this->createUser('empty-checkout@example.com');

        $this->actingAs($user)
            ->get(route('checkout'))
            ->assertRedirect(route('cart'))
            ->assertSessionHas('error');
    }

    public function test_invalid_checkout_details_and_payment_method_are_rejected(): void
    {
        [$user] = $this->createCartForUser();

        $this->actingAs($user)
            ->from(route('checkout'))
            ->post(route('checkout.store'), $this->checkoutData([
                'customer_name' => '',
                'customer_email' => 'not-an-email',
                'customer_phone' => str_repeat('1', 31),
                'shipping_address' => '',
                'payment_method' => 'wire-transfer',
            ]))
            ->assertRedirect(route('checkout'))
            ->assertSessionHasErrors([
                'customer_name',
                'customer_email',
                'customer_phone',
                'shipping_address',
                'payment_method',
            ]);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_simulated_payment_requires_a_supported_outcome(): void
    {
        [$user] = $this->createCartForUser();

        $this->actingAs($user)
            ->from(route('checkout'))
            ->post(route('checkout.store'), $this->checkoutData([
                'payment_method' => 'simulated',
                'simulated_result' => 'processing',
            ]))
            ->assertRedirect(route('checkout'))
            ->assertSessionHasErrors('simulated_result');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_cod_order_is_saved_with_item_snapshot_and_clears_cart(): void
    {
        [$user, $cart, $product] = $this->createCartForUser();

        $response = $this->actingAs($user)->post(route('checkout.store'), $this->checkoutData([
            'payment_method' => 'cod',
        ]));

        $order = Order::with('items')->firstOrFail();
        $response->assertRedirect(route('checkout.orders.show', $order->order_number));
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'total' => '501.50',
        ]);
        $this->assertSame($product->name, $order->items->first()->product_name);
        $this->assertSame('501.50', $order->total);
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertDatabaseHas('carts', ['id' => $cart->id, 'user_id' => $user->id]);
    }

    public function test_successful_simulated_payment_marks_order_paid(): void
    {
        [$user] = $this->createCartForUser();

        $this->actingAs($user)
            ->post(route('checkout.store'), $this->checkoutData([
                'payment_method' => 'simulated',
                'simulated_result' => 'success',
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'payment_method' => 'simulated',
            'payment_status' => 'paid',
        ]);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_failed_simulated_payment_preserves_cart_and_creates_no_order(): void
    {
        [$user] = $this->createCartForUser();

        $this->actingAs($user)
            ->from(route('checkout'))
            ->post(route('checkout.store'), $this->checkoutData([
                'payment_method' => 'simulated',
                'simulated_result' => 'failure',
            ]))
            ->assertRedirect(route('checkout'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_user_cannot_view_another_users_order(): void
    {
        $owner = $this->createUser('order-owner@example.com');
        $otherUser = $this->createUser('other-order-user@example.com');
        $order = Order::create([
            'user_id' => $owner->id,
            'order_number' => 'test-order-number',
            'customer_name' => $owner->name,
            'customer_email' => $owner->email,
            'customer_phone' => '0123456789',
            'shipping_address' => 'Test address',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'status' => 'placed',
            'total' => 100,
        ]);

        $this->actingAs($otherUser)
            ->get(route('checkout.orders.show', $order->order_number))
            ->assertNotFound();
    }

    private function createCartForUser(): array
    {
        $user = $this->createUser('checkout-' . uniqid() . '@example.com');
        $product = Product::create([
            'name' => 'Checkout Product',
            'brand' => 'Test Brand',
            'image' => 'checkout-product.jpg',
            'price' => 250.75,
        ]);
        $cart = Cart::create(['user_id' => $user->id, 'session_id' => null]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'size' => 'Medium',
            'quantity' => 2,
            'price' => 250.75,
        ]);

        return [$user, $cart, $product];
    }

    private function createUser(string $email): User
    {
        return User::create([
            'name' => 'Checkout User',
            'email' => $email,
            'password' => 'password123',
        ]);
    }

    private function checkoutData(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Checkout User',
            'customer_email' => 'checkout@example.com',
            'customer_phone' => '0123456789',
            'shipping_address' => '123 Test Street',
            'payment_method' => 'cod',
        ], $overrides);
    }
}