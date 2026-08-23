<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Blade;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Compiler-level tests: scoped-slot directive compilation and the document-order scanner that
 * attributes slots to their enclosing component. Every scoped slot captures its definition-site
 * scope — there is no non-capturing variant.
 */
class ScopedSlotCompilerTest extends TestCase
{
    // --- Directive-level: @slot(... bind (...)) compilation ------------------------------------

    public function test_zero_arg_scoped_directive_compiles_to_scope_bound_closure()
    {
        $compiled = Blade::compileString("@slot('tabOverview', ['label' => 'Overview'] bind ())\nBODY\n@endslot");

        $this->assertStringContainsString(
            "\$__env->slot('tabOverview', function (\$__scope) use (\$__env) { extract(\$__scope, EXTR_SKIP); ?>",
            $compiled
        );
        $this->assertStringContainsString("['label' => 'Overview'], get_defined_vars()); ?>", $compiled);
    }

    public function test_parameterized_scoped_directive_prepends_scope_before_bindings()
    {
        $compiled = Blade::compileString("@slot('columnDate', [] bind (\$value, \$entity))\nBODY\n@endslot");

        $this->assertStringContainsString(
            "function (\$__scope, \$value, \$entity) use (\$__env) { extract(\$__scope, EXTR_SKIP); ?>",
            $compiled
        );
        $this->assertStringContainsString('[], get_defined_vars()); ?>', $compiled);
    }

    public function test_explicit_use_attribute_is_accepted_and_discarded()
    {
        // `use=` is obsolete now that scope is captured wholesale. It must neither reach the closure's
        // use list nor leak into the slot's attribute bag — templates carrying it just keep compiling.
        $compiled = Blade::compileString(
            '<x-sleek::entity-table><x-slot:column-date bind="$value, $entity" use="$foo">D</x-slot:column-date></x-sleek::entity-table>'
        );

        $this->assertStringContainsString('function ($__scope, $value, $entity) use ($__env)', $compiled);
        $this->assertStringNotContainsString('$foo', $compiled);
    }

    public function test_explicit_bind_slot_in_unregistered_component_captures_scope_too()
    {
        $compiled = Blade::compileString("@slot('foo', ['a' => 'b'] bind (\$x))\nBODY\n@endslot");

        $this->assertStringContainsString(
            "\$__env->slot('foo', function (\$__scope, \$x) use (\$__env) { extract(\$__scope, EXTR_SKIP); ?>",
            $compiled
        );
        $this->assertStringContainsString("['a' => 'b'], get_defined_vars()); ?>", $compiled);
    }

    public function test_plain_slot_compiles_unchanged()
    {
        $compiled = Blade::compileString("@slot('foo', null, [])\nBODY\n@endslot");

        $this->assertStringContainsString("\$__env->slot('foo', null, []); ?>", $compiled);
        $this->assertStringContainsString('$__env->endSlot(); ?>', $compiled);
    }

    public function test_self_closing_slot_renders_as_an_empty_attribute_carrier()
    {
        $this->blade('<x-bs::card><x-slot:header class="marker" /></x-bs::card>')
            ->assertSee('class="card-header marker"', false);
    }

    /**
     * A slot in an unregistered component still compiles to the plain, eager form. `@slot` is already
     * lowered to `$__env->slot(...)` by the time compileString returns, so we assert on that.
     */

    // --- Scanner: attribution to enclosing component ------------------------------------------

    public function test_registered_component_slot_compiles_as_capture()
    {
        $compiled = Blade::compileString(
            '<x-sleek::tabs.pills><x-slot:tab-one label="One">A</x-slot:tab-one></x-sleek::tabs.pills>'
        );

        $this->assertStringContainsString('function ($__scope) use ($__env)', $compiled);
        $this->assertStringContainsString('get_defined_vars()', $compiled);
    }

    public function test_self_closing_registered_slot_keeps_its_component_attribution()
    {
        $compiled = Blade::compileString(
            '<x-sleek::tabs><x-slot:tab-one label="One" /></x-sleek::tabs>'
        );

        $this->assertStringContainsString('function ($__scope) use ($__env)', $compiled);
        $this->assertStringContainsString('get_defined_vars()', $compiled);
    }

    public function test_same_slot_name_in_unregistered_component_compiles_plain()
    {
        // tab-one inside entity-form (not registered for tab-*) must stay an eager, plain slot.
        $compiled = Blade::compileString(
            '<x-sleek::entity-form><x-slot:tab-one label="One">A</x-slot:tab-one></x-sleek::entity-form>'
        );

        $this->assertStringContainsString("\$__env->slot('tabOne', null,", $compiled);
        $this->assertStringNotContainsString('get_defined_vars', $compiled);
    }

    public function test_self_closing_component_between_slots_does_not_skew_attribution()
    {
        // The self-closing <x-icon/> must not be pushed onto the stack; the second slot still
        // attributes to tabs.pills and compiles as capture.
        $compiled = Blade::compileString(
            '<x-sleek::tabs.pills>'
            . '<x-slot:tab-one label="One">A</x-slot:tab-one>'
            . '<x-icon star />'
            . '<x-slot:tab-two label="Two">B</x-slot:tab-two>'
            . '</x-sleek::tabs.pills>'
        );

        $this->assertStringContainsString("\$__env->slot('tabOne'", $compiled);
        $this->assertStringContainsString("\$__env->slot('tabTwo'", $compiled);
        // Both scoped => two get_defined_vars closes.
        $this->assertSame(2, substr_count($compiled, 'get_defined_vars()'));
    }

    public function test_slot_after_nested_component_reattributes_to_outer()
    {
        // A nested unregistered component closes, then a further tab slot must re-attribute to the
        // outer tabs.pills (capture), proving the stack popped correctly.
        $compiled = Blade::compileString(
            '<x-sleek::tabs.pills>'
            . '<x-slot:tab-one label="One"><x-sleek::entity-form><x-slot:foo bind="$x">n</x-slot:foo></x-sleek::entity-form></x-slot:tab-one>'
            . '<x-slot:tab-two label="Two">B</x-slot:tab-two>'
            . '</x-sleek::tabs.pills>'
        );

        // Outer tab-one and tab-two are zero-arg scoped; inner foo is scoped via its explicit bind.
        $this->assertSame(3, substr_count($compiled, 'get_defined_vars()'));
        $this->assertStringContainsString('function ($__scope, $x) use ($__env)', $compiled);
    }

    public function test_zero_arg_slot_with_bind_is_a_compile_error()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('receives no arguments');

        Blade::compileString(
            '<x-sleek::tabs.pills><x-slot:tab-one label="One" bind="$x">A</x-slot:tab-one></x-sleek::tabs.pills>'
        );
    }
}
