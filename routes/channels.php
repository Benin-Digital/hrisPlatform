<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('entite.{id}', function ($user, $id) {
    return (int) $user->entite_id === (int) $id;
});

Broadcast::channel('espace.{id}', function ($user, $id) {
    return $user->espaces()->where('espace_id', $id)->exists();
});

Broadcast::channel('App.Models.Utilisateur.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal pour le destinataire direct
//Broadcast::channel('App.Models.Utilisateur.{id}', function ($user, $id) {
    //return (int) $user->id === (int) $id;
//});

// Canal pour les Super Admins
Broadcast::channel('private-super-admin', function ($user) {
    return $user->hasRole('super_admin');
});