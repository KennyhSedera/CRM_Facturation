<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use SergiX44\Nutgram\Nutgram;

class TelegramController extends Controller
{

    public function handle(Request $request, Nutgram $bot)
    {
        try {
            $bot->run();

            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            Log::error('Telegram webhook error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Internal error'], 500);
        }
    }

    public function createCompany($id, Request $request, Nutgram $bot)
    {
        Log::info("Creating company via Telegram WebApp for user ID: $id");

        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255|unique:companies,company_name',
            'company_email' => 'required|email|unique:companies,company_email',
            'company_phone' => 'nullable|string|max:20',
            'company_website' => 'nullable|url|max:255',
            'company_description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);


        DB::beginTransaction();

        try {
            $validated = $validator->validated();

            $company = Company::create($validated);

            $adminUser = User::create([
                'name' => 'Admin ' . $company->company_name,
                'email' => $company->company_email,
                'password' => Hash::make($company->company_name),
                'company_id' => $company->company_id,
                'user_role' => 'admin_company',
            ]);

            DB::commit();

            return $bot->sendMessage(
                text: "✅ <b>Company created successfully</b>\n\n" .
                "Name: " . $company->company_name . "\n" .  // Display the company name
                "Email: " . $company->company_email . "\n" .  // Display the company email
                "Description: " . $company->company_description . "\n" .  // Display the company description
                "Phone: " . $company->company_phone . "\n" .  // Display the company phone
                "Website: " . $company->company_website . "\n" .  // Display the company website
                "User Role: " . $adminUser->user_role . "\n",  // Display the user role
                parse_mode: 'HTML'
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error creating company',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
