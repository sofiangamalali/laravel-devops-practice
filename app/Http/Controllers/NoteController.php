<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * Display the last 50 notes, newest first.
     */
    public function index(): JsonResponse
    {
        $notes = Note::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return response()->json($notes, 200);
    }

    /**
     * Store a newly created note.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:500'],
        ]);

        $note = Note::create($validated);

        return response()->json($note, 201);
    }
}
