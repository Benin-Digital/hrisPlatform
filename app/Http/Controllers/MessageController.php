<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Utilisateur;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Liste des utilisateurs avec qui on peut discuter.
     */
    public function contacts()
    {
        $users = Utilisateur::where('id', '!=', Auth::id())
            ->select('id', 'prenom', 'nom', 'email', 'type')
            ->orderBy('nom')
            ->get()
            ->map(function($u) {
                return [
                    'id' => $u->id,
                    'name' => "{$u->prenom} {$u->nom}",
                    'type' => $u->type,
                    'initials' => strtoupper(substr($u->prenom, 0, 1) . substr($u->nom, 0, 1))
                ];
            });

        return response()->json($users);
    }

    /**
     * Récupère l'historique des messages pour un espace ou un utilisateur.
     */
    public function index(Request $request, $id)
    {
        $userId = Auth::id();
        $espaceId = $request->query('espace_id');

        $query = Message::with(['auteur:id,prenom,nom']);

        if ($espaceId) {
            // Messages de l'espace
            $query->where('espace_id', $espaceId);
        } else {
            // Discussion privée entre deux utilisateurs
            $query->where(function($q) use ($userId, $id) {
                $q->where('utilisateur_id', $userId)->where('destinataire_id', $id);
            })
            ->orWhere(function($q) use ($userId, $id) {
                $q->where('utilisateur_id', $id)->where('destinataire_id', $userId);
            })
            ->whereNull('espace_id');
        }

        $messages = $query->orderBy('created_at', 'asc')->get();

        return response()->json($messages);
    }

    /**
     * Envoie un message.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'destinataire_id' => 'nullable|exists:utilisateurs,id',
            'espace_id' => 'nullable|exists:espaces_collaboratifs,id',
            'contenu' => 'required|string',
        ]);

        $destinataire_id = $validated['destinataire_id'] ?? null;
        $espace_id = $validated['espace_id'] ?? null;

        if (!$destinataire_id && !$espace_id) {
            return response()->json(['error' => 'Destinataire ou Espace requis'], 422);
        }

        $message = Message::create([
            'utilisateur_id' => Auth::id(),
            'destinataire_id' => $validated['destinataire_id'] ?? null,
            'espace_id' => $validated['espace_id'] ?? null,
            'contenu' => $validated['contenu'],
            'entite_id' => Auth::user()->entite_id,
        ]);

        $message->load('auteur');

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message);
    }
}