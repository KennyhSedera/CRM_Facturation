<?php
namespace App\Services;

use App\Models\Client;
use App\Models\Quote;
use Illuminate\Support\Carbon;

class CrmService
{
    public function getInactiveClients($days = 30)
    {
        return Client::whereDoesntHave('quotes', function ($query) use ($days) {
            $query->where('created_at', '>=', now()->subDays($days));
        })->get();
    }

    public function getClientsToFollowUp()
    {
        return Client::whereHas('quotes', function ($query) {
            $query->where('status', 'en attente')
                  ->where('created_at', '<', now()->subDays(7));
        })->get();
    }

    public function markClientAsContacted(Client $client)
    {
        $client->update(['last_contacted_at' => now()]);
    }
}
