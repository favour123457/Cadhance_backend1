<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\TemplateStatus;
use Illuminate\Http\Request;

class TemplatesController extends Controller
{
    public function index()
    {
        // Show pinned first, then by rank score — mirrors marketplace order
        $templates = Template::with(['user', 'template_status'])
            ->orderByRaw('is_pinned DESC, pin_position ASC, rank_score DESC')
            ->get();
        return view('admin.templates.index', compact('templates'));
    }

    public function show(Template $template)
    {
        $template->load(['user', 'template_status']);
        return view('admin.templates.show', compact('template'));
    }

    public function edit(Template $template)
    {
        $templateStatuses = TemplateStatus::all();
        return view('admin.templates.edit', compact('template', 'templateStatuses'));
    }

    public function update(Request $request, Template $template)
    {
        $request->validate([
            'template_status_id' => 'required|exists:template_statuses,id',
            'title'              => 'required|string|max:255',
            'price'              => 'nullable|numeric|min:0',
        ]);

        $template->update([
            'template_status_id' => $request->template_status_id,
            'title'              => $request->title,
            'description'        => $request->description,
            'includes'           => $request->includes,
            'price'              => $request->price,
        ]);

        flash()->success('Template updated successfully.');
        return redirect()->route('templates.index');
    }

    public function destroy(Template $template)
    {
        $template->delete();
        flash()->success('Template deleted successfully.');
        return redirect()->route('templates.index');
    }

    /**
     * Toggle the admin "Top / Priority" pin for a template.
     *
     * POST admin/templates/{template}/toggle-pin
     *
     * Rules:
     * - Only 2 templates can be pinned at a time (positions 1 & 2).
     * - If template is already pinned, unpin it and shift remaining pinned
     *   templates up.
     * - If not pinned and fewer than 2 slots are taken, assign next position.
     */
    public function togglePin(Template $template)
    {
        if ($template->is_pinned) {
            // Unpin this template
            $unpinnedPosition = $template->pin_position;
            $template->update(['is_pinned' => false, 'pin_position' => 0]);

            // Shift remaining pinned templates up to fill the gap
            Template::where('is_pinned', true)
                ->where('pin_position', '>', $unpinnedPosition)
                ->orderBy('pin_position')
                ->each(function ($t) {
                    $t->update(['pin_position' => $t->pin_position - 1]);
                });

            flash()->success('"' . $template->title . '" removed from top positions.');
        } else {
            $maxSlots       = 2;
            $currentPinned  = Template::where('is_pinned', true)->count();

            if ($currentPinned >= $maxSlots) {
                flash()->warning('Only ' . $maxSlots . ' templates can be pinned at a time. Unpin one first.');
                return redirect()->route('templates.index');
            }

            $nextPosition = $currentPinned + 1; // 1 or 2
            $template->update(['is_pinned' => true, 'pin_position' => $nextPosition]);

            flash()->success('"' . $template->title . '" pinned to position #' . $nextPosition . ' in the marketplace.');
        }

        return redirect()->route('templates.index');
    }
}
