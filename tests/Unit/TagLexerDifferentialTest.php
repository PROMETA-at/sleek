<?php

namespace Tests\Unit;

use Illuminate\View\Compilers\BladeCompiler;
use PHPUnit\Framework\Attributes\DataProvider;
use Prometa\Sleek\Blade\ComponentTagCompiler;
use Tests\Fixtures\LegacyComponentTagCompiler;
use Tests\TestCase;
use Throwable;

/**
 * Acceptance gate for the tag lexer: it must produce byte-identical output to the regex passes it
 * replaced, on every template this package ships and on the grammar's known sharp edges.
 *
 * Both compilers run over the same source and are compared on their *outcome* — compiled string or
 * thrown exception — because plenty of inputs legitimately fail to resolve a component class, and
 * failing the same way is part of being identical.
 */
class TagLexerDifferentialTest extends TestCase
{
    public function test_shipped_templates_compile_identically()
    {
        $templates = $this->templates();

        $this->assertNotEmpty($templates, 'No Blade templates found to diff against.');

        foreach ($templates as $path) {
            $this->assertCompilesIdentically(file_get_contents($path), $path);
        }
    }

    #[DataProvider('edgeCases')]
    public function test_edge_cases_compile_identically(string $source)
    {
        $this->assertCompilesIdentically($source, $source);
    }

    /**
     * Inputs ported from Laravel's BladeComponentTagCompilerTest, plus the cases the regexes were
     * known to fumble: `>` and `/>` inside values, multiline attributes, nested parens in `@class`,
     * and unpaired tags.
     *
     * @return array<string, array{0: string}>
     */
    public static function edgeCases(): array
    {
        $cases = [
            // --- Slots ---------------------------------------------------------------------
            '<x-slot name="foo">'."\n".'</x-slot>',
            '<x-slot:foo>'."\n".'</x-slot>',
            '<x-slot :name="$foo">'."\n".'</x-slot>',
            '<x-slot :name="$foo->name">'."\n".'</x-slot>',
            '<x-slot name="foo" class="font-bold">'."\n".'</x-slot>',
            '<x-slot:foo class="font-bold">'."\n".'</x-slot>',
            '<x-slot name="foo" :class="$classes">'."\n".'</x-slot>',
            '<x-slot name="foo" @class($classes)>'."\n".'</x-slot>',
            '<x-slot name="foo" @style($styles)>'."\n".'</x-slot>',
            '<x-slot:foo-bar>x</x-slot>',
            '<x-slot:foo name="bar">x</x-slot>',
            '<x-slot:foo :name="$bar">x</x-slot>',
            '<x-slot name=foo>x</x-slot>',
            '<x-slot:foo />',
            '<x-slot>x</x-slot >',
            '</x-slot junk here>',

            // --- Components ----------------------------------------------------------------
            '<div><x-alert type="foo" limit="5" @click="foo" wire:click="changePlan(\'{{ $plan }}\')" required x-intersect.margin.-50%.0px="visibleSection = \'profile\'" /><x-alert /></div>',
            '<div><x-card /></div>',
            '<div><x-alert type="" limit=\'\' @click="" required /></div>',
            '<x-profile user-id="1"></x-profile>',
            '<x-profile :user-id="1"></x-profile>',
            '<x-profile :$userId></x-profile>',
            '<x-profile :userId="User::$id"></x-profile>',
            '<x-input :label="Input::$label" :$name value="Joe"></x-input>',
            '<x-input value="Joe" :$name :label="Input::$label"></x-input>',
            '<x-profile :$userId/>',
            '<x-profile :userId="User::$id"/>',
            '<x-input :label="Input::$label" value="Joe" :$name />',
            '<x-input :$name :label="Input::$label" value="Joe" />',
            '<x-profile :user-id="1" ::title="user.name"></x-profile>',
            '<x-profile :src="\'foo\'"></x-profile>',
            '<x-profile @class(["bar"=>true])></x-profile>',
            '<x-profile @style(["bar"=>true])></x-profile>',
            '<x-foo:alert></x-foo:alert>',
            '<x:foo:alert></x-foo:alert>',
            '<div><x-alert/></div>',
            '<x-alert class="bar" wire:model="foo" x-on:click="bar" @click="baz" />',
            '<x-alert title="foo" class="bar" wire:model="foo" />',
            '<x-profile class="bar" {{ $attributes }} wire:model="foo"></x-profile>',
            '<div><x-alert title="foo" class="bar" {{ $attributes->merge([\'class\' => \'test\']) }} wire:model="foo" /></div>',
            '<x-profile></x-profile>Words',
            '<x-alert/>Words',
            '<x-alert :title="$title" class="bar" />',
            '<x-alert>'."\n".'</x-alert>',
            '<x-package::anonymous-component :name="\'Taylor\'" :age="31" wire:model="foo" />',
            '<x-admin.auth::anonymous-component :name="\'Taylor\'" :age="31" wire:model="foo" />',
            '<x-profile {{ $attributes }} />',
            // Uses resolvable components: with two unresolvable ones the two implementations report
            // a *different* one first — see test_unresolvable_components_are_reported_in_document_order.
            '<x-sleek::card><x-sleek::icon {{ $attributes }} /></x-sleek::card>',

            // --- Sharp edges ---------------------------------------------------------------
            '<x-alert :title="$a > $b" />',
            '<x-alert :title="$a <=> $b"></x-alert>',
            '<x-alert title="a/>b">x</x-alert>',
            '<x-alert title=\'a/>b\' />',
            '<x-alert'."\n".'    title="foo"'."\n".'    :subtitle="$bar"'."\n".'/>',
            '<x-alert'."\n".'    title="foo"'."\n".'>x</x-alert>',
            '<x-alert @class(["a" => foo(bar(1)), "b" => true]) />',
            '<x-alert @class([]) @style([]) title="x" />',
            '<x-alert :title=$a->b>',
            '<x-alert title=bar/>',
            '<x-alert title=bar baz />',

            // Terminator ambiguity: one scan now decides open vs self-closing on arrival, where the
            // old passes decided it by running two whole grammars in order. These are the shapes
            // where an unquoted value, a `/` and a `>` can be read more than one way.
            '<x-alert a=b c/>',
            '<x-alert a=b c>',
            '<x-alert a=b/ >',
            '<x-alert a=b/>c',
            '<x-alert a="x/>y" >',
            '<x-alert a="/>" b=c/>',
            '<x-alert a=b c="/>" />',
            '<x-alert @class(["a" => "/>"]) >',
            '<x-alert @class(["a" => "/>"]) />',
            '<x-alert a=b c d e/>',
            '<x-alert a=b c d e>',
            '</x-alert>',
            '<x-alert>',
            '<x-alert><x-slot:foo>a</x-alert>',
            '<x-a><x-slot:one><x-b><x-slot:two>x</x-slot></x-b></x-slot></x-a>',
            '<x- >',
            '<x->',
            '<x-alert {{ $attributes }} />',
            '<x-alert {{ $foo }}></x-alert>',
            'plain text with a < and a > and no tags',
            '{{ $a < $b }}<x-alert />',
        ];

        return array_combine($cases, array_map(fn ($case) => [$case], $cases));
    }

    /**
     * Deliberate divergence #1. Sleek widened spread attributes to accept any variable, not just
     * `$attributes` — but the widening was applied to the opening and slot patterns only, leaving
     * `componentSelfClosingPattern()` on Laravel's `$attributes`. A self-closing tag spreading any
     * other variable therefore matched no pattern at all and was emitted as literal text, rendering
     * the raw tag into the page. The lexer applies one spread rule to all three grammars.
     */
    public function test_a_self_closing_tag_may_spread_any_variable()
    {
        $legacy = $this->outcome(new LegacyComponentTagCompiler(...$this->compilerArguments()), '<x-sleek::icon {{ $spread }} />');
        $lexed = $this->outcome(new ComponentTagCompiler(...$this->compilerArguments()), '<x-sleek::icon {{ $spread }} />');

        $this->assertSame('<x-sleek::icon {{ $spread }} />', $legacy, 'Expected the old passes to leave this uncompiled.');
        $this->assertStringContainsString('##BEGIN-COMPONENT-CLASS##', $lexed);
        $this->assertStringContainsString('$spread', $lexed);

        // Also with no space before the terminator, where the spread abuts the `/>`.
        $this->assertStringContainsString(
            '##BEGIN-COMPONENT-CLASS##',
            $this->outcome(new ComponentTagCompiler(...$this->compilerArguments()), '<x-sleek::icon {{ $spread }}/>')
        );

        // It now compiles to what the equivalent paired tag always did.
        $paired = $this->outcome(new ComponentTagCompiler(...$this->compilerArguments()), '<x-sleek::icon {{ $spread }}></x-sleek::icon>');

        $this->assertSame(
            $paired,
            str_replace("\n@endComponentClass##END-COMPONENT-CLASS##", ' @endComponentClass##END-COMPONENT-CLASS##', $lexed)
        );
    }

    /**
     * Deliberate divergence #2: the regex passes compiled every self-closing tag before any opening
     * tag, so with several unresolvable components the reported one depended on which pass reached
     * it first. Emission is now in document order, so the first offender is reported.
     */
    public function test_unresolvable_components_are_reported_in_document_order()
    {
        $compiler = new ComponentTagCompiler(...$this->compilerArguments());

        $this->assertSame(
            'InvalidArgumentException: Unable to locate a class or view for component [container].',
            $this->outcome($compiler, '<x-container><x-profile /></x-container>')
        );
    }

    protected function assertCompilesIdentically(string $source, string $label): void
    {
        $this->assertSame(
            $this->outcome(new LegacyComponentTagCompiler(...$this->compilerArguments()), $source),
            $this->outcome(new ComponentTagCompiler(...$this->compilerArguments()), $source),
            "Lexer output diverged for [{$label}]."
        );
    }

    /**
     * The compiled string, or a stable description of whatever the compiler threw instead.
     */
    protected function outcome(object $compiler, string $source): string
    {
        try {
            return $compiler->compile($source);
        } catch (Throwable $e) {
            return $e::class.': '.$e->getMessage();
        }
    }

    /**
     * @return array{0: array<string, string>, 1: array<string, string>, 2: BladeCompiler}
     */
    protected function compilerArguments(): array
    {
        /** @var BladeCompiler $blade */
        $blade = $this->app['blade.compiler'];

        return [$blade->getClassComponentAliases(), $blade->getClassComponentNamespaces(), $blade];
    }

    /**
     * @return array<int, string>
     */
    protected function templates(): array
    {
        $paths = [];

        foreach ([dirname(__DIR__, 2).'/src/resources/views', dirname(__DIR__, 2).'/workbench'] as $root) {
            if (! is_dir($root)) {
                continue;
            }

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));

            foreach ($files as $file) {
                if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                    $paths[] = $file->getPathname();
                }
            }
        }

        sort($paths);

        return $paths;
    }
}
