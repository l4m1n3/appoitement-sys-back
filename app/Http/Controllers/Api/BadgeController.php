<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    public function index()
    {
        return response()->json(Badge::with('employe')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'employe_id' => 'required|exists:employes,id',
            // 'code_unique' => 'required|string',
            'type' => 'required',
            'actif' => 'boolean'
        ]);
        $code_unique = '2024INF';
        $badge = Badge::create([
            'employe_id' => $request->employe_id,
            'code_unique' => $code_unique,
            'type' => $request->type,
            'actif' => $request->actif
        ]);

        return response()->json($badge, 201);
    }

    public function show(Badge $badge)
    {
        return response()->json($badge->load('employe'));
    }

    public function update(Request $request, Badge $badge)
    {
        $badge->update($request->validate([
            'employe_id' => 'required|exists:employes,id',
            'code' => 'required|string|unique:badges,code,' . $badge->id,
            'type' => 'required|in:RFID,QR',
            'actif' => 'boolean'
        ]));

        return response()->json($badge);
    }

    public function destroy(Badge $badge)
    {
        $badge->delete();
        return response()->json(null, 204);
    }
}
