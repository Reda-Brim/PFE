<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\Etudiant;
use App\Models\Equipe;
use App\Models\Encadrant;
use App\Models\Sujets;
use App\Models\Projet;
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
            'codeApoge' => 'required|max:255|unique:etudiants',
            'filiere' => 'required|string|in:BDD,SID,RES',
            'password' => 'required|string|min:5',
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
            'codeApoge' => $request->codeApoge,
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
            'encadrant_code' => 'required|max:255|unique:encadrants',
            'specialite' => 'required|string|max:255',
            'password' => 'required|string|min:5',
        ]);
    
        // Hachage du mot de passe
        $hashedPassword = Hash::make($request->password);
    
        // Création de l'utilisateur encadrant
        $encadrant = Encadrant::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'encadrant_code' => $request->encadrant_code,
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
            'codeApoge' => 'sometimes|required|max:255|unique:etudiants,codeApoge,' . $id,
            'filiere' => 'sometimes|required|string|in:BDD,SID,RES',
            'password' => 'nullable|string|min:5',
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
        if ($request->has('codeApoge')) {
            $etudiant->codeApoge = $request->codeApoge;
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
            'encadrant_code' => 'sometimes|required|max:255|unique:encadrants,encadrant_code,' . $id,
            'specialite' => 'sometimes|required|string|max:255',
            'password' => 'nullable|string|min:5',
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
        if ($request->has('encadrant_code')) {
            $encadrant->encadrant_code = $request->encadrant_code;
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

public function listEtudiantsSansEquipe()
{
    // Retrieve all students who are not in any team
    $etudiantsSansEquipe = Etudiant::whereDoesntHave('equipe1')
        ->whereDoesntHave('equipe2')
        ->whereDoesntHave('equipe3')
        ->get();

    return response()->json(['etudiants_sans_equipe' => $etudiantsSansEquipe], 200);
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

public function assignEncadrant(Request $request, $id)
{
    // Validation des données d'entrée
    $request->validate([
        'encadrant_code' => 'required|exists:encadrants,encadrant_code',
    ]);

    // Trouver l'équipe par son ID
    $equipe = Equipe::find($id);

    if (!$equipe) {
        return response()->json(['error' => 'Équipe introuvable.'], 404);
    }

    // Vérification que l'encadrant n'encadre pas plus de deux équipes
    $encadrantTeamsCount = Equipe::where('encadrant_code', $request->encadrant_code)->count();
    if ($encadrantTeamsCount >= 2) {
        return response()->json(['error' => 'Cet encadrant encadre déjà deux équipes.'], 409);
    }

    // Mettre à jour l'encadrant de l'équipe
    $equipe->encadrant_code = $request->encadrant_code;
    $equipe->save();

    return response()->json(['message' => 'Encadrant assigné avec succès', 'equipe' => $equipe], 200);
}


public function listEquipes()
{
    $equipes = Equipe::with(['etudiant1', 'etudiant2', 'etudiant3', 'encadrant'])->get();
    return response()->json(['equipes' => $equipes], 200);
}
public function addMemberToEquipe(Request $request, Equipe $equipe)
    {
        // Validation des données d'entrée
        $request->validate([
            'codeApoge' => 'required|exists:etudiants,codeApoge',
        ]);

        $etudiant = Etudiant::where('codeApoge', $request->codeApoge)->first();

        // Vérifier si l'étudiant est déjà dans une autre équipe
        $isAlreadyInAnotherEquipe = Equipe::where(function ($query) use ($etudiant) {
            $query->where('etudiant_1_codeApoge', $etudiant->codeApoge)
                  ->orWhere('etudiant_2_codeApoge', $etudiant->codeApoge)
                  ->orWhere('etudiant_3_codeApoge', $etudiant->codeApoge);
        })->exists();

        if ($isAlreadyInAnotherEquipe) {
            return response()->json(['error' => 'L\'étudiant est déjà dans une autre équipe.'], 409);
        }

        // Ajouter l'étudiant à l'équipe
        if (!$equipe->etudiant_2_codeApoge) {
            $equipe->etudiant_2_codeApoge = $etudiant->codeApoge;
        } elseif (!$equipe->etudiant_3_codeApoge) {
            $equipe->etudiant_3_codeApoge = $etudiant->codeApoge;
        } else {
            return response()->json(['error' => 'L\'équipe est déjà complète.'], 409);
        }

        $equipe->save();

        return response()->json(['message' => 'Étudiant ajouté à l\'équipe avec succès.'], 200);
    }


    public function removeMemberFromEquipe(Request $request, Equipe $equipe)
    {
        // Validation des données d'entrée
        $request->validate([
            'codeApoge' => 'required|exists:etudiants,codeApoge',
        ]);

        $codeApoge = $request->codeApoge;

        // Vérifier quel membre de l'équipe doit être supprimé
        if ($equipe->etudiant_1_codeApoge == $codeApoge) {
            // Supprimer le chef d'équipe
            $equipe->etudiant_1_codeApoge = $equipe->etudiant_2_codeApoge;
            $equipe->etudiant_2_codeApoge = $equipe->etudiant_3_codeApoge;
            $equipe->etudiant_3_codeApoge = null;
        } elseif ($equipe->etudiant_2_codeApoge == $codeApoge) {
            // Supprimer le deuxième membre
            $equipe->etudiant_2_codeApoge = $equipe->etudiant_3_codeApoge;
            $equipe->etudiant_3_codeApoge = null;
        } elseif ($equipe->etudiant_3_codeApoge == $codeApoge) {
            // Supprimer le troisième membre
            $equipe->etudiant_3_codeApoge = null;
        } else {
            return response()->json(['error' => 'L\'étudiant n\'est pas membre de cette équipe.'], 404);
        }

        $equipe->save();

        return response()->json(['message' => 'Membre supprimé de l\'équipe avec succès.'], 200);
    }

    public function deleteEquipe($id)
    {
        // Trouver l'équipe par son ID
        $equipe = Equipe::find($id);

        if (!$equipe) {
            return response()->json(['error' => 'Équipe introuvable.'], 404);
        }

        // Supprimer l'équipe
        $equipe->delete();

        return response()->json(['message' => 'Équipe supprimée avec succès.'], 200);
    }
 
    public function createEquipe(Request $request)
{
    // Validation des données d'entrée
    $request->validate([
        'etudiant_1_codeApoge' => 'required|exists:etudiants,codeApoge',
        'etudiant_2_codeApoge' => 'required|exists:etudiants,codeApoge',
        'etudiant_3_codeApoge' => 'nullable|exists:etudiants,codeApoge',
        'encadrant_code' => 'nullable|exists:encadrants,encadrant_code',
    ]);

    // Récupération des étudiants
    $etudiant1 = Etudiant::where('codeApoge', $request->etudiant_1_codeApoge)->first();
    $etudiant2 = Etudiant::where('codeApoge', $request->etudiant_2_codeApoge)->first();
    $etudiant3 = $request->etudiant_3_codeApoge ? Etudiant::where('codeApoge', $request->etudiant_3_codeApoge)->first() : null;

    // Vérification que les étudiants n'appartiennent pas déjà à une équipe
    $etudiant1Equipe = Equipe::where('etudiant_1_codeApoge', $etudiant1->codeApoge)
                        ->orWhere('etudiant_2_codeApoge', $etudiant1->codeApoge)
                        ->orWhere('etudiant_3_codeApoge', $etudiant1->codeApoge)
                        ->exists();

    $etudiant2Equipe = Equipe::where('etudiant_1_codeApoge', $etudiant2->codeApoge)
                        ->orWhere('etudiant_2_codeApoge', $etudiant2->codeApoge)
                        ->orWhere('etudiant_3_codeApoge', $etudiant2->codeApoge)
                        ->exists();

    $etudiant3Equipe = $etudiant3 ? Equipe::where('etudiant_1_codeApoge', $etudiant3->codeApoge)
                                    ->orWhere('etudiant_2_codeApoge', $etudiant3->codeApoge)
                                    ->orWhere('etudiant_3_codeApoge', $etudiant3->codeApoge)
                                    ->exists() : false;

    if ($etudiant1Equipe || $etudiant2Equipe || $etudiant3Equipe) {
        return response()->json(['error' => 'Un ou plusieurs étudiants appartiennent déjà à une autre équipe.'], 409);
    }

    // Vérification que les étudiants sont de la même filière
    if ($etudiant1->filiere !== $etudiant2->filiere || ($etudiant3 && $etudiant1->filiere !== $etudiant3->filiere)) {
        return response()->json(['error' => 'Les étudiants doivent appartenir à la même filière.'], 409);
    }

    // Vérification que l'encadrant n'encadre pas plus de deux équipes
    if ($request->encadrant_code) {
        $encadrantTeamsCount = Equipe::where('encadrant_code', $request->encadrant_code)->count();
        if ($encadrantTeamsCount >= 2) {
            return response()->json(['error' => 'Cet encadrant encadre déjà deux équipes.'], 409);
        }
    }

    // Création de l'équipe
    $equipe = Equipe::create([
        'etudiant_1_codeApoge' => $request->etudiant_1_codeApoge,
        'etudiant_2_codeApoge' => $request->etudiant_2_codeApoge,
        'etudiant_3_codeApoge' => $request->etudiant_3_codeApoge,
        'encadrant_code' => $request->encadrant_code,
    ]);

    return response()->json(['message' => 'Équipe créée avec succès', 'equipe' => $equipe], 201);
}

public function getStatistics() {
    // Gather various statistics
    $totalEtudiants = Etudiant::count();
    $totalEquipes = Equipe::count();
    $totalProjets = Projet::count();
    $completedProjects = Projet::where('etat', 'termine')->count();
    $inProgressProjects = Projet::where('etat', 'en_cours')->count();
    $pendingProjects = Projet::where('etat', 'suspendu')->count();

    return response()->json([
        'totalEtudiants' => $totalEtudiants,
        'totalEquipes' => $totalEquipes,
        'totalProjets' => $totalProjets,
        'completedProjects' => $completedProjects,
        'inProgressProjects' => $inProgressProjects,
        'pendingProjects' => $pendingProjects,
    ], 200);
}
}