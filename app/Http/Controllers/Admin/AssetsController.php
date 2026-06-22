<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\User;
use App\Models\AssetStatus;
use App\Models\DesignCategory;
use App\Models\LicenseType;
use Illuminate\Http\Request;

class AssetsController extends Controller
{
    public function index()
    {
        $assets = Asset::with(['user', 'asset_status', 'design_category'])
            ->orderByRaw('is_pinned DESC, pin_position ASC, rank_score DESC')
            ->get();
        return view('admin.assets.index', compact('assets'));
    }

    public function show(Asset $asset)
    {
        $asset->load(['user', 'asset_status', 'design_category', 'license_type', 'asset_files']);
        return view('admin.assets.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        $assetStatuses    = AssetStatus::all();
        $designCategories = DesignCategory::all();
        $licenseTypes     = LicenseType::all();
        return view('admin.assets.edit', compact('asset', 'assetStatuses', 'designCategories', 'licenseTypes'));
    }

    public function update(Request $request, Asset $asset)
    {
        $request->validate([
            'asset_status_id'    => 'required|exists:asset_statuses,id',
            'title'              => 'required|string|max:255',
            'price'              => 'nullable|numeric|min:0',
            'service_charge'     => 'nullable|numeric|min:0',
            'rating'             => 'nullable|numeric|min:0|max:5',
            'affiliate_commission_rate' => 'nullable|numeric|min:0|max:100',
            'customization_price' => 'nullable|numeric|min:0',
            'design_category_id' => 'nullable|exists:design_categories,id',
            'license_type_id'    => 'nullable|exists:license_types,id',
        ]);

        $asset->update([
            'title'                     => $request->title,
            'description'               => $request->description,
            'detail_view'               => $request->detail_view,
            'unique_code'               => $request->unique_code,
            'price'                     => $request->price,
            'service_charge'            => $request->service_charge,
            'rating'                    => $request->rating,
            'tools_used'                => $request->tools_used,
            'available_file_formats'    => $request->available_file_formats,
            'asset_status_id'           => $request->asset_status_id,
            'design_category_id'        => $request->design_category_id,
            'license_type_id'           => $request->license_type_id,
            'visibility'                => $request->boolean('visibility'),
            'affiliate_settings'        => $request->boolean('affiliate_settings'),
            'affiliate_commission_rate' => $request->affiliate_commission_rate,
            'customization_available'   => $request->boolean('customization_available'),
            'customization_price'       => $request->customization_price,
            'is_advanced_upload'        => $request->boolean('is_advanced_upload'),
            'has_video'                 => $request->boolean('has_video'),
            'has_sample'                => $request->boolean('has_sample'),
        ]);

        flash()->success('Asset updated successfully.');
        return redirect()->route('assets.index');
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();
        flash()->success('Asset deleted successfully.');
        return redirect()->route('assets.index');
    }

    /**
     * Toggle the admin "Top / Priority" pin for an asset.
     * POST admin/assets/{asset}/toggle-pin
     * Max 2 pinned at a time.
     */
    public function togglePin(Asset $asset)
    {
        if ($asset->is_pinned) {
            $unpinnedPosition = $asset->pin_position;
            $asset->update(['is_pinned' => false, 'pin_position' => 0]);
            Asset::where('is_pinned', true)
                ->where('pin_position', '>', $unpinnedPosition)
                ->orderBy('pin_position')
                ->each(function ($a) {
                    $a->update(['pin_position' => $a->pin_position - 1]);
                });
            flash()->success('"' . $asset->name . '" removed from top positions.');
        } else {
            $maxSlots = 2;
            $currentPinned = Asset::where('is_pinned', true)->count();
            if ($currentPinned >= $maxSlots) {
                flash()->warning('Only ' . $maxSlots . ' assets can be pinned at a time. Unpin one first.');
                return redirect()->route('assets.index');
            }
            $nextPosition = $currentPinned + 1;
            $asset->update(['is_pinned' => true, 'pin_position' => $nextPosition]);
            flash()->success('"' . $asset->name . '" pinned to position #' . $nextPosition . ' in the marketplace.');
        }
        return redirect()->route('assets.index');
    }
}
