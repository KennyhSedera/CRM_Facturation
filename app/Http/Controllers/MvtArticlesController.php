<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MvtArticle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MvtArticlesController extends Controller
{
    // 1️⃣ Liste tous les mouvements
    public function index()
    {
        $mouvements = MvtArticle::with(['user', 'article'])->get();

        return response()->json([
            'success' => true,
            'data' => $mouvements
        ]);
    }

    // 2️⃣ Crée un nouveau mouvement
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mvtType' => 'required|string|in:entrée,sortie',
            'mvt_quantity' => 'required|integer|min:1',
            'mvt_date' => 'required|date',
            'article_id' => 'required|exists:articles,article_id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        try {
            $validated = $validator->validated();
            $validated['user_id'] = $user->id;

            $mvt = MvtArticle::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Mouvement créé avec succès',
                'data' => $mvt
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du mouvement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 3️⃣ Affiche un mouvement
    public function show($id)
    {
        $mvt = MvtArticle::with(['user', 'article'])->find($id);

        if (!$mvt) {
            return response()->json([
                'success' => false,
                'message' => 'Mouvement non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $mvt
        ]);
    }

    // 4️⃣ Mettre à jour un mouvement
    public function update(Request $request, $id)
    {
        $mvt = MvtArticle::find($id);

        if (!$mvt) {
            return response()->json([
                'success' => false,
                'message' => 'Mouvement non trouvé'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'mvtType' => 'sometimes|required|string|in:entrée,sortie',
            'mvt_quantity' => 'sometimes|required|integer|min:1',
            'mvt_date' => 'sometimes|required|date',
            'article_id' => 'sometimes|required|exists:articles,article_id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $mvt->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Mouvement mis à jour avec succès',
            'data' => $mvt
        ]);
    }

    // 5️⃣ Supprimer un mouvement
    public function destroy($id)
    {
        $mvt = MvtArticle::find($id);

        if (!$mvt) {
            return response()->json(data: [
                'success' => false,
                'message' => 'Mouvement non trouvé'
            ], status: 404);
        }

        $mvt->delete();

        return response()->json(data: [
            'success' => true,
            'message' => 'Mouvement supprimé avec succès'
        ]);
    }
}
