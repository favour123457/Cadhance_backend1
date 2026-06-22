<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminDataResource;
use App\Http\Resources\BankResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\FaqResource;
use App\Http\Resources\GeneralTypeResource;
use App\Http\Resources\GeneralTypeTwoResource;
use App\Http\Resources\NoteResource;
use App\Http\Resources\StateResource;
use App\Http\Resources\SubscriptionPlanResource;
use App\Models\AdminData;
use App\Models\Bank;
use App\Models\Contact;
use App\Models\Country;
use App\Models\CustomizationCategory;
use App\Models\CustomizationStatus;
use App\Models\DesignCategory;
use App\Models\DesignType;
use App\Models\DocumentType;
use App\Models\Faq;
use App\Models\GroupStatus;
use App\Models\LicenseType;
use App\Models\Note;
use App\Models\NoteType;
use App\Models\NotificationType;
use App\Models\OtpType;
use App\Models\Platform;
use App\Models\PrimaryRole;
use App\Models\Skill;
use App\Models\SiteJobStatus;
use App\Models\State;
use App\Models\SubscriptionPlan;
use App\Models\TemplateStatus;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Asset;
use App\Models\Escrow;
use App\Models\Group;
use App\Models\Template;
use App\Models\WalletHistory;
use App\Models\WalletHistoryStatus;
use App\Models\WalletHistoryType;
use Tymon\JWTAuth\Facades\JWTAuth;

class DashboardController extends Controller
{
    public function contactus(Request $request)
    {
        $name = $request->name;
        $email = $request->email;
        $phone = $request->phone;
        $subject = $request->subject;
        $message = $request->message;

        Contact::create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject,
            'message' => $message,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Your message has been sent successfully!',
        ]);
    }

    public function faqs()
    {
        $faqs = Faq::all();

        return response()->json(FaqResource::collection($faqs));
    }

    public function note(Request $request)
    {
        $note_type_id = $request->note_type_id;

        $note = Note::where('note_type_id', $note_type_id)->first();

        return response()->json(new NoteResource($note));
    }

    public function getStates($country_id)
    {
        $states = State::where('country_id', $country_id)->get();

        return response()->json(StateResource::collection($states));
    }

    public function getAllSettings()
    {
        $faqs = Faq::all();
        $note_types = NoteType::all();
        $otp_types = OtpType::all();
        $notes = Note::all();
        $notification_types = NotificationType::all();
        $countries = Country::all();
        $user_types = UserType::all();
        $admin_datas = AdminData::all();
        $bank = Bank::all();
        $design_categories = DesignCategory::all();
        $design_types = DesignType::all();
        $primary_roles = PrimaryRole::all();
        $skills = Skill::all();
        $platforms = Platform::all();
        $license_types = LicenseType::all();
        $template_statuses = TemplateStatus::all();
        $group_statuses = GroupStatus::all();
        $site_job_statuses = SiteJobStatus::all();
        $customization_statuses = CustomizationStatus::all();
        $customization_categories = CustomizationCategory::all();
        $document_types = DocumentType::all();
        $subscription_plans = SubscriptionPlan::where('active', true)->get();
        $wallet_history_types = WalletHistoryType::all();
        $wallet_history_statuses = WalletHistoryStatus::all();

        return response()->json([
            'faqs' => FaqResource::collection($faqs),
            'note_types' => GeneralTypeResource::collection($note_types),
            'otp_types' => GeneralTypeResource::collection($otp_types),
            'notes' => NoteResource::collection($notes),
            'notification_types' => GeneralTypeResource::collection($notification_types),
            'countries' => CountryResource::collection($countries),
            'user_types' => GeneralTypeTwoResource::collection($user_types),
            'admin_datas' => AdminDataResource::collection($admin_datas),
            'banks' => BankResource::collection($bank),
            'design_categories' => GeneralTypeTwoResource::collection($design_categories),
            'design_types' => GeneralTypeTwoResource::collection($design_types),
            'primary_roles' => GeneralTypeTwoResource::collection($primary_roles),
            'skills' => GeneralTypeResource::collection($skills),
            'platforms' => GeneralTypeResource::collection($platforms),
            'license_types' => GeneralTypeTwoResource::collection($license_types),
            'template_statuses' => GeneralTypeResource::collection($template_statuses),
            'group_statuses' => GeneralTypeResource::collection($group_statuses),
            'site_job_statuses' => GeneralTypeResource::collection($site_job_statuses),
            'customization_statuses' => GeneralTypeResource::collection($customization_statuses),
            'customization_categories' => GeneralTypeTwoResource::collection($customization_categories),
            'document_types' => GeneralTypeResource::collection($document_types),
            'subscription_plans' => SubscriptionPlanResource::collection($subscription_plans),
            'wallet_history_types' => GeneralTypeTwoResource::collection($wallet_history_types),
            'wallet_history_statuses' => GeneralTypeTwoResource::collection($wallet_history_statuses),
        ]);
    }

    public function analytics()
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $wallet = $user->wallet;

        // Stats
        $totalEarned = WalletHistory::where('wallet_id', $wallet->id)
            ->where('wallet_history_type_id', 1)
            ->where('wallet_history_status_id', 2)
            ->sum('amount');

        $totalWithdrawn = WalletHistory::where('wallet_id', $wallet->id)
            ->where('wallet_history_type_id', 2)
            ->sum('amount');

        $inEscrow = Escrow::where('user_id', $user->id)->sum('amount');

        // Chart: last 30 days earnings grouped by date
        $chart = WalletHistory::where('wallet_id', $wallet->id)
            ->where('wallet_history_type_id', 1)
            ->where('wallet_history_status_id', 2)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(amount) as amount')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top selling assets (user's own, by revenue from purchases)
        $topAssets = Asset::where('assets.user_id', $user->id)
            ->leftJoin(
                DB::raw('(SELECT purchasable_id, SUM(amount_paid) as total_revenue FROM user_purchases WHERE purchasable_type = \'asset\' GROUP BY purchasable_id) as up'),
                'assets.id', '=', 'up.purchasable_id'
            )
            ->select('assets.id', 'assets.title', 'assets.thumbnail', 'assets.purchase_count', DB::raw('COALESCE(up.total_revenue, 0) as revenue'))
            ->orderByDesc('revenue')
            ->take(5)
            ->get()
            ->values()
            ->map(function ($asset, $index) {
                return [
                    'rank'    => $index + 1,
                    'id'      => $asset->id,
                    'name'    => $asset->title,
                    'revenue' => (float) $asset->revenue,
                    'sales'   => (int) $asset->purchase_count,
                    'img'     => $asset->thumbnail ? Storage::disk('r2')->url($asset->thumbnail) : null,
                ];
            });

        // All assets (user's own, ordered by revenue)
        $allAssets = Asset::where('assets.user_id', $user->id)
            ->leftJoin(
                DB::raw('(SELECT purchasable_id, SUM(amount_paid) as total_revenue FROM user_purchases WHERE purchasable_type = \'asset\' GROUP BY purchasable_id) as up2'),
                'assets.id', '=', 'up2.purchasable_id'
            )
            ->select('assets.id', 'assets.title', 'assets.thumbnail', 'assets.purchase_count', DB::raw('COALESCE(up2.total_revenue, 0) as revenue'))
            ->orderByDesc('revenue')
            ->take(10)
            ->get()
            ->values()
            ->map(function ($asset, $index) {
                return [
                    'rank'    => $index + 1,
                    'id'      => $asset->id,
                    'name'    => $asset->title,
                    'revenue' => (float) $asset->revenue,
                    'sales'   => (int) $asset->purchase_count,
                    'img'     => $asset->thumbnail ? Storage::disk('r2')->url($asset->thumbnail) : null,
                ];
            });

        // Top selling templates (user's own, by revenue)
        $topTemplates = Template::where('templates.user_id', $user->id)
            ->leftJoin(
                DB::raw('(SELECT purchasable_id, SUM(amount_paid) as total_revenue FROM user_purchases WHERE purchasable_type = \'template\' GROUP BY purchasable_id) as tp'),
                'templates.id', '=', 'tp.purchasable_id'
            )
            ->select('templates.id', 'templates.title', 'templates.thumbnail', 'templates.purchase_count', DB::raw('COALESCE(tp.total_revenue, 0) as revenue'))
            ->orderByDesc('revenue')
            ->take(5)
            ->get()
            ->values()
            ->map(function ($template, $index) {
                return [
                    'rank'    => $index + 1,
                    'id'      => $template->id,
                    'name'    => $template->title,
                    'revenue' => (float) $template->revenue,
                    'sales'   => (int) $template->purchase_count,
                    'img'     => $template->thumbnail ? Storage::disk('r2')->url($template->thumbnail) : null,
                ];
            });

        // Top groups (user's own, by subscribers count)
        $topGroups = Group::where('user_id', $user->id)
            ->orderByDesc('subscribers_count')
            ->take(5)
            ->get()
            ->values()
            ->map(function ($group, $index) {
                return [
                    'rank'               => $index + 1,
                    'id'                 => $group->id,
                    'name'               => $group->title,
                    'subscribers_count'  => (int) $group->subscribers_count,
                    'platform'           => $group->platform?->name,
                ];
            });

        return response()->json([
            'stats' => [
                'current_balance' => (float) $wallet->balance,
                'total_earned'    => (float) $totalEarned,
                'total_withdrawn' => (float) $totalWithdrawn,
                'in_escrow'       => (float) $inEscrow,
            ],
            'chart'         => $chart,
            'top_assets'    => $topAssets,
            'all_assets'    => $allAssets,
            'top_templates' => $topTemplates,
            'top_groups'    => $topGroups,
        ]);
    }
}
