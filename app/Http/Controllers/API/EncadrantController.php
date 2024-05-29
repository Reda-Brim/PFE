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

use Illuminate\Support\Facades\Auth;


class EncadrantController extends Controller
{
    public function listEquipes(Request $request)
    {
        $encadrantCode = $request->user()->encadrant_code;
        $equipes = Equipe::where('encadrant_code', $encadrantCode)->get();
        return response()->json(['equipes' => $equipes]);
    }

    public function listSujets()
    {
        $sujets = Sujets::all();
        return response()->json(['sujets' => $sujets]);
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
public function assignSujetToEquipe(Request $request, $equipeId)
    {
        // Validation des données d'entrée
        $request->validate([
            'sujet_id' => 'required|exists:sujets,id',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'description' => 'nullable|string',
        ]);

        // Récupération de l'équipe
        $equipe = Equipe::findOrFail($equipeId);

        // Vérification que l'encadrant est responsable de l'équipe
        if ($equipe->encadrant_code !== auth()->user()->encadrant_code) {
            return response()->json(['error' => 'Vous n\'êtes pas autorisé à attribuer des sujets à cette équipe.'], 403);
        }

        // Vérification que le sujet est disponible
        $sujet = Sujets::findOrFail($request->sujet_id);
        if (!$sujet->disponible) {
            return response()->json(['error' => 'Le sujet n\'est pas disponible.'], 409);
        }

        // Mise à jour de la disponibilité du sujet
        $sujet->disponible = false;
        $sujet->save();

        // Création du projet
        $projet = Projet::create([
            'sujet_id' => $sujet->id,
            'equipe_id' => $equipe->id,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'description' => $request->description,
            'etat' => 'en_cours',
        ]);

        return response()->json(['message' => 'Sujet attribué avec succès à l\'équipe et projet créé.', 'projet' => $projet], 200);
    }

    public function addTacheToProjet(Request $request, $projetId)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'document' => 'nullable|file|mimes:pdf|max:2048', // Optional document
        ]);

        $projet = Projet::findOrFail($projetId);

        if ($projet->equipe->encadrant_code !== Auth::user()->encadrant_code) {
            return response()->json(['error' => 'Vous n\'êtes pas autorisé à ajouter des tâches à ce projet.'], 403);
        }

        $tache = Tache::create([
            'projet_id' => $projet->id,
            'titre' => $request->titre,
            'description' => $request->description,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'etat' => 'en_cours',
        ]);

        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('documents');
            $firebase = (new Factory)
                ->withServiceAccount(storage_path('app/pfe-files-firebase-adminsdk-rp3sy-cfd99cff86.json'))
                ->createStorage();
            $bucket = $firebase->getBucket();

            $firebaseStoragePath = 'documents/' . basename($documentPath);
            $bucket->upload(fopen(storage_path('app/' . $documentPath), 'r'), ['name' => $firebaseStoragePath]);

            // Get the public download URL
            $downloadUrl = $bucket->object($firebaseStoragePath)->signedUrl(new \DateTime('+1 year'));

            Document::create([
                'tache_id' => $tache->id,
                'lien' => $downloadUrl,
            ]);

            // Delete local file after upload
            Storage::delete($documentPath);
        }

        return response()->json(['message' => 'Tâche ajoutée avec succès', 'tache' => $tache], 201);
    }

    public function updateTache(Request $request, $tacheId)
    {
        $tache = Tache::findOrFail($tacheId);

        // Validation des données d'entrée avec les règles conditionnelles
        $request->validate([
            'titre' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'date_debut' => 'sometimes|required|date',
            'date_fin' => 'sometimes|required|date|after_or_equal:date_debut',
            'etat' => 'sometimes|required|in:en_cours,terminee,suspendue',
            'document' => 'nullable|file|mimes:pdf|max:2048', // Optional document
        ]);

        // Vérification que l'encadrant est responsable du projet lié à cette tâche
        if ($tache->projet->equipe->encadrant_code !== Auth::user()->encadrant_code) {
            return response()->json(['error' => 'Vous n\'êtes pas autorisé à modifier cette tâche.'], 403);
        }

        // Mise à jour des informations de la tâche
        $tache->update($request->only(['titre', 'description', 'date_debut', 'date_fin', 'etat']));

        // Gérer le document s'il est fourni
        if ($request->hasFile('document')) {
            // Supprimer l'ancien document s'il existe
            $existingDocument = Document::where('tache_id', $tache->id)->first();
            if ($existingDocument) {
                Storage::delete('documents/' . basename($existingDocument->lien));
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

    public function deleteTache($tacheId)
    {
        $tache = Tache::findOrFail($tacheId);

        // Vérification que l'encadrant est responsable du projet lié à cette tâche
        if ($tache->projet->equipe->encadrant_code !== Auth::user()->encadrant_code) {
            return response()->json(['error' => 'Vous n\'êtes pas autorisé à supprimer cette tâche.'], 403);
        }

        // Supprimer les documents associés à la tâche
        $documents = Document::where('tache_id', $tache->id)->get();
        foreach ($documents as $document) {
            // Supprimer le fichier de Firebase Storage
            $firebase = (new Factory)
                ->withServiceAccount(storage_path('app/pfe-files-firebase-adminsdk-rp3sy-cfd99cff86.json'))
                ->createStorage();
            $bucket = $firebase->getBucket();
            $bucket->object('documents/' . basename($document->lien))->delete();

            // Supprimer l'entrée de la base de données
            $document->delete();
        }

        // Supprimer la tâche
        $tache->delete();

        return response()->json(['message' => 'Tâche supprimée avec succès'], 200);
    }
}
