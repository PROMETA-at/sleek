<?php

namespace Tests\Unit;

use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Fixtures\SideEffect;
use Tests\TestCase;

/**
 * Behavior tests for lazy tab slots (Task 4): inactive tab bodies must not execute, while the
 * active tab renders inline. Query-param and HTMX paths swap a crafted Request into the container.
 */
class TabsLazyRenderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        SideEffect::reset();
    }

    protected function swapRequest(array $query = [], array $headers = []): void
    {
        $request = Request::create('/', 'GET', $query);
        foreach ($headers as $key => $value) {
            $request->headers->set($key, $value);
        }
        $this->app->instance('request', $request);
    }

    protected function template(): string
    {
        return '<x-sleek::tabs.pills>'
            . '<x-slot:tab-one label="One">{{ \Tests\Fixtures\SideEffect::hit("one") }}CONTENT_ONE</x-slot:tab-one>'
            . '<x-slot:tab-two label="Two">{{ \Tests\Fixtures\SideEffect::hit("two") }}CONTENT_TWO</x-slot:tab-two>'
            . '</x-sleek::tabs.pills>';
    }

    public function test_inactive_tab_body_does_not_execute_and_active_one_does()
    {
        $html = $this->blade($this->template())->__toString();

        $this->assertStringContainsString('CONTENT_ONE', $html);
        $this->assertStringNotContainsString('CONTENT_TWO', $html);
        $this->assertSame(1, SideEffect::count('one'));
        $this->assertSame(0, SideEffect::count('two'));
    }

    public function test_inactive_tab_body_that_would_fatally_error_is_never_run()
    {
        // The June spec's empirical repro: an inactive tab body that dereferences null renders fine
        // because the body never executes; activating it via ?tab= would reproduce the error.
        $template = '<x-sleek::tabs.pills>'
            . '<x-slot:tab-one label="One">SAFE</x-slot:tab-one>'
            . '<x-slot:tab-two label="Two">{{ null->explode() }}</x-slot:tab-two>'
            . '</x-sleek::tabs.pills>';

        $html = $this->blade($template)->__toString();

        $this->assertStringContainsString('SAFE', $html);
    }

    public function test_query_param_selects_tab_and_first_tab_body_stays_dormant()
    {
        $this->swapRequest(['tab' => 'two']);

        $html = $this->blade($this->template())->__toString();

        $this->assertStringContainsString('CONTENT_TWO', $html);
        $this->assertStringNotContainsString('CONTENT_ONE', $html);
        $this->assertSame(0, SideEffect::count('one'));
        $this->assertSame(1, SideEffect::count('two'));
    }

    public function test_htmx_fragment_request_returns_requested_tab_content()
    {
        $this->swapRequest(['tab' => 'two'], ['HX-Request' => 'true']);

        $html = $this->blade($this->template())->__toString();

        $this->assertStringContainsString('CONTENT_TWO', $html);
        $this->assertSame(0, SideEffect::count('one'));
        $this->assertSame(1, SideEffect::count('two'));
    }

    #[DataProvider('presetProvider')]
    public function test_all_presets_render_active_content_and_defer_inactive($preset)
    {
        SideEffect::reset();

        $template = "<x-sleek::tabs.$preset>"
            . '<x-slot:tab-one label="One">{{ \Tests\Fixtures\SideEffect::hit("one") }}CONTENT_ONE</x-slot:tab-one>'
            . '<x-slot:tab-two label="Two">{{ \Tests\Fixtures\SideEffect::hit("two") }}CONTENT_TWO</x-slot:tab-two>'
            . "</x-sleek::tabs.$preset>";

        $html = $this->blade($template)->__toString();

        $this->assertStringContainsString('CONTENT_ONE', $html, "preset [$preset] should render the active tab body");
        $this->assertSame(1, SideEffect::count('one'), "preset [$preset] active body should run once");
        $this->assertSame(0, SideEffect::count('two'), "preset [$preset] inactive body should not run");
        // Nav labels stay eager for every preset.
        $this->assertStringContainsString('One', $html);
        $this->assertStringContainsString('Two', $html);
    }

    public static function presetProvider(): array
    {
        return [['pills'], ['vertical'], ['card'], ['collapse']];
    }

    public function test_scope_capture_makes_outer_variables_and_loop_available_in_tab_body()
    {
        // $greeting (blade data), $loop and $item (from the wrapping @foreach) all exist at the slot
        // definition site; the body references them with no use= — get_defined_vars() must carry them in.
        $template = '@foreach ([\'first\'] as $item)'
            . '<x-sleek::tabs.pills>'
            . '<x-slot:tab-one label="One">VAL:{{ $greeting }}:{{ $loop->index }}:{{ $item }}</x-slot:tab-one>'
            . '</x-sleek::tabs.pills>'
            . '@endforeach';

        $html = $this->blade($template, ['greeting' => 'Hi'])->__toString();

        $this->assertStringContainsString('VAL:Hi:0:first', $html);
    }
}
