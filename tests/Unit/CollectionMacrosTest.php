<?php

namespace Tests\Unit;

use Illuminate\Support\Collection;
use Tests\TestCase;

class CollectionMacrosTest extends TestCase
{
    /**
     * Test the withEmptyOption macro with default label.
     *
     * @return void
     */
    public function test_with_empty_option_default_label()
    {
        // Create a collection
        $collection = collect([
            'key1' => 'Value 1',
            'key2' => 'Value 2',
        ]);

        // Apply the macro with default label
        $result = $collection->withEmptyOption();

        // Assert the collection has one more item
        $this->assertEquals(3, $result->count());

        // Assert the first item is the empty option with default label
        $this->assertEquals('No value selected', $result->first());
        $this->assertEquals('', $result->keys()->first());

        // Assert the original items are still present
        $this->assertEquals('Value 1', $result['key1']);
        $this->assertEquals('Value 2', $result['key2']);
    }

    /**
     * Test the withEmptyOption macro with custom label.
     *
     * @return void
     */
    public function test_with_empty_option_custom_label()
    {
        // Create a collection
        $collection = collect([
            'key1' => 'Value 1',
            'key2' => 'Value 2',
        ]);

        // Apply the macro with custom label
        $result = $collection->withEmptyOption('Select an option');

        // Assert the collection has one more item
        $this->assertEquals(3, $result->count());

        // Assert the first item is the empty option with custom label
        $this->assertEquals('Select an option', $result->first());
        $this->assertEquals('', $result->keys()->first());

        // Assert the original items are still present
        $this->assertEquals('Value 1', $result['key1']);
        $this->assertEquals('Value 2', $result['key2']);
    }
}
