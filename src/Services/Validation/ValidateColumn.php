<?php
namespace Jsadways\ScopeFilter\Services\Validation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Jsadways\ScopeFilter\Core\Service\Validation\Contracts\ValidationContract;

class ValidateColumn implements ValidationContract
{
    public function __construct(protected Model $target){}
    public function extract(Collection $filters): Collection
    {
        // TODO: Implement extract() method.
        $tableName = $filters->keys()[0];

        return collect($filters[$tableName])->filter(function ($filter)use($tableName){
//            $connection = app('db')->connection();
//
//            if ($connection->getDriverName() === 'mongodb') {
//                // MongoDB: 直接返回 true，不檢查欄位
//                return true;
//            }
//
//            return Schema::hasColumn($tableName,$filter['field']);

            return $this->_hasColumnForModel($tableName,$filter['field']);
        });
    }

    /**
     * 檢查指定 Model 是否使用 MongoDB
     *
     * @return bool
     */
    protected function _isMongoDBModel(): bool
    {
        return $this->target->getConnection()->getDriverName() === 'mongodb';
    }

    /**
     * 檢查指定 Model 的欄位是否存在
     *
     * @param string $tableName
     * @param string $column
     * @return bool
     */
    protected function _hasColumnForModel(string $tableName, string $column): bool
    {
        if ($this->_isMongoDBModel()) {
            // MongoDB: 檢查是否在 fillable 中
            return in_array($column, $this->target->getFillable());
        }

        return Schema::hasColumn($tableName, $column);
    }
}
