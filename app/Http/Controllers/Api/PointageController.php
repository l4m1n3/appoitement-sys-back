<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Pointage;
use App\Models\Employe;
use App\Models\Portique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PointageController extends Controller
{
    /**
     * Liste tous les pointages avec relations
     */
    public function index()
    {
        return response()->json(Pointage::with(['badge.employe', 'portique'])->get());
    }

    /**
     * Crée un nouveau pointage avec vérification de position GPS et doublons
     */
   
public function store(Request $request)
{
    try {
        //code...
         $request->validate([
        'type' => 'required|in:entree,sortie,ENTREE,SORTIE',
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
    ]);


    $user = Auth::user();
    $employe = Employe::where('user_id', $user->id)->first();

    if (!$user) {
        return response()->json(['error' => 'Non authentifié'], 401);
    }

    // Badge assigné à l'employé
    $badge = Badge::where('employe_id', $employe->id)->first();
    if (!$badge) {
        return response()->json(['error' => 'Aucun badge assigné à cet employé'], 400);
    }

    // Normalisation du type pour PostgreSQL
    $typeMap = [
        'ENTREE' => 'entrée',
        'entree' => 'entrée',
        'SORTIE' => 'sortie',
        'sortie' => 'sortie',
    ];
    $type = $typeMap[$request->type];

    // Vérifier dernier pointage
    $dernier = Pointage::where('employe_id', $employe->id)->latest('date_heure')->first();
    if ($dernier && $dernier->type === $type) {
        return response()->json(['error' => "Vous avez déjà effectué un pointage de type {$type}."], 400);
    }

    // // Coordonnées de l’entreprise
    // $entrepriseLat = 13.5797;
    // $entrepriseLng = 2.0991;
    // $rayon = 15000000; // mètres autorisés
    // Coordonnées de l’entreprise
$entrepriseLat = 10.57975;
$entrepriseLng = 2.09850;
$rayon = 100; // mètres autorisés


    // Calcul de distance
    $distance = $this->distanceMetres($request->latitude, $request->longitude, $entrepriseLat, $entrepriseLng);
    if ($distance > $rayon) {
        return response()->json(['error' => 'Pointage impossible : hors de la zone autorisée'], 403);
    }

    // Enregistrement du pointage
    $pointage = Pointage::create([
        'employe_id' => $employe->id,
        'badge_id' => $badge->id,
        'type' => $type,
        'date_heure' => now(),
        'latitude' => $request->latitude,
        'longitude' => $request->longitude,
    ]);

    return response()->json([
        'message' => 'Pointage enregistré avec succès',
        'data' => $pointage,
    ], 201);
    } catch (\Throwable $th) {
       // Log de l'erreur pour debug
            Log::error('Erreur lors du pointage : ' . $th->getMessage(), [
                'trace' => $th->getTraceAsString()
            ]);
    }
   
}

    /**
     * Calcule la distance entre deux coordonnées (en mètres)
     */
    private function distanceMetres($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371000; // rayon terrestre (m)
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Affiche les détails d’un pointage
     */
    public function show(Pointage $pointage)
    {
        return response()->json($pointage->load(['badge.employe', 'portique']));
    }

    /**
     * Met à jour un pointage existant
     */
    public function update(Request $request, Pointage $pointage)
    {
        $validated = $request->validate([
            'badge_id' => 'required|exists:badges,id',
            'portique_id' => 'nullable|exists:portiques,id',
            'date_heure' => 'required|date',
            'type' => 'required|in:ENTREE,SORTIE',
        ]);

        $pointage->update($validated);
        return response()->json($pointage);
    }

    /**
     * Supprime un pointage
     */
    public function destroy(Pointage $pointage)
    {
        $pointage->delete();
        return response()->json(['message' => 'Pointage supprimé'], 204);
    }

    /**
     * Liste des employés actuellement présents
     */
    public function present()
    {
        $present = Employe::whereHas('pointages')
            ->get()
            ->map(function ($employe) {
                $dernierPointage = $employe->pointages->sortByDesc('date_heure')->first();
                return [
                    'employe' => $employe,
                    'dernier_pointage' => $dernierPointage,
                ];
            })
            ->filter(fn($item) => $item['dernier_pointage'] && $item['dernier_pointage']->type === 'ENTREE');

        return response()->json($present->values());
    }

    /**
     * Historique complet d’un employé
     */
    public function historique($employe_id)
    {
        $user = Auth::user();

        // Sécurité : l’utilisateur ne peut consulter que son propre historique
        if ($user->id != $employe_id && !$user->is_admin) {
            return response()->json(['error' => 'Accès refusé'], 403);
        }

        $pointages = Pointage::with(['badge', 'portique'])
            ->whereHas('badge', fn($q) => $q->where('employe_id', $employe_id))
            ->orderByDesc('date_heure')
            ->get();

        return response()->json($pointages);
    }

    /**
     * Dernier pointage d’un employé
     */
    public function dernierPointage($employe_id)
    {
        $pointage = Pointage::with(['badge'])
            ->whereHas('badge', fn($q) => $q->where('employe_id', $employe_id))
            ->latest('date_heure')
            ->first();

        return response()->json($pointage);
    }

    /**
     * Pointages par portique et période donnée
     */
    public function parPortique(Request $request, $portique_id)
    {
        $request->validate([
            'debut' => 'required|date',
            'fin' => 'required|date|after_or_equal:debut',
        ]);

        $pointages = Pointage::with(['badge.employe'])
            ->where('portique_id', $portique_id)
            ->whereBetween('date_heure', [$request->debut, $request->fin])
            ->orderBy('date_heure')
            ->get();

        return response()->json($pointages);
    }

    /**
     * Présence quotidienne
     */
    public function presenceParJour(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $date = $request->date;

        $pointages = Employe::with('pointages')->get()->filter(function ($employe) use ($date) {
            $dernier = $employe->pointages
                ->where('date_heure', '<=', $date . ' 23:59:59')
                ->sortByDesc('date_heure')
                ->first();
            return $dernier && $dernier->type === 'ENTREE';
        });

        return response()->json([
            'date' => $date,
            'present_count' => $pointages->count(),
            'employes' => $pointages->values()
        ]);
    }

    /**
     * Liste des portiques autorisés (actifs)
     */
    public function authorizedPortiques()
    {
        $portiques = Portique::where('is_active', true)->get(['id', 'nom', 'mac_address']);
        return response()->json($portiques);
    }
}
