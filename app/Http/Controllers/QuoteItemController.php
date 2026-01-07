<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QuoteItem;
use Illuminate\Support\Facades\Validator;

class QuoteItemController extends Controller
{
    // 1️⃣ Liste tous les items de devis
    public function index()
    {
        $items = QuoteItem::with(['quote', 'article'])->get();

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    // 2️⃣ Crée un nouvel item de devis
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'tva' => 'nullable|numeric|min:0',
            'quote_id' => 'required|exists:quotes,quote_id',
            'article_id' => 'required|exists:articles,article_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $item = QuoteItem::create($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Item de devis créé avec succès',
                'data' => $item
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 3️⃣ Affiche un item spécifique
    public function show($id)
    {
        $item = QuoteItem::with(['quote', 'article'])->find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $item
        ]);
    }

    // 4️⃣ Mettre à jour un item
    public function update(Request $request, $id)
    {
        $item = QuoteItem::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item non trouvé'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'unit_price' => 'sometimes|required|numeric|min:0',
            'quantity' => 'sometimes|required|integer|min:1',
            'tva' => 'nullable|numeric|min:0',
            'quote_id' => 'sometimes|required|exists:quotes,quote_id',
            'article_id' => 'sometimes|required|exists:articles,article_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $item->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Item mis à jour avec succès',
            'data' => $item
        ]);
    }

    // 5️⃣ Supprimer un item
    public function destroy($id)
    {
        $item = QuoteItem::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item non trouvé'
            ], 404);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item supprimé avec succès'
        ]);
    }
}
