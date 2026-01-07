<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    // 1️⃣ Liste tous les articles
    public function index()
    {
        $articles = Article::with('user')->get();

        return response()->json([
            'success' => true,
            'data' => $articles
        ]);
    }

    // 2️⃣ Crée un nouvel article
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'article_name' => 'required|string|max:255',
            'article_reference' => 'required|string|max:100|unique:articles,article_reference',
            'article_source' => 'nullable|string|max:255',
            'article_unite' => 'nullable|string|max:50',
            'selling_price' => 'nullable|numeric',
            'article_tva' => 'nullable|numeric',
            'quantity_stock' => 'nullable|integer',
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

            $article = Article::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Article created successfully',
                'data' => $article
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating article',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 3️⃣ Voir un article
    public function show($id)
    {
        $article = Article::with('user')->find($id);

        if (!$article) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $article
        ]);
    }

    // 4️⃣ Mettre à jour un article
    public function update(Request $request, $id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'article_name' => 'sometimes|required|string|max:255',
            'article_reference' => 'sometimes|required|string|max:100|unique:articles,article_reference,' . $id . ',article_id',
            'article_source' => 'nullable|string|max:255',
            'article_unite' => 'nullable|string|max:50',
            'selling_price' => 'nullable|numeric',
            'article_tva' => 'nullable|numeric',
            'quantity_stock' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $article->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Article updated successfully',
            'data' => $article
        ]);
    }

    // 5️⃣ Supprimer un article
    public function destroy($id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found'
            ], 404);
        }

        $article->delete();

        return response()->json([
            'success' => true,
            'message' => 'Article deleted successfully'
        ]);
    }
}
