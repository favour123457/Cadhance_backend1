<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\CustomizationRequest;
use App\Models\Group;
use App\Models\Template;
use App\Models\User;
use App\Models\UserSubscription;

class SubscriptionService
{
    /**
     * Get the user's current active, non-expired subscription.
     */
    public function getActiveSubscription(User $user): ?UserSubscription
    {
        return UserSubscription::where('user_id', $user->id)
            ->where('active', true)
            ->where('expire_at', '>=', now()->toDateString())
            ->with('subscription_plan.subscription_type')
            ->first();
    }

    /**
     * Returns 'free', 'pro', or 'firm'.
     */
    public function getSubscriptionType(User $user): string
    {
        $sub = $this->getActiveSubscription($user);
        if (!$sub) {
            return 'free';
        }

        $typeName = strtolower($sub->subscription_plan->subscription_type->name ?? '');

        if ($typeName === 'pro') {
            return 'pro';
        }
        if ($typeName === 'firm') {
            return 'firm';
        }

        return 'free';
    }

    public function isPro(User $user): bool
    {
        return $this->getSubscriptionType($user) === 'pro';
    }

    public function isFirm(User $user): bool
    {
        return $this->getSubscriptionType($user) === 'firm';
    }

    public function isFree(User $user): bool
    {
        return $this->getSubscriptionType($user) === 'free';
    }

    public function hasActiveSubscription(User $user): bool
    {
        return !$this->isFree($user);
    }

    /**
     * Asset uploads:
     *   FREE  → 10 per month
     *   PRO   → unlimited
     *   FIRM  → unlimited
     */
    public function canUploadAsset(User $user): array
    {
        return ['allowed' => true, 'used' => 0, 'limit' => 999, 'remaining' => 999];
    }

    /**
     * Group creation:
     *   FREE  → 1 lifetime
     *   PRO   → unlimited
     *   FIRM  → unlimited
     */
    public function canCreateGroup(User $user): array
    {
        return ['allowed' => true, 'used' => 0, 'limit' => 999, 'remaining' => 999];
    }

    /**
     * Template marketplace uploads:
     *   FREE  → 10 per month
     *   PRO   → unlimited
     *   FIRM  → unlimited
     */
    public function canUploadTemplate(User $user): array
    {
        return ['allowed' => true, 'used' => 0, 'limit' => 999, 'remaining' => 999];
    }

    /**
     * Customization profile signals / applications:
     *   FREE  → 1 lifetime
     *   PRO   → 20 per month
     *   FIRM  → unlimited
     */
    public function canSendCustomizationRequest(User $user): array
    {
        return ['allowed' => true, 'used' => 0, 'limit' => 999, 'remaining' => 999];
    }

    /**
     * Advanced upload tools (embedded, prototype, etc.):
     *   FREE  → not allowed
     *   PRO   → allowed
     *   FIRM  → allowed
     */
    public function canUseAdvancedTools(User $user): bool
    {
        return true;
    }

    /**
     * Social links on profile:
     *   FREE  → hidden
     *   PRO   → visible
     *   FIRM  → visible
     */
    public function hasSocialLinksVisible(User $user): bool
    {
        return true;
    }

    /**
     * Returns the badge slug for the user ('pro', 'firm', or null).
     */
    public function getBadge(User $user): ?string
    {
        $type = $this->getSubscriptionType($user);

        if ($type === 'pro') {
            return 'pro';
        }
        if ($type === 'firm') {
            return 'firm';
        }

        return null;
    }

    /**
     * Top Firms landing page slideshare:
     *   Only FIRM subscribers are eligible.
     */
    public function isTopFirmEligible(User $user): bool
    {
        return $this->isFirm($user);
    }

    /**
     * Full access summary for the authenticated user.
     */
    public function getAccessInfo(User $user): array
    {
        $type = $this->getSubscriptionType($user);

        return [
            'subscription_type'      => $type,
            'badge'                  => $this->getBadge($user),
            'can_use_advanced_tools' => $this->canUseAdvancedTools($user),
            'social_links_visible'   => $this->hasSocialLinksVisible($user),
            'is_top_firm_eligible'   => $this->isTopFirmEligible($user),
            'asset_uploads'          => $this->canUploadAsset($user),
            'group_creations'        => $this->canCreateGroup($user),
            'template_uploads'       => $this->canUploadTemplate($user),
            'customization_requests' => $this->canSendCustomizationRequest($user),
        ];
    }
}
