<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionType;
use Illuminate\Http\Request;

class SubscriptionPlansController extends Controller
{
    public function index()
    {
        $subscriptionPlans = SubscriptionPlan::with('subscription_type')->latest()->get();
        return view('admin.subscription-plans.index', compact('subscriptionPlans'));
    }

    public function create()
    {
        $subscriptionTypes = SubscriptionType::all();
        return view('admin.subscription-plans.add', compact('subscriptionTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                 => 'required|string|max:255',
            'monthly_price'        => 'required|numeric|min:0',
            'annual_price'         => 'required|numeric|min:0',
            'subscription_type_id' => 'required|exists:subscription_types,id',
        ]);

        SubscriptionPlan::create(array_merge(
            $request->only(['name', 'subscription_type_id', 'description', 'duration_days']),
            [
                'monthly_price' => $request->monthly_price,
                'annual_price'  => $request->annual_price,
                'price'         => $request->monthly_price, // keep legacy field in sync
            ]
        ));

        flash()->success('Subscription plan created successfully.');
        return redirect()->route('subscription-plans.index');
    }

    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionTypes = SubscriptionType::all();
        return view('admin.subscription-plans.edit', compact('subscriptionPlan', 'subscriptionTypes'));
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $request->validate([
            'name'                 => 'required|string|max:255',
            'monthly_price'        => 'required|numeric|min:0',
            'annual_price'         => 'required|numeric|min:0',
            'subscription_type_id' => 'required|exists:subscription_types,id',
        ]);

        $subscriptionPlan->update(array_merge(
            $request->only(['name', 'subscription_type_id', 'description', 'duration_days']),
            [
                'monthly_price' => $request->monthly_price,
                'annual_price'  => $request->annual_price,
                'price'         => $request->monthly_price, // keep legacy field in sync
                'active'        => $request->boolean('active'),
            ]
        ));

        flash()->success('Subscription plan updated successfully.');
        return redirect()->route('subscription-plans.index');
    }

    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->delete();
        flash()->success('Subscription plan deleted successfully.');
        return redirect()->route('subscription-plans.index');
    }
}
