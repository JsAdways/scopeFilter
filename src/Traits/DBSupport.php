<?php

namespace Jsadways\ScopeFilter\Traits;

use Illuminate\Support\Facades\DB;

trait DBSupport
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
            // MongoDB: 從 fillable 取得欄位，型態一律視為 text
            return collect($this->getFillable())->mapWithKeys(fn($col) => [$col => 'text'])->toArray();
        }

        $columns = DB::select("
            SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
        ", [$tableName]);

        return collect($columns)->pluck('DATA_TYPE', 'COLUMN_NAME')->toArray();
    }


}
