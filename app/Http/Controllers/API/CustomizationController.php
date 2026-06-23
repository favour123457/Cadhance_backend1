<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomizationChatMessageResource;
use App\Http\Resources\CustomizationChatResource;
use App\Http\Resources\CustomizationRequestResource;
use App\Http\Resources\CustomizationRevisionResource;
use App\Models\Asset;
use App\Models\CustomizationChat;
use App\Models\CustomizationChatMessage;
use App\Models\CustomizationMilestone;
use App\Models\CustomizationPriceAdjustment;
use App\Models\CustomizationRequest;
use App\Models\CustomizationRevision;
use App\Models\Notification;
use App\Models\Template;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Events\ChatMessageSent;

class CustomizationController extends Controller
{
    public function myReceivedRequests()
    {
        $user = $this->authenticatedUser();

        $requests = CustomizationRequest::where('designer_id', $user->id)
            ->with(['asset.user', 'template.user', 'user', 'designer', 'customization_status', 'milestones', 'price_adjustments', 'accepted_price_adjustment', 'chat.messages.sender_user', 'revisions'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(CustomizationRequestResource::collection($requests));
    }

    public function mySentRequests()
    {
        $user = $this->authenticatedUser();

        $requests = CustomizationRequest::where('user_id', $user->id)
            ->with(['asset.user', 'template.user', 'user', 'designer', 'customization_status', 'milestones', 'price_adjustments', 'accepted_price_adjustment', 'chat.messages.sender_user', 'revisions'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(CustomizationRequestResource::collection($requests));
    }

    public function show($id)
    {
        $user = $this->authenticatedUser();

        $customization = CustomizationRequest::with(['asset.user', 'template.user', 'user', 'designer', 'customization_status', 'milestones', 'price_adjustments', 'accepted_price_adjustment', 'chat.messages.sender_user', 'revisions'])
            ->find($id);

        if (!$customization || !$this->isParticipant($customization, $user->id)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid customization request!'
            ], 403);
        }

        return response()->json(new CustomizationRequestResource($customization));
    }

    public function store(Request $request)
    {
        $user = $this->authenticatedUser();

        $assetId = $request->asset_id;
        $templateId = $request->template_id;

        if ((!$assetId && !$templateId) || ($assetId && $templateId)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Provide either asset_id or template_id.'
            ], 400);
        }

        $asset = null;
        $template = null;
        $designerId = null;
        $basePrice = 0;

        if ($assetId) {
            $asset = Asset::find($assetId);

            if (!$asset) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid asset!'
                ], 400);
            }

            if (!$asset->customization_available) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'This asset does not offer customization!'
                ], 400);
            }

            $designerId = $asset->user_id;
            $basePrice = (float) $asset->customization_price;
        }

        if ($templateId) {
            $template = Template::find($templateId);
            if (!$template) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid template!'
                ], 400);
            }

            $designerId = $template->user_id;
            $basePrice = (float) $template->price;
        }

        // Allow optional override of designer_id (e.g., a firm assigning a different worker)
        if ($request->filled('designer_id')) {
            $overrideId = (int) $request->designer_id;
            $designerUser = \App\Models\User::find($overrideId);
            if ($designerUser) {
                $designerId = $overrideId;
            }
        }

        if ($designerId === $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'You cannot create a customization request for your own item.'
            ], 400);
        }

        // Check customization request limits:
        //   FREE → 1 lifetime | PRO → 20/month | FIRM → unlimited
        $limitCheck = app(SubscriptionService::class)->canSendCustomizationRequest($user);
        if (!$limitCheck['allowed']) {
            return response()->json(['status' => 'failed', 'message' => $limitCheck['message']], 403);
        }

        $customization = CustomizationRequest::create([
            'asset_id' => $assetId ?? 0,
            'template_id' => $templateId,
            'user_id' => $user->id,
            'designer_id' => $designerId,
            'description' => $request->description,
            'reason' => $request->reason,
            'price' => $request->price ?? $basePrice,
            'final_price' => null,
            'customization_status_id' => 1,
        ]);

        $chat = $this->createChatForCustomization($customization);
        $itemTitle = $asset?->title ?? $template?->title ?? 'Customization';
        $chat->messages()->create([
            'sender_user_id' => $user->id,
            'message_type' => 'customization_request',
            'message' => 'Customization request started.',
            'meta' => json_encode([
                'title' => $itemTitle,
                'milestone' => '',
                'status' => 'Pending',
                'description' => $customization->description,
                'reason' => $customization->reason,
                'price' => $customization->price,
            ]),
        ]);
        $chat->update(['last_message_at' => now()]);

        $itemTitle = $asset?->title ?? $template?->title ?? 'item';

        Notification::create([
            'user_id' => $designerId,
            'title' => 'New Customization Request',
            'message' => $user->name . ' sent a customization request for "' . $itemTitle . '"!',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Customization request sent successfully!',
            'customization' => new CustomizationRequestResource($customization->fresh(['asset.user', 'template.user', 'user', 'designer', 'customization_status', 'milestones', 'price_adjustments', 'accepted_price_adjustment', 'chat.messages.sender_user', 'revisions'])),
            'chat' => new CustomizationChatResource($chat->fresh(['asset', 'template', 'client_user', 'owner_user', 'messages.sender_user'])),
        ]);
    }

    public function updateStatus(Request $request)
    {
        $user = $this->authenticatedUser();

        $customization_id = $request->customization_id;
        $customization_status_id = $request->customization_status_id;

        $customization = CustomizationRequest::find($customization_id);

        if (!$customization || !$this->isParticipant($customization, $user->id)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid customization request!'
            ], 403);
        }

        $customization->update(['customization_status_id' => $customization_status_id]);
        $customization->load('customization_status');

        $chat = $this->createChatForCustomization($customization);
        $itemTitle = $customization->asset?->title ?? $customization->template?->title ?? 'Customization';
        $statusName = $customization->customization_status?->name ?? 'Pending';

        // Keep previous customization request messages in sync with the latest status
        CustomizationChatMessage::where('customization_chat_id', $chat->id)
            ->where('message_type', 'customization_request')
            ->get()
            ->each(function ($msg) use ($statusName) {
                $meta = json_decode($msg->meta ?? '{}', true);
                $meta['status'] = $statusName;
                $msg->update(['meta' => json_encode($meta)]);
            });

        $chat->messages()->create([
            'sender_user_id' => $user->id,
            'message_type' => 'customization_request',
            'message' => 'Customization status changed.',
            'meta' => json_encode([
                'title' => $itemTitle,
                'milestone' => '',
                'status' => $statusName,
                'customization_status_id' => $customization_status_id,
                'reason' => $request->reason,
            ]),
        ]);
        $chat->update(['last_message_at' => now()]);

        Notification::create([
            'user_id' => $user->id == $customization->user_id ? $customization->designer_id : $customization->user_id,
            'title' => 'Customization Request Update',
            'message' => 'Your customization request status has been updated!',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Customization request status updated successfully!',
            'customization' => new CustomizationRequestResource($customization->fresh(['asset.user', 'template.user', 'user', 'designer', 'customization_status', 'milestones', 'price_adjustments', 'accepted_price_adjustment', 'chat.messages.sender_user', 'revisions'])),
        ]);
    }

    public function addMilestone(Request $request)
    {
        $user = $this->authenticatedUser();

        $customization_id = $request->customization_id;
        $customization = CustomizationRequest::find($customization_id);

        if (!$customization || !$this->isParticipant($customization, $user->id)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid customization request!'
            ], 403);
        }

        $milestone = CustomizationMilestone::create([
            'customization_request_id' => $customization_id,
            'title' => $request->title,
            'price' => $request->price,
        ]);

        $chat = $this->createChatForCustomization($customization);
        $chat->messages()->create([
            'sender_user_id' => $user->id,
            'message_type' => 'milestone',
            'message' => 'Milestone added.',
            'meta' => json_encode([
                'title' => $request->title,
                'file' => '',
                'description' => 'Price: ' . $request->price,
                'status' => 'Release Needed',
                'price' => $request->price,
            ]),
        ]);
        $chat->update(['last_message_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Milestone added successfully!',
            'milestone' => new \App\Http\Resources\CustomizationMilestoneResource($milestone),
        ]);
    }

    public function removeMilestone(Request $request)
    {
        $user = $this->authenticatedUser();

        $milestone_id = $request->milestone_id;
        $milestone = CustomizationMilestone::find($milestone_id);

        if (!$milestone) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid milestone!'
            ], 400);
        }

        $customization = CustomizationRequest::find($milestone->customization_request_id);
        if (!$customization || !$this->isParticipant($customization, $user->id)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized access!'
            ], 403);
        }

        $milestone->delete();

        $chat = $this->createChatForCustomization($customization);
        $chat->messages()->create([
            'sender_user_id' => $user->id,
            'message_type' => 'system',
            'message' => 'Milestone removed.',
            'meta' => json_encode([
                'milestone_id' => $milestone_id,
            ]),
        ]);
        $chat->update(['last_message_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Milestone removed successfully!',
        ]);
    }

    // Backward-compatible endpoint. It now creates a pending adjustment request.
    public function addPriceAdjustment(Request $request)
    {
        $user = $this->authenticatedUser();

        $customization_id = $request->customization_id;
        $customization = CustomizationRequest::find($customization_id);

        if (!$customization || !$this->isParticipant($customization, $user->id)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid customization request!'
            ], 403);
        }

        $adjustment = CustomizationPriceAdjustment::create([
            'customization_request_id' => $customization_id,
            'price' => $request->amount,
            'reason' => $request->reason,
            'requested_by_user_id' => $user->id,
            'status' => 'pending',
        ]);

        $itemTitle = $customization->asset?->title ?? $customization->template?->title ?? 'Customization';
        $chat = $this->createChatForCustomization($customization);
        $chat->messages()->create([
            'sender_user_id' => $user->id,
            'message_type' => 'price_adjustment',
            'message' => 'Price adjustment requested.',
            'meta' => json_encode([
                'price_adjustment_id' => $adjustment->id,
                'title' => $itemTitle,
                'milestone' => '',
                'proposedAmount' => '$' . $request->amount,
                'amount' => $request->amount,
                'reason' => $request->reason,
                'status' => 'Pending',
            ]),
        ]);
        $chat->update(['last_message_at' => now()]);

        Notification::create([
            'user_id' => $user->id == $customization->user_id ? $customization->designer_id : $customization->user_id,
            'title' => 'Price Adjustment',
            'message' => 'A price adjustment of ' . $request->amount . ' has been requested for your customization request!',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Price adjustment request submitted successfully!',
            'adjustment' => new \App\Http\Resources\CustomizationPriceAdjustmentResource($adjustment),
        ]);
    }

    public function chat($customizationId)
    {
        $user = $this->authenticatedUser();

        $customization = CustomizationRequest::find($customizationId);
        if (!$customization || !$this->isParticipant($customization, $user->id)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid customization request!'
            ], 403);
        }

        $chat = $this->createChatForCustomization($customization)
            ->fresh(['asset', 'template', 'client_user', 'owner_user', 'messages.sender_user']);

        return response()->json(new CustomizationChatResource($chat));
    }

    public function sendMessage(Request $request)
    {
        $user = $this->authenticatedUser();

        $customization = CustomizationRequest::find($request->customization_id);
        if (!$customization || !$this->isParticipant($customization, $user->id)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid customization request!'
            ], 403);
        }

        if (!$request->filled('message') && !$request->hasFile('file')) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Please provide a message or attach a file.'
            ], 400);
        }

        $chat = $this->createChatForCustomization($customization);

        $attachment = null;
        $attachmentName = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('customizations/chat', $fileName, 'r2');
            $attachment = 'customizations/chat/' . $fileName;
            $attachmentName = $file->getClientOriginalName();
        }

        $messageType = $request->message_type ?: ($attachment ? 'file' : 'text');

        $meta = $request->meta;
        if (!$meta && in_array($messageType, ['deliverable', 'milestone', 'revision_request'])) {
            $itemTitle = $customization->asset?->title ?? $customization->template?->title ?? 'Customization';
            if ($messageType === 'deliverable') {
                $meta = json_encode([
                    'file' => $attachmentName ?? '',
                    'description' => $request->message ?? '',
                    'milestone' => '',
                    'status' => 'Under review',
                ]);
            } elseif ($messageType === 'milestone') {
                $meta = json_encode([
                    'title' => $request->message ?? 'Milestone',
                    'file' => $attachmentName ?? '',
                    'description' => '',
                    'status' => 'Release Needed',
                ]);
            } elseif ($messageType === 'revision_request') {
                $meta = json_encode([
                    'file' => $attachmentName ?? '',
                    'note' => $request->message ?? '',
                    'milestone' => '',
                ]);
            }
        }

        $message = CustomizationChatMessage::create([
            'customization_chat_id' => $chat->id,
            'sender_user_id' => $user->id,
            'message_type' => $messageType,
            'message' => $request->message,
            'attachment' => $attachment,
            'attachment_name' => $attachmentName,
            'meta' => $meta,
        ]);

        // Broadcast synchronously so it works on shared hosting without a queue worker.
        try {
            broadcast(new ChatMessageSent($message));
            \Log::info('Chat message broadcasted', ['message_id' => $message->id, 'chat_id' => $chat->id]);
        } catch (\Exception $e) {
            \Log::error('Chat message broadcast failed', ['error' => $e->getMessage(), 'message_id' => $message->id]);
        }

        $chat->update(['last_message_at' => now()]);

        Notification::create([
            'user_id' => $user->id == $customization->user_id ? $customization->designer_id : $customization->user_id,
            'title' => 'New Message',
            'message' => $user->name . ' sent you a message!',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Message sent successfully!',
            'chat_message' => new CustomizationChatMessageResource($message->fresh('sender_user')),
        ]);
    }

    // Client requests a price adjustment, owner accepts/rejects it.
    public function requestPriceAdjustment(Request $request)
    {
        $user = $this->authenticatedUser();

        $customization = CustomizationRequest::find($request->customization_id);
        if (!$customization || !$this->isParticipant($customization, $user->id)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid customization request!'
            ], 403);
        }

        if ((int) $customization->user_id !== (int) $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Only the client can request a price adjustment.'
            ], 403);
        }

        $adjustment = CustomizationPriceAdjustment::create([
            'customization_request_id' => $customization->id,
            'price' => $request->amount,
            'reason' => $request->reason,
            'requested_by_user_id' => $user->id,
            'status' => 'pending',
            'is_final' => false,
        ]);

        $itemTitle = $customization->asset?->title ?? $customization->template?->title ?? 'Customization';
        $chat = $this->createChatForCustomization($customization);
        $chat->messages()->create([
            'sender_user_id' => $user->id,
            'message_type' => 'price_adjustment',
            'message' => 'Client requested a price adjustment.',
            'meta' => json_encode([
                'price_adjustment_id' => $adjustment->id,
                'title' => $itemTitle,
                'milestone' => '',
                'proposedAmount' => '$' . $adjustment->price,
                'amount' => $adjustment->price,
                'reason' => $adjustment->reason,
                'status' => 'Pending',
            ]),
        ]);
        $chat->update(['last_message_at' => now()]);

        Notification::create([
            'user_id' => $customization->designer_id,
            'title' => 'Price Adjustment Requested',
            'message' => 'A new price adjustment request has been submitted by the client.',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Price adjustment request submitted.',
            'adjustment' => new \App\Http\Resources\CustomizationPriceAdjustmentResource($adjustment),
        ]);
    }

    public function respondPriceAdjustment(Request $request)
    {
        $user = $this->authenticatedUser();

        $status = strtolower((string) $request->status);
        if (!in_array($status, ['accepted', 'rejected'])) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Status must be accepted or rejected.'
            ], 400);
        }

        $adjustment = CustomizationPriceAdjustment::find($request->price_adjustment_id);
        if (!$adjustment) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid price adjustment.'
            ], 400);
        }

        $customization = CustomizationRequest::find($adjustment->customization_request_id);
        if (!$customization || !$this->isParticipant($customization, $user->id)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid customization request!'
            ], 403);
        }

        if ((int) $customization->designer_id !== (int) $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Only the owner can accept or reject a price adjustment.'
            ], 403);
        }

        if ($adjustment->status !== 'pending') {
            return response()->json([
                'status' => 'failed',
                'message' => 'This price adjustment has already been decided.'
            ], 400);
        }

        $adjustment->update([
            'status' => $status,
            'decision_reason' => $request->reason,
            'responded_by_user_id' => $user->id,
            'responded_at' => now(),
            'is_final' => $status === 'accepted',
        ]);

        if ($status === 'accepted') {
            CustomizationPriceAdjustment::where('customization_request_id', $customization->id)
                ->where('id', '!=', $adjustment->id)
                ->update(['is_final' => false]);

            $customization->update([
                'price' => $adjustment->price,
                'final_price' => $adjustment->price,
                'accepted_price_adjustment_id' => $adjustment->id,
            ]);
        }

        $itemTitle = $customization->asset?->title ?? $customization->template?->title ?? 'Customization';
        $chat = $this->createChatForCustomization($customization);
        $statusName = ucfirst($status);

        // Keep the original price adjustment request message in sync
        CustomizationChatMessage::where('customization_chat_id', $chat->id)
            ->where('message_type', 'price_adjustment')
            ->get()
            ->each(function ($msg) use ($adjustment, $statusName) {
                $meta = json_decode($msg->meta ?? '{}', true);
                if (($meta['price_adjustment_id'] ?? null) == $adjustment->id) {
                    $meta['status'] = $statusName;
                    $msg->update(['meta' => json_encode($meta)]);
                }
            });

        $chat->messages()->create([
            'sender_user_id' => $user->id,
            'message_type' => 'price_adjustment',
            'message' => 'Price adjustment has been ' . $status . '.',
            'meta' => json_encode([
                'price_adjustment_id' => $adjustment->id,
                'title' => $itemTitle,
                'milestone' => '',
                'proposedAmount' => '$' . $adjustment->price,
                'amount' => $adjustment->price,
                'reason' => $adjustment->reason,
                'status' => $statusName,
                'decision_reason' => $request->reason,
                'final_price' => $customization->final_price,
            ]),
        ]);
        $chat->update(['last_message_at' => now()]);

        Notification::create([
            'user_id' => $customization->user_id,
            'title' => 'Price Adjustment ' . ucfirst($status),
            'message' => 'Your price adjustment request has been ' . $status . '.',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Price adjustment ' . $status . ' successfully.',
            'adjustment' => new \App\Http\Resources\CustomizationPriceAdjustmentResource($adjustment->fresh()),
            'customization' => new CustomizationRequestResource($customization->fresh(['asset.user', 'template.user', 'user', 'designer', 'customization_status', 'milestones', 'price_adjustments', 'accepted_price_adjustment', 'chat.messages.sender_user', 'revisions'])),
        ]);
    }

    // New endpoint: client requests a revision with optional note and file.
    public function requestRevision(Request $request)
    {
        $user = $this->authenticatedUser();

        $customization = CustomizationRequest::find($request->customization_id);
        if (!$customization || !$this->isParticipant($customization, $user->id)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid customization request!'
            ], 403);
        }

        if ((int) $customization->user_id !== (int) $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Only the client can request a revision.'
            ], 403);
        }

        if (!$request->filled('note') && !$request->hasFile('file')) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Please provide a revision note or attach a file.'
            ], 400);
        }

        $attachment = null;
        $attachmentName = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('customizations/revisions', $fileName, 'r2');
            $attachment = 'customizations/revisions/' . $fileName;
            $attachmentName = $file->getClientOriginalName();
        }

        $revision = CustomizationRevision::create([
            'customization_request_id' => $customization->id,
            'requested_by_user_id' => $user->id,
            'note' => $request->note,
            'attachment' => $attachment,
            'attachment_name' => $attachmentName,
            'status' => 'pending',
        ]);

        $chat = $this->createChatForCustomization($customization);
        $chat->messages()->create([
            'sender_user_id' => $user->id,
            'message_type' => 'revision_request',
            'message' => 'Revision requested by client.',
            'attachment' => $attachment,
            'attachment_name' => $attachmentName,
            'meta' => json_encode([
                'revision_id' => $revision->id,
                'file' => $attachmentName ?? '',
                'note' => $revision->note,
                'milestone' => '',
            ]),
        ]);
        $chat->update(['last_message_at' => now()]);

        Notification::create([
            'user_id' => $customization->designer_id,
            'title' => 'Revision Requested',
            'message' => 'A client requested a revision on an active customization.',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Revision request sent successfully.',
            'revision' => new CustomizationRevisionResource($revision->fresh(['requested_by_user'])),
        ]);
    }

    public function respondRevision(Request $request)
    {
        $user = $this->authenticatedUser();

        $status = strtolower((string) $request->status);
        if (!in_array($status, ['accepted', 'rejected'])) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Status must be accepted or rejected.'
            ], 400);
        }

        $revision = CustomizationRevision::find($request->revision_id);
        if (!$revision) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid revision request.'
            ], 400);
        }

        $customization = CustomizationRequest::find($revision->customization_request_id);
        if (!$customization || !$this->isParticipant($customization, $user->id)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid customization request!'
            ], 403);
        }

        if ((int) $customization->designer_id !== (int) $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Only the owner can respond to revision requests.'
            ], 403);
        }

        if ($revision->status !== 'pending') {
            return response()->json([
                'status' => 'failed',
                'message' => 'This revision request has already been decided.'
            ], 400);
        }

        $revision->update([
            'status' => $status,
            'decision_reason' => $request->reason,
            'responded_by_user_id' => $user->id,
            'responded_at' => now(),
        ]);

        $chat = $this->createChatForCustomization($customization);
        $statusName = ucfirst($status);

        // Keep the original revision request message in sync
        CustomizationChatMessage::where('customization_chat_id', $chat->id)
            ->where('message_type', 'revision_request')
            ->get()
            ->each(function ($msg) use ($revision, $statusName) {
                $meta = json_decode($msg->meta ?? '{}', true);
                if (($meta['revision_id'] ?? null) == $revision->id) {
                    $meta['status'] = $statusName;
                    $msg->update(['meta' => json_encode($meta)]);
                }
            });

        $chat->messages()->create([
            'sender_user_id' => $user->id,
            'message_type' => 'revision_request',
            'message' => 'Revision request has been ' . $status . '.',
            'meta' => json_encode([
                'revision_id' => $revision->id,
                'file' => $revision->attachment_name ?? '',
                'note' => $revision->note,
                'milestone' => '',
                'status' => $statusName,
                'reason' => $request->reason,
            ]),
        ]);
        $chat->update(['last_message_at' => now()]);

        Notification::create([
            'user_id' => $customization->user_id,
            'title' => 'Revision ' . ucfirst($status),
            'message' => 'Your revision request has been ' . $status . '.',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Revision request ' . $status . ' successfully.',
            'revision' => new CustomizationRevisionResource($revision->fresh(['requested_by_user'])),
        ]);
    }

    private function authenticatedUser()
    {
        $token = JWTAuth::parseToken();
        return $token->authenticate();
    }

    private function isParticipant(CustomizationRequest $customization, int $userId): bool
    {
        return (int) $customization->user_id === (int) $userId || (int) $customization->designer_id === (int) $userId;
    }

    private function createChatForCustomization(CustomizationRequest $customization): CustomizationChat
    {
        $asset = $customization->asset_id ? Asset::find($customization->asset_id) : null;
        $template = $customization->template_id ? Template::find($customization->template_id) : null;

        $title = $asset
            ? 'Asset Customization: ' . $asset->title
            : 'Template Customization: ' . ($template?->title ?? 'Untitled');

        return CustomizationChat::firstOrCreate(
            ['customization_request_id' => $customization->id],
            [
                'asset_id' => $customization->asset_id,
                'template_id' => $customization->template_id,
                'client_user_id' => $customization->user_id,
                'owner_user_id' => $customization->designer_id,
                'title' => $title,
                'last_message_at' => now(),
            ]
        );
    }
}
