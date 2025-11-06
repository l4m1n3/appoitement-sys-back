<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use App\Models\User;
use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmployeController extends Controller
{
    public function index()
    {
        return response()->json(Employe::with(['service','poste'])->get());
    }

    // public function store(Request $request)
    // { 
    //     $request->validate([
    //         'nom' => 'required|string',
    //         'prenom' => 'required|string',
    //         'email' => 'required|email|unique:employes,email',
    //         'telephone' => 'required|string|unique:employes,telephone',
    //         'date_naissance' => 'required|date',
    //         'service_id' => 'required|exists:services,id',
    //         'poste_id' => 'required|exists:postes,id',
    //     ]);

    //      $user = User::create([
    //             'name' => $request->name,
    //             'email' => $request->email,
    //             'password' => Hash::make($request->password),
    //         ]);

    //     $employe = Employe::create();

    //     return response()->json($employe, 201);
    // }
         public function store(Request $request)
{
    // Validation des champs
    $request->validate([
        'nom' => 'required|string|max:255',
        'prenom' => 'required|string|max:255',
        'email' => 'required|email|unique:employes,email',
        'telephone' => 'required|string|unique:employes,telephone',
        'date_naissance' => 'required|date',
        'service_id' => 'required|exists:services,id',
        'poste_id' => 'required|exists:postes,id',
    ]);

    try {
        DB::beginTransaction();

        // 1️⃣ Création du user
        // On va générer un mot de passe temporaire pour le user (sera le code du badge)
        $tempPassword = null; // sera défini après création du badge

        $user = User::create([
            'name' => $request->nom . ' ' . $request->prenom,
            'email' => $request->email,
            'password' => Hash::make('temp'), // temporaire
        ]);

        // 2️⃣ Création de l'employé
        $employe = Employe::create([
            'user_id' => $user->id, // maintenant $user existe
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'date_naissance' => $request->date_naissance,
            'service_id' => $request->service_id,
            'poste_id' => $request->poste_id,
        ]);

        // 3️⃣ Création du badge
        // $code_unique = 'MFP' . date('Y') . $employe->id;
        $code_unique = 'MFP' . date('Y') . str_pad($employe->id, 4, '0', STR_PAD_LEFT);
        $badge = Badge::create([
            'employe_id' => $employe->id,
            'code_unique' => $code_unique,
            'type' => 'RFID',
            'actif' => true,
        ]);

        // 4️⃣ Mettre à jour le mot de passe du user avec le code unique du badge
        $user->update(['password' => Hash::make($code_unique)]);

        DB::commit();

        return response()->json([
            'message' => 'Employé, User et Badge créés avec succès',
            'user' => $user,
            'employe' => $employe,
            'badge' => $badge,
        ], 201);

    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Erreur lors de la création',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    public function show(Employe $employe)
    {
        return response()->json($employe->load(['service','poste']));
    }

    public function update(Request $request, Employe $employe)
    {
        $employe->update($request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email|unique:employes,email,' . $employe->id,
            'telephone' => 'required|string|unique:employes,telephone,' . $employe->id,
            'date_naissance' => 'required|date',
            'service_id' => 'required|exists:services,id',
            'poste_id' => 'required|exists:postes,id',
        ]));

        return response()->json($employe);
    }

    public function destroy(Employe $employe)
    {
        $employe->delete();
        return response()->json(null, 204);
    }
}
