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
    //         // Exemple de validation si tu veux la réactiver plus tard
    //         // $validated = $request->validate([
    //         //     'portique_id' => 'required|exists:portiques,id',
    //         //     'type' => 'required|in:entrée,sortie',
    //         // ]);

    //         // ⚙️ Récupérer un badge lié à l’employé concerné
    //         // (à adapter selon ta logique)
    //         $badge = Badge::where('employe_id', $request->employe_id)->first();
    //         $date_heure = now();
    //         if (!$badge) {
    //             return response()->json([
    //                 'error' => 'Aucun badge trouvé pour cet employé.'
    //             ], 404);
    //         }

    //         // ✅ Créer le pointage avec badge_id inclus
    //         $pointage = Pointage::create([
    //             'employe_id' => $request->employe_id,
    //             'portique_id' => $request->portique_id,
    //             'badge_id' => $badge->id,
    //             'type' => $request->type,
    //             'date_heure' => $date_heure,
    //         ]);

    //         // Charger les relations
    //         $pointage->load(['badge.employe', 'portique']);

    //         return response()->json($pointage, 201);
    //     } catch (\Exception $e) {
    //         Log::error('Erreur lors de la création du pointage: ' . $e->getMessage());
    //         return response()->json([
    //             'error' => 'Erreur lors de la création du pointage',
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }
public function store(Request $request)
{
    $request->validate([
        'portique_mac' => 'required|string',
        'type' => 'required|in:entrée,sortie',
    ]);

    // 1. Récupérer l'employé connecté
    $employe = Auth::user(); // ou via token
    if (!$employe) {
        return response()->json(['error' => 'Non authentifié'], 401);
    }

    // 2. Trouver le portique par MAC
    $portique = \App\Models\Portique::where('mac_address', $request->portique_mac)->first();
    if (!$portique) {
        return response()->json(['error' => 'Portique non autorisé'], 403);
    }

    // 3. Trouver le badge de l'employé
    $badge = Badge::where('employe_id', $employe->id)->first();
    if (!$badge) {
        return response()->json(['error' => 'Aucun badge assigné'], 400);
    }

    // 4. Créer le pointage
    $pointage = Pointage::create([
        'employe_id' => $employe->id,
        'portique_id' => $portique->id,
        'badge_id' => $badge->id,
        'type' => $request->type,
        'date_heure' => now(),
    ]);

    $pointage->load(['badge.employe', 'portique']);
    return response()->json($pointage, 201);
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
