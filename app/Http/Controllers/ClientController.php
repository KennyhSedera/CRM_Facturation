<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * Display a listing of clients
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            $perPage = $request->get('per_page', 5);
            $search = $request->get('search');
            $status = $request->get('status');

            if ($user->user_role === 'super_admin') {
                $query = Client::with(['user', 'company']);
                if (!empty($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('client_name', 'ILIKE', "%{$search}%")
                            ->orWhere('client_email', 'ILIKE', "%{$search}%")
                            ->orWhere('client_phone', 'ILIKE', "%{$search}%")
                            ->orWhere('client_reference', 'ILIKE', "%{$search}%");
                    });
                }
                if (!is_null($status)) {
                    $query->where('client_status', $status);
                }
                $clients = $query
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage);
                return response()->json([
                    'success' => true,
                    'message' => 'Clients retrieved successfully',
                    'data' => $clients
                ], 200);
            }
            $query = Client::where('company_id', $user->company_id)
                ->with(['user', 'company']);

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('client_name', 'ILIKE', "%{$search}%")
                        ->orWhere('client_email', 'ILIKE', "%{$search}%")
                        ->orWhere('client_phone', 'ILIKE', "%{$search}%")
                        ->orWhere('client_reference', 'ILIKE', "%{$search}%");
                });
            }

            // Status filter
            if (!is_null($status)) {
                $query->where('client_status', $status);
            }

            $clients = $query
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Clients retrieved successfully',
                'data' => $clients
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving clients',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created client
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email|unique:clients,client_email',
            'client_phone' => 'nullable|string|max:20',
            'client_cin' => 'nullable|string|max:50',
            'client_adress' => 'nullable|string|max:255',
            'client_city' => 'nullable|string|max:100',
            'client_country' => 'nullable|string|max:100',
            'client_status' => 'nullable|in:active,inactive',
            'client_note' => 'nullable|string',
            'client_reference' => 'nullable|string|max:100|unique:clients,client_reference',
            'company_id' => 'nullable|numeric',
            'user_id' => 'nullable|numeric',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $validated = $validator->validated();

            $user = Auth::user();

            // Add user_id and company_id
            $validated['user_id'] = $user->id;
            $validated['company_id'] = $user->company_id;
            $validated['client_status'] = $validated['client_status'] ?? 'active';

            // Generate reference if not provided
            if (empty($validated['client_reference'])) {
                $validated['client_reference'] = 'CLT-' . strtoupper(substr(uniqid(), -8));
            }

            $client = Client::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Client created successfully',
                'data' => $client
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating client',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified client
     */
    public function show($id, $request)
    {
        try {

            $user = Auth::user();
            $client = Client::where('user_id', $user->id)
                ->with(['quotes', 'invoices', 'user', 'company'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Client retrieved successfully',
                'data' => $client
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving client',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified client
     */
    public function update(Request $request, $id)
    {
        try {

            $user = Auth::user();
            $client = Client::where('user_id', $user->id)->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'client_name' => 'required|string|max:255',
                'client_email' => 'required|email|unique:clients,client_email,' . $id,
                'client_phone' => 'nullable|string|max:20',
                'client_cin' => 'nullable|string|max:50',
                'client_address' => 'nullable|string|max:255',
                'client_city' => 'nullable|string|max:100',
                'client_country' => 'nullable|string|max:100',
                'client_status' => 'nullable|in:active,inactive',
                'client_note' => 'nullable|string',
                'client_reference' => 'nullable|string|max:100|unique:clients,client_reference,' . $id,
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();
            $client->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Client updated successfully',
                'data' => $client->fresh()
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating client',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified client
     */
    public function destroy($id, $request)
    {
        try {

            $user = Auth::user();
            $client = Client::where('user_id', $user->id)->findOrFail($id);

            // Check if client has related quotes or invoices
            if ($client->quotes()->count() > 0 || $client->invoices()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete client with existing quotes or invoices',
                    'data' => [
                        'quotes_count' => $client->quotes()->count(),
                        'invoices_count' => $client->invoices()->count()
                    ]
                ], 409);
            }

            $client->delete();

            return response()->json([
                'success' => true,
                'message' => 'Client deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting client',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle client status
     */
    public function toggleStatus($id, $request)
    {
        try {

            $user = Auth::user();
            $client = Client::where('user_id', $user->id)->findOrFail($id);

            $client->client_status = $client->client_status === 'active' ? 'inactive' : 'active';
            $client->save();

            return response()->json([
                'success' => true,
                'message' => 'Client status updated successfully',
                'data' => [
                    'id' => $client->id,
                    'client_status' => $client->client_status
                ]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating client status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get client statistics
     */
    public function statistics($id, $request)
    {
        try {

            $user = Auth::user();
            $client = Client::where('user_id', $user->id)->findOrFail($id);

            $stats = [
                'total_quotes' => $client->quotes()->count(),
                'total_invoices' => $client->invoices()->count(),
                'pending_invoices' => $client->invoices()->where('status', 'pending')->count(),
                'paid_invoices' => $client->invoices()->where('status', 'paid')->count(),
                'total_amount' => $client->invoices()->sum('total_amount'),
                'paid_amount' => $client->invoices()->where('status', 'paid')->sum('total_amount'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Client statistics retrieved successfully',
                'data' => $stats
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getByCompany(Request $request, $id)
    {
        try {
            $perPage = $request->get('per_page', 5);
            $search = $request->get('search');
            $status = $request->get('status');

            $query = Client::where('company_id', $id)
                ->with(['user', 'company']);

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('client_name', 'LIKE', "%{$search}%")
                        ->orWhere('client_email', 'LIKE', "%{$search}%")
                        ->orWhere('client_phone', 'LIKE', "%{$search}%")
                        ->orWhere('client_reference', 'LIKE', "%{$search}%");
                });
            }

            if (!is_null($status)) {
                $query->where('client_status', $status);
            }

            $clients = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Clients retrieved successfully',
                'data' => $clients
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving clients',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
