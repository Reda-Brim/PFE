<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\Etudiant;
use App\Models\Encadrant;
use App\Models\Sujets;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;


class AdminController extends Controller
{
    public function addEtudiant(Request $request)
{
    // Validation des données d'entrée
    $request->validate([
        'nom' => 'required|string|max:255',
        'prenom' => 'required|string|max:255',
        'email' => 'required|email|unique:etudiants',
        'cin' => 'required|string|max:255|unique:etudiants',
        'cne' => 'required|string|max:255|unique:etudiants',
        'filiere' => 'required|string|in:BDD,SID,RES',
        'password' => 'required|string|min:6|confirmed',
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
        'id' => $etudiant->id,
        'username' => $etudiant->email,
        'token' => $token,
        'type' => 'etudiant',
        'message' => 'Enregistrement réussi'
    ], 201);
}

    public function addEncadrant(Request $request)
    {
        // Validation des données d'entrée
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:encadrants',
            'specialite' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Hachage du mot de passe
        $hashedPassword = Hash::make($request->password);

        // Création de l'utilisateur encadrant
        $encadrant = Encadrant::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'specialite' => $request->specialite,
            'password' => $hashedPassword,
        ]);

        // Génération du jeton d'accès
        $token = $encadrant->createToken("EncadrantToken")->accessToken;

        // Retourner les informations de l'utilisateur et le jeton d'accès avec un code de statut 201 (Créé)
        return response()->json([
            'message' => 'Encadrant ajouté avec succès',
            'id' => $encadrant->id,
            'username' => $encadrant->email,
            'token' => $token,
            'type' => 'encadrant'
        ], 201);
    }
    
   public function updateEtudiant(Request $request, $id)
{
    // Valider les données d'entrée
    $request->validate([
        'nom' => 'sometimes|required|string|max:255',
        'prenom' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|email|unique:etudiants,email,' . $id,
        'cin' => 'sometimes|required|string|max:255|unique:etudiants,cin,' . $id,
        'cne' => 'sometimes|required|string|max:255|unique:etudiants,cne,' . $id,
        'filiere' => 'sometimes|required|string|max:255',
        'password' => 'nullable|string|min:6|confirmed',
    ]);

    // Recherche de l'étudiant à mettre à jour
    $etudiant = Etudiant::findOrFail($id);

    // Mise à jour des champs
    if ($request->has('nom')) {
        $etudiant->nom = $request->nom;
    }
    if ($request->has('prenom')) {
        $etudiant->prenom = $request->prenom;
    }
    if ($request->has('email')) {
        $etudiant->email = $request->email;
    }
    if ($request->has('cin')) {
        $etudiant->cin = $request->cin;
    }
    if ($request->has('cne')) {
        $etudiant->cne = $request->cne;
    }
    if ($request->has('filiere')) {
        $etudiant->filiere = $request->filiere;
    }
    
    // Mise à jour du mot de passe s'il est fourni
    if ($request->has('password')) {
        $etudiant->password = Hash::make($request->password);
    }

    // Enregistrement des modifications
    $etudiant->save();

    return response()->json(['message' => 'Étudiant mis à jour avec succès', 'etudiant' => $etudiant]);
}

public function updateEncadrant(Request $request, $id)
{
    // Valider les données d'entrée
    $request->validate([
        'nom' => 'sometimes|required|string|max:255',
        'prenom' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|email|unique:encadrants,email,' . $id,
        'cin' => 'sometimes|required|string|max:255|unique:encadrants,cin,' . $id,
        'specialite' => 'sometimes|required|string|max:255',
        'password' => 'nullable|string|min:6|confirmed',
    ]);

    // Recherche de l'encadrant à mettre à jour
    $encadrant = Encadrant::findOrFail($id);

    // Mise à jour des champs
    if ($request->has('nom')) {
        $encadrant->nom = $request->nom;
    }
    if ($request->has('prenom')) {
        $encadrant->prenom = $request->prenom;
    }
    if ($request->has('email')) {
        $encadrant->email = $request->email;
    }
    if ($request->has('cin')) {
        $encadrant->cin = $request->cin;
    }
    if ($request->has('specialite')) {
        $encadrant->specialite = $request->specialite;
    }
    
    // Mise à jour du mot de passe s'il est fourni
    if ($request->has('password')) {
        $encadrant->password = Hash::make($request->password);
    }

    // Enregistrement des modifications
    $encadrant->save();

    return response()->json(['message' => 'Encadrant mis à jour avec succès', 'encadrant' => $encadrant]);
}


public function deleteEtudiant($id)
{
    // Recherche de l'étudiant à supprimer
    $etudiant = Etudiant::findOrFail($id);

    // Suppression de l'étudiant
    $etudiant->delete();

    return response()->json(['message' => 'Étudiant supprimé avec succès']);
}

public function deleteEncadrant($id)
{
    // Recherche de l'encadrant à supprimer
    $encadrant = Encadrant::findOrFail($id);

    // Suppression de l'encadrant
    $encadrant->delete();

    return response()->json(['message' => 'Encadrant supprimé avec succès']);
}

public function listEtudiants()
{
    $etudiants = Etudiant::all();
    return response()->json(['etudiants' => $etudiants]);
}

public function listEncadrants()
{
    $encadrants = Encadrant::all();
    return response()->json(['encadrants' => $encadrants]);
}

public function getEtudiant($id)
{
    $etudiant = Etudiant::findOrFail($id);
    return response()->json(['etudiant' => $etudiant]);
}

public function getEncadrant($id)
{
    $encadrant = Encadrant::findOrFail($id);
    return response()->json(['encadrant' => $encadrant]);
}

public function getAdmin(Request $request)
{
    $admin = $request->user();
    return response()->json(['admin' => $admin]);
}

public function updateAdmin(Request $request)
{
    // Récupérer l'administrateur authentifié
    $admin = $request->user();

    // Valider les données d'entrée
    $request->validate([
        'nom' => 'sometimes|required|string|max:255',
        'prenom' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|email|unique:admins,email,' . $admin->id,
        'password' => 'nullable|string|min:6|confirmed',
    ]);

    // Mise à jour des champs
    if ($request->has('nom')) {
        $admin->nom = $request->nom;
    }
    if ($request->has('prenom')) {
        $admin->prenom = $request->prenom;
    }
    if ($request->has('email')) {
        $admin->email = $request->email;
    }
    // Mise à jour du mot de passe s'il est fourni
    if ($request->has('password')) {
        $admin->password = Hash::make($request->password);
    }

    // Enregistrement des modifications
    $admin->save();

    return response()->json(['message' => 'Informations administrateur mises à jour avec succès', 'admin' => $admin]);
}

public function addSujet(Request $request)
{
    try {
        // Validation des données d'entrée
        $request->validate([
            'nom' => 'required|string|max:255',
            'document' => 'required|file|mimes:pdf|max:2048', // Ajout de la validation pour le document PDF
        ]);

        // Vérification de l'existence d'un sujet avec le même nom
        if (Sujets::where('nom', $request->nom)->exists()) {
            return response()->json(['error' => 'Un sujet avec ce nom existe déjà.'], 409);
        }

        // Enregistrement temporaire du fichier PDF et calcul du hachage
        $documentPath = $request->file('document')->store('documents');
        $documentHash = md5_file(storage_path('app/' . $documentPath));

        // Vérification de l'existence d'un fichier avec le même hachage
        if (Sujets::where('document_hash', $documentHash)->exists()) {
            Storage::delete($documentPath); // Supprimer le fichier temporaire
            return response()->json(['error' => 'Un fichier identique existe déjà.'], 409);
        }

        // Configuration de Firebase
        $firebase = (new Factory)->withServiceAccount(storage_path('app/pfe-files-firebase-adminsdk-rp3sy-cfd99cff86.json'))->createStorage();
        $bucket = $firebase->getBucket();

        // Téléverser le fichier PDF vers Firebase Storage
        $firebaseStoragePath = 'documents/' . basename($documentPath);
        $bucket->upload(fopen(storage_path('app/' . $documentPath), 'r'), ['name' => $firebaseStoragePath]);

        // Obtenir l'URL de téléchargement public
        $downloadUrl = $bucket->object($firebaseStoragePath)->signedUrl(new \DateTime('+1 year'));

        // Création du sujet dans la base de données MySQL avec l'URL du fichier et le hachage
        $sujet = Sujets::create([
            'nom' => $request->nom,
            'document' => $downloadUrl,
            'document_hash' => $documentHash, // Ajout du hachage dans la base de données
        ]);

        // Supprimer le fichier temporaire après téléversement réussi
        Storage::delete($documentPath);

        return response()->json(['message' => 'Sujet ajouté avec succès', 'sujet' => $sujet], 201);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erreur lors de l\'ajout du sujet: ' . $e->getMessage()], 500);
    }
}
public function updateSujet(Request $request, $id)
{
    try {
        // Validation des données d'entrée
        $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'document' => 'sometimes|file|mimes:pdf|max:2048', // La validation du document est optionnelle
        ]);

        $sujet = Sujets::findOrFail($id);
        $updateData = [];

        if ($request->has('nom')) {
            $updateData['nom'] = $request->nom;
        }

        if ($request->hasFile('document')) {
            // Enregistrement temporaire du fichier PDF et calcul du hachage
            $documentPath = $request->file('document')->store('documents');
            $documentHash = md5_file(storage_path('app/' . $documentPath));

            // Vérification de l'existence d'un fichier avec le même hachage
            if (Sujets::where('document_hash', $documentHash)->where('id', '<>', $id)->exists()) {
                Storage::delete($documentPath); // Supprimer le fichier temporaire
                return response()->json(['error' => 'Un fichier identique existe déjà.'], 409);
            }

            // Configuration de Firebase
            $firebase = (new Factory)->withServiceAccount(storage_path('app/pfe-files-firebase-adminsdk-rp3sy-cfd99cff86.json'))->createStorage();
            $bucket = $firebase->getBucket();

            // Téléverser le fichier PDF vers Firebase Storage
            $firebaseStoragePath = 'documents/' . basename($documentPath);
            $bucket->upload(fopen(storage_path('app/' . $documentPath), 'r'), ['name' => $firebaseStoragePath]);

            // Obtenir l'URL de téléchargement public
            $downloadUrl = $bucket->object($firebaseStoragePath)->signedUrl(new \DateTime('+1 year'));

            $updateData['document'] = $downloadUrl;
            $updateData['document_hash'] = $documentHash;

            // Supprimer le fichier temporaire après téléversement réussi
            Storage::delete($documentPath);
        }

        // Mise à jour du sujet dans la base de données MySQL
        $sujet->update($updateData);

        return response()->json(['message' => 'Sujet mis à jour avec succès', 'sujet' => $sujet], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erreur lors de la mise à jour du sujet: ' . $e->getMessage()], 500);
    }
}
public function deleteSujet($id)
{
    try {
        $sujet = Sujets::findOrFail($id);

        // Supprimer le fichier du stockage Firebase
        $firebase = (new Factory)->withServiceAccount(storage_path('app/pfe-files-firebase-adminsdk-rp3sy-cfd99cff86.json'))->createStorage();
        $bucket = $firebase->getBucket();
        $firebaseStoragePath = 'documents/' . basename($sujet->document);
        $object = $bucket->object($firebaseStoragePath);
        if ($object->exists()) {
            $object->delete();
        }

        // Supprimer le sujet de la base de données
        $sujet->delete();

        return response()->json(['message' => 'Sujet supprimé avec succès'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erreur lors de la suppression du sujet: ' . $e->getMessage()], 500);
    }
}

}