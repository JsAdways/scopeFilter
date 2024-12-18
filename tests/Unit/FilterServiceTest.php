<?php

namespace Jsadways\ScopeFilter\Tests\Unit\FilterServiceTest;

use App\Models\Campaign;
use Jsadways\ScopeFilter\Services\Filter\FilterFormatDto;
use Jsadways\ScopeFilter\Services\Filter\FilterGetRelationModelDto;
use Jsadways\ScopeFilter\Services\Filter\FilterService;
use Tests\TestCase;

class FilterServiceTest extends TestCase
{
    public function test_happy_path_format():void
    {
        $filters = collect([
            'name_eq'=>'kevin',
            'agency_id_eq'=>1,
            'campaign_status_eq'=>5
        ]);
        $result = (new FilterService())->format(new FilterFormatDto($filters));

        $this->assertIsArray($result);
        $this->assertJsonStringEqualsJsonString('[0,1,2]',json_encode(array_keys($result)));
        $this->assertEquals('name',$result[0]['field']);
    }

    public function test_happy_path_getRelationModel():void
    {
        $campaign = new Campaign();
        $result = (new FilterService())->getRelationModel(new FilterGetRelationModelDto($campaign,'campaign_cue'))->getTable();

        $this->assertEquals('campaign_cue',$result);
    }

//    public function test_missing_system()
//    {
//        $payload = [
//            'system' => '人員系統',
//            'repository' => 'SettingReceiptDollarType',
//            'condition' => '{"filter":{"status_eq":1},"per_page":"0"}'
//        ];
//        $result = (new CrossService())->fetch(new CrossDto(...$payload));
//
//        $this->assertIsArray($result);
//        $this->assertJsonStringEqualsJsonString('["status_code","data"]',json_encode(array_keys($result)));
//        $this->assertEquals(42000,$result['status_code']);
//        $this->assertEquals('Data Api URL not found',$result['data']);
//    }
//
//    public function test_missing_repository()
//    {
//        $payload = [
//            'system' => '財務系統',
//            'repository' => 'SettingReceiptDollarType1',
//            'condition' => '{"filter":{"status_eq":1},"per_page":"0"}'
//        ];
//        $result = (new CrossService())->fetch(new CrossDto(...$payload));
//
//        $this->assertIsArray($result);
//        $this->assertJsonStringEqualsJsonString('["status_code","data"]',json_encode(array_keys($result)));
//        $this->assertEquals(42000,$result['status_code']);
//        $this->assertEquals('Class not found.',$result['data']);
//    }
}
