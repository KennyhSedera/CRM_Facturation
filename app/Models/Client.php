<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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

        // ✅ Vérifier si les tables existent avant de compter
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
            // Si erreur, on n'affiche pas les stats
        }

        return $info;
    }
}
