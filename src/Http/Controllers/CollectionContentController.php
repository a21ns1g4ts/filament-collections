<?php

namespace A21ns1g4ts\FilamentCollections\Http\Controllers;

use A21ns1g4ts\FilamentCollections\Models\CollectionApi;
use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use A21ns1g4ts\FilamentCollections\Models\CollectionData;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CollectionContentController extends Controller
{
    public function index(Request $request)
    {
        $collectionsRequest = $request->input('collections', []);
        $paginateConfigs = filter_var($request->input('paginate_configs', false), FILTER_VALIDATE_BOOLEAN);

        if (empty($collectionsRequest) && $paginateConfigs) {
            return $this->getPaginatedCollectionConfigs($request);
        }

        if (empty($collectionsRequest)) {
            return response()->json(['error' => 'No collections were provided.'], 400);
        }

        $response = [];

        foreach ($collectionsRequest as $key => $options) {
            $config = $this->getCollectionConfig($key);

            if (! $config) {
                $response[$key] = ['error' => 'Coleção não encontrada.'];

                continue;
            }

            $limit = (int) ($options['limit'] ?? 10);
            $paginated = filter_var($options['paginated'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $page = (int) ($options['page'] ?? 1);

            $query = CollectionData::query()
                ->where('collection_config_id', $config->id)
                ->latest();

            if ($paginated) {
                $allItems = $query->get();

                $paginatedItems = $this->paginateCollection($allItems, $limit, $page);

                $response[$key] = [
                    'meta' => [
                        'key' => $key,
                        'title' => $config->description ?? $key,
                        'schema' => $config->schema,
                        'limit' => $limit,
                        'paginated' => true,
                        'count' => $paginatedItems->count(),
                        'total' => $paginatedItems->total(),
                    ],
                    'items' => $this->loadRelationships(
                        collect($paginatedItems->items())->map(fn ($item) => $this->parsePayload($item, $config)),
                        $config,
                        array_filter(explode(',', $request->input('relations', '')))
                    )->values(),
                    'pagination' => [
                        'current_page' => $paginatedItems->currentPage(),
                        'last_page' => $paginatedItems->lastPage(),
                        'per_page' => $paginatedItems->perPage(),
                        'total' => $paginatedItems->total(),
                    ],
                ];
            } else {
                $items = $query->take($limit)->get();

                $response[$key] = [
                    'meta' => [
                        'key' => $key,
                        'title' => $config->description ?? $key,
                        'schema' => $config->schema,
                        'limit' => $limit,
                        'paginated' => false,
                        'count' => $items->count(),
                        'total' => $items->count(),
                    ],
                    'items' => $this->loadRelationships(
                        collect($items)->map(fn ($item) => $this->parsePayload($item, $config)),
                        $config,
                        array_filter(explode(',', $request->input('relations', '')))
                    )->values(),
                    'pagination' => null,
                ];
            }
        }

        return response()->json($response);
    }

    public function store(Request $request)
    {
        $key = $request->input('key');
        $payload = $request->input('payload');

        if (! $key || ! is_array($payload)) {
            return response()->json(['error' => 'A chave da coleção e o payload são obrigatórios.'], 422);
        }

        $config = $this->getCollectionConfig($key);

        if (! $config) {
            return response()->json(['error' => 'Coleção não encontrada.'], 404);
        }

        $token = $request->user()?->currentAccessToken();
        $api = CollectionApi::where('personal_access_token_id', $token?->id)
            ->where('collection_config_id', $config->id)
            ->first();

        if (! $api || ! $api->active) {
            return response()->json(['error' => 'Acesso negado.'], 403);
        }

        $schema = $config->schema ?? [];
        $rules = [];
        $attributeNames = [];

        foreach ($schema as $field) {
            $name = $field['name'];
            $type = $field['type'] ?? 'text';
            $required = $field['required'] ?? false;
            $unique = $field['unique'] ?? false;
            $label = $field['label'] ?? $field['name'];

            $ruleSet = [];

            if ($required) {
                $ruleSet[] = 'required';
            } else {
                $ruleSet[] = 'nullable';
            }

            switch ($type) {
                case 'number':
                    $ruleSet[] = 'numeric';

                    break;
                case 'boolean':
                    $ruleSet[] = 'boolean';

                    break;
                case 'date':
                    $ruleSet[] = 'date';

                    break;
                case 'datetime':
                    $ruleSet[] = 'date_format:Y-m-d H:i:s';

                    break;
                case 'json':
                    $ruleSet[] = 'array';

                    break;
                default:
                    $ruleSet[] = 'string';

                    break;
            }

            if ($unique) {
                $ruleSet[] = Rule::unique('collection_data', "payload->{$name}")
                    ->where(
                        fn ($query) => $query
                            ->where('collection_config_id', $config->id)
                            ->where("payload->{$name}", $payload[$name] ?? null)
                    );
            }

            $rules["payload.{$name}"] = $ruleSet;
            $attributeNames["payload.{$name}"] = $label;
        }

        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
            'payload' => 'required|array',
        ] + $rules, [], $attributeNames);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $payload = $validator->validated()['payload'];

        $payload['uuid'] = Str::uuid()->toString();

        $record = CollectionData::create([
            'collection_config_id' => $config->id,
            'payload' => $payload,
        ]);

        return response()->json([
            'message' => 'Registro criado com sucesso.',
            'data' => $record,
        ], 201);
    }

    private function parsePayload($item, $config): mixed
    {
        $schema = $config->schema;

        if (! is_array($schema)) {
            return $item;
        }

        $jsonFields = collect($schema)
            ->where('type', '=', 'json')
            ->pluck('name')
            ->toArray();

        $item = $item->toArray();

        foreach ($jsonFields as $field) {
            $raw = $item['payload'][$field] ?? null;

            if (is_array($raw)) {
                continue;
            }

            if (is_string($raw)) {
                $cleaned = trim($raw);

                if (str_starts_with($cleaned, "['") || str_starts_with($cleaned, "['")) {
                    $cleaned = str_replace("'", '"', $cleaned);
                }

                $cleaned = preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', '', $cleaned);

                $decoded = json_decode($cleaned, true);
            } else {
                $decoded = null;
            }

            $item['payload'][$field] = is_array($decoded) ? $decoded : [];
        }

        return $item;
    }

    private function loadRelationships(Collection $items, CollectionConfig $config, array $requestedRelations): Collection
    {
        if (empty($requestedRelations) || empty($config->relationships)) {
            return $items;
        }

        $definedRelationships = collect($config->relationships);

        foreach ($requestedRelations as $requestedRelationName) {
            $relation = $definedRelationships->firstWhere('name', $requestedRelationName);

            if (! $relation) {
                continue; // Requested relation not defined in config
            }

            $targetCollectionKey = $relation['target_collection_key'];
            $localField = $relation['local_field'];
            $foreignField = $relation['foreign_field'] ?? 'uuid';
            $type = $relation['type'];

            $targetConfig = $this->getCollectionConfig($targetCollectionKey);

            if (! $targetConfig) {
                continue; // Target collection not found
            }

            $targetCollectionData = CollectionData::query()
                ->where('collection_config_id', $targetConfig->id)
                ->get();

            foreach ($items as $item) {
                $relatedData = [];

                if ($type === 'one_to_many') {
                    // Current item's localField contains UUIDs of related items in target collection
                    $localFieldValues = (array) ($item->payload[$localField] ?? []);
                    $relatedData = $targetCollectionData->filter(function ($targetItem) use ($localFieldValues, $foreignField) {
                        return in_array($targetItem->payload[$foreignField] ?? null, $localFieldValues);
                    })->map(fn ($targetItem) => $this->parsePayload($targetItem, $targetConfig))->values();
                } elseif ($type === 'many_to_one') {
                    // Target collection's foreignField contains UUID of current item
                    $currentUuid = $item->payload['uuid'] ?? null;
                    $relatedData = $targetCollectionData->first(function ($targetItem) use ($currentUuid, $foreignField) {
                        return ($targetItem->payload[$foreignField] ?? null) === $currentUuid;
                    });
                    if ($relatedData) {
                        $relatedData = $this->parsePayload($relatedData, $targetConfig);
                    }
                }

                $item->payload[$requestedRelationName] = $relatedData;
            }
        }

        return $items;
    }

    private function getCollectionConfig(string $key): ?CollectionConfig
    {
        return CollectionConfig::query()->where('key', $key)->first();
    }

    private function paginateCollection(Collection $collection, int $perPage, int $page): LengthAwarePaginator
    {
        $offset = ($page - 1) * $perPage;
        $items = $collection->slice($offset, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $collection->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    private function getPaginatedCollectionConfigs(Request $request)
    {
        $limit = (int) ($request->input('limit', 10));
        $page = (int) ($request->input('page', 1));

        $configs = CollectionConfig::latest()->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'configs' => [
                'meta' => [
                    'key' => 'configs',
                    'title' => 'Configurações de Coleções',
                    'limit' => $limit,
                    'paginated' => true,
                    'count' => $configs->count(),
                    'total' => $configs->total(),
                ],
                'items' => $configs->items(),
                'pagination' => [
                    'current_page' => $configs->currentPage(),
                    'last_page' => $configs->lastPage(),
                    'per_page' => $configs->perPage(),
                    'total' => $configs->total(),
                ],
            ],
        ]);
    }
}
