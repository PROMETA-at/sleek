<?php

namespace Tests\Unit;

use Tests\TestCase;

class GridComponentTest extends TestCase
{
    public function test_row_and_col_render_with_aggressive_defaults(): void
    {
        $this->blade('<x-bs::row><x-bs::col>Content</x-bs::col></x-bs::row>')
            ->assertSee('class="row"', false)
            ->assertSee('class="col"', false)
            ->assertSee('Content');
    }

    public function test_row_builds_responsive_layout_classes(): void
    {
        $this->blade(<<<'BLADE'
            <x-bs::row
                cols="1"
                gutter="3"
                gutter-y="2"
                align="center"
                class="custom-row"
            >
                <x-slot:md cols="2" gutter-y="3" justify="between" />
                <x-slot:lg gutter-x="4" />
                Content
            </x-bs::row>
            BLADE)
            ->assertSee('row-cols-1', false)
            ->assertSee('row-cols-md-2', false)
            ->assertSee('g-3', false)
            ->assertSee('gx-lg-4', false)
            ->assertSee('gy-2', false)
            ->assertSee('gy-md-3', false)
            ->assertSee('align-items-center', false)
            ->assertSee('justify-content-md-between', false)
            ->assertSee('custom-row', false);
    }

    public function test_col_builds_responsive_layout_classes(): void
    {
        $this->blade(<<<'BLADE'
            <x-bs::col
                span="12"
                id="details"
                class="custom-col"
            >
                <x-slot:sm align="center" />
                <x-slot:md span="6" order="first" />
                <x-slot:lg offset="2" />
                <x-slot:xl span="auto" order="2" />
                Content
            </x-bs::col>
            BLADE)
            ->assertSee('col-12', false)
            ->assertSee('col-md-6', false)
            ->assertSee('col-xl-auto', false)
            ->assertSee('offset-lg-2', false)
            ->assertSee('order-md-first', false)
            ->assertSee('order-xl-2', false)
            ->assertSee('align-self-sm-center', false)
            ->assertSee('id="details"', false)
            ->assertSee('custom-col', false);
    }

    public function test_breakpoint_only_span_does_not_force_equal_width_below_it(): void
    {
        $this->blade('<x-bs::col><x-slot:md span="6" />Content</x-bs::col>')
            ->assertSee('class="col-md-6"', false)
            ->assertDontSee('class="col col-md-6"', false);
    }
}
