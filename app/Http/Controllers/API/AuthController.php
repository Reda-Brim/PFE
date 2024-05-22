<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\Etudiant;
use App\Models\Encadrant;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validation des données d'entrée
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:4',
        ]);

        // Recherche de l'utilisateur par email
        $user = null;
        $type = null;
        $email = $request->email;
        $password = $request->password;

        // Vérification si un admin existe avec cet email
        $admin = Admin::where('email', $email)->first();
        if ($admin) {
            if (Hash::check($password, $admin->password)) {
                $user = $admin;
                $type = 'admin';
            } else {
                return response()->json(['error' => 'Incorrect password'], 401);
            }
        }

        // Si aucun admin n'est trouvé, vérifiez les étudiants
        if (!$user) {
            $etudiant = Etudiant::where('email', $email)->first();
            if ($etudiant) {
                if (Hash::check($password, $etudiant->password)) {
                    $user = $etudiant;
                    $type = 'etudiant';
                } else {
                    return response()->json(['error' => 'Incorrect password'], 401);
                }
            }
        }

        // Si aucun étudiant n'est trouvé, vérifiez les encadrants
        if (!$user) {
            $encadrant = Encadrant::where('email', $email)->first();
            if ($encadrant) {
                if (Hash::check($password, $encadrant->password)) {
                    $user = $encadrant;
                    $type = 'encadrant';
                } else {
                    return response()->json(['error' => 'Incorrect password'], 401);
                }
            }
        }

        // Si aucun utilisateur n'est trouvé correspondant à l'email
        if (!$user) {
            return response()->json(['error' => 'Email not found'], 404);
        }

        // Génération du jeton d'accès
        $token = $user->createToken("{$type}Token")->accessToken;
        $id = $user->id;
        $username = $user->email;

        // Retourner les informations de l'utilisateur et le jeton d'accès
        return response()->json([
            'id' => $id,
            'username' => $username,
            'token' => $token,
            'type' => $type
        ], 200);
    }


    public function registerEtudiant(Request $request)
    {
        // Validation des données d'entrée
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:etudiants',
            'cin' => 'required|string|max:255',
            'cne' => 'required|string|max:255',
            'filiere' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string|min:6',
        ]);
    
        // Hachage du mot de passe
        $hashedPassword = Hash::make($request->password);
    
        // Création de l'utilisateur étudiant
        $etudiant = Etudiant::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'cin' => $request->cin,
            'cne' => $request->cne,
            'filiere' => $request->filiere,
            'password' => $hashedPassword,
        ]);
    
        // Génération du jeton d'accès
        $token = $etudiant->createToken("EtudiantToken")->accessToken;
    
        // Retourner les informations de l'utilisateur et le jeton d'accès
        return response()->json([
            'message' => 'Étudiant enregistré avec succès',
            'id' => $etudiant->id,
            'username' => $etudiant->email,
            'token' => $token,
            'type' => 'etudiant'
        ], 201);
    }

    public function registerEncadrant(Request $request)
{
    // Validation des données d'entrée
    $request->validate([
        'nom' => 'required|string|max:255',
        'prenom' => 'required|string|max:255',
        'email' => 'required|email|unique:encadrants',
        'specialite' => 'required|string|max:255',
        'password' => 'required|string|min:6|confirmed',
        'password_confirmation' => 'required|string|min:6',
    ]);

    // Hachage du mot de passe
    $hashedPassword = Hash::make($request->password);

    // Création de l'encadrant
    $encadrant = Encadrant::create([
        'nom' => $request->nom,
        'prenom' => $request->prenom,
        'email' => $request->email,
        'specialite' => $request->specialite,
        'password' => $hashedPassword,
    ]);

    // Génération du jeton d'accès
    $token = $encadrant->createToken("EncadrantToken")->accessToken;

    // Retourner les informations de l'encadrant et le jeton d'accès
    return response()->json([
        'message' => 'Encadrant enregistré avec succès',
        'id' => $encadrant->id,
        'username' => $encadrant->email,
        'token' => $token,
        'type' => 'encadrant'
    ], 201);
}

}