<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Pointage;
use App\Models\Employe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage as StorageBase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator as ValidatorBase;
use Illuminate\Support\Facades\Log;

class PointageController extends Controller
{
    public function index()
    {
        return response()->json(Pointage::with(['badge.employe', 'portique'])->get());
    }

    // public function store(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             // 'portique_mac' => 'required|string',
    //             'type' => 'required|in:entrée,sortie',
    //         ]);

    //         // 1. Récupérer l'employé connecté
    //         $employe = Auth::user(); // ou via token
    //         if (!$employe) {
    //             return response()->json(['error' => 'Non authentifié'], 401);
    //         }

    //         // 2. Trouver le portique par MAC
    //         // $portique = \App\Models\Portique::where('mac_address', $request->portique_mac)->first();
    //         // if (!$portique) {
    //         //     return response()->json(['error' => 'Portique non autorisé'], 403);
    //         // }

    //         // 3. Trouver le badge de l'employé
    //         $badge = Badge::where('employe_id', $employe->id)->first();
    //         if (!$badge) {
    //             return response()->json(['error' => 'Aucun badge assigné'], 400);
    //         }

    //         // 4. Créer le pointage
    //         $pointage = Pointage::create([
    //             'employe_id' => $employe->id,
    //             // 'portique_id' => $portique->id,
    //             'badge_id' => $badge->id,
    //             'type' => $request->type,
    //             'date_heure' => now(),
    //         ]);

    //         $pointage->load(['badge.employe', 'portique']);
    //         return response()->json($pointage, 201);
    //     } catch (\Throwable $th) {
    //         Log::error($th->getMessage());
    //         return response()->json(['message' => 'User pointage failed'], 500);
    //     }
    // }
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:entrée,sortie',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $employe = Auth::user();
        if (!$employe) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        $badge = Badge::where('employe_id', $employe->id)->first();
        if (!$badge) {
            return response()->json(['error' => 'Aucun badge assigné'], 400);
        }

        // Coordonnées de l'entreprise et tolérance
        $entrepriseLat = 13.5116; // exemple
        $entrepriseLng = 2.1254;
        $rayon = 100; // mètres

        // Calcul distance
        $distance = $this->distanceMetres($request->latitude, $request->longitude, $entrepriseLat, $entrepriseLng);

        if ($distance > $rayon) {
            return response()->json(['error' => 'Pointage impossible : hors de la zone autorisée'], 403);
        }

        // Créer le pointage
        $pointage = Pointage::create([
            'employe_id' => $employe->id,
            'badge_id' => $badge->id,
            'type' => $request->type,
            'date_heure' => now(),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude
        ]);

        return response()->json($pointage, 201);
    }

    // Méthode utilitaire pour calculer la distance en mètres
    private function distanceMetres($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371000; // m
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    public function show(Pointage $pointage)
    {
        return response()->json($pointage->load(['badge.employe', 'portique']));
    }

    public function update(Request $request, Pointage $pointage)
    {
        $pointage->update($request->validate([
            'badge_id' => 'required|exists:badges,id',
            'portique_id' => 'required|exists:portiques,id',
            'date_heure' => 'required|date',
            'type' => 'required|in:ENTREE,SORTIE',
        ]));

        return response()->json($pointage);
    }

    public function destroy(Pointage $pointage)
    {
        $pointage->delete();
        return response()->json(null, 204);
    }

    public function present()
    {
        $present = Employe::whereHas('pointages', function ($query) {
            $query->latest('date_heure');
        })->get()->map(function ($employe) {
            $dernierPointage = $employe->pointages->sortByDesc('date_heure')->first();
            return [
                'employe' => $employe,
                'dernier_pointage' => $dernierPointage,
            ];
        });

        return response()->json($present->values());
    }

    public function historique($employe_id)
    {
        $pointages = \App\Models\Pointage::with(['badge', 'portique'])
            ->whereHas('badge', function ($q) use ($employe_id) {
                $q->where('employe_id', $employe_id);
            })
            ->orderBy('date_heure', 'desc')
            ->get();

        return response()->json($pointages);
    }
    public function dernierPointage($employe_id)
    {
        $pointage = \App\Models\Pointage::with(['badge', 'portique'])
            ->whereHas('badge', fn($q) => $q->where('employe_id', $employe_id))
            ->latest('date_heure')
            ->first();

        return response()->json($pointage);
    }
    public function parPortique(Request $request, $portique_id)
    {
        $request->validate([
            'debut' => 'required|date',
            'fin' => 'required|date|after_or_equal:debut',
        ]);

        $pointages = \App\Models\Pointage::with(['badge.employe'])
            ->where('portique_id', $portique_id)
            ->whereBetween('date_heure', [$request->debut, $request->fin])
            ->orderBy('date_heure')
            ->get();

        return response()->json($pointages);
    }
    public function presenceParJour(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $date = $request->date;

        $pointages = \App\Models\Employe::with('pointages')->get()->filter(function ($employe) use ($date) {
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
}
