<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
            'company_email' => 'required|email|unique:companies,company_email',
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
            'plan_status' => 'nullable|in:free,premium,entreprise,basic',
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

        DB::beginTransaction();

        try {
            $validated = $validator->validated();

            if ($request->hasFile('company_logo')) {
                $validated['company_logo'] = $request->file('company_logo')->store('company-logos', 'public');
            }

            $validated['is_active'] = $request->has('is_active') ? true : false;
            $validated['plan_status'] = $validated['plan_status'] ?? 'trial';

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
