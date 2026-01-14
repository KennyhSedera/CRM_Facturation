<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Client;

/**
 * @property int $quote_id
 * @property int $user_id
 * @property int $client_id
 * @property int $vendeur_id
 * @property string $total_amount
 * @property string|null $mode_paiement
 * @property string $quote_status
 * @property string $quote_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Client|null $client
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereModePaiement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereQuoteDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereQuoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereQuoteStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereVendeurId($value)
 * @mixin \Eloquent
 */
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
