<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cart_is_attached_to_account_after_registration(): void
    {
        $product = $this->createProduct('Guest Cart Product');

        $this->post(route('addtocart', $product->id), ['size' => 'Medium', 'quantity' => 2]);
        $sessionId = session('cart_session_id');
        $guestCart = Cart::where('session_id', $sessionId)->firstOrFail();

        $this->post(route('register.store'), [
            'name' => 'New Shopper',
            'email' => 'new-shopper@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'new-shopper@example.com')->firstOrFail();
        $this->assertDatabaseHas('carts', [
            'id' => $guestCart->id,
            'user_id' => $user->id,
            'session_id' => null,
        ]);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $guestCart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        $this->get(route('cart'))->assertOk()->assertSee('Guest Cart Product');
        $this->assertNull(session('cart_session_id'));
    }

    public function test_guest_cart_items_merge_with_matching_account_items_on_login(): void
    {
        $user = User::create([
            'name' => 'Existing Shopper',
            'email' => 'existing-shopper@example.com',
            'password' => 'password123',
        ]);
        $matchingProduct = $this->createProduct('Matching Product');
        $newProduct = $this->createProduct('New Product');
        $userCart = Cart::create(['user_id' => $user->id, 'session_id' => null]);
        CartItem::create([
            'cart_id' => $userCart->id,
            'product_id' => $matchingProduct->id,
            'size' => 'Medium',
            'quantity' => 1,
            'price' => 250,
        ]);

        $this->post(route('addtocart', $matchingProduct->id), ['size' => 'Medium', 'quantity' => 2]);
        $this->post(route('addtocart', $matchingProduct->id), ['size' => 'Small', 'quantity' => 1]);
        $this->post(route('addtocart', $newProduct->id), ['size' => 'Small', 'quantity' => 1]);
        $guestCartId = Cart::where('session_id', session('cart_session_id'))->value('id');

        $this->post(route('login.store'), [
            'email' => 'existing-shopper@example.com',
            'password' => 'password123',
        ]);

        $this->assertDatabaseMissing('carts', ['id' => $guestCartId]);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $userCart->id,
            'product_id' => $matchingProduct->id,
            'size' => 'Medium',
            'quantity' => 3,
        ]);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $userCart->id,
            'product_id' => $matchingProduct->id,
            'size' => 'Small',
            'quantity' => 1,
        ]);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $userCart->id,
            'product_id' => $newProduct->id,
            'size' => 'Small',
            'quantity' => 1,
        ]);
    }

    public function test_user_cannot_remove_another_users_cart_item(): void
    {
        $owner = User::create([
            'name' => 'Cart Owner',
            'email' => 'owner@example.com',
            'password' => 'password123',
        ]);
        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
            'password' => 'password123',
        ]);
        $product = $this->createProduct('Protected Product');
        $otherCart = Cart::create(['user_id' => $otherUser->id, 'session_id' => null]);
        $item = CartItem::create([
            'cart_id' => $otherCart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 250,
        ]);

        $this->actingAs($owner)
            ->delete(route('remove', $item->id))
            ->assertNotFound();

        $this->assertDatabaseHas('cart_items', ['id' => $item->id]);
    }

    public function test_add_to_cart_rejects_invalid_quantity_and_size(): void
    {
        $product = $this->createProduct('Validated Product');
        $url = route('addtocart', $product->id);

        foreach ([0, -1, '1.5', 'many', 100] as $quantity) {
            $this->from(route('sproduct', $product->id))
                ->post($url, ['size' => 'Medium', 'quantity' => $quantity])
                ->assertSessionHasErrors('quantity');
        }

        $this->from(route('sproduct', $product->id))
            ->post($url, ['size' => 'Invalid size', 'quantity' => 1])
            ->assertSessionHasErrors('size');

        $this->assertDatabaseCount('carts', 0);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_add_to_cart_defaults_quantity_to_one_when_omitted(): void
    {
        $product = $this->createProduct('Default Quantity Product');

        $this->post(route('addtocart', $product->id), ['size' => 'Small']);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'size' => 'Small',
            'quantity' => 1,
        ]);
    }

    public function test_add_to_cart_returns_not_found_for_a_missing_product(): void
    {
        $this->post(route('addtocart', 999999), ['size' => 'Medium', 'quantity' => 1])
            ->assertNotFound();

        $this->assertDatabaseCount('carts', 0);
        $this->assertDatabaseCount('cart_items', 0);
    }

    private function createProduct(string $name): Product
    {
        return Product::create([
            'name' => $name,
            'brand' => 'Test Brand',
            'image' => 'test.jpg',
            'price' => 250,
        ]);
    }
}
