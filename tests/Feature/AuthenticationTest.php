<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_login_and_registration_forms(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Đăng nhập');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Tạo tài khoản');
    }

    public function test_registration_creates_an_account_and_logs_the_user_in(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('index'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertNotSame('password123', User::where('email', 'test@example.com')->value('password'));
    }

    public function test_registration_rejects_an_email_that_is_already_registered(): void
    {
        User::create([
            'name' => 'Existing User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Another User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('email');
    }

    public function test_user_can_log_in_with_valid_credentials(): void
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('index'));
        $this->assertAuthenticated();
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_log_out(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}