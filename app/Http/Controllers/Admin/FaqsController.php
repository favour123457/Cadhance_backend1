<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqsController extends Controller
{
    public function index()
    {
        $faqs = Faq::latest()->get();
        return view('admin.faqs.index', compact('faqs'));
    }

    public function create()
    {
        return view('admin.faqs.add');
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string',
            'answer'   => 'required|string',
        ]);

        Faq::create($request->only(['question', 'answer']));

        flash()->success('FAQ created successfully.');
        return redirect()->route('faqs.index');
    }

    public function edit(Faq $faq)
    {
        return view('admin.faqs.edit', compact('faq'));
    }

    public function update(Request $request, Faq $faq)
    {
        $request->validate([
            'question' => 'required|string',
            'answer'   => 'required|string',
        ]);

        $faq->update($request->only(['question', 'answer']));

        flash()->success('FAQ updated successfully.');
        return redirect()->route('faqs.index');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();
        flash()->success('FAQ deleted successfully.');
        return redirect()->route('faqs.index');
    }
}
