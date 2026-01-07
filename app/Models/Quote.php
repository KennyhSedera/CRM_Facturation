<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Client;

class Quote extends Model
{
    protected $table = 'quotes';

    protected $primaryKey = 'quote_id';
    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'total_amount',
        'mode_paiement',
        'quote_status',
        'quote_date',
        'user_id',
        'client_id',
    ];

    // Relation avec l'utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relation avec le client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
