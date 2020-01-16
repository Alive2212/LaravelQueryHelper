<?php
/**
 * Created by PhpStorm.
 * User: alive
 * Date: 10/8/17
 * Time: 4:04 AM
 */

namespace Alive2212\LaravelQueryHelper;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class QueryHelper
{
    /**
     * filter Delimiter
     * @var
     */
    protected $filterDelimiter = '.';


    /**
     * @var string
     */
    protected $queryFilterTitle = 'query_filters';

    /**
     * @param $query
     * @param $filters
     * @return mixed
     */
    public function orDeepFilter($query, $filters)
    {
        $result = $query;
        $keys = explode('.', $filters[0]['key']);
        if (collect($keys)->count() == 2) {
            $result = $result->orWhereHas($keys[0], function ($query) use ($filters, $keys) {
                foreach ($filters as $filter) {
                    $keys = explode('.', $filter['key']);
                    $query->where($keys[1], $filter['operator'], $filter['value']);
                }
            });
        }
        return $result;
    }

    /**
     * @param $query
     * @param $param
     * @return mixed
     */
    public function orderBy($query, $param)
    {
        $result = $query;
        if ($param->count()) {
            $query->orderBy($param[0], $param[1]);
        }
        return $result;
    }

    /**
     * @param $params
     * @param $field
     * @return array
     */
    public function getValueArray($params, $field)
    {
        $result = [];
        foreach ($params as $key => $item) {
            array_push($result, $item[$field]);
        }
        return $result;
    }

    /**
     * @param Builder $model
     * @param array $filters
     * @return Builder
     */
    public function smartDeepFilter(Builder $model, array $filters): Builder
    {
        $filters = $this->filterAdaptor($filters);
        $model = $this->addCondition($model, $filters);
        return $model;
    }

    /**
     * @param array $filters
     * @return array
     */
    public function filterAdaptor(array $filters): array
    {
        $adaptedFilters = [];
        foreach ($filters as $filter) {
            array_push($adaptedFilters, $this->filterWhereConditionAdaptor($filter));
        }
        return $adaptedFilters;
    }

    /**
     * @param array $filters
     * @return array
     */
    public function filterWhereConditionAdaptor(array $filters): array
    {
        $result = [];
        foreach ($filters as $filter) {
            $result = array_merge_recursive($result, $this->getHierarchyFilterKey($filter));
        }
        return $result;
    }

    public function getHierarchyFilterKey(array $filter): array
    {
        $filterKeyParams = $this->getFilterKey($filter);
        $firstFilterKey = $filterKeyParams[0];
        if (count($filterKeyParams) > 1) {
            unset($filterKeyParams[0]);
            $filter[0] = implode($this->filterDelimiter, $filterKeyParams);
            $result[$firstFilterKey] = $this->getHierarchyFilterKey($filter);
        } else {
            $firstFilterOperator = $this->getFilterOperator($filter);
            $firstFilterValue = $this->getFilterValue($filter);
            $result[$this->queryFilterTitle] = [
                [
                    $firstFilterKey,
                    $firstFilterOperator,
                    $firstFilterValue,
                ],
            ];
        }
        return $result;
    }

    /**
     * @param Builder $model
     * @param array $filters
     * @return Builder
     */
    public function addCondition(Builder $model, array $filters): Builder
    {
        $firstFilter = $filters[0];
        $modelQuery = $model->where(function ($query) use ($firstFilter) {
            $this->addWhereCondition($query, $firstFilter);
        });
        unset($filters[0]);
        foreach ($filters as $filter) {
            $modelQuery = $modelQuery->orWhere(function ($query) use ($filter) {
                $this->addWhereCondition($query, $filter);
            });
        }
        return $modelQuery;
    }

    /**
     * @param Builder $model
     * @param array $filters
     * @return Builder
     */
    public function addWhereCondition(Builder $model, array $filters): Builder
    {
        foreach ($filters as $filterKey => $filtersValues) {
            if ($filterKey == $this->queryFilterTitle) {
                $model = $model->where($filtersValues);
            } else {
                $model = $model->whereHas($filterKey, function (Builder $query) use ($model, $filtersValues) {
                    $query = $this->addWhereCondition($query,$filtersValues);
                });
            }
        }
        return $model;
    }

    /**
     * @return string
     */
    public function getFilterDelimiter():string
    {
        return $this->filterDelimiter;
    }

    /**
     * @param $filterDelimiter
     * @return QueryHelper
     */
    public function setFilterDelimiter($filterDelimiter):QueryHelper
    {
        $this->filterDelimiter = $filterDelimiter;
        return $this;
    }

    /**
     * @param array $filter
     * @return array
     */
    public function getFilterKey(array $filter): array
    {
        if (array_key_exists('key', $filter)) {
            $result = explode($this->filterDelimiter, $filter['key']);
        } else {
            $result = explode($this->filterDelimiter, $filter[0]);
        }
        return $result;
    }

    /**
     * @param array $filter
     * @return string
     */
    public function getFilterOperator(array $filter): string
    {
        return array_key_exists('operator', $filter) ? $filter['operator'] : $filter[1];
    }

    /**
     * @param array $filter
     * @return string
     */
    public function getFilterValue(array $filter): string
    {
        return array_key_exists('value', $filter) ? $filter['value'] : $filter[2];
    }
}