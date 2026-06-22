<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Escrow;
use Illuminate\Http\Request;

class EscrowsController extends Controller
{
    public function index()
    {
        $escrows = Escrow::with(['customization_request.user'])->latest()->get();
        return view('admin.escrows.index', compact('escrows'));
    }

    public function show(Escrow $escrow)
    {
        $escrow->load(['customization_request.user', 'escrow_histories']);
        return view('admin.escrows.show', compact('escrow'));
    }

    public function destroy(Escrow $escrow)
    {
        $escrow->delete();
        flash()->success('Escrow record deleted successfully.');
        return redirect()->route('escrows.index');
    }
}
