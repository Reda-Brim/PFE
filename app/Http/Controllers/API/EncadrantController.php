<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Equipe;
use App\Models\Sujets;
use App\Models\Projet;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;


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

    
}
