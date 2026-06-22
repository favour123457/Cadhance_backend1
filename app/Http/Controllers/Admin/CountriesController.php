<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountriesController extends Controller
{
    public function index()
    {
        $countries = Country::latest()->get();
        return view('admin.countries.index', compact('countries'));
    }

    public function create()
    {
        return view('admin.countries.add');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255|unique:countries,name',
            'code'      => 'nullable|integer',
            'flag'      => 'nullable|string',
            'continent' => 'nullable|string|max:255',
            'currency'  => 'nullable|string|max:255',
            'sanctioned'=> 'boolean',
        ]);

        Country::create($request->only(['name', 'code', 'flag', 'continent', 'currency', 'sanctioned']));

        flash()->success('Country created successfully.');
        return redirect()->route('countries.index');
    }

    public function edit(Country $country)
    {
        return view('admin.countries.edit', compact('country'));
    }

    public function update(Request $request, Country $country)
    {
        $request->validate([
            'name'      => 'required|string|max:255|unique:countries,name,' . $country->id,
            'code'      => 'nullable|integer',
            'flag'      => 'nullable|string',
            'continent' => 'nullable|string|max:255',
            'currency'  => 'nullable|string|max:255',
            'sanctioned'=> 'boolean',
        ]);

        $country->update($request->only(['name', 'code', 'flag', 'continent', 'currency', 'sanctioned']));

        flash()->success('Country updated successfully.');
        return redirect()->route('countries.index');
    }

    public function destroy(Country $country)
    {
        $country->delete();
        flash()->success('Country deleted successfully.');
        return redirect()->route('countries.index');
    }
}
