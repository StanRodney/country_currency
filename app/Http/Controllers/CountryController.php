<?php

namespace App\Http\Controllers;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Country;
use Carbon\Carbon;

class CountryController extends Controller
{
    //  POST /countries/refresh
    public function refresh()
    {
        try {
            // Fetch countries
            $countriesResponse = Http::timeout(15)->get('https://restcountries.com/v2/all?fields=name,capital,region,population,flag,currencies');
            if (!$countriesResponse->successful()) {
                return response()->json([
                    'error' => 'External data source unavailable',
                    'details' => 'Could not fetch data from restcountries API'
                ], 503);
            }

            $exchangeResponse = Http::timeout(15)->get('https://open.er-api.com/v6/latest/USD');
            if (!$exchangeResponse->successful()) {
                return response()->json([
                    'error' => 'External data source unavailable',
                    'details' => 'Could not fetch data from exchange rate API'
                ], 503);
            }

            $countriesData = $countriesResponse->json();
            $exchangeRates = $exchangeResponse->json()['rates'] ?? [];
            $now = Carbon::now();

            DB::beginTransaction();

            foreach ($countriesData as $data) {
                $currencyCode = $data['currencies'][0]['code'] ?? null;
                $exchangeRate = $currencyCode && isset($exchangeRates[$currencyCode]) ? $exchangeRates[$currencyCode] : null;
                $randomMultiplier = rand(1000, 2000);

                $estimatedGDP = ($exchangeRate && $exchangeRate != 0)
                    ? ($data['population'] * $randomMultiplier) / $exchangeRate
                    : 0;

                Country::updateOrCreate(
                    ['name' => $data['name']],
                    [
                        'capital' => $data['capital'] ?? null,
                        'region' => $data['region'] ?? null,
                        'population' => $data['population'],
                        'currency_code' => $currencyCode,
                        'exchange_rate' => $exchangeRate,
                        'estimated_gdp' => $estimatedGDP,
                        'flag_url' => $data['flag'] ?? null,
                        'last_refreshed_at' => $now
                    ]
                );
            }

            DB::commit();

            // Generate summary image
            $this->generateSummaryImage();

            return response()->json(['message' => 'Countries refreshed successfully', 'last_refreshed_at' => $now], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Internal server error',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    //  GET /countries

    public function index(Request $request)
    {
        $query = \App\Models\Country::query();

        // Filter: region (case-insensitive)
        if ($request->filled('region')) {
            $region = $request->get('region');
            $query->whereRaw('LOWER(region) = ?', [strtolower($region)]);
        }

        // Filter: currency (case-insensitive) - expects a currency code like "NGN"
        if ($request->filled('currency')) {
            $currency = strtoupper($request->get('currency'));
            $query->whereRaw('UPPER(currency_code) = ?', [$currency]);
        }

        // Optional: exact name match (useful)
        if ($request->filled('name')) {
            $name = $request->get('name');
            $query->whereRaw('LOWER(name) = ?', [strtolower($name)]);
        }

        // Sorting: gdp_desc or gdp_asc
        if ($request->filled('sort')) {
            $sort = $request->get('sort');
            if ($sort === 'gdp_desc') {
                $query->orderBy('estimated_gdp', 'desc');
            } elseif ($sort === 'gdp_asc') {
                $query->orderBy('estimated_gdp', 'asc');
            }
        } else {
            // sensible default ordering (optional)
            $query->orderBy('name', 'asc');
        }

        $results = $query->get();


        $data = $results->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'capital' => $item->capital,
                'region' => $item->region,
                'population' => (int)$item->population,
                'currency_code' => $item->currency_code,
                'exchange_rate' => $item->exchange_rate !== null ? (float)$item->exchange_rate : null,
                'estimated_gdp' => $item->estimated_gdp !== null ? (float)$item->estimated_gdp : null,
                'flag_url' => $item->flag_url,
                'last_refreshed_at' => $item->last_refreshed_at ? $item->last_refreshed_at->toIso8601String() : null,
            ];
        });

        return response()->json($data, 200);
    }


    //  GET /countries/{name}
    public function show($name)
    {
        $country = Country::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();

        if (!$country) {
            return response()->json([
                'error' => 'Country not found'
            ], 404);
        }

        return response()->json($country);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'capital' => 'nullable|string',
            'region' => 'nullable|string',
            'population' => 'nullable|integer',
            'currency_code' => 'nullable|string',
            'exchange_rate' => 'nullable|numeric',
            'estimated_gdp' => 'nullable|numeric',
            'flag_url' => 'nullable|string',
        ]);

        //  Update or create new record if it doesn't exist
        $country = Country::updateOrCreate(
            ['name' => $validated['name']],
            [
                'capital' => $validated['capital'] ?? '',
                'region' => $validated['region'] ?? '',
                'population' => $validated['population'] ?? 0,
                'currency_code' => $validated['currency_code'] ?? '',
                'exchange_rate' => $validated['exchange_rate'] ?? null,
                'estimated_gdp' => $validated['estimated_gdp'] ?? 0,
                'flag_url' => $validated['flag_url'] ?? '',
            ]
        );

        return response()->json([
            'message' => 'Country created or updated successfully.',
            'data' => $country
        ], 200);
    }
    //  DELETE /countries/{name}
    public function destroy($name)
    {
        $country = Country::where('name', $name)->first();

        if (!$country) {
            return response()->json(['error' => 'Country not found'], 404);
        }

        $country->delete();
        return response()->json(null, 204);
    }

    //  GET /status
    public function status()
    {
        $total = Country::count();
        $lastRefreshed = Country::max('last_refreshed_at');

        return response()->json([
            'total_countries' => $total,
            'last_refreshed_at' => $lastRefreshed
        ]);
    }

    //  GET /countries/image
    public function summaryImage()
    {
        $path = storage_path('app/public/cache/summary.png');

        if (!file_exists($path)) {
            return response()->json(['error' => 'Summary image not found'], 404);
        }

        return response()->file($path);
    }

//  Generate Summary Image
    private function generateSummaryImage()
    {
        $totalCountries = \App\Models\Country::count();
        $topCountries = \App\Models\Country::orderByDesc('estimated_gdp')->take(5)->get(['name', 'estimated_gdp']);
        $timestamp = now()->toDateTimeString();

        $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
        $image = $manager->create(800, 600)->fill('#f9fafb');

        $image->text('Country Summary Report', 400, 50, function ($font) {
            $font->size(36);
            $font->color('#111827');
            $font->align('center');
            $font->valign('top');
        });

        $image->text("Total Countries: " . $totalCountries, 50, 120, function ($font) {
            $font->size(24);
            $font->color('#374151');
        });

        $image->text("Last Refreshed: " . now()->toDateTimeString(), 50, 160, function ($font) {
            $font->size(20);
            $font->color('#6b7280');
        });

        $y = 230;
        $image->text("Top 5 Countries by GDP:", 50, $y, function ($font) {
            $font->size(24);
            $font->color('#111827');
        });
        $y += 40;

        foreach ($topCountries as $index => $country) {
            $rank = $index + 1;
            $text = "{$rank}. {$country->name} — GDP: " . number_format($country->estimated_gdp, 2);
            $image->text($text, 70, $y, function ($font) {
                $font->size(20);
                $font->color('#1f2937');
            });
            $y += 35;
        }

        //  Save image to public storage so it’s web-accessible
        $path = storage_path('app/public/cache/summary.png');
        $image->save($path);
    }

}

