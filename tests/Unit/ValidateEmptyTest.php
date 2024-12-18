<?php

namespace Jsadways\ScopeFilter\Tests\Unit\ValidateEmptyTest;

use Jsadways\ScopeFilter\Services\Validation\ValidateEmpty;
use Tests\TestCase;

class ValidateEmptyTest extends TestCase
{
    public function test_happy_path_extract(): void
    {
        $condition = collect([
            "name_eq" => 0,
            "agency_id_eq" => 1,
            "campaign_status_eq" => 5
        ]);

        $result = (new ValidateEmpty())->extract($condition);

        $this->assertIsArray($result->toArray());
        $this->assertJsonStringEqualsJsonString('["name_eq","agency_id_eq","campaign_status_eq"]', json_encode(array_keys($result->toArray())));
        $this->assertEquals(0,$result['name_eq']);
    }

    public function test_name_in_empty_extract(): void
    {
        $condition = collect([
            "name_eq" => '',
            "agency_id_eq" => 1,
            "campaign_status_eq" => 5
        ]);

        $result = (new ValidateEmpty())->extract($condition);

        $this->assertIsArray($result->toArray());
        $this->assertJsonStringEqualsJsonString('["agency_id_eq","campaign_status_eq"]', json_encode(array_keys($result->toArray())));
        $this->assertNotContains('name_eq',$result->keys());
    }

    public function test_name_in_null_extract(): void
    {
        $condition = collect([
            "name_eq" => null,
            "agency_id_eq" => 1,
            "campaign_status_eq" => 5
        ]);

        $result = (new ValidateEmpty())->extract($condition);

        $this->assertIsArray($result->toArray());
        $this->assertJsonStringEqualsJsonString('["agency_id_eq","campaign_status_eq"]', json_encode(array_keys($result->toArray())));
        $this->assertNotContains('name_eq',$result->keys());
    }
}
