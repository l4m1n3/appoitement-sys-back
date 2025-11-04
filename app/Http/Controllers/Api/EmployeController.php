<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use Illuminate\Http\Request;

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
    // Validation des champs principaux
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

        // 1️⃣ Générer le code unique pour le badge
        $attempts = 0;
        $maxAttempts = 10;
        do {
            $code_unique = 'MFP' . date('Y') . strtoupper(Str::random(6));
            try {
                // On vérifie que le code n'existe pas déjà
                if (!Badge::where('code_unique', $code_unique)->exists()) {
                    break; // code unique OK
                }
            } catch (\Throwable $e) {
                if (++$attempts >= $maxAttempts) {
                    throw new \Exception("Impossible de générer un code unique pour le badge.");
                }
            }
        } while (true);

        // 2️⃣ Création de l'utilisateur avec mot de passe = code unique du badge
        $user = User::create([
            'name' => $request->nom . ' ' . $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($code_unique),
        ]);

        // 3️⃣ Création de l'employé
        $employe = Employe::create([
            'user_id' => $user->id,
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'date_naissance' => $request->date_naissance,
            'service_id' => $request->service_id,
            'poste_id' => $request->poste_id,
        ]);

        // 4️⃣ Création du badge de type RFID
        $badge = Badge::create([
            'employe_id' => $employe->id,
            'code_unique' => $code_unique,
            'type' => 'rfid',
            'actif' => true,
        ]);

        DB::commit();

        return response()->json([
            'message' => 'Employé, User et Badge RFID créés avec succès',
            'user' => $user,
            'employe' => $employe,
            'badge' => $badge,
            'mot_de_passe_defaut' => $code_unique, // utile pour communiquer le mot de passe initial
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
