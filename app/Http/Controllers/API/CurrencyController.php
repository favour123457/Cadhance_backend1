<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Currency;

class CurrencyController extends Controller
{
    public function index()
    {
        $currencies = Currency::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'symbol', 'flag', 'country_id']);
        
        return response()->json($currencies);
    }
}
