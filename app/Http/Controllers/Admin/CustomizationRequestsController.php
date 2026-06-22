<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomizationRequest;
use App\Models\CustomizationStatus;
use Illuminate\Http\Request;

class CustomizationRequestsController extends Controller
{
    public function index()
    {
        $requests = CustomizationRequest::with(['user', 'customization_status'])->latest()->get();
        return view('admin.customization-requests.index', compact('requests'));
    }

    public function show(CustomizationRequest $customizationRequest)
    {
        $customizationRequest->load([
            'user',
            'customization_status',
            'milestones',
            'price_adjustments',
            'escrow',
        ]);
        return view('admin.customization-requests.show', compact('customizationRequest'));
    }

    public function edit(CustomizationRequest $customizationRequest)
    {
        $statuses = CustomizationStatus::all();
        return view('admin.customization-requests.edit', compact('customizationRequest', 'statuses'));
    }

    public function update(Request $request, CustomizationRequest $customizationRequest)
    {
        $request->validate([
            'customization_status_id' => 'required|exists:customization_statuses,id',
            'price'                   => 'nullable|numeric|min:0',
        ]);

        $customizationRequest->update($request->only(['customization_status_id', 'price', 'description']));

        flash()->success('Customization request updated successfully.');
        return redirect()->route('customization-requests.index');
    }

    public function destroy(CustomizationRequest $customizationRequest)
    {
        $customizationRequest->delete();
        flash()->success('Customization request deleted successfully.');
        return redirect()->route('customization-requests.index');
    }
}
