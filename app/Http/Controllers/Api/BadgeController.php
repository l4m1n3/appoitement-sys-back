<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;

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
        'type' => 'required|string',
        'actif' => 'boolean'
    ]);

    $attempts = 0;
    $maxAttempts = 10;

    do {
        $code_unique = 'MFP' . date('Y') . strtoupper(Str::random(6));

        try {
            $badge = Badge::create([
                'employe_id' => $request->employe_id,
                'code_unique' => $code_unique,
                'type' => $request->type,
                'actif' => $request->boolean('actif')
            ]);

            return response()->json($badge, 201);
        } catch (UniqueConstraintViolationException $e) {
            if (++$attempts >= $maxAttempts) {
                return response()->json(['error' => 'Unable to generate unique code.'], 500);
            }
        }
    } while (true);
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
