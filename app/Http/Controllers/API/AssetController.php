<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssetResource;
use App\Models\Asset;
use App\Models\AssetFile;
use App\Models\User;
use App\Models\UserPurchase;
use App\Services\PaymentFulfillmentService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $design_category_id = $request->design_category_id;
        $license_type_id = $request->license_type_id;
        $sort = strtolower((string) $request->get('sort', 'new'));

        $assets = Asset::with([
                'user' => fn($q) => $q->withRatingStats()->with('country'),
                'design_category',
                'license_type',
                'asset_status',
                'asset_files'
            ])
            ->where('asset_status_id', 1)
            ->where('visibility', true)
            ->when($search, fn($q) => $q->where('title', 'like', "%$search%"))
            ->when($design_category_id, fn($q) => $q->where('design_category_id', $design_category_id))
            ->when($license_type_id, fn($q) => $q->where('license_type_id', $license_type_id))
            ->when($sort === 'popular', fn($q) => $q->orderByDesc('purchase_count')->orderByDesc('review_count')->orderByDesc('created_at'))
            ->when(in_array($sort, ['most-liked', 'most_liked', 'liked'], true), fn($q) => $q->orderByDesc('favorite_count')->orderByDesc('created_at'))
            ->when($sort === 'new', fn($q) => $q->orderBy('created_at', 'desc'))
            ->when(!in_array($sort, ['popular', 'most-liked', 'most_liked', 'liked', 'new'], true), fn($q) => $q->orderBy('created_at', 'desc'))
            ->get();

        return response()->json(AssetResource::collection($assets));
    }

    public function mostLiked()
    {
        $assets = Asset::with([
                'user' => fn($q) => $q->withRatingStats()->with('country'),
                'design_category',
                'license_type',
                'asset_status',
                'asset_files'
            ])
            ->where('asset_status_id', 1)
            ->where('visibility', true)
            ->orderByDesc('favorite_count')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(AssetResource::collection($assets));
    }

    public function show($id)
    {
        $asset = Asset::with([
                'user' => fn($q) => $q->withRatingStats()->with('country'),
                'design_category',
                'license_type',
                'asset_status',
                'asset_files'
            ])
            ->find($id);

        if (!$asset) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Asset not found!'
            ], 404);
        }

        $asset->increment('view_count');

        return response()->json(new AssetResource($asset));
    }

    public function myAssets(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $includeUnpublished = filter_var($request->query('include_unpublished', false), FILTER_VALIDATE_BOOLEAN);

        $assets = Asset::with([
                'user' => fn($q) => $q->withRatingStats()->with('country'),
                'design_category',
                'license_type',
                'asset_status',
                'asset_files'
            ])
            ->where('user_id', $user->id)
            ->when(!$includeUnpublished, fn($q) => $q->where('asset_status_id', 1)->where('visibility', true))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(AssetResource::collection($assets));
    }

    /**
     * Get public assets for a specific user profile.
     * GET /assets/user/{user_id}
     */
    public function getUserAssets($user_id)
    {
        if (!is_numeric($user_id) || (int) $user_id <= 0) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid user ID.'
            ], 400);
        }

        $assets = Asset::with([
                'user' => fn($q) => $q->withRatingStats()->with('country'),
                'design_category',
                'license_type',
                'asset_status',
                'asset_files'
            ])
            ->where('user_id', $user_id)
            ->where('asset_status_id', 1)
            ->where('visibility', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(AssetResource::collection($assets));
    }

    public function store(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $service = app(SubscriptionService::class);

        // Check monthly upload limit (FREE: 10/month; PRO/FIRM: unlimited)
        $check = $service->canUploadAsset($user);
        if (!$check['allowed']) {
            return response()->json(['status' => 'failed', 'message' => $check['message']], 403);
        }

        // Advanced upload tools (embedded, prototype, etc.) – Pro/Firm only
        if ($request->boolean('is_advanced_upload') && !$service->canUseAdvancedTools($user)) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Advanced upload tools are only available on Pro or Firm subscriptions.',
            ], 403);
        }

        $thumbnail = '';
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('assets/thumbnails', $fileName, 'r2');
            $thumbnail = 'assets/thumbnails/' . $fileName;
        }

        $asset = Asset::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'service_charge' => $request->service_charge ?? 0,
            'design_category_id' => $request->design_category_id,
            'thumbnail' => $thumbnail,
            'unique_code' => strtoupper(Str::random(10)),
            'tools_used' => $request->tools_used,
            'specifications' => $request->specifications,
            'available_file_formats' => $request->available_file_formats,
            'license_type_id' => $request->license_type_id ?? 1,
            'detail_view' => $request->detail_view ?? '',
            'visibility' => $request->visibility ?? true,
            'affiliate_settings' => $request->affiliate_settings ?? false,
            'affiliate_commission_rate' => $request->affiliate_commission_rate ?? 0,
            'customization_available' => $request->customization_available ?? false,
            'customization_price' => $request->customization_price ?? 0,
            'is_advanced_upload' => $request->boolean('is_advanced_upload'),
            'asset_status_id' => 1,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Asset created successfully!',
            'asset' => new AssetResource($asset),
        ]);
    }

    public function update(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $asset_id = $request->asset_id;
        $asset = Asset::find($asset_id);

        if (!$asset || $asset->user_id != $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid asset!'
            ], 400);
        }

        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('assets/thumbnails', $fileName, 'r2');
            $asset->update(['thumbnail' => 'assets/thumbnails/' . $fileName]);
        }

        $asset->update($request->except(['asset_id', 'user_id', 'thumbnail', 'unique_code']));

        return response()->json([
            'status' => 'success',
            'message' => 'Asset updated successfully!',
            'asset' => new AssetResource($asset->fresh()),
        ]);
    }

    public function destroy(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $asset_id = $request->asset_id;
        $asset = Asset::find($asset_id);

        if (!$asset || $asset->user_id != $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid asset!'
            ], 400);
        }

        AssetFile::where('asset_id', $asset->id)->delete();
        $asset->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Asset deleted successfully!',
        ]);
    }

    public function addFile(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $asset_id = $request->asset_id;
        $is_preview = $request->is_preview ?? false;
        $asset = Asset::find($asset_id);

        if (!$asset || $asset->user_id != $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid asset!'
            ], 400);
        }

        if (!$request->hasFile('files')) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No file uploaded!'
            ], 400);
        }

        $uploadedFiles = [];
        foreach ($request->file('files') as $file) {
            $fileName = time() . '_' . $file->getClientOriginalName();
            // All files go to R2; access control is enforced by the download endpoint.
            $file->storeAs('assets/files', $fileName, 'r2');

            $assetFile = AssetFile::create([
                'asset_id' => $asset->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => 'assets/files/' . $fileName,
                'is_preview' => $is_preview,
            ]);

            $uploadedFiles[] = new \App\Http\Resources\AssetFileResource($assetFile->setRelation('asset', $asset));
        }

        return response()->json([
            'status' => 'success',
            'message' => count($uploadedFiles) . ' file(s) added successfully!',
            'files' => $uploadedFiles,
        ]);
    }

    public function removeFile(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $file_id = $request->file_id;
        $assetFile = AssetFile::find($file_id);

        if (!$assetFile) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid file!'
            ], 400);
        }

        $asset = Asset::find($assetFile->asset_id);
        if (!$asset || $asset->user_id != $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized access!'
            ], 403);
        }

        $assetFile->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'File removed successfully!',
        ]);
    }

    /**
     * Initiate asset purchase via Flutterwave.
     * POST /assets/purchase?asset_id=123&amount=100&currency=NGN
     */
    public function purchaseAsset(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|integer',
            'amount'   => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
        ]);

        $token = JWTAuth::parseToken();
        $user  = $token->authenticate();

        $asset = Asset::find($request->asset_id);
        if (!$asset) {
            return response()->json(['status' => 'failed', 'message' => 'Asset not found!'], 404);
        }

        if ($asset->user_id == $user->id) {
            return response()->json(['status' => 'failed', 'message' => 'You cannot purchase your own asset!'], 400);
        }

        $existingPurchase = UserPurchase::where('user_id', $user->id)
            ->where('purchasable_type', 'asset')
            ->where('purchasable_id', $asset->id)
            ->first();

        if ($existingPurchase) {
            if ($existingPurchase->status === 'completed') {
                return response()->json(['status' => 'failed', 'message' => 'You have already purchased this asset!'], 400);
            }
            // Remove stale pending/failed record so the user can retry payment.
            $existingPurchase->delete();
        }

        $totalPriceUSD = $asset->price + $asset->service_charge;
        $currency      = strtoupper($request->input('currency', 'USD'));
        $amount        = (float) $request->input('amount', $totalPriceUSD);
        $tx_ref        = 'asset_' . $asset->id . '_' . $user->id . '_' . \Illuminate\Support\Str::random(12);

        // Create pending purchase record
        $purchase = UserPurchase::create([
            'user_id'          => $user->id,
            'purchasable_type' => 'asset',
            'purchasable_id'   => $asset->id,
            'amount_paid'      => $amount,
            'currency'         => $currency,
            'tx_ref'           => $tx_ref,
            'status'           => 'pending',
        ]);

        $flutterwave  = new \App\Services\FlutterwaveService();
        $redirect_url = url('/api/assets/purchase/callback');

        $response = $flutterwave->initiatePayment(
            amount:       $amount,
            currency:     $currency,
            tx_ref:       $tx_ref,
            redirect_url: $redirect_url,
            customer: [
                'email'       => $user->email,
                'name'        => $user->name,
                'phonenumber' => $user->phone ?? '',
            ],
            title: 'Asset Purchase: ' . $asset->title
        );

        if (($response['status'] ?? '') !== 'success') {
            $purchase->delete();
            return response()->json([
                'status'  => 'failed',
                'message' => $response['message'] ?? 'Could not generate payment link.',
            ], 502);
        }

        // Store the Flutterwave transaction ID so callbacks can verify the exact transaction.
        $purchase->update(['transaction_id' => $response['data']['id'] ?? null]);

        return response()->json([
            'status'       => 'success',
            'payment_link' => $response['data']['link'],
            'tx_ref'       => $tx_ref,
        ]);
    }

    /**
     * Flutterwave callback for asset purchases.
     * GET /assets/purchase/callback?status=successful&tx_ref=...&transaction_id=...
     */
    public function purchaseCallback(Request $request)
    {
        $status         = $request->query('status');
        $tx_ref         = $request->query('tx_ref');
        $transaction_id = (int) $request->query('transaction_id');

        if ($status !== 'successful' || !$tx_ref || !$transaction_id) {
            return response()->json(['status' => 'failed', 'message' => 'Payment not completed.'], 400);
        }

        $purchase = UserPurchase::where('tx_ref', $tx_ref)
            ->where('purchasable_type', 'asset')
            ->first();

        if (!$purchase) {
            return response()->json(['status' => 'failed', 'message' => 'Purchase not found.'], 404);
        }

        if ($purchase->status === 'completed') {
            return response()->json(['status' => 'success', 'message' => 'Already processed.']);
        }

        // Verify with Flutterwave, including amount/currency and transaction_id checks.
        $verification = verifyFlutterwavePayment(
            transaction_id: $transaction_id,
            tx_ref: $tx_ref,
            expected_amount: (float) $purchase->amount_paid,
            expected_currency: $purchase->currency,
            expected_transaction_id: $purchase->transaction_id,
        );

        if (!$verification['valid']) {
            $purchase->update(['status' => 'failed']);
            return response()->json(['status' => 'failed', 'message' => 'Payment verification failed: ' . $verification['error']], 400);
        }

        // Atomically fulfill the purchase (prevents double fulfillment)
        PaymentFulfillmentService::fulfillAssetPurchase($purchase);

        return response()->json([
            'status'  => 'success',
            'message' => 'Asset purchased successfully!',
        ]);
    }

    /**
     * Stream a file after verifying the user is authorised to access it.
     * Preview files (is_preview=1) are freely downloadable by any authenticated user.
     * Main files (is_preview=0) require ownership or a completed purchase.
     */
    public function downloadAssetFile(Request $request, int $file_id)
    {
        $token = JWTAuth::parseToken();
        $user  = $token->authenticate();

        $assetFile = AssetFile::find($file_id);
        if (!$assetFile) {
            return response()->json(['status' => 'failed', 'message' => 'File not found!'], 404);
        }

        $asset = Asset::find($assetFile->asset_id);
        if (!$asset) {
            return response()->json(['status' => 'failed', 'message' => 'Asset not found!'], 404);
        }

        // Main-file access gate
        if (!$assetFile->is_preview) {
            $isOwner      = $asset->user_id == $user->id;
            $hasPurchased = $asset->price == 0 // free assets are always accessible
                || UserPurchase::where('user_id', $user->id)
                       ->where('purchasable_type', 'asset')
                       ->where('purchasable_id', $asset->id)
                       ->where('status', 'completed')
                       ->exists();

            if (!$isOwner && !$hasPurchased) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'You must purchase this asset to download the main files.',
                ], 403);
            }
        }

        if (Storage::disk('r2')->exists($assetFile->file_path)) {
            return Storage::disk('r2')->download($assetFile->file_path, $assetFile->file_name);
        }

        return response()->json(['status' => 'failed', 'message' => 'File not found on disk!'], 404);
    }

    /**
     * Toggle favorite for an asset.
     * POST /assets/toggle-favorite?asset_id=123
     */
    public function toggleFavorite(Request $request)
    {
        $request->validate(['asset_id' => 'required|integer']);
        
        $token = JWTAuth::parseToken();
        $user  = $token->authenticate();

        $asset = Asset::find($request->asset_id);
        if (!$asset) {
            return response()->json(['status' => 'failed', 'message' => 'Asset not found!'], 404);
        }

        $existing = \App\Models\Favorite::where('user_id', $user->id)
            ->where('favoriteable_type', 'asset')
            ->where('favoriteable_id', $asset->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $asset->decrement('favorite_count');
            return response()->json([
                'status' => 'success',
                'message' => 'Removed from favorites',
                'favorited' => false,
                'favorite_count' => $asset->favorite_count,
            ]);
        } else {
            \App\Models\Favorite::create([
                'user_id' => $user->id,
                'favoriteable_type' => 'asset',
                'favoriteable_id' => $asset->id,
            ]);
            $asset->increment('favorite_count');
            return response()->json([
                'status' => 'success',
                'message' => 'Added to favorites',
                'favorited' => true,
                'favorite_count' => $asset->favorite_count,
            ]);
        }
    }

    public function applyForCustomizationRole(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|integer',
            'category_ids' => 'required|array',
            'category_ids.*' => 'integer',
            'price' => 'required|numeric|min:0',
        ]);

        $token = JWTAuth::parseToken();
        $user  = $token->authenticate();

        $asset = Asset::find($request->asset_id);
        if (!$asset) {
            return response()->json(['status' => 'failed', 'message' => 'Asset not found!'], 404);
        }

        // Check if user is the asset owner
        if ($asset->user_id == $user->id) {
            return response()->json(['status' => 'failed', 'message' => 'You cannot apply for customization on your own asset!'], 400);
        }

        // Check if user already applied for this asset
        $existing = \App\Models\AssetCustomization::where('asset_id', $request->asset_id)
            ->where('designer_id', $user->id)
            ->first();

        if ($existing) {
            // Update existing application
            $existing->update([
                'category_ids' => json_encode($request->category_ids),
                'price' => $request->price,
                'active' => true,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Your customization offer has been updated successfully!',
                'data' => $existing,
            ]);
        }

        // Create new application
        $customization = \App\Models\AssetCustomization::create([
            'asset_id' => $request->asset_id,
            'designer_id' => $user->id,
            'category_ids' => json_encode($request->category_ids),
            'price' => $request->price,
            'expire_at' => now()->addMonths(6)->toDateTimeString(), // 6 months validity
            'active' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Your customization offer has been submitted successfully!',
            'data' => $customization,
        ]);
    }

    /**
     * Get designers offering customization for a specific asset
     */
    public function getCustomizationDesigners(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|integer',
        ]);

        $assetId = $request->input('asset_id');

        // Get active customization offers for this asset from designers only (not clients)
        $customizations = \App\Models\AssetCustomization::where('asset_id', $assetId)
            ->where('active', true)
            ->where('expire_at', '>', now())
            ->whereHas('designer', function ($query) {
                $query->whereHas('account_type', function ($q) {
                    $q->whereRaw('LOWER(name) != ?', ['client']);
                });
            })
            ->with([
                'designer' => function ($q) {
                    $q->withRatingStats()->with(['account_type', 'offer_type', 'profile.primary_role', 'profile.design_category', 'country']);
                }
            ])
            ->get();

        $designers = $customizations->map(function ($customization) {
            $designer = $customization->designer;
            if (!$designer) return null;

            $profile = $designer->profile;
            $displayName = $profile && $profile->is_studio_name_display_name
                ? ($profile->studio_name ?: $designer->name)
                : $designer->name;

            // Ensure category_ids is always an array
            $categoryIds = $customization->category_ids;
            if (is_string($categoryIds)) {
                $categoryIds = json_decode($categoryIds, true) ?? [];
            }
            if (!is_array($categoryIds)) {
                $categoryIds = [];
            }

            $assetAvg = (float) ($designer->assets_rating_avg ?? 0);
            $templateAvg = (float) ($designer->templates_rating_avg ?? 0);
            $assetReviews = (int) ($designer->assets_reviews_sum ?? 0);
            $templateReviews = (int) ($designer->templates_reviews_sum ?? 0);
            $totalReviews = $assetReviews + $templateReviews;

            $averageRating = $totalReviews > 0
                ? round((($assetAvg * $assetReviews) + ($templateAvg * $templateReviews)) / $totalReviews, 1)
                : round(max($assetAvg, $templateAvg), 1);

            return [
                'id' => $designer->id,
                'name' => $displayName,
                'full_name' => $designer->name,
                'avatar' => $designer->profile_picture ? Storage::disk('r2')->url($designer->profile_picture) : null,
                'role' => $profile && $profile->primary_role ? $profile->primary_role->name : 'Designer',
                'rating' => $averageRating,
                'total_reviews' => $totalReviews,
                'price' => $customization->price,
                'category_ids' => $categoryIds,
                'customization_id' => $customization->id,
                'studio_name' => $profile ? $profile->studio_name : null,
                'bio' => $profile ? $profile->bio : null,
            ];
        })->filter()->values();

        return response()->json([
            'status' => 'success',
            'data' => $designers,
        ]);
    }
}

