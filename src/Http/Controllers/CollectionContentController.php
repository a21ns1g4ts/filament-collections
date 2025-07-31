<?php

namespace A21ns1g4ts\FilamentCollections\Http\Controllers;

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use A21ns1g4ts\FilamentCollections\Models\CollectionData;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CollectionContentController extends Controller
{
    public function index(Request $request, string $collectionKey)
    {
        $config = CollectionConfig::where('key', $collectionKey)->first();

        if (! $config) {
            return response()->json(['error' => 'Collection not found.'], 404);
        }

        $query = CollectionData::where('collection_config_id', $config->id);

        if ($request->has('filters')) {
            foreach ($request->input('filters') as $field => $value) {
                $query->where("payload->{$field}", $value);
            }
        }

        if ($request->has('search')) {
            foreach ($request->input('search') as $field => $value) {
                $query->where("payload->{$field}", 'like', "%{$value}%");
            }
        }

        return response()->json($query->paginate());
    }

    public function store(Request $request, string $collectionKey)
    {
        $config = CollectionConfig::where('key', $collectionKey)->first();

        if (! $config) {
            return response()->json(['error' => 'Collection not found.'], 404);
        }

        $validator = Validator::make($request->all(), $this->getValidationRules($config));

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $payload = $validator->validated()['payload'];
        $payload['uuid'] = Str::uuid()->toString();

        $record = CollectionData::create([
            'collection_config_id' => $config->id,
            'payload' => $payload,
        ]);

        return response()->json($record, 201);
    }

    public function show(string $collectionKey, string $id)
    {
        $record = $this->findRecord($collectionKey, $id);

        if (! $record) {
            return response()->json(['error' => 'Record not found.'], 404);
        }

        return response()->json($record);
    }

    public function update(Request $request, string $collectionKey, string $id)
    {
        $record = $this->findRecord($collectionKey, $id);

        if (! $record) {
            return response()->json(['error' => 'Record not found.'], 404);
        }

        $config = $record->config;
        $validator = Validator::make($request->all(), $this->getValidationRules($config, $record->id));

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $payload = $validator->validated()['payload'];
        $currentPayload = $record->payload;

        foreach ($payload as $key => $value) {
            $currentPayload[$key] = $value;
        }

        $currentPayload['uuid'] = $record->payload['uuid'];

        $record->payload = $currentPayload;
        $record->save();

        return response()->json($record);
    }

    public function destroy(string $collectionKey, string $id)
    {
        $record = $this->findRecord($collectionKey, $id);

        if (! $record) {
            return response()->json(['error' => 'Record not found.'], 404);
        }

        $record->delete();

        return response()->json(null, 204);
    }

    protected function findRecord(string $collectionKey, string $id): ?CollectionData
    {
        $config = CollectionConfig::where('key', $collectionKey)->first();

        if (! $config) {
            return null;
        }

        return CollectionData::where('collection_config_id', $config->id)
            ->where("payload->uuid", $id)
            ->first();
    }

    protected function getValidationRules(CollectionConfig $config, ?int $recordId = null): array
    {
        $rules = ['payload' => ['required', 'array']];

        if (is_array($config->schema) && isset($config->schema['properties'])) {
            foreach ($config->schema['properties'] as $fieldName => $field) {
                if ($fieldName === 'uuid') {
                    continue;
                }

                $fullFieldName = "payload.{$fieldName}";
                $fieldRules = [];

                if (isset($field['required']) && $field['required']) {
                    $fieldRules[] = ($recordId === null) ? 'required' : 'sometimes';
                } else {
                    $fieldRules[] = 'nullable';
                }

                if (isset($field['unique']) && $field['unique']) {
                    $uniqueRule = Rule::unique('collection_data', "payload->{$fieldName}");
                    if ($recordId) {
                        $uniqueRule->ignore($recordId);
                    }
                    $fieldRules[] = $uniqueRule;
                }

                switch ($field['type']) {
                    case 'number':
                        $fieldRules[] = 'numeric';
                        break;
                    case 'boolean':
                        $fieldRules[] = 'boolean';
                        break;
                    case 'date':
                        $fieldRules[] = 'date';
                        break;
                    case 'datetime':
                        $fieldRules[] = 'date_format:Y-m-d H:i:s';
                        break;
                    case 'json':
                        $fieldRules[] = 'array';
                        break;
                    default:
                        $fieldRules[] = 'string';
                        break;
                }

                $rules[$fullFieldName] = $fieldRules;
            }
        }

        return $rules;
    }
}
