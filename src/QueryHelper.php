<?php
/**
 * Created by PhpStorm.
 * User: alive
 * Date: 10/8/17
 * Time: 4:04 AM
 */

namespace Alive2212\LaravelQueryHelper;

use Illuminate\Database\Eloquent\Builder;

class QueryHelper
{
    /**
     * filter Delimiter
     * @var
     */
    protected $filterDelimiter = '.';

    protected $orWhereConditionLevel = 1;

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
            return $query->orderBy($param[0], $param[1]);
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
    public function smartDeepFilter($model, array $filters)
    {
        $filters = $this->filterAdaptor($filters);
        $model = $this->addWhereCondition($model, $filters);
        return $model;
    }

    /**
     * @param array $filters
     * @param int $level
     * @param array $adaptedFilters
     * @return array
     */
    public function filterAdaptor(array $filters, $level = 0, $adaptedFilters = []): array
    {
        foreach ($filters as $filter) {
            if (is_array($filter[0])) {
                $result = $this->filterAdaptor($filter, $level + 1);
            } else {
                $result = $this->getHierarchyFilterKey($filter);
            }
            $key = $level % 2 == $this->orWhereConditionLevel ?
                'or_' . $this->queryFilterTitle :
                'and_' . $this->queryFilterTitle;
            $adaptedFilters = array_merge_recursive($adaptedFilters, [$key => $result]);
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

    /**
     * @param array $filter
     * @return array
     */
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
                    $firstFilterValue == null ? null : $firstFilterValue,
                ],
            ];
        }
        return $result;
    }

    /**
     * @param Builder $model
     * @param array $filters
     * @param string $type
     * @return Builder
     */
    public function addCondition(Builder $model, array $filters, string $type = ''): Builder
    {
        foreach ($filters as $filterKey => $filterValues) {
            $currentFilter = [$filterKey => $filterValues];
            $model = $model->Where(function ($query) use ($currentFilter) {
                $this->addWhereCondition($query, $currentFilter, $this->orWhereConditionLevel == 0 ? 'and' : 'or');
            });
        }
        return $model;
    }

    /**
     * @param Builder $model
     * @param array $filters
     * @param string $type
     * @param int $index
     * @return Builder
     */
    public function addWhereCondition($model, array $filters, $type = 'and', $index = 0)
    {
        // Get key and value
        $firstFilterKey = array_key_first($filters);
        if ($firstFilterKey === null) {
            return $model;
        }
        $firstFilterValue = $filters[$firstFilterKey];

        switch ($firstFilterKey) {
            // if final query
            case $this->queryFilterTitle:

                $firstFilterInnerKey = array_key_first($filters[$firstFilterKey]);
                if ($firstFilterInnerKey === null) {
                    return $model;
                }

                // if not first record and type is or
                if ($type == "or" && $index > 0) {
                    $model = $model->orWhere([$firstFilterValue[$firstFilterInnerKey]]);
                } else {
                    $whereCondition = $firstFilterValue[$firstFilterInnerKey];
                    if (strtolower($whereCondition[2]) == "null") {
                        if (
                            strtolower($whereCondition[1]) == "is" ||
                            $whereCondition[1] == "="
                        ) {
                            $model = $model->whereNull($whereCondition[0]);
                        } elseif (
                            strtolower($whereCondition[1]) == "not" ||
                            $whereCondition[1] == "<>" ||
                            $whereCondition[1] == "!="
                        ) {
                            $model = $model->whereNotNull($whereCondition[0]);
                        }
                    } else {
                        $model = $model->where([$whereCondition]);
                    }
                }

                // Get Inner key and value
                if (count($filters[$firstFilterKey]) === 1) {
                    break;
                }
                unset($filters[$firstFilterKey][$firstFilterInnerKey]);
                return $this->addWhereCondition($model, $filters, $type, ++$index);

            // if and
            case 'and_' . $this->queryFilterTitle:
                // if not first record and type is or
                if ($type == "or" && $index > 0) {
                    $model = $model->orWhere(function (Builder $builder) use ($firstFilterValue) {
                        $builder = $this->addWhereCondition($builder, $firstFilterValue, 'and', 0);
                        return $builder;
                    });
                } else {
                    $model = $model->where(function (Builder $builder) use ($firstFilterValue) {
                        $builder = $this->addWhereCondition($builder, $firstFilterValue, 'and', 0);
                        return $builder;
                    });
                }
                break;

            // if or
            case 'or_' . $this->queryFilterTitle:
                // if not first record and type is or
                if ($type == "or" && $index > 0) {
                    $model = $model->orWhere(function (Builder $builder) use ($firstFilterValue) {
                        $builder = $this->addWhereCondition($builder, $firstFilterValue, 'or', 0);
                        return $builder;
                    });
                } else {
                    $model = $model->where(function (Builder $builder) use ($firstFilterValue) {
                        $builder = $this->addWhereCondition($builder, $firstFilterValue, 'or', 0);
                        return $builder;
                    });
                }
                break;

            // if relational
            default:
                // if not first record and type is or
                if ($type == "or" && $index > 0) {
                    $model = $model->orWhereHas($firstFilterKey, function (Builder $builder) use ($firstFilterValue) {
                        $builder = $this->addWhereCondition($builder, $firstFilterValue, 'or', 0);
                        return $builder;
                    });
                } else {
                    $model = $model->whereHas($firstFilterKey, function (Builder $builder) use ($firstFilterValue) {
                        $builder = $this->addWhereCondition($builder, $firstFilterValue, 'and', 0);
                        return $builder;
                    });
                }
                break;
        }

        unset($filters[$firstFilterKey]);
        if (count($filters)) {
            return $this->addWhereCondition($model, $filters, $type, ++$index);
        }
        return $model;
    }

    /**
     * @return string
     */
    public function getFilterDelimiter(): string
    {
        return $this->filterDelimiter;
    }

    /**
     * @param $filterDelimiter
     * @return $this
     */
    public function setFilterDelimiter($filterDelimiter): QueryHelper
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
    public function getFilterValue(array $filter): ?string
    {
        return (array_key_exists('value', $filter)) ? $filter['value'] :
            ($filter[2] == null ? "null" : $filter[2]);
    }
}
