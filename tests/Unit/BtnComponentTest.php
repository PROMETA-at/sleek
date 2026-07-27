<?php

namespace Tests\Unit;

use Tests\TestCase;

class BtnComponentTest extends TestCase
{
    public function test_renders_basic_button_defaults()
    {
        $view = $this->blade('<x-bs::btn>Click</x-bs::btn>');
        $view->assertSee('<button', false);
        $view->assertSee('class="btn btn-primary"', false);
        $view->assertSee('type="button"', false);
        $view->assertSee('Click', false);
        // should not include size or block classes by default
        $view->assertDontSee('btn-lg');
        $view->assertDontSee('btn-sm');
        $view->assertDontSee('w-100');
        // disabled attribute not present by default
        $view->assertDontSee('disabled');
    }

    public function test_outline_and_size_variants()
    {
        $view = $this->blade('<x-bs::btn outline variant="secondary" size="lg">Go</x-bs::btn>');
        $view->assertSee('class="btn btn-outline-secondary btn-lg"', false);
    }

    public function test_block_and_active_states()
    {
        $view = $this->blade('<x-bs::btn :block="true" :active="true">Block</x-bs::btn>');
        $view->assertSee('class="btn btn-primary', false);
        $view->assertSee('w-100', false);
        $view->assertSee('active', false);
    }

    public function test_disabled_button_element_has_disabled_attribute_but_no_disabled_class()
    {
        $view = $this->blade('<x-bs::btn :disabled="true">Disabled</x-bs::btn>');
        // button has disabled attribute
        $view->assertSee('<button', false);
        $view->assertSee('disabled', false);
        // there should be no explicit "disabled" class for button
        $view->assertDontSee('class="btn btn-primary disabled"', false);
    }

    public function test_anchor_tag_renders_and_handles_disabled_accessibly()
    {
        $view = $this->blade('<x-bs::btn tag="a" href="/home" :disabled="true">Home</x-bs::btn>');
        $view->assertSee('<a', false);
        $view->assertSee('href="/home"', false);
        // disabled anchor should have role, aria-disabled, tabindex and disabled class
        $view->assertSee('role="button"', false);
        $view->assertSee('aria-disabled="true"', false);
        $view->assertSee('tabindex="-1"', false);
        $view->assertSee('class="btn btn-primary', false);
        $view->assertSee(' disabled"', false);
        // and should NOT have a disabled attribute on <a>
        $view->assertDontSee('<a disabled', false);
    }

    public function test_variant_wrappers_render_correct_classes()
    {
        $variants = [
            'primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'link'
        ];

        foreach ($variants as $variant) {
            $view = $this->blade("<x-bs::btn.$variant>V</x-bs::btn.$variant>");
            $expected = $variant === 'link' ? 'btn btn-link' : 'btn btn-' . $variant;
            $view->assertSee('class="' . $expected . '"', false);
        }
    }

    public function test_anchor_target_attribute_passes_through()
    {
        $view = $this->blade('<x-bs::btn tag="a" href="/x" target="_blank">X</x-bs::btn>');
        $view->assertSee('target="_blank"', false);
    }
}
