<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Tests\TestCase;
use Illuminate\Support\Facades\Blade;

class IconComponentTest extends TestCase
{
    public function test_it_renders_basic_icon()
    {
        $view = $this->blade('<x-icon envelope />');
        $view->assertSee('<i class="bi bi-envelope"></i>', false);
    }

    public function test_it_renders_icon_with_explicit_name_attribute()
    {
        $view = $this->blade('<x-icon name="envelope" />');
        $view->assertSee('<i class="bi bi-envelope"></i>', false);
    }

    public function test_it_renders_icon_with_additional_class()
    {
        $view = $this->blade('<x-icon person class="text-primary" />');
        $view->assertSee('<i class="bi bi-person text-primary"></i>', false);
    }
    
    public function test_it_renders_icon_with_style_attribute()
    {
        $view = $this->blade('<x-icon house style="font-size: 2rem;" />');
        $view->assertSee('<i class="bi bi-house" style="font-size: 2rem;"></i>', false);
    }
    
    public function test_it_renders_icon_with_multiple_attributes()
    {
        $view = $this->blade('<x-icon star class="text-warning" style="font-size: 1.5rem;" title="Favorite" />');
        $view->assertSee('<i class="bi bi-star text-warning" style="font-size: 1.5rem;" title="Favorite"></i>', false);
    }
}
