<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupStatus;
use App\Models\Platform;
use Illuminate\Http\Request;

class GroupsController extends Controller
{
    public function index()
    {
        $groups = Group::with(['user', 'group_status', 'group_subscriptions'])
            ->orderByRaw('is_pinned DESC, pin_position ASC, rank_score DESC')
            ->get();
        return view('admin.groups.index', compact('groups'));
    }

    public function show(Group $group)
    {
        $group->load(['user', 'group_status', 'group_subscriptions.user']);
        return view('admin.groups.show', compact('group'));
    }

    public function edit(Group $group)
    {
        $groupStatuses = GroupStatus::all();
        $platforms     = Platform::all();
        return view('admin.groups.edit', compact('group', 'groupStatuses', 'platforms'));
    }

    public function update(Request $request, Group $group)
    {
        $request->validate([
            'group_status_id' => 'required|exists:group_statuses,id',
            'title'           => 'required|string|max:255',
            'price'           => 'nullable|numeric|min:0',
            'platform_id'     => 'nullable|exists:platforms,id',
            'link'            => 'nullable|url|max:500',
        ]);

        $group->update([
            'group_status_id' => $request->group_status_id,
            'title'           => $request->title,
            'description'     => $request->description,
            'price'           => $request->price,
            'link'            => $request->link,
            'platform_id'     => $request->platform_id,
        ]);

        flash()->success('Group updated successfully.');
        return redirect()->route('groups.index');
    }

    public function destroy(Group $group)
    {
        $group->delete();
        flash()->success('Group deleted successfully.');
        return redirect()->route('groups.index');
    }

    /**
     * Toggle the admin "Top / Priority" pin for a group.
     * POST admin/groups/{group}/toggle-pin
     * Max 2 pinned at a time.
     */
    public function togglePin(Group $group)
    {
        if ($group->is_pinned) {
            $unpinnedPosition = $group->pin_position;
            $group->update(['is_pinned' => false, 'pin_position' => 0]);
            Group::where('is_pinned', true)
                ->where('pin_position', '>', $unpinnedPosition)
                ->orderBy('pin_position')
                ->each(function ($g) {
                    $g->update(['pin_position' => $g->pin_position - 1]);
                });
            flash()->success('"' . $group->name . '" removed from top positions.');
        } else {
            $maxSlots = 2;
            $currentPinned = Group::where('is_pinned', true)->count();
            if ($currentPinned >= $maxSlots) {
                flash()->warning('Only ' . $maxSlots . ' groups can be pinned at a time. Unpin one first.');
                return redirect()->route('groups.index');
            }
            $nextPosition = $currentPinned + 1;
            $group->update(['is_pinned' => true, 'pin_position' => $nextPosition]);
            flash()->success('"' . $group->name . '" pinned to position #' . $nextPosition . ' in the marketplace.');
        }
        return redirect()->route('groups.index');
    }
}
