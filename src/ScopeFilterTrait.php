<?php

namespace Jsadways\ScopeFilter;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use ReflectionMethod;

trait ScopeFilterTrait
{
    private array $validKey = ["keyword","or","and","OrRelation_or","AndRelation_or","OrRelation_and","AndRelation_and"];
    private array $directSearchKey = ["or","and"];
    private Collection $tableColumns;
    private Collection $tableRelationColumns;
    private Collection $keywordSearchColumns;
    private Builder $query;

    /**
     * Scope filter
     *
     * 查詢調整過濾 scope
     *
     * @param Builder $query
     * @param array[
     *     'name_k' => ['hello world']' ['{field}_{rule}' => ['value']]
     * ]
     * @return Builder|Exception
     * @throws Exception
     */
    public function scopeFilter(Builder $query, array $filters): Builder|Exception
    {
        try {
            $this->query = $query;
            $filters = collect($filters);

            //存下column name
            $this->tableColumns = collect(Schema::getColumnListing($this->getTable()));
            $this->keywordSearchColumns = collect($this->getFillable());
            $relations = $this->_getAvailableRelations();
            $this->tableRelationColumns = collect([]);
            foreach($relations as $relation){
                $this->tableRelationColumns->put($relation,collect(Schema::getColumnListing($this->{$relation}()->getRelated()->getTable())));
            }

            //整理validKey
            $validFilters = $this->_formatValidKey($filters);
            //keyword挪動到最後才執行
            $keywordSearchCondition = $validFilters->pull('keyword');
            if(!empty($keywordSearchCondition)){
                $validFilters->put('keyword',$keywordSearchCondition);
            }
            $validFilters->map(function ($conditionArray,$validKeyName){
                //實際呼叫_fitXXX
                /** @see _fitKeyword */ /** @see _fitOr */ /** @see _fitAnd */ /** @see _fitOrRelation_or */ /** @see _fitOrRelation_and */ /** @see _fitAndRelation_or */ /** @see _fitAndRelation_and */
                $this->{'_fit'.ucfirst($validKeyName)}($conditionArray);
            });

            return $this->query;
        } catch (Exception $e) {
            throw new Exception("scopeFilter - {$e->getMessage()}");
        }
    }


    /**
     * format valid key
     *
     * 檢查傳入key是否合法以及合併直接搜尋欄位到and
     *
     * @param Collection $filters
     * @return Collection
     */
    protected function _formatValidKey(Collection $filters): Collection
    {
        //符合定義key值
        $validKey = $filters->filter(function($item,$key){
            return collect($this->validKey)->contains($key);
        });
        //符合欄位名稱
        $columnKey = $filters->diffKeys($validKey)->filter(function ($item,$key){
            $element = explode('_',$key);
            array_pop($element);
            $field = implode('_',$element);
            return $this->tableColumns->contains($field);
        });

        if(!$filters->has('and')){
            $filters['and'] = [];
        }
        $filters['and'] = array_merge($filters['and'],$columnKey->toArray());
        $filters->forget($columnKey->keys());

        return $filters;
    }

    /**
     * fit keyword
     *
     * 處理keyword關鍵字查詢語法
     *
     * @param string $value
     * @return void
     * @throws Exception
     */
    protected function _fitKeyword(string $value):void
    {
        $this->query->where(function($sub_query)use($value){
            //組合mode中所有的可提供keyword search column
            $this->keywordSearchColumns->map(function ($column)use($sub_query,$value){
                $filterData = [
                    'field' => $column,
                    'operator' => 'k',
                    'value' => $value,
                ];
                $this->_matchCondition($sub_query,$filterData,'or');
            });

            $this->tableRelationColumns->map(function ($columns,$relation)use($value,$sub_query){
                //組合所有relation中的 column
                $relation_conditions = $columns->reduce(function ($result,$item)use($value){
                    $result[$item.'_k'] = $value;

                    return $result;
                },[]);

                $this->_fitRelation([$relation=>$relation_conditions],'orWhereHas','or',$sub_query);
            });
        });
    }

    /**
     * fit or
     *
     * 處理Or查詢語法
     *
     * @param array $conditionArray
     * @return void
     * @throws Exception
     */
    protected function _fitOr(array $conditionArray): void
    {
        $this->_fitColumn($conditionArray,'or');
    }

    /**
     * fit and
     *
     * 處理And查詢語法
     *
     * @param array $conditionArray
     * @return void
     * @throws Exception
     */
    protected function _fitAnd(array $conditionArray): void
    {
        $this->_fitColumn($conditionArray,'and');
    }

    /**
     * fit column
     *
     * 處理欄位查詢語法
     *
     * @param array $conditionArray
     * @param string $logic
     * @return void
     * @throws Exception
     */
    protected function _fitColumn(array $conditionArray,string $logic): void
    {
        $this->query = $this->query->where(function($sub_query)use($conditionArray,$logic){
            collect($conditionArray)->map(function ($value,$column_condition)use($sub_query,$logic){
                //check key fit table column name
                $formatted = $this->_checkColumnValid($this->tableColumns,$column_condition,$value);
                if($formatted !== false){
                    //if check pass, fit where condition
                    $this->_matchCondition($sub_query,$formatted,$logic);
                }
            });
        });
        //將欄位名稱從keyword可搜尋的欄位中移除
        $this->_removeKeywordSearchColumn($conditionArray);
    }

    /**
     * fit or relation or
     *
     * 處理orWhereHas查詢語法，內部條件為Or
     *
     * @param array $conditionArray
     * @return void
     * @throws Exception
     */
    protected function _fitOrRelation_or(array $conditionArray): void
    {
        $this->_fitRelation($conditionArray,'orWhereHas','or');
    }

    /**
     * fit and relation or
     *
     * 處理whereHas查詢語法，內部條件為Or
     *
     * @param array $conditionArray
     * @return void
     * @throws Exception
     */
    protected function _fitAndRelation_or(array $conditionArray): void
    {
        $this->_fitRelation($conditionArray,'whereHas','or');
    }

    /**
     * fit or relation and
     *
     * 處理orWhereHas查詢語法，內部條件為And
     *
     * @param array $conditionArray
     * @return void
     * @throws Exception
     */
    protected function _fitOrRelation_and(array $conditionArray): void
    {
        $this->_fitRelation($conditionArray,'orWhereHas','and');
    }

    /**
     * fit and relation and
     *
     * 處理whereHas查詢語法，內部條件為and
     *
     * @param array $conditionArray
     * @return void
     * @throws Exception
     */
    protected function _fitAndRelation_and(array $conditionArray): void
    {
        $this->_fitRelation($conditionArray,'whereHas','and');
    }

    /**
     * fit relation
     *
     * 實際處理relation相關語法
     *
     * @param array $conditionArray
     * @param string $whereHas whereHas, orWhereHas
     * @param string $logic and , or
     * @return void
     */
    protected function _fitRelation(array $conditionArray,string $whereHas,string $logic,Builder $query = null):void
    {
        if(empty($query)){
            $query = $this->query;
        }
        collect($conditionArray)->map(function ($conditions, $relationName)use($whereHas,$logic,$query){
            if ($this->_checkRelationValid($relationName)) {
                $query->{$whereHas}($relationName,function(Builder $sub_query)use($relationName,$conditions,$logic){
                    $sub_query->where(function(Builder $relationQuery)use($relationName,$conditions,$logic){
                        collect($conditions)->map(function ($relationValue, $relationColumn_condition) use ($relationName,$relationQuery,$logic) {
                            //check key fit table column name
                            $checkResult = $this->_checkColumnValid($this->tableRelationColumns[$relationName], $relationColumn_condition, $relationValue);
                            if ($checkResult !== false) {
                                //if check pass, fit where condition
                                $this->_matchCondition($relationQuery, $checkResult, $logic);
                            }
                        });
                    });
                });
            }
        });
    }

    /**
     * check column valid
     *
     * 檢查查詢名稱是否為正確的欄位，正確回覆拆分後的field,operator,value
     *
     * @param Collection $columns
     * @param string $key
     * @param Mixed $value
     * @return bool|array
     */
    protected function _checkColumnValid(Collection $columns, string $key,Mixed $value): bool|array
    {
        $element = explode('_',$key);
        $operator = array_pop($element);
        $field = implode('_',$element);

        if($columns->contains($field)){
            return [
                'field' => $field,
                'operator' => $operator,
                'value' => $value
            ];
        }

        return false;
    }

    /**
     * check relation valid
     *
     * 檢查查詢relation名稱是否正確
     *
     * @param string $relation
     * @return bool
     */
    protected function _checkRelationValid(string $relation): bool
    {
        return $this->tableRelationColumns->keys()->contains($relation);
    }

    /**
     * match condition
     *
     * 實際組合filter condition where條件
     *
     * @param Builder $query
     * @param array $filter
     * @param string $logic
     * @return Builder|Exception
     * @throws Exception
     */
    protected function _matchCondition(Builder $query, array $filter, string $logic): Builder|Exception
    {
        try {
            $field = $filter['field'];
            $operator = $filter['operator'];
            $value = $filter['value'];
            $whereString = ($logic === 'or') ? 'orWhere' : 'where';

            if(empty($value)){
                return $query;
            }

            return match ($operator) {
                'k' => $query->{$whereString}($field, 'like', "%{$value}%"),
                'ipp', 'ie' => $query->{$whereString}($field, 'like', "%{$value}"),
                'iel' => $query->{$whereString}($field, 'ilike', "%{$value}"),
                'in' => (is_array($value)) ? $query->{$whereString.'In'}($field, $value) : $query,
                'nin' => (is_array($value)) ? $query->{$whereString.'NotIn'}($field, $value) : $query,
                'ge' => $query->{$whereString}($field, '>=', $value),
                'gt' => $query->{$whereString}($field, '>', $value),
                'ne' => $query->{$whereString}($field, '!=', $value),
                'eq' => $query->{$whereString}($field, '=', $value),
                'lt' => $query->{$whereString}($field, '<', $value),
                'le' => $query->{$whereString}($field, '<=', $value),
                'nl' => $query->{$whereString.'Null'}($field),
                'nnl' => $query->{$whereString.'NotNull'}($field),
                'cge' => $query->{$whereString.'Column'}($field, '>=', $value),
                'cgt' => $query->{$whereString.'Column'}($field, '>', $value),
                'cne' => $query->{$whereString.'Column'}($field, '!=', $value),
                'ceq' => $query->{$whereString.'Column'}($field, '=', $value),
                'clt' => $query->{$whereString.'Column'}($field, '<', $value),
                'cle' => $query->{$whereString.'Column'}($field, '<=', $value),
                'dr' => (is_array($value)) ? $query->{$whereString.'Between'}($field, [$value[0], $value[1]]) : $query
            };


        } catch (Exception $e) {
            throw new Exception("scopeFilter - {$e->getMessage()}");
        }
    }

    /**
     * remove keyword search column
     *
     * 從$this->keywordSearchColumns中移除已經被搜尋過的欄位
     *
     * @param array $conditionArray
     */
    protected function _removeKeywordSearchColumn(array $conditionArray):void
    {
        $columns = collect($conditionArray)->keys();
        $columns->map(function ($columnName){
            $element = explode('_',$columnName);
            array_pop($element);
            $field = implode('_',$element);
            $this->keywordSearchColumns = $this->keywordSearchColumns->reject(function($column)use($field){
                return $column == $field;
            });
        });
    }

    /**
     * get available relation
     *
     * 找到Model中定義之relation
     *
     * @return array ['name']
     */
    protected function _getAvailableRelations(): array
    {
        return array_keys(array_reduce(
            (new ReflectionClass(static::class))->getMethods(ReflectionMethod::IS_PUBLIC),
            function ($result, ReflectionMethod $method) {
                // If this function has a return type
                ($returnType = (string) $method->getReturnType()) &&

                // And this function returns a relation
                is_subclass_of($returnType, Relation::class) &&

                // Add name of this method to the relations array
                ($result = array_merge($result, [$method->getName() => $returnType]));

                return $result;
            }, []
        ));
    }


}
