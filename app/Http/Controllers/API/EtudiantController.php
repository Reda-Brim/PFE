<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Equipe;
use App\Models\Sujets;
use App\Models\Projet;
use App\Models\Tache;
use App\Models\Document;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class EtudiantController extends Controller
{
    public function getAssignedSubject(Request $request)
    {
        $etudiant = Auth::user();
        $equipe = Equipe::where('etudiant_1_codeApoge', $etudiant->codeApoge)
                    ->orWhere('etudiant_2_codeApoge', $etudiant->codeApoge)
                    ->orWhere('etudiant_3_codeApoge', $etudiant->codeApoge)
                    ->first();

        if (!$equipe) {
            return response()->json(['error' => 'Vous ne faites partie d\'aucune équipe.'], 404);
        }

        $projet = Projet::where('equipe_id', $equipe->id)->first();

        if (!$projet) {
            return response()->json(['error' => 'Aucun sujet n\'a été assigné à votre équipe.'], 404);
        }

        $sujet = $projet->sujet;

        return response()->json(['sujet' => $sujet], 200);
    }

    public function getAssignedTasks(Request $request)
    {
        $etudiant = Auth::user();
        $equipe = Equipe::where('etudiant_1_codeApoge', $etudiant->codeApoge)
                    ->orWhere('etudiant_2_codeApoge', $etudiant->codeApoge)
                    ->orWhere('etudiant_3_codeApoge', $etudiant->codeApoge)
                    ->first();

        if (!$equipe) {
            return response()->json(['error' => 'Vous ne faites partie d\'aucune équipe.'], 404);
        }

        $projet = Projet::where('equipe_id', $equipe->id)->first();

        if (!$projet) {
            return response()->json(['error' => 'Aucun sujet n\'a été assigné à votre équipe.'], 404);
        }

        $taches = Tache::where('projet_id', $projet->id)->with('documents')->get();

        return response()->json(['taches' => $taches], 200);
    }

    public function getEquipeMembers(Request $request)
{
    $etudiant = Auth::user();
    $equipe = Equipe::where('etudiant_1_codeApoge', $etudiant->codeApoge)
                ->orWhere('etudiant_2_codeApoge', $etudiant->codeApoge)
                ->orWhere('etudiant_3_codeApoge', $etudiant->codeApoge)
                ->first();

    if (!$equipe) {
        return response()->json(['error' => 'Vous ne faites partie d\'aucune équipe.'], 404);
    }

    $members = [
        'etudiant1' => $equipe->etudiant1,
        'etudiant2' => $equipe->etudiant2,
        'etudiant3' => $equipe->etudiant3,
    ];

    return response()->json(['members' => $members], 200);
}
public function changePassword(Request $request)
    {
        // Validation des données d'entrée
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:4|confirmed',
        ]);

        $etudiant = Auth::user();

        // Vérification du mot de passe actuel
        if (!Hash::check($request->old_password, $etudiant->password)) {
            return response()->json(['error' => 'Le mot de passe actuel est incorrect.'], 400);
        }

        // Mise à jour du mot de passe
        $etudiant->password = Hash::make($request->new_password);
        $etudiant->save();

        return response()->json(['message' => 'Le mot de passe a été changé avec succès.'], 200);
    }
    public function updateTache(Request $request, $tacheId)
    {
        $tache = Tache::findOrFail($tacheId);

        // Validation des données d'entrée avec les règles conditionnelles
        $request->validate([
            'etat' => 'required|in:todo,encours,toreview,termine',
            'document' => 'nullable|file|mimes:pdf|max:2048', // Optional document
        ]);

        // Vérification que l'étudiant fait partie de l'équipe liée à cette tâche
        $etudiant = Auth::user();
        $equipe = $tache->projet->equipe;

        if ($equipe->etudiant_1_codeApoge !== $etudiant->codeApoge &&
            $equipe->etudiant_2_codeApoge !== $etudiant->codeApoge &&
            $equipe->etudiant_3_codeApoge !== $etudiant->codeApoge) {
            return response()->json(['error' => 'Vous n\'êtes pas autorisé à modifier cette tâche.'], 403);
        }

        // Mise à jour de l'état de la tâche
        $tache->etat = $request->etat;
        $tache->save();

        // Gérer le document s'il est fourni
        if ($request->hasFile('document')) {
            // Supprimer l'ancien document s'il existe
            $existingDocument = Document::where('tache_id', $tache->id)->first();
            if ($existingDocument) {
                $firebase = (new Factory)
                    ->withServiceAccount(storage_path('app/pfe-files-firebase-adminsdk-rp3sy-cfd99cff86.json'))
                    ->createStorage();
                $bucket = $firebase->getBucket();
                $bucket->object('documents/' . basename($existingDocument->lien))->delete();
                $existingDocument->delete();
            }

            // Enregistrer le nouveau document
            $documentPath = $request->file('document')->store('documents');
            $firebase = (new Factory)
                ->withServiceAccount(storage_path('app/pfe-files-firebase-adminsdk-rp3sy-cfd99cff86.json'))
                ->createStorage();
            $bucket = $firebase->getBucket();

            $firebaseStoragePath = 'documents/' . basename($documentPath);
            $bucket->upload(fopen(storage_path('app/' . $documentPath), 'r'), ['name' => $firebaseStoragePath]);

            // Obtenir l'URL de téléchargement public
            $downloadUrl = $bucket->object($firebaseStoragePath)->signedUrl(new \DateTime('+1 year'));

            // Enregistrer le lien dans la base de données
            Document::create([
                'tache_id' => $tache->id,
                'lien' => $downloadUrl,
            ]);

            // Supprimer le fichier local après le téléversement
            Storage::delete($documentPath);
        }

        return response()->json(['message' => 'Tâche mise à jour avec succès', 'tache' => $tache], 200);
    }

}
