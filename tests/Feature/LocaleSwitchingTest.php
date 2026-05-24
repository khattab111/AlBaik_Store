<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocaleSwitchingTest extends TestCase
{
    public function test_storefront_defaults_to_arabic_rtl(): void
    {
        foreach (['/', '/about', '/contact', '/brands', '/sitemap', '/accessibility'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee('dir="rtl"', false)
                ->assertSee('متجر البيك');
        }
    }

    public function test_customer_can_switch_to_english_ltr(): void
    {
        $this->from('/')->get('/locale/en')
            ->assertRedirect('/')
            ->assertSessionHas('locale', 'en');

        $this->withSession(['locale' => 'en'])->get('/')
            ->assertOk()
            ->assertSee('dir="ltr"', false)
            ->assertSee('AlBaik Store');
    }

    public function test_unsupported_locale_returns_not_found(): void
    {
        $this->get('/locale/fr')->assertNotFound();
    }
}
