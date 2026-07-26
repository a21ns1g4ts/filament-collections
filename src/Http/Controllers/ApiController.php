<?php

namespace A21ns1g4ts\FilamentCollections\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ApiController extends Controller
{
    protected function applyFilters(Request $request, $query)
    {
        if ($request->has('filters')) {
            $filters = $request->input('filters');
            foreach ($filters as $field => $value) {
                $query->where("payload->{$field}", $value);
            }
        }

        return $query;
    }

    protected function applySearch(Request $request, $query)
    {
        if ($request->has('search')) {
            $search = $request->input('search');
            foreach ($search as $field => $value) {
                $query->where("payload->{$field}", 'like', "%{$value}%");
            }
        }

        return $query;
    }
}
