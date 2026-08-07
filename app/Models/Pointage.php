<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pointage extends Model
{
    protected $fillable = [
        'utilisateur_id',
        'date',
        'heure_entree',
        'heure_sortie',
        'pause_debut',
        'pause_fin',
        'statut',
        'commentaire',
        'minutes_retard',
        'minutes_supplementaires',
        'minutes_travaillees',
        'valide',
        'valide_par',
        'valide_at',
    ];

    protected $casts = [
        'date' => 'date',
        'valide' => 'boolean',
        'valide_at' => 'datetime',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }

    public function validePar()
    {
        return $this->belongsTo(Utilisateur::class, 'valide_par');
    }

    // --- Statuts d'avancement ---
    public function hasArrive(): bool
    {
        return !is_null($this->heure_entree);
    }

    public function hasStartPause(): bool
    {
        return !is_null($this->pause_debut);
    }

    public function hasEndPause(): bool
    {
        return !is_null($this->pause_fin);
    }

    public function hasSortie(): bool
    {
        return !is_null($this->heure_sortie);
    }

    public function isComplete(): bool
    {
        // Une journée est complète dès lors qu'arrivée et sortie sont
        // renseignées. La pause n'est pas obligatoire dans une journée
        // de travail (cohérent avec la badgeuse, qui autorise la sortie
        // sans pause prise).
        return $this->hasArrive() && $this->hasSortie();
    }

    // --- Calculs ---
    public function calculerRetard(): int
    {
        $seuil = '08:30:00';
        if ($this->heure_entree && $this->heure_entree > $seuil) {
            $entree = \Carbon\Carbon::parse($this->heure_entree);
            $seuilCarbon = \Carbon\Carbon::parse($seuil);
            return $entree->diffInMinutes($seuilCarbon);
        }
        return 0;
    }

    public function calculerHeuresSupp(): int
    {
        $seuil = '18:00:00';
        if ($this->heure_sortie && $this->heure_sortie > $seuil) {
            $sortie = \Carbon\Carbon::parse($this->heure_sortie);
            $seuilCarbon = \Carbon\Carbon::parse($seuil);
            return $sortie->diffInMinutes($seuilCarbon);
        }
        return 0;
    }

    public function calculerTempsTravaille(): int
    {
        if (!$this->hasArrive() || !$this->hasSortie()) {
            return 0;
        }

        $entree = \Carbon\Carbon::parse($this->heure_entree);
        $sortie = \Carbon\Carbon::parse($this->heure_sortie);
        $totalMinutes = $sortie->diffInMinutes($entree);

        // Déduire la pause (si elle existe)
        if ($this->hasStartPause() && $this->hasEndPause()) {
            $debutPause = \Carbon\Carbon::parse($this->pause_debut);
            $finPause = \Carbon\Carbon::parse($this->pause_fin);
            $totalMinutes -= $debutPause->diffInMinutes($finPause);
        }

        return max(0, $totalMinutes);
    }
}