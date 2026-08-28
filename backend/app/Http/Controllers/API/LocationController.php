<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LocationController extends Controller
{
    public function search(Request $request)
    {
        $query = trim($request->query('q', ''));

        if (strlen($query) < 1) {
            return response()->json(['data' => []]);
        }

        $locationsArray = Cache::rememberForever('psgc_city_province', function () {
            $http = Http::timeout(10);

            if (app()->environment('local')) {
                $http = $http->withOptions(['verify' => false]);
            }

            $response = $http->get('https://psgc.cloud/api/v2/cities-municipalities');
            $json = $response->json();
            $citiesMunis = $json['data'] ?? $json;

            if (!is_array($citiesMunis)) {
                return [];
            }

            $result = [];

            foreach ($citiesMunis as $item) {
                if (!is_array($item) || empty($item['name'])) {
                    continue;
                }

                $province = is_array($item['province'] ?? null)
                    ? ($item['province']['name'] ?? '')
                    : ($item['province'] ?? '');

                $result[] = [
                    'city' => $item['name'],
                    'province' => $province,
                ];
            }

            return $result;
        });

        if (!is_array($locationsArray)) {
            $locationsArray = [];
        }

        $term = strtolower($query);

        $matches = [];

        foreach ($locationsArray as $loc) {
            if (!is_array($loc)) {
                continue;
            }

            $city = $loc['city'] ?? '';
            $province = $loc['province'] ?? '';

            if (str_contains(strtolower($city), $term) || str_contains(strtolower($province), $term)) {
                $matches[] = [
                    'label' => $province ? "{$city}, {$province}" : $city,
                    'city' => $city,
                    'province' => $province,
                    'exact_start' => str_starts_with(strtolower($city), $term) ? 0 : 1,
                ];
            }
        }

        usort($matches, fn ($a, $b) => $a['exact_start'] <=> $b['exact_start']);

        $results = array_slice($matches, 0, 8);

        foreach ($results as &$r) {
            unset($r['exact_start']);
        }

        return response()->json(['data' => array_values($results)]);
    }

    public function refreshCache()
    {
        Cache::forget('psgc_city_province');
        return response()->json(['message' => 'Location cache cleared']);
    }
}