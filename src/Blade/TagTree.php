<?php

namespace Prometa\Sleek\Blade;

/**
 * Pairs {@see TagLexer}'s tokens into the component nesting they describe, so every slot knows the
 * component it belongs to. The tree is expressed as parent links on the tokens themselves rather
 * than as a separate node graph — emission walks the token stream in document order anyway, and the
 * only structural fact it needs is each slot's enclosing component.
 *
 * Pairing is best-effort by design. Laravel compiles closing tags independently of whether an
 * opening tag matched, so unbalanced markup must not raise here: a stray `</x-foo>` on an empty
 * stack is simply ignored, and an unclosed component stays open to the end of the template.
 */
class TagTree
{
    /**
     * Fill in `enclosingComponent` on every slot-open token.
     *
     * @param  array<int, TagToken>  $tokens
     */
    public function attribute(array $tokens): void
    {
        $stack = [];

        foreach ($tokens as $token) {
            switch ($token->kind) {
                case TagToken::COMPONENT_OPEN:
                    $stack[] = $token->name;
                    break;

                case TagToken::COMPONENT_CLOSE:
                    array_pop($stack);
                    break;

                case TagToken::SLOT_OPEN:
                    // Slots never enter the stack: a slot nested inside another slot still belongs
                    // to the nearest enclosing *component*, and self-closing components have no
                    // body to nest anything in.
                    $token->enclosingComponent = empty($stack) ? null : end($stack);
                    break;
            }
        }
    }
}
