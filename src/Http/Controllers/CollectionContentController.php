<?php

namespace A21ns1g4ts\FilamentCollections\Http\Controllers;

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use A21ns1g4ts\FilamentCollections\Models\CollectionData;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

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
                // Obtem todos os resultados e pagina manualmente
                $allItems = $query->get();

                $paginatedItems = $this->paginateCollection($allItems, $limit, $page);

                $response[$key] = [
                    'meta' => [
                        'key' => $key,
                        'title' => $config->description ?? $key,
                        'limit' => $limit,
                        'paginated' => true,
                        'count' => $paginatedItems->count(),
                        'total' => $paginatedItems->total(),
                    ],
                    'items' => collect($paginatedItems->items())->map(fn ($item) => $item->payload)->values(),
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
                        'limit' => $limit,
                        'paginated' => false,
                        'count' => $items->count(),
                        'total' => $items->count(),
                    ],
                    'items' => $items->map(fn ($item) => $item->payload)->values(),
                    'pagination' => null,
                ];
            }
        }

        return response()->json($response);
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
}
