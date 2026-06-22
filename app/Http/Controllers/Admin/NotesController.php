<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\NoteType;
use Illuminate\Http\Request;

class NotesController extends Controller
{
    public function index()
    {
        $notes = Note::with('note_type')->latest()->get();
        return view('admin.notes.index', compact('notes'));
    }

    public function create()
    {
        $noteTypes = NoteType::all();
        return view('admin.notes.add', compact('noteTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'note_type_id' => 'required|exists:note_types,id',
            'note'         => 'required|string',
        ]);

        Note::create($request->only(['note_type_id', 'note']));

        flash()->success('Note created successfully.');
        return redirect()->route('notes.index');
    }

    public function show(Note $note)
    {
        $note->load('note_type');
        return view('admin.notes.show', compact('note'));
    }

    public function edit(Note $note)
    {
        $noteTypes = NoteType::all();
        return view('admin.notes.edit', compact('note', 'noteTypes'));
    }

    public function update(Request $request, Note $note)
    {
        $request->validate([
            'note_type_id' => 'required|exists:note_types,id',
            'note'         => 'required|string',
        ]);

        $note->update($request->only(['note_type_id', 'note']));

        flash()->success('Note updated successfully.');
        return redirect()->route('notes.index');
    }

    public function destroy(Note $note)
    {
        $note->delete();
        flash()->success('Note deleted successfully.');
        return redirect()->route('notes.index');
    }
}
