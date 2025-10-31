<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Poste;
use Illuminate\Http\Request;

class PosteController extends Controller
{
    public function index()
    {
        return response()->json(Poste::all());
    }

    public function store(Request $request)
    {
        $poste = Poste::create($request->validate([
            'nom' => 'required|string|unique:postes,nom',
        ]));

        return response()->json($poste, 201);
    }

    public function show(Poste $poste)
    {
        return response()->json($poste);
    }

    public function update(Request $request, Poste $poste)
    {
        $poste->update($request->validate([
            'nom' => 'required|string|unique:postes,nom,' . $poste->id,
        ]));

        return response()->json($poste);
    }

    public function destroy(Poste $poste)
    {
        $poste->delete();
        return response()->json(null, 204);
    }
}
