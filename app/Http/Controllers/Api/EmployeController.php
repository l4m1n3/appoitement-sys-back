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

    public function store(Request $request)
    { 
        $employe = Employe::create($request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email|unique:employes,email',
            'telephone' => 'required|string|unique:employes,telephone',
            'date_naissance' => 'required|date',
            'service_id' => 'required|exists:services,id',
            'poste_id' => 'required|exists:postes,id',
        ]));

        return response()->json($employe, 201);
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
