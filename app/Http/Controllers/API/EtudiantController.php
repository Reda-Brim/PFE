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
}
