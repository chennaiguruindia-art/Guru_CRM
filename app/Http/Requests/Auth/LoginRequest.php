<?php

namespace App\Http\Requests\Auth;

use App\Models\Employee;
use App\Models\User;
use App\Support\Phone;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Accepts either phone or employee code / username
            'phone' => ['required_without:login', 'nullable', 'string', 'max:50'],
            'login' => ['sometimes', 'nullable', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $raw = trim((string) ($this->input('login') ?: $this->input('phone')));
        $identifier = Phone::normalize($raw);

        // 1. Try matching by normalized mobile number
        $user = $identifier
            ? User::query()->where('phone', $identifier)->first()
            : null;

        // 2. Try matching by username (which stores Employee ID e.g. EMP-0001) or email
        if (! $user && $raw !== '') {
            $user = User::query()->where('username', $raw)
                ->orWhere('email', $raw)
                ->first();
        }

        // 3. Fallback: match by employee_code directly from Employee record
        if (! $user && $raw !== '') {
            $emp = Employee::query()->where('employee_code', $raw)->first();
            if ($emp && $emp->user_id) {
                $user = User::query()->find($emp->user_id);
            }
        }

        if ($user && $user->status !== 'active') {
            throw ValidationException::withMessages([
                'phone' => 'This account has been deactivated. Please contact the administrator.',
            ]);
        }

        // Resolved to an id first, so the password is only ever checked against
        // a row that matched.
        if (! $user || ! Auth::attempt(['id' => $user->id, 'password' => $this->input('password')], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'phone' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'phone' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('phone')).'|'.$this->ip());
    }
}
