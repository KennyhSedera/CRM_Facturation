<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QuoteController extends Controller
{
    // 1️⃣ Liste tous les devis
    public function index()
    {
        $quotes = Quote::with(['user', 'client'])->get();

        return response()->json([
            'success' => true,
            'data' => $quotes
        ]);
    }

    // 2️⃣ Crée un nouveau devis
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'total_amount' => 'required|numeric|min:0',
            'mode_paiement' => 'nullable|string|max:50',
            'quote_status' => 'nullable|string|in:pending,approved,paid,cancelled',
            'quote_date' => 'required|date',
            'client_id' => 'required|exists:clients,client_id',
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

            $quote = Quote::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Devis créé avec succès',
                'data' => $quote
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du devis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 3️⃣ Affiche un devis
    public function show($id)
    {
        $quote = Quote::with(['user', 'client'])->find($id);

        if (!$quote) {
            return response()->json([
                'success' => false,
                'message' => 'Devis non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $quote
        ]);
    }

    // 4️⃣ Mettre à jour un devis
    public function update(Request $request, $id)
    {
        $quote = Quote::find($id);

        if (!$quote) {
            return response()->json([
                'success' => false,
                'message' => 'Devis non trouvé'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'total_amount' => 'sometimes|required|numeric|min:0',
            'mode_paiement' => 'nullable|string|max:50',
            'quote_status' => 'nullable|string|in:pending,approved,paid,cancelled',
            'quote_date' => 'sometimes|required|date',
            'client_id' => 'sometimes|required|exists:clients,client_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $quote->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Devis mis à jour avec succès',
            'data' => $quote
        ]);
    }

    // 5️⃣ Supprimer un devis
    public function destroy($id)
    {
        $quote = Quote::find($id);

        if (!$quote) {
            return response()->json([
                'success' => false,
                'message' => 'Devis non trouvé'
            ], 404);
        }

        $quote->delete();

        return response()->json([
            'success' => true,
            'message' => 'Devis supprimé avec succès'
        ]);
    }
}
