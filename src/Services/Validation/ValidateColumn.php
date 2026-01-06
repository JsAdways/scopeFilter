<?php
namespace Jsadways\ScopeFilter\Services\Validation;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Jsadways\ScopeFilter\Core\Service\Validation\Contracts\ValidationContract;

class ValidateColumn implements ValidationContract
{
    public function extract(Collection $filters): Collection
    {
        // TODO: Implement extract() method.
        $tableName = $filters->keys()[0];

        return collect($filters[$tableName])->filter(function ($filter)use($tableName){
            $connection = app('db')->connection();

            if ($connection->getDriverName() === 'mongodb') {
                // MongoDB: 直接返回 true，不檢查欄位
                return true;
            }

            return Schema::hasColumn($tableName,$filter['field']);
        });
    }
}
