<?php

namespace Jsadways\ScopeFilter\Tests\Unit\ValidateColumnTest;

use Jsadways\ScopeFilter\Services\Validation\ValidateColumn;
use Tests\TestCase;

class ValidateColumnTest extends TestCase
{
    public function test_happy_path_extract(): void
    {
        $filters = collect(['campaign'=>[
            [
                'field' => 'name',
                'operator' => 'eq',
                'value' => 'kevin'
            ],
            [
                'field' => 'agency_id',
                'operator' => 'eq',
                'value' => 1
            ]
        ]]);
        $result = (new ValidateColumn())->extract($filters);

        $this->assertIsArray($result->toArray());
        $this->assertJsonStringEqualsJsonString('[0,1]', json_encode(array_keys($result->toArray())));
        $this->assertEquals('name',$result[0]['field']);
        $this->assertEquals('agency_id',$result[1]['field']);

    }

    public function test_messing_field_extract():void
    {
        $filters = collect(['campaign'=>[
            [
                'field' => 'name_',
                'operator' => 'eq',
                'value' => 'kevin'
            ],
            [
                'field' => 'agency_id',
                'operator' => 'eq',
                'value' => 1
            ]
        ]]);
        $result = (new ValidateColumn())->extract($filters);

        $this->assertIsArray($result->toArray());
        $this->assertJsonStringEqualsJsonString('[1]', json_encode(array_keys($result->toArray())));
        $this->assertEquals('agency_id',$result[1]['field']);
    }
}
