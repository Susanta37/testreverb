<?php

namespace App\Http\Controllers;

use App\Events\Tutorial;
use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class PriceController extends Controller
{
    public function updatePrice(Request $request)
    {
        $request->validate([
            'price' => 'required|integer',
        ]);

        $price = $request->input('price');

        // Store price in database
        Price::create(['price' => $price]);

        // Broadcast price
        Log::info('Broadcasting price: ' . $price);
        event(new Tutorial('Price: ' . $price));

        return response()->json(['status' => 'Price broadcasted']);
    }

    public function sendRandom(Request $request)
    {
        $randomNumber = rand(1, 100);
        Log::info('Broadcasting random number: ' . $randomNumber);
        event(new Tutorial('Random number: ' . $randomNumber));
        return response()->json(['status' => 'Random number broadcasted']);
    }

    public function getPrices(Request $request)
    {
        $range = $request->query('range', 'today');

        $query = Price::query();

        switch ($range) {
            case '1m':
                $query->where('created_at', '>=', Carbon::now()->subMinute());
                break;
            case '1w':
                $query->where('created_at', '>=', Carbon::now()->subWeek());
                break;
            case '6m':
                $query->where('created_at', '>=', Carbon::now()->subMonths(6));
                break;
            case '1y':
                $query->where('created_at', '>=', Carbon::now()->subYear());
                break;
            case '5y':
                $query->where('created_at', '>=', Carbon::now()->subYears(5));
                break;
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'total':
            default:
                // No filter for total
                break;
        }

        $prices = $query->orderBy('created_at')->get(['price', 'created_at']);
        
        // Calculate min and max prices for chart scaling
        $priceValues = $prices->pluck('price');
        $minPrice = $priceValues->min() ?? 0;
        $maxPrice = $priceValues->max() ?? 100;
        
        // Format labels based on time range
        $formattedLabels = $prices->pluck('created_at')->map(function($date) use ($range) {
            switch ($range) {
                case '1m':
                    return $date->format('H:i:s');
                case '1w':
                case 'today':
                    return $date->format('H:i');
                case '6m':
                case '1y':
                case '5y':
                    return $date->format('M d');
                case 'total':
                default:
                    return $date->format('Y-m-d H:i');
            }
        });

        return response()->json([
            'prices' => $priceValues,
            'labels' => $formattedLabels,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'count' => $prices->count()
        ]);
    }
}