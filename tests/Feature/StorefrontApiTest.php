<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class StorefrontApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_registration_creates_user_and_returns_bearer_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'API Shopper',
            'email' => 'api-shopper@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.email', 'api-shopper@example.test')
            ->assertJsonStructure(['data' => ['user' => ['id', 'name', 'email'], 'token']])
            ->assertJsonMissingPath('data.user.password');

        $this->assertDatabaseHas('users', ['email' => 'api-shopper@example.test']);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_api_login_rejects_invalid_credentials_and_returns_token_for_valid_user(): void
    {
        $user = User::create([
            'name' => 'API Shopper',
            'email' => 'api-login@example.test',
            'password' => 'Password123!',
        ]);

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'Password123!'])
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonStructure(['data' => ['token']]);
    }

    public function test_cart_api_requires_a_token_and_returns_only_authenticated_users_items(): void
    {
        $this->getJson('/api/cart')->assertUnauthorized();

        $user = User::create([
            'name' => 'Cart Owner',
            'email' => 'cart-owner@example.test',
            'password' => 'Password123!',
        ]);
        $product = $this->createProduct();
        $cart = Cart::create(['user_id' => $user->id, 'session_id' => null]);
        $cart->items()->create([
            'product_id' => $product->id,
            'size' => 'Medium',
            'quantity' => 2,
            'price' => $product->price,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/cart')
            ->assertOk()
            ->assertJsonPath('data.0.product_name', $product->name)
            ->assertJsonPath('data.0.quantity', 2)
            ->assertJsonPath('meta.total', '250.00');
    }

    public function test_cart_api_adds_updates_and_removes_items_with_validation(): void
    {
        $user = User::create([
            'name' => 'Cart Editor',
            'email' => 'cart-editor@example.test',
            'password' => 'Password123!',
        ]);
        $product = $this->createProduct();
        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other-cart@example.test',
            'password' => 'Password123!',
        ]);
        $otherCart = Cart::create(['user_id' => $otherUser->id, 'session_id' => null]);
        $otherItem = $otherCart->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 0])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('quantity');

        $created = $this->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'size' => 'Medium',
        ])->assertCreated()->assertJsonPath('data.quantity', 2);
        $itemId = $created->json('data.id');

        $this->patchJson("/api/cart/items/{$itemId}", ['quantity' => 3])
            ->assertOk()
            ->assertJsonPath('data.quantity', 3);

        $this->deleteJson("/api/cart/items/{$otherItem->id}")->assertNotFound();
        $this->assertDatabaseHas('cart_items', ['id' => $otherItem->id]);

        $this->deleteJson("/api/cart/items/{$itemId}")
            ->assertOk()
            ->assertJsonPath('message', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }

    public function test_checkout_api_preserves_cart_after_failure_and_creates_cod_order(): void
    {
        $user = User::create([
            'name' => 'Checkout API User',
            'email' => 'checkout-api@example.test',
            'password' => 'Password123!',
        ]);
        $product = $this->createProduct();
        $cart = Cart::create(['user_id' => $user->id, 'session_id' => null]);
        $cart->items()->create([
            'product_id' => $product->id,
            'size' => 'Medium',
            'quantity' => 2,
            'price' => $product->price,
        ]);
        $checkout = [
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => '0123456789',
            'shipping_address' => '123 API Test Street',
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/orders', $checkout + [
                'payment_method' => 'simulated',
                'simulated_result' => 'failure',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('payment');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('cart_items', 1);

        $response = $this->postJson('/api/orders', $checkout + ['payment_method' => 'cod']);
        $response->assertCreated()
            ->assertJsonPath('data.payment_method', 'cod')
            ->assertJsonPath('data.payment_status', 'unpaid')
            ->assertJsonPath('data.total', '250.00')
            ->assertJsonPath('data.items.0.product_name', $product->name)
            ->assertJsonPath('data.items.0.quantity', 2);

        $orderNumber = $response->json('data.order_number');
        $this->assertDatabaseCount('cart_items', 0);
        Auth::forgetGuards();
        $this->getJson("/api/orders/{$orderNumber}")->assertUnauthorized();
        $this->actingAs($user, 'sanctum')
            ->getJson("/api/orders/{$orderNumber}")
            ->assertOk()
            ->assertJsonPath('data.order_number', $orderNumber);
    }

    public function test_order_api_hides_another_users_order(): void
    {
        $owner = User::create([
            'name' => 'Order Owner',
            'email' => 'order-owner-api@example.test',
            'password' => 'Password123!',
        ]);
        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other-order-api@example.test',
            'password' => 'Password123!',
        ]);
        $order = $owner->orders()->create([
            'order_number' => 'api-test-order',
            'customer_name' => $owner->name,
            'customer_email' => $owner->email,
            'customer_phone' => '0123456789',
            'shipping_address' => 'Private address',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'status' => 'placed',
            'total' => '10.00',
        ]);

        $this->actingAs($otherUser, 'sanctum')
            ->getJson("/api/orders/{$order->order_number}")
            ->assertNotFound();
    }

    public function test_api_logout_revokes_only_the_current_token(): void
    {
        $user = User::create([
            'name' => 'Logout User',
            'email' => 'logout-api@example.test',
            'password' => 'Password123!',
        ]);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();
        Auth::forgetGuards();
        $this->withToken($token)->getJson('/api/cart')->assertUnauthorized();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    private function createProduct(): Product
    {
        return Product::create([
            'name' => 'API Test Product',
            'brand' => 'Test Brand',
            'image' => 'api-test-product.jpg',
            'price' => 125,
        ]);
    }
}
