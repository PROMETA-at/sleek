<?php

namespace Tests\Unit;

use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class FormFieldIdTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['view']->share('errors', new ViewErrorBag());
    }

    public function test_explicit_id_associates_input_and_label(): void
    {
        $view = $this->blade('<x-sleek::form-field name="email" id="profile-email" label="Email" />');

        $view->assertSee('id="profile-email"', false);
        $view->assertSee('for="profile-email"', false);
    }

    public function test_default_id_and_label_association_remain_name_derived(): void
    {
        $view = $this->blade('<x-sleek::form-field name="email" label="Email" />');

        $view->assertSee('id="email"', false);
        $view->assertSee('for="email"', false);
    }

    public function test_same_name_fields_with_distinct_ids_remain_independently_associated(): void
    {
        $view = $this->blade(
            '<x-sleek::form-field name="email" id="billing-email" label="Billing email" />'
            . '<x-sleek::form-field name="email" id="shipping-email" label="Shipping email" />',
        );

        $html = $view->__toString();

        $this->assertStringContainsString('id="billing-email"', $html);
        $this->assertStringContainsString('for="billing-email"', $html);
        $this->assertStringContainsString('id="shipping-email"', $html);
        $this->assertStringContainsString('for="shipping-email"', $html);
    }
}
