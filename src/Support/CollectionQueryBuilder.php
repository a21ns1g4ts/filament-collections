<?php

namespace A21ns1g4ts\FilamentCollections\Support;

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use A21ns1g4ts\FilamentCollections\Models\CollectionData;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class CollectionQueryBuilder
{
    protected Builder $query;
    protected CollectionConfig $config;
    protected Request $request;

    public function __construct(CollectionConfig $config, Request $request)
    {
        $this->config = $config;
        $this->request = $request;
        $this->query = CollectionData::where('collection_config_id', $config->id);
    }

    public function build(): Builder
    {
        $this->applyFilters();
        $this->applySearch();

        return $this->query;
    }

    protected function applyFilters()
    {
        if ($this->request->has('filters')) {
            $filters = $this->request->input('filters');
            foreach ($filters as $field => $value) {
                $fieldSchema = $this->getFieldSchema($field);
                if ($fieldSchema) {
                    $this->applyFilter($field, $value, $fieldSchema);
                }
            }
        }
    }

    protected function applyFilter($field, $value, $schema)
    {
        switch ($schema['type']) {
            case 'date':
                $this->query->whereDate("payload->{$field}", $value);
                break;
            case 'datetime':
                $this->query->where("payload->{$field}", $value);
                break;
            case 'number':
                $this->query->where("payload->{$field}", $value);
                break;
            case 'json':
                $this->query->whereJsonContains("payload->{$field}", $value);
                break;
            default:
                $this->query->where("payload->{$field}", $value);
                break;
        }
    }

    protected function applySearch()
    {
        if ($this->request->has('search')) {
            $search = $this->request->input('search');
            foreach ($search as $field => $value) {
                $this->query->where("payload->{$field}", 'like', "%{$value}%");
            }
        }
    }

    protected function getFieldSchema($fieldName): ?array
    {
        foreach ($this->config->schema as $field) {
            if ($field['name'] === $fieldName) {
                return $field;
            }
        }
        return null;
    }
}