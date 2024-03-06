<?php namespace Tests\Unit;

use Illuminate\Support\Facades\DB;
use Prometa\Sleek\FilterPipeline;
use Tests\TestCase;

class FilterPipelineTest extends TestCase
{
    public function testEmptyFilterAddsNoConstraints() {
        $query = DB::table('test');
        $query = FilterPipeline::from([])->apply($query, 'field', 'value');

        $this->assertEquals('select * from `test`', $query->toSql());
    }

    public function testEqualsFilterAddsWhereClause() {
        $query = DB::table('test');
        $query = FilterPipeline::from('equals')->apply($query, 'field', 'value');

        $this->assertEquals('select * from `test` where `field` = ?', $query->toSql());
        $this->assertEquals(['value'], $query->getBindings());
    }

    public function testLikeFilterAddsWhereClause() {
        $query = DB::table('test');
        $query = FilterPipeline::from('like')->apply($query, 'field', 'value');

        $this->assertEquals('select * from `test` where `field` like ?', $query->toSql());
        $this->assertEquals(['%value%'], $query->getBindings());
    }

    public function testContainsFilterAddsWhereJsonContainsClause() {
        $query = DB::table('test');
        $query = FilterPipeline::from('contains')->apply($query, 'field', 'value');

        $this->assertEquals('select * from `test` where (json_contains(`field`, ?))', $query->toSql());
        $this->assertEquals(['"value"'], $query->getBindings());
    }

    public function testForEachFilterSplitsValueAndCallsSubsequentPipelineWithEachValue() {
        $query = DB::table('test');
        $query = FilterPipeline::from('for_each|like')->apply($query, 'field', 'value1, value2');

        $this->assertEquals('select * from `test` where `field` like ? and `field` like ?', $query->toSql());
        $this->assertEquals(['%value1%', '%value2%'], $query->getBindings());
    }
}
