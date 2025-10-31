<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Portique;
use Illuminate\Http\Request;

class PortiqueController extends Controller
{
    public function index()
    {
        return response()->json(Portique::all());
    }

    public function store(Request $request)
    {
        $portique = Portique::create($request->validate([
            'nom' => 'required|string',
            'emplacement' => 'required|string',
            'mac_address'=>'required'
        ]));

        return response()->json($portique, 201);
    }

    public function show(Portique $portique)
    {
        return response()->json($portique);
    }

    public function update(Request $request, Portique $portique)
    {
        $portique->update($request->validate([
            'nom' => 'required|string',
            'emplacement' => 'required|string',
            'mac_address'=>'required'
        ]));

        return response()->json($portique);
    }

    public function destroy(Portique $portique)
    {
        $portique->delete();
        return response()->json(null, 204);
    }
}
