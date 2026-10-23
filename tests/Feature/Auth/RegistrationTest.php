<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Signup exists only to bootstrap the system: it stays open until the very
 * first account exists (which becomes Super Admin), then closes for good.
 * From then on an Admin creates sub-users from User Management.
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_is_available_before_any_account_exists(): void
    {
        $this->assertSame(0, User::count());

        $this->get('/register')->assertOk();
    }

    public function test_first_registered_user_becomes_super_admin(): void
    {
        $response = $this->post('/register', [
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'phone' => '9876543200',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'owner@example.com')->firstOrFail();

        $this->assertTrue(
            $user->roles->contains('slug', 'super-admin'),
            'The first account is Super Admin.'
        );
        $this->assertTrue($user->hasPermission('users.create'));

        // Signup asks for the mobile because it is the sign-in identifier —
        // an account created without one could never be logged into.
        $this->assertSame('9876543200', $user->phone);
    }

    public function test_registration_screen_closes_once_an_account_exists(): void
    {
        User::factory()->create();

        $this->get('/register')->assertStatus(404);
    }

    /**
     * Signup now asks for a mobile, but closing the door must not turn into a
     * data-entry form — an established system gets the 404, not a validation
     * error naming the fields it would have wanted.
     */
    public function test_new_users_cannot_self_register_after_bootstrap(): void
    {
        User::factory()->create();

        $response = $this->post('/register', [
            'name' => 'Latecomer',
            'email' => 'latecomer@example.com',
            'phone' => '9876543201',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(404);
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'latecomer@example.com']);
    }

    public function test_login_screen_offers_signup_only_while_bootstrap_is_pending(): void
    {
        $this->get('/login')->assertSee('Create the first account');

        User::factory()->create();

        $this->get('/login')->assertDontSee('Create the first account');
    }
}
