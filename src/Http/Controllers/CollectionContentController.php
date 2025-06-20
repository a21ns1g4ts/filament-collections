<?php

namespace A21ns1g4ts\FilamentCollections\Http\Controllers;

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use A21ns1g4ts\FilamentCollections\Models\CollectionData;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CollectionContentController extends Controller
{
    public function index(Request $request)
    {
        $collectionsRequest = $request->input('collections', []);

        if (empty($collectionsRequest)) {
            return response()->json(['error' => 'Nenhuma coleção informada.'], 400);
        }

        $response = [];

        foreach ($collectionsRequest as $key => $options) {
            $limit = isset($options['limit']) ? (int) $options['limit'] : null;
            $paginated = filter_var($options['paginated'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $config = CollectionConfig::query()->where('key', $key)->first();

            if (! $config) {
                $response[$key] = [
                    'error' => 'Coleção não encontrada.',
                ];

                continue;
            }

            $query = CollectionData::query()
                ->where('collection_config_id', $config->id)
                ->latest();

            // Quando `limit` não for informado, retornar todos os itens sem paginação
            if (is_null($limit)) {
                $items = $query->get();
                $paginatedData = null;
            } else {
                $items = $paginated
                    ? $query->paginate($limit)
                    : $query->take($limit)->get();

                $paginatedData = $paginated ? [
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                    'per_page' => $items->perPage(),
                    'total' => $items->total(),
                ] : null;
            }

            $response[$key] = [
                'meta' => [
                    'key' => $key,
                    'title' => $config->description ?? $key,
                    'paginated' => $paginated,
                    'limit' => $limit,
                    'count' => $items->count(),
                    'total' => $paginated ? $items->total() : $items->count(),
                ],
                'items' => $items->map(fn ($item) => $item->payload)->values(), // @phpstan-ignore-line
                'pagination' => $paginatedData,
            ];
        }

        return response()->json($response);
    }
}
