<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TemplateFileResource;
use App\Http\Resources\TemplateResource;
use App\Models\TemplateFile;
use App\Models\Template;
use App\Models\User;
use App\Models\UserPurchase;
use App\Services\PaymentFulfillmentService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $query = Template::with([
                'user' => fn($q) => $q->withRatingStats()->with('country'),
                'template_status',
                'template_files'
            ])
            ->where('template_status_id', 1)
            ->when($search, fn($q) => $q->where('title', 'like', "%$search%"));

        // Pinned (admin-set) templates always come first (positions 1, 2),
        // then organic results sorted by the pre-computed ranking score.
        $templates = $query
            ->orderByRaw('is_pinned DESC, pin_position ASC, rank_score DESC')
            ->get();

        return response()->json(TemplateResource::collection($templates));
    }

    public function show($id)
    {
        $template = Template::with([
                'user' => fn($q) => $q->withRatingStats()->with('country'),
                'template_status',
                'template_files'
            ])
            ->find($id);

        if (!$template) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Template not found!'
            ], 404);
        }

        return response()->json(new TemplateResource($template));
    }

    public function myTemplates()
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $templates = Template::with([
                'user' => fn($q) => $q->withRatingStats()->with('country'),
                'template_status',
                'template_files'
            ])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(TemplateResource::collection($templates));
    }

    /**
     * Get public templates for a specific user profile.
     * GET /templates/user/{user_id}
     */
    public function getUserTemplates($user_id)
    {
        if (!is_numeric($user_id) || (int) $user_id <= 0) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid user ID.'
            ], 400);
        }

        $templates = Template::with([
                'user' => fn($q) => $q->withRatingStats()->with('country'),
                'template_status',
                'template_files'
            ])
            ->where('user_id', $user_id)
            ->where('template_status_id', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(TemplateResource::collection($templates));
    }

    public function store(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        // FREE: 10 templates/month; PRO/FIRM: unlimited
        $check = app(SubscriptionService::class)->canUploadTemplate($user);
        if (!$check['allowed']) {
            return response()->json(['status' => 'failed', 'message' => $check['message']], 403);
        }

        $thumbnail = '';
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('templates/thumbnails', $fileName, 'r2');
            $thumbnail = 'templates/thumbnails/' . $fileName;
        }

        $images = $request->images ?? '';
        if ($request->hasFile('images')) {
            $uploadedImages = [];
            foreach ($request->file('images') as $img) {
                $imgName = time() . '_' . $img->getClientOriginalName();
                $img->storeAs('templates/images', $imgName, 'r2');
                $uploadedImages[] = 'templates/images/' . $imgName;
            }
            $images = implode(',', $uploadedImages);
        }

        $template = Template::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'includes' => $request->includes,
            'price' => $request->price,
            'thumbnail' => $thumbnail,
            'images' => $images,
            'template_status_id' => 1,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Template created successfully!',
            'template' => new TemplateResource($template),
        ]);
    }

    public function update(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $template_id = $request->template_id;
        $template = Template::find($template_id);

        if (!$template || $template->user_id != $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid template!'
            ], 400);
        }

        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('templates/thumbnails', $fileName, 'r2');
            $template->update(['thumbnail' => 'templates/thumbnails/' . $fileName]);
        }

        $template->update($request->except(['template_id', 'user_id', 'thumbnail', 'images']));

        return response()->json([
            'status' => 'success',
            'message' => 'Template updated successfully!',
            'template' => new TemplateResource($template->fresh()),
        ]);
    }

    public function destroy(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $template_id = $request->template_id;
        $template = Template::find($template_id);

        if (!$template || $template->user_id != $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid template!'
            ], 400);
        }

        TemplateFile::where('template_id', $template->id)->delete();
        $template->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Template deleted successfully!',
        ]);
    }

    public function addFile(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $template_id = $request->template_id;
        $is_preview = $request->is_preview ?? false;
        $template = Template::find($template_id);

        if (!$template || $template->user_id != $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid template!'
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
            $file->storeAs('templates/files', $fileName, 'r2');

            $templateFile = TemplateFile::create([
                'template_id' => $template->id,
                'file_name'   => $file->getClientOriginalName(),
                'file_path'   => 'templates/files/' . $fileName,
                'is_preview'  => $is_preview,
            ]);

            $uploadedFiles[] = new TemplateFileResource($templateFile->setRelation('template', $template));
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
        $templateFile = TemplateFile::find($file_id);

        if (!$templateFile) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid file!'
            ], 400);
        }

        $template = Template::find($templateFile->template_id);
        if (!$template || $template->user_id != $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized action!'
            ], 403);
        }

        $templateFile->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'File removed successfully!'
        ]);
    }

    /**
     * Toggle favorite (like) for a template.
     * POST /templates/toggle-favorite?template_id=123
     */
    public function toggleFavorite(Request $request)
    {
        $request->validate(['template_id' => 'required|integer']);

        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $template = Template::find($request->template_id);
        if (!$template) {
            return response()->json(['status' => 'failed', 'message' => 'Template not found!'], 404);
        }

        $existing = \App\Models\Favorite::where('user_id', $user->id)
            ->where('favoriteable_type', 'template')
            ->where('favoriteable_id', $template->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $template->decrement('favorite_count');
            return response()->json([
                'status' => 'success',
                'message' => 'Removed from favorites',
                'favorited' => false,
                'favorite_count' => $template->favorite_count,
            ]);
        } else {
            \App\Models\Favorite::create([
                'user_id' => $user->id,
                'favoriteable_type' => 'template',
                'favoriteable_id' => $template->id,
            ]);
            $template->increment('favorite_count');
            return response()->json([
                'status' => 'success',
                'message' => 'Added to favorites',
                'favorited' => true,
                'favorite_count' => $template->favorite_count,
            ]);
        }
    }

    /**
     * Initiate template purchase via Flutterwave.
     * POST /templates/purchase?template_id=123&amount=100&currency=NGN
     */
    public function purchaseTemplate(Request $request)
    {
        $request->validate([
            'template_id' => 'required|integer',
            'amount'      => 'sometimes|numeric|min:0',
            'currency'    => 'sometimes|string|size:3',
        ]);

        $token    = JWTAuth::parseToken();
        $user     = $token->authenticate();

        $template = Template::find($request->template_id);
        if (!$template) {
            return response()->json(['status' => 'failed', 'message' => 'Template not found!'], 404);
        }

        if ($template->user_id == $user->id) {
            return response()->json(['status' => 'failed', 'message' => 'You cannot purchase your own template!'], 400);
        }

        $existingPurchase = UserPurchase::where('user_id', $user->id)
            ->where('purchasable_type', 'template')
            ->where('purchasable_id', $template->id)
            ->first();

        if ($existingPurchase) {
            if ($existingPurchase->status === 'completed') {
                return response()->json(['status' => 'failed', 'message' => 'You have already purchased this template!'], 400);
            }
            // Remove stale pending/failed record so the user can retry payment.
            $existingPurchase->delete();
        }

        $totalPriceUSD = $template->price;
        $currency      = strtoupper($request->input('currency', 'USD'));
        $amount        = (float) $request->input('amount', $totalPriceUSD);
        $tx_ref        = 'template_' . $template->id . '_' . $user->id . '_' . \Illuminate\Support\Str::random(12);

        $purchase = UserPurchase::create([
            'user_id'          => $user->id,
            'purchasable_type' => 'template',
            'purchasable_id'   => $template->id,
            'amount_paid'      => $amount,
            'currency'         => $currency,
            'tx_ref'           => $tx_ref,
            'status'           => 'pending',
        ]);

        $flutterwave  = new \App\Services\FlutterwaveService();
        $redirect_url = url('/api/templates/purchase/callback');

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
            title: 'Template Purchase: ' . $template->title
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
     * Flutterwave callback for template purchases.
     */
    public function purchaseCallback(Request $request)
    {
        $status         = $request->query('status');
        $tx_ref         = $request->query('tx_ref');
        $transaction_id = (int) $request->query('transaction_id');

        if (!in_array($status, ['successful', 'completed'], true) || !$tx_ref || !$transaction_id) {
            return response()->json(['status' => 'failed', 'message' => 'Payment not completed.'], 400);
        }

        $purchase = UserPurchase::where('tx_ref', $tx_ref)
            ->where('purchasable_type', 'template')
            ->first();

        if (!$purchase || $purchase->status === 'completed') {
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
        PaymentFulfillmentService::fulfillTemplatePurchase($purchase);

        return response()->json(['status' => 'success', 'message' => 'Template purchased successfully!']);
    }

    /**
     * Stream a template file after verifying access rights.
     */
    public function downloadTemplateFile(Request $request, int $file_id)
    {
        $token    = JWTAuth::parseToken();
        $user     = $token->authenticate();

        $templateFile = TemplateFile::find($file_id);
        if (!$templateFile) {
            return response()->json(['status' => 'failed', 'message' => 'File not found!'], 404);
        }

        $template = Template::find($templateFile->template_id);
        if (!$template) {
            return response()->json(['status' => 'failed', 'message' => 'Template not found!'], 404);
        }

        if (!$templateFile->is_preview) {
            $isOwner      = $template->user_id == $user->id;
            $hasPurchased = $template->price == 0
                || UserPurchase::where('user_id', $user->id)
                       ->where('purchasable_type', 'template')
                       ->where('purchasable_id', $template->id)
                       ->where('status', 'completed')
                       ->exists();

            if (!$isOwner && !$hasPurchased) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'You must purchase this template to download the main files.',
                ], 403);
            }
        }

        if (Storage::disk('r2')->exists($templateFile->file_path)) {
            return Storage::disk('r2')->download($templateFile->file_path, $templateFile->file_name);
        }

        return response()->json(['status' => 'failed', 'message' => 'File not found on disk!'], 404);
    }
}
