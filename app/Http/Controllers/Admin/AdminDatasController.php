<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminData;
use Illuminate\Http\Request;

class AdminDatasController extends Controller
{
    public function index()
    {
        $adminDatas = AdminData::latest()->get();
        return view('admin.admin-datas.index', compact('adminDatas'));
    }

    public function create()
    {
        return view('admin.admin-datas.add');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:admin_datas,slug',
            'value'       => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $data = $request->only(['name', 'value', 'description']);
        $data['slug'] = $request->slug ?: \Str::slug($request->name);

        AdminData::create($data);

        flash()->success('Admin data created successfully.');
        return redirect()->route('admin-datas.index');
    }

    public function edit(AdminData $adminData)
    {
        return view('admin.admin-datas.edit', compact('adminData'));
    }

    public function update(Request $request, AdminData $adminData)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:admin_datas,slug,' . $adminData->id,
            'value'       => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $data = $request->only(['name', 'value', 'description']);
        $data['slug'] = $request->slug ?: \Str::slug($request->name);

        $adminData->update($data);

        flash()->success('Admin data updated successfully.');
        return redirect()->route('admin-datas.index');
    }

    public function destroy(AdminData $adminData)
    {
        $adminData->delete();
        flash()->success('Admin data deleted successfully.');
        return redirect()->route('admin-datas.index');
    }
}
