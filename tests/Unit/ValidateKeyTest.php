<?php

namespace Jsadways\ScopeFilter\Tests\Unit\ValidateKeyTest;

use Jsadways\ScopeFilter\Services\Validation\ValidateKey;
use Tests\TestCase;

class ValidateKeyTest extends TestCase
{
    public function test_happy_path_extract():void
    {
        $condition = collect([
            "and" => [
                "name_eq" => 0,
                "agency_id_eq" => 1,
            ],
            "keyword" => "test",
            "or" => [
                "start_dt_eq" => '2024-01-01',
                "end_dt_eq" => '2024-01-01'
            ],
            "campaign_status_eq" => 5
        ]);

        $result = (new ValidateKey())->extract($condition);

        $this->assertIsArray($result->toArray());
        $this->assertJsonStringEqualsJsonString('["and","keyword","or"]', json_encode(array_keys($result->toArray())));
        $this->assertNotContains('campaign_status_eq',$result->keys());
    }
}
