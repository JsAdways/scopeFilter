<?php

namespace Jsadways\DataApi\Tests\Feature\filterTest;

use Tests\TestCase;
use App\Models\Campaign;

class filterTest extends TestCase
{
    public function test_happy_path_keyword():void
    {
        $result_sql = "select * from `campaign` where (`name` like '%test%' or `campaign_number` like '%test%' or `receipt_month` like '%test%' or `memo` like '%test%' or exists (select * from `campaign_cue` where `campaign`.`id` = `campaign_cue`.`campaign_id` and (`data` like '%test%')) or exists (select * from `campaign_publish` where `campaign`.`id` = `campaign_publish`.`campaign_id` and (`org_name` like '%test%' or `file_path` like '%test%')))";
        $filter = [
            'keyword' => 'test',
        ];
        $sql = Campaign::filter($filter)->toRawsql();
        $this->assertEquals($sql,$result_sql);
    }

    public function test_happy_path_and():void
    {
        $result_sql = "select * from `campaign` where (`name` = 0 and `agency_id` = 1 and `receipt_month` = 6)";
        $filter = [
            'and' => [
                'name_eq' =>0,
                'agency_id_eq' =>1,
            ],
            'receipt_month_eq' => 6
        ];
        $sql = Campaign::filter($filter)->toRawsql();
        $this->assertEquals($sql,$result_sql);
    }

    public function test_happy_path_or():void
    {
        $result_sql = "select * from `campaign` where (`name` = 0 or `agency_id` = 1) and (`receipt_month` = 6)";
        $filter = [
            'or' => [
                'name_eq' =>0,
                'agency_id_eq' =>1,
            ],
            'receipt_month_eq' => 6
        ];
        $sql = Campaign::filter($filter)->toRawsql();
        $this->assertEquals($sql,$result_sql);
    }

    public function test_happy_path_OrRelation_or():void
    {
        $result_sql = "select * from `campaign` where (exists (select * from `campaign_cue` where `campaign`.`id` = `campaign_cue`.`campaign_id` and exists (select * from `campaign_cue_month` where `campaign_cue`.`id` = `campaign_cue_month`.`campaign_cue_id` and (`month` = 6 or `status` = 1))) or exists (select * from `campaign_publish` where `campaign`.`id` = `campaign_publish`.`campaign_id` and (`org_name` = 'test' or `file_path` is not null)) and (`receipt_month` = 6))";
        $filter = [
            'OrRelation_or' => [
                'campaign_cue.campaign_cue_month' => ['month_eq'=>6,'status_eq'=>1],
                'campaign_publish' =>['org_name_eq'=>'test','file_path_nnl'=>'1'],
            ],
            'receipt_month_eq' => 6
        ];
        $sql = Campaign::filter($filter)->toRawsql();
        $this->assertEquals($sql,$result_sql);
    }

    public function test_happy_path_OrRelation_and():void
    {
        $result_sql = "select * from `campaign` where (exists (select * from `campaign_cue` where `campaign`.`id` = `campaign_cue`.`campaign_id` and exists (select * from `campaign_cue_month` where `campaign_cue`.`id` = `campaign_cue_month`.`campaign_cue_id` and (`month` = 6 and `status` = 1))) or exists (select * from `campaign_publish` where `campaign`.`id` = `campaign_publish`.`campaign_id` and (`org_name` = 'test' and `file_path` is not null)) and (`receipt_month` = 6))";
        $filter = [
            'OrRelation_and' => [
                'campaign_cue.campaign_cue_month' => ['month_eq'=>6,'status_eq'=>1],
                'campaign_publish' =>['org_name_eq'=>'test','file_path_nnl'=>'1'],
            ],
            'receipt_month_eq' => 6
        ];
        $sql = Campaign::filter($filter)->toRawsql();
        $this->assertEquals($sql,$result_sql);
    }

    public function test_happy_path_AndRelation_or():void
    {
        $result_sql = "select * from `campaign` where exists (select * from `campaign_cue` where `campaign`.`id` = `campaign_cue`.`campaign_id` and exists (select * from `campaign_cue_month` where `campaign_cue`.`id` = `campaign_cue_month`.`campaign_cue_id` and (`month` = 6 or `status` = 1))) and exists (select * from `campaign_publish` where `campaign`.`id` = `campaign_publish`.`campaign_id` and (`org_name` = 'test' or `file_path` is not null)) and (`receipt_month` = 6)";
        $filter = [
            'AndRelation_or' => [
                'campaign_cue.campaign_cue_month' => ['month_eq'=>6,'status_eq'=>1],
                'campaign_publish' =>['org_name_eq'=>'test','file_path_nnl'=>'1'],
            ],
            'receipt_month_eq' => 6
        ];
        $sql = Campaign::filter($filter)->toRawsql();
        $this->assertEquals($sql,$result_sql);
    }

    public function test_happy_path_AndRelation_and():void
    {
        $result_sql = "select * from `campaign` where exists (select * from `campaign_cue` where `campaign`.`id` = `campaign_cue`.`campaign_id` and exists (select * from `campaign_cue_month` where `campaign_cue`.`id` = `campaign_cue_month`.`campaign_cue_id` and (`month` = 6 and `status` = 1))) and exists (select * from `campaign_publish` where `campaign`.`id` = `campaign_publish`.`campaign_id` and (`org_name` = 'test' and `file_path` is not null)) and (`receipt_month` = 6)";
        $filter = [
            'AndRelation_and' => [
                'campaign_cue.campaign_cue_month' => ['month_eq'=>6,'status_eq'=>1],
                'campaign_publish' =>['org_name_eq'=>'test','file_path_nnl'=>'1'],
            ],
            'receipt_month_eq' => 6
        ];
        $sql = Campaign::filter($filter)->toRawsql();
        $this->assertEquals($sql,$result_sql);
    }

    public function test_happy_path_derive_method():void
    {
        $result_sql = "select * from `campaign` where exists (select * from `campaign_cue` where `campaign`.`id` = `campaign_cue`.`campaign_id` and exists (select * from `campaign_cue_month` where `campaign_cue`.`id` = `campaign_cue_month`.`campaign_cue_id` and (`status` = 1 and `valuation_id` = 6))) and exists (select * from `campaign_publish` where `campaign`.`id` = `campaign_publish`.`campaign_id` and (`file_path` is not null and `org_name` = 'test')) and (`receipt_month` = 6 and `signStatus` = 5)";
        $filter = [
            'AndRelation_and' => [
                'campaign_cue.campaign_cue_month' => ['campaign_cue_month_status_eq'=>6,'status_eq'=>1],
                'campaign_publish' =>['campaign_publish_status_eq'=>'test','file_path_nnl'=>'1'],
            ],
            'campaign_status_eq' => 5,
            'receipt_month_eq' => 6
        ];
        $sql = Campaign::filter($filter)->toRawsql();
        $this->assertEquals($sql,$result_sql);
    }

    public function test_happy_path_mixed():void
    {
        $result_sql = "select * from `campaign` where (`name` = 0 and `agency_id` = 1 and `receipt_month` = 6 and `signStatus` = 5) and (`organization` = 'ABC' or `creator_id` = 177) and exists (select * from `campaign_cue` where `campaign`.`id` = `campaign_cue`.`campaign_id` and exists (select * from `campaign_cue_month` where `campaign_cue`.`id` = `campaign_cue_month`.`campaign_cue_id` and (`status` = 1 and `valuation_id` = 6))) and exists (select * from `campaign_publish` where `campaign`.`id` = `campaign_publish`.`campaign_id` and (`file_path` is not null and `org_name` = 'test')) and (`campaign_number` like '%test%' or `memo` like '%test%' or exists (select * from `campaign_cue` where `campaign`.`id` = `campaign_cue`.`campaign_id` and (`data` like '%test%')) or exists (select * from `campaign_publish` where `campaign`.`id` = `campaign_publish`.`campaign_id` and (`org_name` like '%test%' or `file_path` like '%test%')))";
        $filter = [
            'keyword' => 'test',
            'and' => [
                'name_eq' =>0,
                'agency_id_eq' =>1,
            ],
            'or' => [
                'organization_eq' =>'ABC',
                'creator_id_eq' =>177,
            ],
            'AndRelation_and' => [
                'campaign_cue.campaign_cue_month' => ['campaign_cue_month_status_eq'=>6,'status_eq'=>1],
                'campaign_publish' =>['campaign_publish_status_eq'=>'test','file_path_nnl'=>'1'],
            ],
            'campaign_status_eq' => 5,
            'receipt_month_eq' => 6
        ];
        $sql = Campaign::filter($filter)->toRawsql();
        $this->assertEquals($sql,$result_sql);
    }
}
