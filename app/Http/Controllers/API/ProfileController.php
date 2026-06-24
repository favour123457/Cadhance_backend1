<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicDesignerResource;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\UserPortfolioResource;
use App\Http\Resources\UserSkillResource;
use App\Models\Profile;
use App\Models\Skill;
use App\Models\User;
use App\Models\UserPortfolio;
use App\Models\UserPortfolioMedia;
use App\Models\UserSkill;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProfileController extends Controller
{
    /**
     * Returns profiles of active Firm subscribers for the landing page slideshare.
     */
    public function topFirms(Request $request)
    {
        $designers = $this->publicDesignersQuery($request, true)->get();

        return response()->json(PublicDesignerResource::collection($this->sortPublicDesigners($designers, $request)));
    }

    public function designers(Request $request)
    {
        $designers = $this->publicDesignersQuery($request)->get();

        return response()->json(PublicDesignerResource::collection($this->sortPublicDesigners($designers, $request)));
    }

    public function getMyProfile()
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        // Auto-create an empty profile if the user doesn't have one yet
        $profile = Profile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'design_category_id' => 1,
                'primary_role_id'    => 1,
                'visibility'         => true,
                'is_studio_name_display_name' => false,
            ]
        );

        // Load the user relationship and other related data
        $profile->load(['user' => fn($q) => $q->withRatingStats()->with('country'), 'primary_role', 'design_category']);

        return response()->json(new ProfileResource($profile, true));
    }

    public function getProfile($user_id)
    {
        $currentUser = null;
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            // No token or invalid token — public viewer
        }
        $isOwner = $currentUser && (int) $currentUser->id === (int) $user_id;

        $profileQuery = Profile::with([
            'user' => fn($q) => $q->withRatingStats()->with('country'),
            'primary_role',
            'design_category'
        ])
            ->where('user_id', $user_id);

        // Public profiles require visibility = true.
        // The owner can always view their own profile, even in draft/private mode.
        if (!$isOwner) {
            $profileQuery->where('visibility', true);
        }

        $profile = $profileQuery->first();

        if (!$profile) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Profile not found!'
            ], 404);
        }

        return response()->json(new ProfileResource($profile, $isOwner));
    }

    private function publicDesignersQuery(Request $request, bool $topFirmsOnly = false)
    {
        $search = trim((string) $request->get('search', ''));
        $offer = strtolower((string) $request->get('offer', ''));

        return User::query()
            ->withRatingStats()
            ->with([
                'country',
                'offer_type',
                'profile.design_category',
                'profile.primary_role',
                'user_subscriptions.subscription_plan.subscription_type',
            ])
            ->withCount([
                'assets as assets_count' => fn($q) => $q->where('asset_status_id', 1)->where('visibility', true),
                'templates as templates_count' => fn($q) => $q->where('template_status_id', 1),
            ])
            ->whereHas('account_type', fn($q) => $q->whereRaw('LOWER(name) != ?', ['client']))
            ->whereHas('profile', fn($q) => $q->where('visibility', true))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhereHas('country', fn($country) => $country->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('profile', function ($profile) use ($search) {
                            $profile->where('studio_name', 'like', "%{$search}%")
                                ->orWhere('bio', 'like', "%{$search}%")
                                ->orWhereHas('primary_role', fn($role) => $role->where('name', 'like', "%{$search}%"))
                                ->orWhereHas('design_category', fn($category) => $category->where('name', 'like', "%{$search}%"));
                        });
                });
            })
            ->when($offer === 'customization', fn($q) => $q->whereIn('offer_type_id', [2, 3]))
            ->when($offer === 'sell-designs', fn($q) => $q->whereIn('offer_type_id', [1, 3]))
            ->when($topFirmsOnly, function ($q) {
                $q->whereHas('user_subscriptions', function ($subscription) {
                    $subscription->where('active', true)
                        ->where('expire_at', '>=', now()->toDateString())
                        ->whereHas('subscription_plan.subscription_type', fn($type) => $type->whereRaw('LOWER(name) = ?', ['firm']));
                });
            });
    }

    private function sortPublicDesigners($designers, Request $request)
    {
        $sort = strtolower((string) $request->get('sort', 'new'));

        return match ($sort) {
            'top-rated', 'top_rated' => $designers->sortByDesc(function ($designer) {
                $assetAvg = (float) ($designer->assets_rating_avg ?? 0);
                $templateAvg = (float) ($designer->templates_rating_avg ?? 0);
                $assetReviews = (int) ($designer->assets_reviews_sum ?? 0);
                $templateReviews = (int) ($designer->templates_reviews_sum ?? 0);
                $totalReviews = $assetReviews + $templateReviews;

                if ($totalReviews > 0) {
                    return ((($assetAvg * $assetReviews) + ($templateAvg * $templateReviews)) / $totalReviews) * 1000 + $totalReviews;
                }

                return max($assetAvg, $templateAvg);
            })->values(),
            default => $designers->sortByDesc('created_at')->values(),
        };
    }

    public function updateProfile(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $profile = Profile::where('user_id', $user->id)->first();

        // Build update data, only including non-null values but keeping empty strings
        $data = [];
        
        if ($request->has('bio')) {
            $data['bio'] = $request->input('bio');
        }
        
        if ($request->has('studio_name')) {
            $data['studio_name'] = $request->input('studio_name');
        }
        
        if ($request->has('visibility') && !is_null($request->visibility)) {
            $data['visibility'] = $this->toBool($request->visibility);
        }
        
        if ($request->has('design_category_id') && !is_null($request->design_category_id)) {
            $data['design_category_id'] = $request->design_category_id;
        }
        
        if ($request->has('primary_role_id') && !is_null($request->primary_role_id)) {
            $data['primary_role_id'] = $request->primary_role_id;
        }
        
        if ($request->has('is_studio_name_display_name') && !is_null($request->is_studio_name_display_name)) {
            $data['is_studio_name_display_name'] = $this->toBool($request->is_studio_name_display_name);
        }
        
        if ($request->has('social_links')) {
            $data['social_links'] = $request->input('social_links');
        }

        if ($profile) {
            $profile->update($data);
        } else {
            $profile = Profile::create(array_merge([
                'user_id' => $user->id,
                'design_category_id' => $request->design_category_id ?? 1,
                'primary_role_id' => $request->primary_role_id ?? 1,
                'visibility' => true,
                'is_studio_name_display_name' => false,
                'banner_image' => 'profiles/default_banner.png',
            ], $data));
        }

        if ($request->hasFile('banner')) {
            $file = $request->file('banner');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('profiles', $fileName, 'r2');

            $complete_path = 'profiles/' . $fileName;
            $profile->update([
                'banner_image' => $complete_path,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully!',
            'profile' => new ProfileResource($profile->fresh(), true),
        ]);
    }

    /**
     * Convert a string/boolean value to a proper boolean.
     */
    private function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return (int) $value !== 0;
        }
        return in_array(strtolower(trim((string) $value)), ['true', 'yes', 'on'], true);
    }

    public function getMySkills()
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $skills = UserSkill::where('user_id', $user->id)->get();

        return response()->json(UserSkillResource::collection($skills));
    }

    public function addSkill(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $skill_id = $request->skill_id;
        $skill = Skill::find($skill_id);

        if (!$skill) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid skill!'
            ], 400);
        }

        $exists = UserSkill::where('user_id', $user->id)->where('skill_id', $skill_id)->first();
        if ($exists) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Skill already added!'
            ], 400);
        }

        $userSkill = UserSkill::create([
            'user_id' => $user->id,
            'skill_id' => $skill_id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Skill added successfully!',
            'skill' => new UserSkillResource($userSkill),
        ]);
    }

    public function removeSkill(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $user_skill_id = $request->user_skill_id;
        $userSkill = UserSkill::find($user_skill_id);

        if (!$userSkill || $userSkill->user_id != $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid skill!'
            ], 400);
        }

        $userSkill->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Skill removed successfully!',
        ]);
    }

    public function getMyPortfolios()
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $portfolios = UserPortfolio::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        return response()->json(UserPortfolioResource::collection($portfolios));
    }

    public function getUserPortfolios($user_id)
    {
        $portfolios = UserPortfolio::where('user_id', $user_id)->orderBy('created_at', 'desc')->get();

        return response()->json(UserPortfolioResource::collection($portfolios));
    }

    public function storePortfolio(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $portfolio = UserPortfolio::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'design_category_id' => $request->design_category_id,
            'design_type_id' => $request->design_type_id,
            'description' => $request->description,
            'duration' => $request->duration,
            'tools_used' => $request->tools_used,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Portfolio created successfully!',
            'portfolio' => new UserPortfolioResource($portfolio),
        ]);
    }

    public function updatePortfolio(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $portfolio_id = $request->portfolio_id;
        $portfolio = UserPortfolio::find($portfolio_id);

        if (!$portfolio || $portfolio->user_id != $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid portfolio!'
            ], 400);
        }

        $portfolio->update($request->except(['portfolio_id', 'user_id']));

        return response()->json([
            'status' => 'success',
            'message' => 'Portfolio updated successfully!',
            'portfolio' => new UserPortfolioResource($portfolio->fresh()),
        ]);
    }

    public function destroyPortfolio(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $portfolio_id = $request->portfolio_id;
        $portfolio = UserPortfolio::find($portfolio_id);

        if (!$portfolio || $portfolio->user_id != $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid portfolio!'
            ], 400);
        }

        UserPortfolioMedia::where('user_portfolio_id', $portfolio->id)->delete();
        $portfolio->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Portfolio deleted successfully!',
        ]);
    }

    public function addPortfolioMedia(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $portfolio_id = $request->portfolio_id;
        $document_type_id = $request->document_type_id;

        $portfolio = UserPortfolio::find($portfolio_id);

        if (!$portfolio || $portfolio->user_id != $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid portfolio!'
            ], 400);
        }

        if (!$request->hasFile('file')) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No file uploaded!'
            ], 400);
        }

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('portfolios', $fileName, 'r2');
        $url = 'portfolios/' . $fileName;

        $media = UserPortfolioMedia::create([
            'user_portfolio_id' => $portfolio->id,
            'document_type_id' => $document_type_id,
            'url' => $url,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Media added successfully!',
            'media' => [
                'id' => $media->id,
                'url' => $media->url,
                'document_type_id' => $media->document_type_id,
                'created_at' => $media->created_at,
            ],
        ]);
    }

    public function removePortfolioMedia(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $media_id = $request->media_id;
        $media = UserPortfolioMedia::find($media_id);

        if (!$media) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid media!'
            ], 400);
        }

        $portfolio = UserPortfolio::find($media->user_portfolio_id);
        if (!$portfolio || $portfolio->user_id != $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized access!'
            ], 403);
        }

        $media->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Media removed successfully!',
        ]);
    }

    public function uploadIdentity(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $path = Storage::disk('r2')->putFile('profiles/identity', $request->file('file'));

        $profile = \App\Models\Profile::where('user_id', $user->id)->first();
        if ($profile) {
            $profile->identity_document = $path;
            $profile->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Identity document uploaded successfully!',
            'path' => $path,
        ]);
    }
}
