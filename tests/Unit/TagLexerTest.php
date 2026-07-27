<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Prometa\Sleek\Blade\TagLexer;
use Prometa\Sleek\Blade\TagToken;
use Prometa\Sleek\Blade\TagTree;

/**
 * Token-level tests for the tag lexer. The differential harness proves the lexer agrees with the
 * regexes it replaced; these pin down *why* it does, on the cases regex tag matching is known to
 * fumble — and they need no Laravel container, since the lexer resolves nothing.
 */
class TagLexerTest extends TestCase
{
    // --- Tag boundaries ------------------------------------------------------------------------

    public function test_greater_than_inside_a_quoted_value_does_not_end_the_tag()
    {
        $token = $this->onlyTag('<x-alert :title="$a > $b">');

        $this->assertSame(TagToken::COMPONENT_OPEN, $token->kind);
        $this->assertSame('<x-alert :title="$a > $b">', $token->text);
        $this->assertSame(' :title="$a > $b"', $token->attributes);
    }

    public function test_self_closing_marker_inside_a_quoted_value_does_not_close_the_tag()
    {
        $token = $this->onlyTag('<x-alert title="a/>b" />');

        $this->assertSame(TagToken::COMPONENT_SELF_CLOSE, $token->kind);
        $this->assertSame('<x-alert title="a/>b" />', $token->text);
    }

    public function test_unquoted_value_gives_back_the_self_closing_slash()
    {
        $token = $this->onlyTag('<x-alert title=bar/>');

        $this->assertSame(TagToken::COMPONENT_SELF_CLOSE, $token->kind);
        $this->assertSame(' title=bar', $token->attributes);
    }

    public function test_attributes_may_span_lines()
    {
        $source = "<x-alert\n    title=\"foo\"\n    :subtitle=\"\$bar\"\n>";

        $this->assertSame($source, $this->onlyTag($source)->text);
    }

    public function test_class_directive_may_nest_parentheses()
    {
        $token = $this->onlyTag('<x-alert @class(["a" => foo(bar(1)), "b" => true]) />');

        $this->assertSame(TagToken::COMPONENT_SELF_CLOSE, $token->kind);
        $this->assertSame(' @class(["a" => foo(bar(1)), "b" => true]) ', $token->attributes);
    }

    public function test_spread_attributes_accept_any_variable_in_every_grammar()
    {
        foreach ([
            '<x-alert {{ $spread }} />' => TagToken::COMPONENT_SELF_CLOSE,
            '<x-alert {{ $spread }}>' => TagToken::COMPONENT_OPEN,
            '<x-slot:foo {{ $spread }}>' => TagToken::SLOT_OPEN,
        ] as $source => $kind) {
            $token = $this->onlyTag($source);

            $this->assertSame($kind, $token->kind, "Expected [{$source}] to lex as {$kind}.");
            $this->assertStringContainsString('{{ $spread }}', $token->attributes);
        }
    }

    public function test_a_dangling_arrow_prevents_the_tag_from_closing()
    {
        // `->` before `>` is what the old patterns' lookbehind guarded against; nothing here forms
        // a tag, so the whole thing stays literal text.
        $tokens = (new TagLexer)->tokenize('<x-alert :title=$a->b>');

        $this->assertCount(1, $tokens);
        $this->assertSame(TagToken::TEXT, $tokens[0]->kind);
    }

    public function test_non_tags_stay_literal_text()
    {
        foreach (['plain < and > text', '<x->', '<div class="x">', '{{ $a < $b }}'] as $source) {
            $tokens = (new TagLexer)->tokenize($source);

            $this->assertCount(1, $tokens, "Expected [{$source}] to lex as one text token.");
            $this->assertSame(TagToken::TEXT, $tokens[0]->kind);
        }
    }

    public function test_text_between_tags_is_preserved_verbatim()
    {
        $tokens = (new TagLexer)->tokenize('a<x-alert />b</x-alert>c');

        $this->assertSame(
            [TagToken::TEXT, TagToken::COMPONENT_SELF_CLOSE, TagToken::TEXT, TagToken::COMPONENT_CLOSE, TagToken::TEXT],
            array_column($tokens, 'kind')
        );
        $this->assertSame('a<x-alert />b</x-alert>c', implode('', array_column($tokens, 'text')));
    }

    // --- Slots ---------------------------------------------------------------------------------

    public function test_slot_name_forms()
    {
        $inline = $this->onlyTag('<x-slot:foo-bar>');
        $this->assertSame(TagToken::SLOT_OPEN, $inline->kind);
        $this->assertSame('foo-bar', $inline->inlineName);

        $named = $this->onlyTag('<x-slot name="foo">');
        $this->assertSame('"foo"', $named->slotName);

        $bound = $this->onlyTag('<x-slot :name="$foo->name">');
        $this->assertSame('"$foo->name"', $bound->boundName);

        $combined = $this->onlyTag('<x-slot:foo name="bar">');
        $this->assertSame('foo', $combined->inlineName);
        $this->assertSame('"bar"', $combined->slotName);
    }

    public function test_a_self_closing_slot_is_not_a_slot()
    {
        // The slot pattern never accepted `/>`, so such a tag fell through to the component passes.
        $token = $this->onlyTag('<x-slot:foo />');

        $this->assertSame(TagToken::COMPONENT_SELF_CLOSE, $token->kind);
        $this->assertSame('slot:foo', $token->name);
    }

    public function test_slot_closing_tag_tolerates_junk_before_the_bracket()
    {
        $this->assertSame(TagToken::SLOT_CLOSE, $this->onlyTag('</x-slot junk here>')->kind);
    }

    // --- Pairing -------------------------------------------------------------------------------

    public function test_slots_are_attributed_to_their_nearest_enclosing_component()
    {
        $tokens = $this->attributed('<x-a><x-slot:one><x-b><x-slot:two>x</x-slot></x-b></x-slot></x-a>');

        $this->assertSame('a', $this->slotNamed($tokens, 'one')->enclosingComponent);
        $this->assertSame('b', $this->slotNamed($tokens, 'two')->enclosingComponent);
    }

    public function test_self_closing_components_never_enclose_a_slot()
    {
        $tokens = $this->attributed('<x-a><x-b /><x-slot:one>x</x-slot></x-a>');

        $this->assertSame('a', $this->slotNamed($tokens, 'one')->enclosingComponent);
    }

    public function test_a_slot_outside_any_component_has_no_enclosing_component()
    {
        $tokens = $this->attributed('<x-slot:one>x</x-slot>');

        $this->assertNull($this->slotNamed($tokens, 'one')->enclosingComponent);
    }

    public function test_unpaired_tags_do_not_derail_pairing()
    {
        // A stray close on an empty stack is ignored; an unclosed component stays open.
        $tokens = $this->attributed('</x-stray><x-a><x-slot:one>x');

        $this->assertSame('a', $this->slotNamed($tokens, 'one')->enclosingComponent);
    }

    // --- Helpers -------------------------------------------------------------------------------

    protected function onlyTag(string $source): TagToken
    {
        $tags = array_values(array_filter(
            (new TagLexer)->tokenize($source),
            fn (TagToken $token) => $token->kind !== TagToken::TEXT
        ));

        $this->assertCount(1, $tags, "Expected [{$source}] to lex as exactly one tag.");

        return $tags[0];
    }

    /**
     * @return array<int, TagToken>
     */
    protected function attributed(string $source): array
    {
        $tokens = (new TagLexer)->tokenize($source);

        (new TagTree)->attribute($tokens);

        return $tokens;
    }

    /**
     * @param  array<int, TagToken>  $tokens
     */
    protected function slotNamed(array $tokens, string $inlineName): TagToken
    {
        foreach ($tokens as $token) {
            if ($token->kind === TagToken::SLOT_OPEN && $token->inlineName === $inlineName) {
                return $token;
            }
        }

        $this->fail("No slot named [{$inlineName}] in the token stream.");
    }
}
