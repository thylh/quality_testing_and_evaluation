<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create($validated);
        $this->mergeGuestCart($request, $user);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('index');
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email hoặc mật khẩu không đúng.'])
                ->onlyInput('email');
        }

        $user = Auth::user();
        if ($user instanceof User) {
            $this->mergeGuestCart($request, $user);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function mergeGuestCart(Request $request, User $user): void
    {
        $sessionId = $request->session()->get('cart_session_id');
        if (! $sessionId) {
            return;
        }

        DB::transaction(function () use ($sessionId, $user) {
            $guestCart = Cart::whereNull('user_id')
                ->where('session_id', $sessionId)
                ->with('items')
                ->first();

            if (! $guestCart) {
                return;
            }

            $userCart = Cart::where('user_id', $user->id)->with('items')->first();
            if (! $userCart) {
                $guestCart->user_id = $user->id;
                $guestCart->session_id = null;
                $guestCart->save();

                return;
            }

            foreach ($guestCart->items as $guestItem) {
                $matchingItems = $userCart->items()->where('product_id', $guestItem->product_id);
                if ($guestItem->size === null) {
                    $matchingItems->whereNull('size');
                } else {
                    $matchingItems->where('size', $guestItem->size);
                }

                $userItem = $matchingItems->first();
                if ($userItem) {
                    $userItem->increment('quantity', $guestItem->quantity ?? 0);
                    $guestItem->delete();
                } else {
                    $guestItem->cart_id = $userCart->id;
                    $guestItem->save();
                }
            }

            $guestCart->delete();
        });

        $request->session()->forget('cart_session_id');
    }
}
