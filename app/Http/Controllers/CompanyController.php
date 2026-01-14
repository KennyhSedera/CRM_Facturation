<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    public function index()
    {
        try {
            $companies = Company::with('users')->get();

            return response()->json([
                'success' => true,
                'message' => 'Companies retrieved successfully',
                'data' => $companies
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving companies',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255|unique:companies,company_name',
            'company_email' => [
                'required',
                'email',
                Rule::unique('companies', 'company_email'),
                function ($attribute, $value, $fail) {
                    if (User::where('email', $value)->exists()) {
                        $fail('Cet email est déjà utilisé par un utilisateur.');
                    }
                }
            ],
            'company_phone' => 'nullable|string|max:20',
            'company_website' => 'nullable|max:255',
            'company_address' => 'nullable|string|max:255',
            'company_city' => 'nullable|string|max:100',
            'company_postal_code' => 'nullable|string|max:20',
            'company_country' => 'nullable|string|max:100',
            'company_registration_number' => 'nullable|string|max:100',
            'company_tax_number' => 'nullable|string|max:100',
            'company_description' => 'nullable|string',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'plan_status' => 'nullable|in:free,premium,entreprise,basic',
            'plan_start_date' => 'nullable|date',
            'plan_end_date' => 'nullable|date|after:plan_start_date',
            'company_currency' => 'nullable|string|max:3',
            'company_timezone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ], [
            'company_name.required' => 'Le nom de l’entreprise est obligatoire.',
            'company_name.string' => 'Le nom de l’entreprise doit être une chaîne de caractères.',
            'company_name.max' => 'Le nom de l’entreprise ne peut pas dépasser 255 caractères.',
            'company_name.unique' => 'Ce nom d’entreprise existe déjà.',

            'company_email.required' => 'L’email de l’entreprise est obligatoire.',
            'company_email.email' => 'L’email de l’entreprise doit être une adresse valide.',
            'company_email.unique' => 'Cet email est déjà utilisé.',

            'company_phone.string' => 'Le téléphone doit être une chaîne de caractères.',
            'company_phone.max' => 'Le téléphone ne peut pas dépasser 20 caractères.',

            'company_website.max' => 'Le site web ne peut pas dépasser 255 caractères.',

            'company_address.string' => 'L’adresse doit être une chaîne de caractères.',
            'company_address.max' => 'L’adresse ne peut pas dépasser 255 caractères.',

            'company_city.string' => 'La ville doit être une chaîne de caractères.',
            'company_city.max' => 'La ville ne peut pas dépasser 100 caractères.',

            'company_postal_code.string' => 'Le code postal doit être une chaîne de caractères.',
            'company_postal_code.max' => 'Le code postal ne peut pas dépasser 20 caractères.',

            'company_country.string' => 'Le pays doit être une chaîne de caractères.',
            'company_country.max' => 'Le pays ne peut pas dépasser 100 caractères.',

            'company_registration_number.string' => 'Le numéro d’enregistrement doit être une chaîne.',
            'company_registration_number.max' => 'Le numéro d’enregistrement ne peut pas dépasser 100 caractères.',

            'company_tax_number.string' => 'Le numéro de TVA doit être une chaîne.',
            'company_tax_number.max' => 'Le numéro de TVA ne peut pas dépasser 100 caractères.',

            'company_logo.image' => 'Le logo doit être une image.',
            'company_logo.mimes' => 'Le logo doit être au format jpeg, png, jpg ou svg.',
            'company_logo.max' => 'Le logo ne peut pas dépasser 2 Mo.',

            'plan_status.in' => 'Le statut du plan doit être : free, premium, entreprise ou basic.',

            'plan_start_date.date' => 'La date de début du plan doit être une date valide.',
            'plan_end_date.date' => 'La date de fin du plan doit être une date valide.',
            'plan_end_date.after' => 'La date de fin doit être après la date de début.',

            'company_currency.string' => 'La devise doit être une chaîne de caractères.',
            'company_currency.max' => 'La devise ne peut pas dépasser 3 caractères.',

            'company_timezone.string' => 'Le fuseau horaire doit être une chaîne de caractères.',
            'company_timezone.max' => 'Le fuseau horaire ne peut pas dépasser 50 caractères.',

            'is_active.boolean' => 'Le statut actif doit être vrai ou faux.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $validated = $validator->validated();

            if ($request->hasFile('company_logo')) {
                $validated['company_logo'] = $request->file('company_logo')->store('company-logos', 'public');
            }

            $validated['is_active'] = true;
            $validated['plan_status'] = $validated['plan_status'] ?? 'free';

            $company = Company::create($validated);

            $adminUser = User::create([
                'name' => 'Admin ' . $company->company_name,
                'email' => $company->company_email,
                'password' => Hash::make($company->company_name),
                'company_id' => $company->company_id,
                'user_role' => 'admin_company',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Company and admin user created successfully',
                'data' => [
                    'company' => $company,
                    'admin_user' => [
                        'id' => $adminUser->id,
                        'name' => $adminUser->name,
                        'email' => $adminUser->email,
                        'user_role' => $adminUser->user_role,
                    ]
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($validated['company_logo'])) {
                Storage::disk('public')->delete($validated['company_logo']);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error creating company',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified company
     */
    public function show($id)
    {
        try {
            $company = Company::with('users')->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Company retrieved successfully',
                'data' => $company
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving company',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified company
     */
    public function update(Request $request, $id)
    {
        try {
            $company = Company::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'company_name' => 'required|string|max:255',
                'company_email' => 'required|email|unique:companies,company_email,' . $id . ',company_id',
                'company_phone' => 'nullable|string|max:20',
                'company_website' => 'nullable|url|max:255',
                'company_address' => 'nullable|string|max:255',
                'company_city' => 'nullable|string|max:100',
                'company_postal_code' => 'nullable|string|max:20',
                'company_country' => 'nullable|string|max:100',
                'company_registration_number' => 'nullable|string|max:100',
                'company_tax_number' => 'nullable|string|max:100',
                'company_description' => 'nullable|string',
                'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
                'plan_status' => 'nullable|in:trial,active,expired,cancelled',
                'plan_start_date' => 'nullable|date',
                'plan_end_date' => 'nullable|date|after:plan_start_date',
                'company_currency' => 'nullable|string|max:3',
                'company_timezone' => 'nullable|string|max:50',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Handle logo upload
            if ($request->hasFile('company_logo')) {
                // Delete old logo
                if ($company->company_logo) {
                    Storage::disk('public')->delete($company->company_logo);
                }
                $validated['company_logo'] = $request->file('company_logo')->store('company-logos', 'public');
            }

            $validated['is_active'] = $request->has('is_active') ? true : false;

            $company->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Company updated successfully',
                'data' => $company->fresh()
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating company',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified company
     */
    public function destroy($id)
    {
        try {
            $company = Company::findOrFail($id);

            // Delete logo if exists
            if ($company->company_logo) {
                Storage::disk('public')->delete($company->company_logo);
            }

            $company->delete();

            return response()->json([
                'success' => true,
                'message' => 'Company deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting company',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle company active status
     */
    public function toggleStatus($id)
    {
        try {
            $company = Company::findOrFail($id);
            $company->is_active = !$company->is_active;
            $company->save();

            return response()->json([
                'success' => true,
                'message' => 'Company status updated successfully',
                'data' => [
                    'company_id' => $company->company_id,
                    'is_active' => $company->is_active
                ]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating company status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get company users
     */
    public function getUsers($id)
    {
        try {
            $company = Company::with('users')->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Company users retrieved successfully',
                'data' => $company->users
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving company users',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
