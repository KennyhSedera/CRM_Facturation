<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * @property int $client_id
 * @property string $client_email
 * @property string $client_name
 * @property string|null $client_adress
 * @property string|null $client_cin
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $client_phone
 * @property string|null $client_city
 * @property string|null $client_country
 * @property string $client_status
 * @property string|null $client_note
 * @property string|null $client_reference
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Quote> $quotes
 * @property-read int|null $quotes_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client inactive()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientAdress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientCin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereUserId($value)
 * @mixin \Eloquent
 */
class Client extends Model
{
    protected $table = 'clients';

    protected $primaryKey = 'client_id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'client_name',
        'client_email',
        'client_phone',
        'client_cin',
        'client_adress',
        'client_city',
        'client_country',
        'client_status',
        'client_note',
        'client_reference',
        'user_id',
        'company_id',
    ];

    protected $casts = [
        'client_status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all quotes for this client
     */
    public function quotes()
    {
        return $this->hasMany(Quote::class, 'client_id');
    }

    /**
     * Get all invoices for this client
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'client_id');
    }

    /**
     * Get the user that owns this client
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the company that owns this client
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    /**
     * Scope for active clients
     */
    public function scopeActive($query)
    {
        return $query->where('client_status', 'active');
    }

    /**
     * Scope for inactive clients
     */
    public function scopeInactive($query)
    {
        return $query->where('client_status', 'inactive');
    }

    /**
     * Créer un nouveau client
     */
    public static function createClient(array $data, int $userId, int $companyId)
    {
        return self::create([
            'client_name' => $data['client_name'],
            'client_email' => $data['client_email'] ?? null,
            'client_phone' => $data['client_phone'] ?? null,
            'client_cin' => $data['client_cin'] ?? null,
            'client_adress' => $data['client_adress'] ?? null,
            'client_city' => $data['client_city'] ?? null,
            'client_country' => $data['client_country'] ?? null,
            'client_status' => $data['client_status'] ?? 'active',
            'client_note' => $data['client_note'] ?? null,
            'client_reference' => $data['client_reference'] ?? self::generateReference(),
            'user_id' => $userId,
            'company_id' => $companyId,
        ]);
    }

    /**
     * Générer une référence unique
     */
    public static function generateReference(): string
    {
        do {
            $reference = 'CLT-' . strtoupper(Str::random(8));
        } while (self::where('client_reference', $reference)->exists());

        return $reference;
    }

    /**
     * Vérifier si le client est actif
     */
    public function isActive(): bool
    {
        return $this->client_status === 'active';
    }

    /**
     * Obtenir le nombre de devis
     */
    public function getTotalQuotes(): int
    {
        return $this->quotes()->count();
    }

    /**
     * Obtenir le nombre de factures
     */
    public function getTotalInvoices(): int
    {
        return $this->invoices()->count();
    }

    /**
     * Formater pour affichage Telegram
     */
    public function formatForDisplay(): string
    {
        $info = "👤 <b>{$this->client_name}</b>\n";
        $info .= "📋 Réf: <code>{$this->client_reference}</code>\n";

        if ($this->client_phone) {
            $info .= "📞 {$this->client_phone}\n";
        }

        if ($this->client_email) {
            $info .= "📧 {$this->client_email}\n";
        }

        if ($this->client_cin) {
            $info .= "🆔 CIN: {$this->client_cin}\n";
        }

        if ($this->client_adress) {
            $info .= "📍 {$this->client_adress}\n";
        }

        $statusEmoji = $this->isActive() ? '✅' : '❌';
        $statusText = $this->isActive() ? 'Actif' : 'Inactif';
        $info .= "{$statusEmoji} Statut: {$statusText}\n";

        try {
            if (Schema::hasTable('quotes')) {
                $quotesCount = $this->getTotalQuotes();
            } else {
                $quotesCount = 0;
            }

            if (Schema::hasTable('invoices')) {
                $invoicesCount = $this->getTotalInvoices();
            } else {
                $invoicesCount = 0;
            }

            $info .= "📊 {$quotesCount} devis • {$invoicesCount} factures";
        } catch (\Exception $e) {

        }

        return $info;
    }

    public static function getMaxClients(string $plan): int
    {
        $limits = [
            'free' => 3,
            'premium' => 500,
            'enterprise' => 999999,
        ];

        return $limits[$plan] ?? 3;
    }
}
