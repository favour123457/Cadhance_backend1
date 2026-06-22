<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AllGeneralSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $settings = GeneralSetting::latest()->get();

        return view('admin.all-general-settings.index')->with([
            'settings' => $settings,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.all-general-settings.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'model' => 'required',
            'dbname' => 'required',
            'is_description' => 'required',
            'active' => 'required',
        ]);

        $name = $request->name;
        $slug = Str::slug($name);
        $model = $request->model;
        $dbname = $request->dbname;
        $is_description = $request->is_description;
        $active = $request->active;

        $dfetaure  = GeneralSetting::create([
                    'name' => $name,
                    'slug' => $slug,
                    'model' => $model,
                    'dbname' => $dbname,
                    'is_description' => $is_description,
                    'active' => $active,
                ]);

        flash()->success('General Setting created successfully.');
        return redirect()->route('all-general-settings.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $setting = GeneralSetting::where('id', $id)->firstOrFail();

        return view('admin.all-general-settings.edit')->with([
                'setting' => $setting,
            ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required',
            'model' => 'required',
            'dbname' => 'required',
            'is_description' => 'required',
            'active' => 'required',
        ]);

        $setting = GeneralSetting::where('id', $id)->firstOrFail();

        $name = $request->name;
        $slug = Str::slug($name);
        $model = $request->model;
        $dbname = $request->dbname;
        $is_description = $request->is_description;
        $active = $request->active;

        $setting->update([
                    'name' => $name,
                    'slug' => $slug,
                    'model' => $model,
                    'dbname' => $dbname,
                    'is_description' => $is_description,
                    'active' => $active,
                ]);

        flash()->success('General Setting updated successfully.');
        return redirect()->route('all-general-settings.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $setting = GeneralSetting::find($id);

        $setting->delete();

        flash()->success('General Setting deleted successfully.');
        return redirect()->route('all-general-settings.index');
    }
}
