<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Models\Job;


class JobFilterService
{
    public function applyFilters(Builder $query, ?string $filterString): Builder
    {
        if (!$filterString) {
            return $query;
        }

        // Parse the filter string into structured conditions
        $conditions = $this->parseFilterString($filterString);

        foreach ($conditions as $condition) {
            if ($condition['type'] === 'basic') {
                $query->where($condition['field'], $condition['operator'], $condition['value']);
            } elseif ($condition['type'] === 'relationship') {
                $this->applyRelationshipFilter($query, $condition);
            } elseif ($condition['type'] === 'eav') {
                $this->applyEavFilters($query, $condition);
            }
        }

        return $query;
    }

    private function parseFilterString($filterString): array
    {
        $conditions = [];
        
        // Normalize spaces
        $filterString = preg_replace('/\s+/', ' ', trim($filterString));

        // Match conditions using regex
        preg_match_all('/(\w+)\s*(=|!=|>=|<=|>|<|HAS_ANY|IS_ANY)\s*\(?([^)]+)\)?/', $filterString, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $field = $match[1];
            $operator = $match[2];
            $value = $match[3];

            // Convert comma-separated values into an array
            $valuesArray = explode(',', $value);

            // Determine condition type (basic, relationship, or EAV)
            if (in_array($field, ['job_type', 'status', 'salary_min', 'salary_max'])) {
                $conditions[] = [
                    'type' => 'basic',
                    'field' => $field,
                    'operator' => $operator,
                    'value' => count($valuesArray) === 1 ? $valuesArray[0] : $valuesArray
                ];
            } elseif (in_array($field, ['languages', 'locations'])) {
                $conditions[] = [
                    'type' => 'relationship',
                    'field' => $field,
                    'operator' => $operator,
                    'values' => $valuesArray
                ];
            } elseif (strpos($field, 'attribute:') === 0) {
                $attributeName = str_replace('attribute:', '', $field);
                $conditions[] = [
                    'type' => 'eav',
                    'attribute' => $attributeName,
                    'operator' => $operator,
                    'value' => count($valuesArray) === 1 ? $valuesArray[0] : $valuesArray
                ];
            }
        }

        return $conditions;
    }

    private function applyRelationshipFilter(Builder $query, array $condition)
    {
        $relation = $condition['field']; // 'languages' or 'locations'
        $operator = $condition['operator'];
        $values = $condition['values'];

        if ($operator === 'HAS_ANY') {
            $query->whereHas($relation, function ($q) use ($values) {
                $q->whereIn('name', $values);
            });
        } elseif ($operator === 'IS_ANY') {
            $query->whereHas($relation, function ($q) use ($values) {
                $q->whereIn('title', $values);
            });
        }
    }

    private function applyEavFilters(Builder $query, array $condition)
    {
        $attributeName = $condition['attribute'];
        $operator = $condition['operator'];
        $value = $condition['value'];

        $query->whereHas('jobAttributes', function ($q) use ($attributeName, $operator, $value) {
            $q->whereHas('attribute', function ($q1) use ($attributeName) {
                $q1->where('name', $attributeName);
            });

            if (is_array($value)) {
                $q->whereIn('job_attribute_values.value', $value);
            } else {
                $q->where('job_attribute_values.value', $operator, $value);
            }
        });
    }
}

