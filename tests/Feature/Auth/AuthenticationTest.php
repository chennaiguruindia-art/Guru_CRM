<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * People sign in with the mobile number on their account, not an email
 * address: the sales team shares a single inbox, so an email does not
 * identify a person, and one number belongs to exactly one login.
 */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Mobile Number');
    }

    public function test_users_can_authenticate_with_their_mobile_number(): void
    {
        $user = User::factory()->create(['phone' => '9876543210']);

        $response = $this->post('/login', [
            'phone' => '9876543210',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        User::factory()->create(['phone' => '9876543210']);

        $this->post('/login', [
            'phone' => '9876543210',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_an_email_address_is_no_longer_a_login_identifier(): void
    {
        $user = User::factory()->create();

        // The address is still on the account — it is simply not what gets
        // typed into the sign-in box any more.
        $this->post('/login', [
            'phone' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    /**
     * The number is matched however somebody happened to write it, or people
     * would be locked out for dropping the country code.
     */
    public function test_the_mobile_is_matched_however_it_was_typed(): void
    {
        $user = User::factory()->create(['phone' => '9876543210']);

        foreach (['9876543210', '98765 43210', '09876543210', '+91 98765 43210', '919876543210'] as $typed) {
            $this->post('/login', ['phone' => $typed, 'password' => 'password']);

            $this->assertSame(
                $user->id,
                auth()->id(),
                "Expected '{$typed}' to sign in as this user."
            );

            $this->post('/logout');
        }
    }

    /**
     * The whole point of moving to mobiles is that one number means one
     * person, so the database has to refuse to hold two.
     */
    public function test_the_database_will_not_hold_two_logins_on_one_number(): void
    {
        User::factory()->create(['phone' => '9876543210']);

        $this->expectException(QueryException::class);

        User::factory()->create(['phone' => '9876543210']);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
