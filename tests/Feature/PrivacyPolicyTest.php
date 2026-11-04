<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrivacyPolicyTest extends TestCase
{
    public function test_privacy_policy_is_public_and_renders_its_content(): void
    {
        $this->get(route('privacy-policy'))
            ->assertOk()
            ->assertSee('Privacy Policy')
            ->assertSee('Retention and your choices')
            ->assertSee('CRM administrator');
    }

    public function test_sign_in_page_links_to_the_privacy_policy(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('privacy-policy'));
    }
}
