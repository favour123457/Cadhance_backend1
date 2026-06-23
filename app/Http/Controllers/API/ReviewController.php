<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Asset;
use App\Models\Review;
use App\Models\Template;
use App\Models\UserPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReviewController extends Controller
{
    /**
     * GET /api/reviews/my
     * Seller dashboard: reviews received on the authenticated user's assets & templates.
     */
    public function my()
    {
        $user = JWTAuth::parseToken()->authenticate();

        // All assets and templates owned by this seller
        $assets    = Asset::where('user_id', $user->id)->orderBy('review_count', 'desc')->get();
        $templates = Template::where('user_id', $user->id)->orderBy('review_count', 'desc')->get();

        $assetIds    = $assets->pluck('id');
        $templateIds = $templates->pluck('id');

        // All reviews on this seller's items (for accurate aggregate stats)
        $allReviews = Review::where(function ($q) use ($assetIds) {
            $q->where('reviewable_type', 'asset')->whereIn('reviewable_id', $assetIds);
        })->orWhere(function ($q) use ($templateIds) {
            $q->where('reviewable_type', 'template')->whereIn('reviewable_id', $templateIds);
        })->get();

        $total   = $allReviews->count();
        $average = $total > 0 ? round($allReviews->avg('rating'), 1) : 0;

        $breakdown = [];
        for ($i = 1; $i <= 5; $i++) {
            $breakdown[$i] = $allReviews->where('rating', $i)->count();
        }

        // Lightweight item shape for the reviews dashboard
        $mapItem = fn($item) => [
            'id'           => $item->id,
            'title'        => $item->title,
            'unique_code'  => $item->unique_code ?? null,
            'thumbnail'    => $item->thumbnail ? Storage::disk('r2')->url($item->thumbnail) : null,
            'rating'       => $item->rating ?? 0,
            'review_count' => $item->review_count ?? 0,
        ];

        return response()->json([
            'stats' => [
                'total'     => $total,
                'average'   => $average,
                'breakdown' => $breakdown,
            ],
            'assets'    => $assets->map($mapItem)->values(),
            'templates' => $templates->map($mapItem)->values(),
        ]);
    }

    /**
     * GET /api/reviews/{type}/{id}
     * All individual reviews for a specific asset or template (public-ish, filtered to owner).
     */
    public function forItem($type, $id)
    {
        if (!in_array($type, ['asset', 'template'])) {
            return response()->json(['message' => 'Invalid reviewable type.'], 422);
        }

        $query = Review::where('reviewable_type', $type)
            ->where('reviewable_id', $id);

        $allReviews = $query->get();
        $total   = $allReviews->count();
        $average = $total > 0 ? round($allReviews->avg('rating'), 1) : 0;

        $breakdown = [];
        for ($i = 1; $i <= 5; $i++) {
            $breakdown[$i] = $allReviews->where('rating', $i)->count();
        }

        $reviews = Review::with('user')
            ->where('reviewable_type', $type)
            ->where('reviewable_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'stats' => [
                'total'     => $total,
                'average'   => $average,
                'breakdown' => $breakdown,
            ],
            'reviews' => ReviewResource::collection($reviews)
        ]);
    }

    /**
     * POST /api/reviews/store
     * A buyer submits a review on a purchased asset or template.
     */
    public function store(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $type  = $request->reviewable_type;
        $id    = (int) $request->reviewable_id;
        $rating = (int) $request->rating;
        $comment = $request->comment;

        if (!in_array($type, ['asset', 'template'])) {
            return response()->json(['message' => 'Invalid reviewable type.'], 422);
        }

        if ($rating < 1 || $rating > 5) {
            return response()->json(['message' => 'Rating must be between 1 and 5.'], 422);
        }

        // Only buyers who purchased the item may review it
        $purchased = UserPurchase::where('user_id', $user->id)
            ->where('purchasable_type', $type)
            ->where('purchasable_id', $id)
            ->exists();

        if (!$purchased) {
            return response()->json(['message' => 'You must purchase this item before reviewing it.'], 403);
        }

        $review = Review::updateOrCreate(
            ['user_id' => $user->id, 'reviewable_type' => $type, 'reviewable_id' => $id],
            ['rating' => $rating, 'comment' => $comment]
        );

        $this->syncAggregates($type, $id);

        return response()->json(new ReviewResource($review->load('user')));
    }

    /**
     * Recompute and persist the aggregate rating and review_count on the item.
     */
    private function syncAggregates(string $type, int $id): void
    {
        $reviews = Review::where('reviewable_type', $type)->where('reviewable_id', $id)->get();
        $count   = $reviews->count();
        $avg     = $count > 0 ? round($reviews->avg('rating'), 2) : 0;

        if ($type === 'asset') {
            Asset::where('id', $id)->update(['review_count' => $count, 'rating' => $avg]);
        } elseif ($type === 'template') {
            Template::where('id', $id)->update(['review_count' => $count, 'rating' => $avg]);
        }
    }
}
