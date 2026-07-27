<?php

namespace Tests\Unit;

use InvalidArgumentException;
use Tests\TestCase;

/**
 * Entity-table column slots (Task 5): parameterized scoped slots keep their explicit bind, now
 * enforced at compile time, and gain outer-scope access without use=.
 */
class EntityTableColumnSlotTest extends TestCase
{
    protected array $rows = [
        ['title' => 'Hello'],
        ['title' => 'World'],
    ];

    public function test_column_slot_with_bind_renders_per_row()
    {
        $html = $this->blade(
            '<x-sleek::entity-table :entities="$rows" :columns="[\'title\']">'
            . '<x-slot:column-title bind="$value, $entity">CELL:{{ $value }}</x-slot:column-title>'
            . '</x-sleek::entity-table>',
            ['rows' => $this->rows]
        )->__toString();

        $this->assertStringContainsString('CELL:Hello', $html);
        $this->assertStringContainsString('CELL:World', $html);
    }

    public function test_column_slot_without_bind_is_a_compile_error_naming_the_expected_bind()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('bind="$value, $entity"');

        $this->blade(
            '<x-sleek::entity-table :entities="$rows" :columns="[\'title\']">'
            . '<x-slot:column-title>{{ $value }}</x-slot:column-title>'
            . '</x-sleek::entity-table>',
            ['rows' => $this->rows]
        )->__toString();
    }

    public function test_column_slot_reaches_outer_scope_without_use()
    {
        $html = $this->blade(
            '@php($suffix = "!")'
            . '<x-sleek::entity-table :entities="$rows" :columns="[\'title\']">'
            . '<x-slot:column-title bind="$value, $entity">{{ $value }}{{ $suffix }}</x-slot:column-title>'
            . '</x-sleek::entity-table>',
            ['rows' => $this->rows]
        )->__toString();

        $this->assertStringContainsString('Hello!', $html);
        $this->assertStringContainsString('World!', $html);
    }

    public function test_column_slot_with_explicit_use_still_compiles()
    {
        $html = $this->blade(
            '@php($suffix = "?")'
            . '<x-sleek::entity-table :entities="$rows" :columns="[\'title\']">'
            . '<x-slot:column-title bind="$value, $entity" use="$suffix">{{ $value }}{{ $suffix }}</x-slot:column-title>'
            . '</x-sleek::entity-table>',
            ['rows' => $this->rows]
        )->__toString();

        $this->assertStringContainsString('Hello?', $html);
    }
}
