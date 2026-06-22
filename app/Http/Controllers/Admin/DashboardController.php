<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Asset;
use App\Models\Template;
use App\Models\CustomizationRequest;
use App\Models\Escrow;
use App\Models\SiteJob;
use App\Models\Wallet;
use App\Models\WalletHistory;
use App\Models\Group;
use App\Models\SubscriptionPlan;
use App\Models\Contact;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers              = User::count();
        $totalAssets             = Asset::count();
        $totalTemplates          = Template::count();
        $totalCustomizationReqs  = CustomizationRequest::count();
        $totalEscrowValue        = Escrow::sum('amount') ?? 0;
        $totalWalletBalance      = Wallet::sum('balance') ?? 0;
        $totalSiteJobs           = SiteJob::count();
        $totalGroups             = Group::count();
        $totalContacts           = Contact::count();
        $totalSubscriptionPlans  = SubscriptionPlan::count();

        // Monthly user registrations (last 12 months)
        $monthlyUsers = $this->getMonthlyData(User::class, 'created_at');

        // Monthly assets uploaded (last 12 months)
        $monthlyAssets = $this->getMonthlyData(Asset::class, 'created_at');

        // Monthly wallet transactions (last 12 months)
        $monthlyWalletTx = $this->getMonthlyData(WalletHistory::class, 'created_at');

        // Recent users
        $recentUsers = User::latest()->take(5)->get();

        // Recent customization requests
        $recentRequests = CustomizationRequest::with(['user', 'customization_status'])->latest()->take(5)->get();

        // Recent wallet histories
        $recentWalletHistories = WalletHistory::with(['wallet.user', 'wallet_history_type'])->latest()->take(5)->get();

        return view('admin.dashboard.index', compact(
            'totalUsers',
            'totalAssets',
            'totalTemplates',
            'totalCustomizationReqs',
            'totalEscrowValue',
            'totalWalletBalance',
            'totalSiteJobs',
            'totalGroups',
            'totalContacts',
            'totalSubscriptionPlans',
            'monthlyUsers',
            'monthlyAssets',
            'monthlyWalletTx',
            'recentUsers',
            'recentRequests',
            'recentWalletHistories'
        ));
    }

    private function getMonthlyData($model, $dateColumn = 'created_at')
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $data[] = $model::whereYear($dateColumn, Carbon::now()->subMonths($i)->year)
                ->whereMonth($dateColumn, Carbon::now()->subMonths($i)->month)
                ->count();
        }
        return $data;
    }
}
