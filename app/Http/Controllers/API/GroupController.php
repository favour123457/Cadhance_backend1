<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\GroupResource;
use App\Http\Resources\GroupSubscriptionResource;
use App\Models\Group;
use App\Models\GroupSubscription;
use App\Models\Notification;
use App\Services\PaymentFulfillmentService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $platform_id = $request->platform_id;

        $groups = Group::where('group_status_id', 1)
            ->when($search, fn($q) => $q->where('title', 'like', "%$search%"))
            ->when($platform_id, fn($q) => $q->where('platform_id', $platform_id))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(GroupResource::collection($groups));
    }

    public function show($id)
    {
        $group = Group::find($id);

        if (!$group) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Group not found!'
            ], 404);
        }

        return response()->json(new GroupResource($group));
    }

    public function myGroups()
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $groups = Group::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(GroupResource::collection($groups));
    }

    public function store(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        // FREE: 1 group lifetime; PRO/FIRM: unlimited
        $check = app(SubscriptionService::class)->canCreateGroup($user);
        if (!$check['allowed']) {
            return response()->json(['status' => 'failed', 'message' => $check['message']], 403);
        }

        $group = Group::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'link' => $request->link,
            'platform_id' => $request->platform_id,
            'group_status_id' => 1,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Group created successfully!',
            'group' => new GroupResource($group),
        ]);
    }

    public function update(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $group_id = $request->group_id;
        $group = Group::find($group_id);

        if (!$group || $group->user_id != $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid group!'
            ], 400);
        }

        $group->update($request->except(['group_id', 'user_id']));

        return response()->json([
            'status' => 'success',
            'message' => 'Group updated successfully!',
            'group' => new GroupResource($group->fresh()),
        ]);
    }

    public function destroy(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $group_id = $request->group_id;
        $group = Group::find($group_id);

        if (!$group || $group->user_id != $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid group!'
            ], 400);
        }

        if (GroupSubscription::where('group_id', $group->id)->count() > 0) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Cannot delete a group with active subscribers.',
            ], 400);
        }

        GroupSubscription::where('group_id', $group->id)->delete();
        $group->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Group deleted successfully!',
        ]);
    }

    /**
     * Subscribe to a group via Flutterwave payment.
     * POST /groups/subscribe?group_id=123&amount=100&currency=NGN
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'group_id' => 'required|integer',
            'amount'   => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
        ]);

        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $group = Group::find($request->group_id);
        if (!$group) {
            return response()->json(['status' => 'failed', 'message' => 'Invalid group!'], 400);
        }

        $exists = GroupSubscription::where('user_id', $user->id)->where('group_id', $group->id)->first();
        if ($exists) {
            return response()->json(['status' => 'failed', 'message' => 'Already subscribed to this group!'], 400);
        }

        $priceUSD = $group->price;
        $currency = strtoupper($request->input('currency', 'USD'));
        $amount   = (float) $request->input('amount', $priceUSD);
        $tx_ref   = 'group_' . $group->id . '_' . $user->id . '_' . \Illuminate\Support\Str::random(12);

        // Create pending subscription
        $subscription = GroupSubscription::create([
            'user_id'           => $user->id,
            'group_id'          => $group->id,
            'subscription_date' => now()->toDateString(),
            'amount_paid'       => $amount,
            'currency'          => $currency,
            'tx_ref'            => $tx_ref,
            'status'            => 'pending',
        ]);

        $flutterwave  = new \App\Services\FlutterwaveService();
        $redirect_url = url('/api/groups/subscribe/callback');

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
            title: 'Group Subscription: ' . $group->title
        );

        if (($response['status'] ?? '') !== 'success') {
            $subscription->delete();
            return response()->json([
                'status'  => 'failed',
                'message' => $response['message'] ?? 'Could not generate payment link.',
            ], 502);
        }

        return response()->json([
            'status'       => 'success',
            'payment_link' => $response['data']['link'],
            'tx_ref'       => $tx_ref,
        ]);
    }

    /**
     * Flutterwave callback for group subscriptions.
     */
    public function subscribeCallback(Request $request)
    {
        $status         = $request->query('status');
        $tx_ref         = $request->query('tx_ref');
        $transaction_id = (int) $request->query('transaction_id');

        if ($status !== 'successful' || !$tx_ref || !$transaction_id) {
            return response()->json(['status' => 'failed', 'message' => 'Payment not completed.'], 400);
        }

        $subscription = GroupSubscription::where('tx_ref', $tx_ref)->first();
        if (!$subscription || $subscription->status === 'completed') {
            return response()->json(['status' => 'success', 'message' => 'Already processed.']);
        }

        $flutterwave = new \App\Services\FlutterwaveService();
        $verification = $flutterwave->verifyTransaction($transaction_id);
        $data = $verification['data'] ?? null;

        if (
            ($verification['status'] ?? '') !== 'success' ||
            ($data['status'] ?? '') !== 'successful' ||
            ($data['tx_ref'] ?? '') !== $tx_ref
        ) {
            $subscription->update(['status' => 'failed']);
            return response()->json(['status' => 'failed', 'message' => 'Payment verification failed.'], 400);
        }

        // Atomically fulfill the subscription (prevents double fulfillment)
        $fulfilled = PaymentFulfillmentService::fulfillGroupSubscription($subscription);

        if ($fulfilled) {
            $group = Group::find($subscription->group_id);
            if ($group) {
                Notification::create([
                    'user_id' => $group->user_id,
                    'title'   => 'New Group Subscriber',
                    'message' => $subscription->user->name . ' subscribed to "' . $group->title . '"!',
                ]);
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Subscribed successfully!']);
    }

    public function unsubscribe(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $group_id = $request->group_id;
        $group = Group::find($group_id);

        if (!$group) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid group!'
            ], 400);
        }

        $subscription = GroupSubscription::where('user_id', $user->id)->where('group_id', $group_id)->first();
        if (!$subscription) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Not subscribed to this group!'
            ], 400);
        }

        $subscription->delete();
        $group->decrement('subscribers_count');

        return response()->json([
            'status' => 'success',
            'message' => 'Unsubscribed from group successfully!',
        ]);
    }

    public function mySubscriptions()
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $subscriptions = GroupSubscription::where('user_id', $user->id)->get();

        return response()->json(GroupSubscriptionResource::collection($subscriptions));
    }
}
