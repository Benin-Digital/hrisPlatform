<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Events\MessageSent;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $entiteId = $user->entite_id;

        $messages = Message::with('utilisateur')->where('entite_id', $entiteId)->orderBy('created_at', 'asc')->get();

        return Inertia::render('Chat/Index', [
            'messages' => $messages,
            'entiteId' => $entiteId,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'contenu' => 'required|string|max:1000',
        ]);

        $user = $request->user();
        $message = Message::create([
            'utilisateur_id' => $user->id,
            'entite_id' => $user->entite_id,
            'contenu' => $request->contenu,
            'parent_id' => $request->parent_id ?? null,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message->load('utilisateur'));
    }
}