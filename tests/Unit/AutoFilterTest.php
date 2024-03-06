<?php namespace Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AutoFilterTest extends TestCase
{
  public function testApplyAutoFilter() {
    request()->merge([
      'name' => 'John',
      'email' => '@gmail.com',
      'tags' => 'tag1,tag2'
    ]);
    $query = (new class extends Model {
      protected $table = 'some_table';
      public array $filterConfiguration = [
        'name' => 'like',
        'email' => 'like',
        'tags' => 'for_each|contains',
      ];
    })::query()
      ->autoFilter();

    $this->assertEquals(
      'select * from `some_table` where `name` like ? and `email` like ? and (json_contains(`tags`, ?)) and (json_contains(`tags`, ?))',
      $query->toSql()
    );
  }

  public function testApplyAutoFilterWithParameters() {
    request()->merge([
      'name' => 'John',
      'email' => '@gmail.com',
      'tags' => 'tag1,tag2'
    ]);
    $query = DB::table('some_table')
      ->autoFilter([
        'name' => 'like',
        'email' => 'like',
        'tags' => 'for_each|contains',
      ]);

    $this->assertEquals(
      'select * from `some_table` where `name` like ? and `email` like ? and (json_contains(`tags`, ?)) and (json_contains(`tags`, ?))',
      $query->toSql()
    );
  }
}
