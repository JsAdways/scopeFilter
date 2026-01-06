<?php

namespace Jsadways\ScopeFilter\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

trait MongoDBSupport
{
    abstract public function getConnection();
    abstract public function getFillable();
    /**
     * 檢查是否為 MongoDB 連接
     */
    protected function _isMongoDB(): bool
    {
        return $this->getConnection()->getDriverName() === 'mongodb';
    }

    /**
     * 取得欄位列表（支援 MongoDB）
     *
     * @param string $tableName
     * @return array
     */
    protected function _getColumnListing(string $tableName): array
    {
        if ($this->_isMongoDB()) {
            // MongoDB: 從 fillable 取得欄位
            return $this->getFillable();
        }

        return Schema::getColumnListing($tableName);
    }

    /**
     * 取得欄位類型（支援 MongoDB）
     *
     * @param string $tableName
     * @param string $column
     * @return string
     */
    protected function _getColumnType(string $tableName, string $column): string
    {
        if ($this->_isMongoDB()) {
            // MongoDB 預設都當作 text 類型
            return 'text';
        }

        return Schema::getColumnType($tableName, $column);
    }

    /**
     * 檢查欄位是否存在（支援 MongoDB）
     *
     * @param string $tableName
     * @param string $column
     * @return bool
     */
    protected function _hasColumn(string $tableName, string $column): bool
    {
        if ($this->_isMongoDB()) {
            // MongoDB: 檢查是否在 fillable 中
            return in_array($column, $this->getFillable());
        }

        return Schema::hasColumn($tableName, $column);
    }

    /**
     * 取得 Relation Model 的欄位列表（支援 MongoDB）
     *
     * @param Model $relationModel
     * @param string $tableName
     * @return array
     */
    protected function _getRelationColumnListing(Model $relationModel, string $tableName): array
    {
        if ($this->_isMongoDB()) {
            // MongoDB: 從 relation model 的 fillable 取得
            return $relationModel->getFillable();
        }

        return Schema::getColumnListing($tableName);
    }

    /**
     * 取得 Relation Model 的欄位類型（支援 MongoDB）
     *
     * @param string $tableName
     * @param string $column
     * @return string
     */
    protected function _getRelationColumnType(string $tableName, string $column): string
    {
        if ($this->_isMongoDB()) {
            // MongoDB 預設都當作 text 類型
            return 'text';
        }

        return Schema::getColumnType($tableName, $column);
    }
}
