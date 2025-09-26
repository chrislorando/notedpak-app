<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SupabaseService;
use DB;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\ValidationException;
use Log;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, SupabaseService $supabase): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $payload = [
            'id'    => (string) \Illuminate\Support\Str::uuid(),
            'email' => $request->email,
            'name'  => $request->name,
            'password'          => Hash::make($request->password),
            'email_verified_at' => now(),
        ];

        $check = $supabase->getUserByEmail($request->email);

        if (!empty($check['success']) && !empty($check['data'])) {
            throw ValidationException::withMessages([
                'email' => __('validation.unique', ['attribute' => 'email']),
            ]);
        } else {
            DB::beginTransaction();

            try {
                $user = User::create($payload);
                event(new Registered($user));

                $payload['id'] = $user->id;
                $payload['created_at'] = $user->created_at;
                $payload['updated_at'] = $user->updated_at; 

                $result = $supabase->addUser($payload);

                if (! $result['success']) {
                    throw new \Exception("Supabase error: " . json_encode($result['error']));
                }

                DB::commit();
                Auth::login($user);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('User sync error: ' . $e->getMessage());

                return back()->withErrors([
                    'email' => __('auth.failed'),
                ]);
            }
        }

  

        return to_route('dashboard');
    }
}
