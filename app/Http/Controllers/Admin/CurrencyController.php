<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Country;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index()
    {
        $currencies = Currency::with('country')
            ->orderBy('name')
            ->paginate(20);
        
        return view('admin.currencies.index', compact('currencies'));
    }

    public function create()
    {
        $countries = Country::orderBy('name')->get();
        return view('admin.currencies.create', compact('countries'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'symbol2' => 'nullable|string|max:10',
            'flag' => 'required|string',
            'country_id' => 'required|exists:countries,id',
            'exchange_rate' => 'required|numeric|min:0',
            'is_base_currency' => 'boolean',
            'active' => 'boolean',
        ]);

        Currency::create([
            'name' => $request->name,
            'symbol' => $request->symbol,
            'symbol2' => $request->symbol2,
            'flag' => $request->flag,
            'country_id' => $request->country_id,
            'exchange_rate' => $request->exchange_rate,
            'is_base_currency' => $request->is_base_currency ?? false,
            'active' => $request->active ?? true,
        ]);

        return redirect()->route('currencies.index')
            ->with('success', 'Currency created successfully!');
    }

    public function edit(Currency $currency)
    {
        $countries = Country::orderBy('name')->get();
        return view('admin.currencies.edit', compact('currency', 'countries'));
    }

    public function update(Request $request, Currency $currency)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'symbol2' => 'nullable|string|max:10',
            'flag' => 'required|string',
            'country_id' => 'required|exists:countries,id',
            'exchange_rate' => 'required|numeric|min:0',
            'is_base_currency' => 'boolean',
            'active' => 'boolean',
        ]);

        $currency->update([
            'name' => $request->name,
            'symbol' => $request->symbol,
            'symbol2' => $request->symbol2,
            'flag' => $request->flag,
            'country_id' => $request->country_id,
            'exchange_rate' => $request->exchange_rate,
            'is_base_currency' => $request->is_base_currency ?? false,
            'active' => $request->active ?? $currency->active,
        ]);

        return redirect()->route('currencies.index')
            ->with('success', 'Currency updated successfully!');
    }

    public function destroy(Currency $currency)
    {
        $currency->delete();
        
        return redirect()->route('currencies.index')
            ->with('success', 'Currency deleted successfully!');
    }

    public function updateExchangeRates()
    {
        $currencies = Currency::where('active', true)->get();
        $updated = 0;
        $failed = [];

        foreach ($currencies as $currency) {
            if ($currency->is_base_currency) {
                $currency->update(['exchange_rate' => 1.0000]);
                $updated++;
                continue;
            }

            try {
                // Fetch USD → currency rate from fiat API
                $rate = getForexPrice($currency->symbol, 'USD');
                $currency->update(['exchange_rate' => $rate]);
                $updated++;
            } catch (\Exception $e) {
                $failed[] = $currency->symbol;
                \Log::error("Failed to update rate for {$currency->symbol}: " . $e->getMessage());
            }
        }

        $message = "Updated exchange rates for {$updated} currencies.";
        if (!empty($failed)) {
            $message .= ' Skipped: ' . implode(', ', $failed) . '.';
        }

        return redirect()->route('currencies.index')
            ->with('success', $message);
    }
}
