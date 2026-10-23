<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\Phone;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Signup exists only to create the very first account.
     *
     * @throws ValidationException
     */
    public function create(): View
    {
        abort_if(static::alreadyBootstrapped(), 404);

        return view('auth.register');
    }

    /**
     * Create the first account and make it Super Admin.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Mobile is the sign-in identifier, so it is collected here rather
        // than left to be filled in later — an account with none cannot log in.
        $request->merge(['phone' => Phone::normalize($request->input('phone'))]);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:30', 'regex:/^\d{10,15}$/', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'phone.required' => 'A mobile number is required — it is what you will sign in with.',
            'phone.regex' => 'That does not look like a mobile number. Enter 10 digits, with or without the +91.',
        ]);

        $user = DB::transaction(function () use ($request) {
            // Re-check inside the transaction: two people hitting signup at the
            // same instant must not both end up Super Admin.
            abort_if(static::alreadyBootstrapped(), 404);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'status' => 'active',
            ]);

            // firstOrCreate rather than lookup: the bootstrap account must
            // never end up with no role, even if the seeder has not run.
            $role = Role::firstOrCreate(
                ['slug' => 'super-admin'],
                ['name' => 'Super Admin', 'description' => 'The first account created by signup. Full control, and cannot be assigned to anyone else.']
            );

            $user->roles()->attach($role->id);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * True once any account exists — signup is then permanently closed.
     *
     * The lock stops two simultaneous signups both becoming Super Admin. It is
     * compiled away on SQLite (which the test suite uses), and is only a
     * belt-and-braces guard — the real protection is the transaction.
     */
    private static function alreadyBootstrapped(): bool
    {
        return User::query()->lockForUpdate()->limit(1)->first() !== null;
    }
}
