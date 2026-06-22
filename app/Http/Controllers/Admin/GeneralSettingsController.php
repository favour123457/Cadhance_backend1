<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;

class GeneralSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($slug)
    {
        $gsetting = GeneralSetting::where('slug', $slug)->firstOrFail();
        $model = $gsetting->model;

        $settings = $model::all();

        return view('admin.general-settings.index')->with([
            'settings' => $settings,
            'gsetting' => $gsetting,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($slug)
    {
        $gsetting = GeneralSetting::where('slug', $slug)->firstOrFail();

        return view('admin.general-settings.add')->with([
            'gsetting' => $gsetting,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($slug, Request $request)
    {
        $gsetting = GeneralSetting::where('slug', $slug)->firstOrFail();
        $model = $gsetting->model;


        if ($model == 'App\Models\Permission') {
            $model::create([
                'name' => 'browse_'.$request->name,
                'description' => $request->description,
            ]);
            $model::create([
                'name' => 'add_'.$request->name,
                'description' => $request->description,
            ]);
            $model::create([
                'name' => 'edit_'.$request->name,
                'description' => $request->description,
            ]);
            $model::create([
                'name' => 'delete_'.$request->name,
                'description' => $request->description,
            ]);
        } else {
            if ($gsetting->is_description == 1) {
                $model::create([
                    'name' => $request->name,
                    'description' => $request->description,
                ]);
            } else {
                $model::create([
                    'name' => $request->name,
                ]);
            }
        }


        flash()->success($gsetting->name . ' created successfully.');
        return redirect()->route('settings.index', $gsetting->slug);
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
    public function edit($slug, $id)
    {
        $gsetting = GeneralSetting::where('slug', $slug)->firstOrFail();
        $model = $gsetting->model;

        $setting = $model::where('id', $id)->firstOrFail();

        return view('admin.general-settings.edit')->with([
            'setting' => $setting,
            'gsetting' => $gsetting,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($slug, $id, Request $request)
    {
        $gsetting = GeneralSetting::where('slug', $slug)->firstOrFail();
        $model = $gsetting->model;

        $setting = $model::where('id', $id)->firstOrFail();

        if ($gsetting->is_description == 1) {
            $setting->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);
        } else {
            $setting->update([
                'name' => $request->name,
            ]);
        }

        flash()->success($gsetting->name . ' updated successfully.');
        return redirect()->route('settings.index', $gsetting->slug);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($slug, $id)
    {
        $gsetting = GeneralSetting::where('slug', $slug)->firstOrFail();
        $model = $gsetting->model;

        $setting = $model::where('id', $id)->firstOrFail();

        $setting->delete();

        flash()->success($gsetting->name . ' deleted successfully.');
        return redirect()->route('settings.index', $gsetting->slug);
    }
}
