<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class WebAppController extends Controller
{
    public function form(Request $request)
    {
        return Inertia::render('Telegram/CompanyFormTelegram', [
            'telegram_id' => $request->get('user_id'),
        ]);
    }

    // ✅ Ajouter cette méthode pour valider le formulaire d'entreprise
    public function validateCompany(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255|min:2',
            'company_email' => 'required|email|max:255',
            'company_description' => 'required|string|min:10|max:500',
            'company_phone' => 'required|string|min:8|max:20',
            'company_website' => 'nullable|url|max:255',
            'company_address' => 'required|string|min:5|max:500',
        ], [
            'company_name.required' => 'Le nom de l\'entreprise est requis',
            'company_name.min' => 'Le nom doit contenir au moins 2 caractères',
            'company_email.required' => 'L\'email est requis',
            'company_email.email' => 'L\'email doit être valide',
            'company_description.required' => 'La description est requise',
            'company_description.min' => 'La description doit contenir au moins 10 caractères',
            'company_phone.required' => 'Le téléphone est requis',
            'company_phone.min' => 'Le téléphone doit contenir au moins 8 caractères',
            'company_address.required' => 'L\'adresse est requise',
            'company_address.min' => 'L\'adresse doit contenir au moins 5 caractères',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Données validées avec succès'
        ]);
    }

    // Garder l'ancienne méthode si vous en avez besoin
    public function validate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telephone' => 'nullable|string|max:20',
            'message' => 'required|string|max:1000',
        ], [
            'nom.required' => 'Le nom est requis',
            'email.required' => 'L\'email est requis',
            'email.email' => 'L\'email doit être valide',
            'message.required' => 'Le message est requis',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Données validées avec succès'
        ]);
    }
}
