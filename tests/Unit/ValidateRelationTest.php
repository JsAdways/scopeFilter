<?php

namespace Jsadways\ScopeFilter\Tests\Unit\ValidateKeyTest;

use App\Models\Campaign;
use Jsadways\ScopeFilter\Services\Validation\ValidateRelation;
use Tests\TestCase;

class ValidateRelationTest extends TestCase
{
    public function test_happy_path_extract():void
    {
        $condition = collect([
            "campaign_cue.campaign_cue_month" => [
                "campaign_cue_month_status_eq" => 6,
            ]
        ]);

        $result = (new ValidateRelation(new Campaign()))->extract($condition);

        $this->assertIsArray($result->toArray());
        $this->assertJsonStringEqualsJsonString('["campaign_cue.campaign_cue_month"]', json_encode($result->keys()));
    }

    public function test_missing_relation_extract():void
    {
        $condition = collect([
            "campaign_cue.campaign_cue_month_" => [
                "campaign_cue_month_status_eq" => 6,
            ]
        ]);

        $result = (new ValidateRelation(new Campaign()))->extract($condition);

        $this->assertIsArray($result->toArray());
        $this->assertEquals(0,count($result->keys()));
    }
}
