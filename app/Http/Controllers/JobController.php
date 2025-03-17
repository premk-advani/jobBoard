<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\JobFilterService;


class JobController extends Controller
{
    protected $jobFilterService;

    public function __construct(JobFilterService $jobFilterService)
    {
        $this->jobFilterService = $jobFilterService;
    }


    public function getDataByService(Request $request)
    {

        $query = Job::query();
        $filterString = $request->query('filter');

        // Apply filters using the service class
        $query = $this->jobFilterService->applyFilters($query, $filterString);

        return response()->json($query->paginate(10));

    }

    public function getDataSimple(Request $request)
    {

        $query = Job::query();

        // Retrieve the filter parameter
        $filterString = $request->query('filter');

        if ($filterString) {
            $this->applyCustomFilters($query, $filterString);
        }

        $jobs = $query->paginate(10);
        
        return response()->json($jobs);
    }

    private function applyCustomFilters($query, $filterString)
    {
        // Convert the filter string to a structured format
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
    }

    private function parseFilterString($filterString)
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

    private function applyRelationshipFilter($query, $condition)
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

    private function applyFilters($query, $filter)
    {
        // Parsing and applying complex filters based on field type
        $filters = json_decode($filter, true); // Assuming filters come as a JSON string

        foreach ($filters as $field => $condition) {
            // Example: field = 'title', condition = { "operator": "LIKE", "value": "%developer%" }
            switch ($field) {
                case 'title':
                case 'description':
                case 'company_name':
                    $this->applyStringFilters($query, $field, $condition);
                    break;
                case 'salary_min':
                case 'salary_max':
                    $this->applyNumericFilters($query, $field, $condition);
                    break;
                case 'is_remote':
                    $this->applyBooleanFilters($query, $field, $condition);
                    break;
                case 'job_type':
                case 'status':
                    $this->applyEnumFilters($query, $field, $condition);
                    break;
                case 'published_at':
                case 'created_at':
                    $this->applyDateFilters($query, $field, $condition);
                    break;
                default:
                    // Handle relationships or EAV filters (dynamic attributes)
                    break;
            }
        }
    }

    private function applyStringFilters($query, $field, $condition)
    {
        if ($condition['operator'] == 'LIKE') {
            $query->where($field, 'LIKE', $condition['value']);
        } elseif ($condition['operator'] == '=') {
            $query->where($field, '=', $condition['value']);
        } elseif ($condition['operator'] == '!=') {
            $query->where($field, '!=', $condition['value']);
        }
    }

    private function applyNumericFilters($query, $field, $condition)
    {
        if ($condition['operator'] == '=') {
            $query->where($field, '=', $condition['value']);
        } elseif ($condition['operator'] == '!=') {
            $query->where($field, '!=', $condition['value']);
        } elseif ($condition['operator'] == '>') {
            $query->where($field, '>', $condition['value']);
        } elseif ($condition['operator'] == '<') {
            $query->where($field, '<', $condition['value']);
        } elseif ($condition['operator'] == '>=') {
            $query->where($field, '>=', $condition['value']);
        } elseif ($condition['operator'] == '<=') {
            $query->where($field, '<=', $condition['value']);
        }
    }

    private function applyBooleanFilters($query, $field, $condition)
    {
        if ($condition['operator'] == '=') {
            $query->where($field, '=', $condition['value']);
        } elseif ($condition['operator'] == '!=') {
            $query->where($field, '!=', $condition['value']);
        }
    }

    private function applyEnumFilters($query, $field, $condition)
    {
        if ($condition['operator'] == '=') {
            $query->where($field, '=', $condition['value']);
        } elseif ($condition['operator'] == '!=') {
            $query->where($field, '!=', $condition['value']);
        } elseif ($condition['operator'] == 'IN') {
            $query->whereIn($field, $condition['value']);
        }
    }

    private function applyDateFilters($query, $field, $condition)
    {
        if ($condition['operator'] == '=') {
            $query->whereDate($field, '=', $condition['value']);
        } elseif ($condition['operator'] == '!=') {
            $query->whereDate($field, '!=', $condition['value']);
        } elseif ($condition['operator'] == '>') {
            $query->whereDate($field, '>', $condition['value']);
        } elseif ($condition['operator'] == '<') {
            $query->whereDate($field, '<', $condition['value']);
        } elseif ($condition['operator'] == '>=') {
            $query->whereDate($field, '>=', $condition['value']);
        } elseif ($condition['operator'] == '<=') {
            $query->whereDate($field, '<=', $condition['value']);
        }
    }

    private function applyEavFilters($query, $condition)
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

