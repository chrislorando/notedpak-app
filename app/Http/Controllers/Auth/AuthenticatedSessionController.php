<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\SupabaseService;
use Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, SupabaseService $supabase): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        $check = $supabase->getUserByEmail($credentials['email']);

        if ($check['success'] && !empty($check['data'])) {
            $remote = $check['data'][0]; 
            $local = User::where('email', $credentials['email'])->first();

            if (! Hash::check($credentials['password'], $remote['password'])) {
                throw ValidationException::withMessages([
                    'email' => __('auth.failed'),
                ]);
            }

            if ($local) {
                if ($remote['updated_at'] > $local->updated_at) {
                    $local->update([
                        'name' => $remote['name'],
                        'password' => $remote['password'], 
                    ]);
                } else {
                    $supabase->updateUser($local->id, $local->toArray());
                }
            } else {
                
                $local = User::create([
                    'id'       => $remote['id'],
                    'name'     => $remote['name'],
                    'email'    => $remote['email'],
                    'password' => $remote['password'],
                    'email_verified_at' => $remote['email_verified_at'],
                    'created_at' => $remote['created_at'],
                    'updated_at' => $remote['updated_at'],
                ]);
            }
        }

        $request->authenticate();
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
