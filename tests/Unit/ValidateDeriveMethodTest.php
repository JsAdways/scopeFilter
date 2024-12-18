<?php

namespace Jsadways\ScopeFilter\Tests\Unit\ValidateDeriveMethodTest;

use App\Models\Campaign;
use Jsadways\ScopeFilter\Services\Validation\ValidateDeriveMethod;
use Tests\TestCase;

class ValidateDeriveMethodTest extends TestCase
{
    public function test_happy_path_extract(): void
    {
        $filters = collect(['campaign_cue.campaign_cue_month'=>[
            [
                'field' => 'campaign_cue_month_status',
                'operator' => 'eq',
                'value' => 5
            ]
        ]]);

        $result = (new ValidateDeriveMethod(new Campaign()))->extract($filters);

        $this->assertIsArray($result->toArray());
        $this->assertJsonStringEqualsJsonString('[0]', json_encode(array_keys($result->toArray())));
        $this->assertEquals('campaign_cue_month_status',$result[0]['field']);
    }

    public function test_missing_method_extract(): void
    {
        $filters = collect(['campaign_cue.campaign_cue_month'=>[
            [
                'field' => 'campaign_cue_month_status1',
                'operator' => 'eq',
                'value' => 5
            ]
        ]]);

        $result = (new ValidateDeriveMethod(new Campaign()))->extract($filters);

        $this->assertIsArray($result->toArray());
        $this->assertJsonStringEqualsJsonString('[]', json_encode(array_keys($result->toArray())));
    }
}
