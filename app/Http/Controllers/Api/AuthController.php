<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function register(Request $request)
    {
        try {
            // dd($request->all());
            $request->validate([
                'name' => 'required|string|max:100',
                'email' => 'required|string|email|max:100|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json(['message' => 'User registered successfully', 'token' => $token, 'user' => $user], 201);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json(['message' => 'User registration failed'], 500);
        }
    }


    public function login(Request $request)
    {
        try {
            // Validation des données
            $credentials = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            // Tentative d'authentification
            if (!Auth::attempt($credentials)) {
                throw ValidationException::withMessages([
                    'email' => ['Adresse e-mail ou mot de passe incorrect.'],
                ]);
            }

            // Récupération de l'utilisateur connecté
            $user = $request->user();
            $token = $user->createToken('auth_token')->plainTextToken;
            // Retour JSON en cas de succès
            return response()->json([
                'message' => 'Connexion réussie',
                'user' => $user,
                'token' => $token
            ]);
        } catch (ValidationException $ve) {
            // Retour JSON pour erreurs de validation ou d'identifiants
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Throwable $th) {
            // Log de l'erreur pour debug
            Log::error('Erreur lors de la connexion : ' . $th->getMessage(), [
                'trace' => $th->getTraceAsString()
            ]);

            // Retour JSON pour erreurs serveur
            return response()->json([
                'message' => 'Une erreur est survenue, veuillez réessayer plus tard.'
            ], 500);
        }
    }


    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
