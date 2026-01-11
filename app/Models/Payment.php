<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Payment extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'company_id',
        'user_id',
        'payment_reference',
        'payment_method',
        'plan_type',
        'action_type',
        'amount',
        'currency',
        'transaction_id',
        'transaction_proof',
        'status',
        'confirmed_at',
        'confirmed_by',
        'notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'confirmed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Créer un nouveau paiement
     */
    public static function createPayment(array $data): self
    {
        return self::create([
            'company_id' => $data['company_id'],
            'user_id' => $data['user_id'],
            'payment_reference' => self::generateReference(),
            'payment_method' => $data['payment_method'],
            'plan_type' => $data['plan_type'],
            'action_type' => $data['action_type'],
            'transaction_proof' => $data['transaction_proof'] ?? null,
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'MGA',
            'status' => 'pending',
        ]);
    }

    /**
     * Générer une référence unique
     */
    public static function generateReference(): string
    {
        do {
            $reference = 'PAY-' . strtoupper(Str::random(10));
        } while (self::where('payment_reference', $reference)->exists());

        return $reference;
    }

    /**
     * Confirmer le paiement et activer le plan
     */
    public function confirm(int $adminId, ?string $notes = null): bool
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => $adminId,
            'notes' => $notes,
        ]);

        // Activer ou renouveler le plan
        return $this->activatePlan();
    }

    /**
     * Activer le plan après confirmation
     */
    private function activatePlan(): bool
    {
        $company = $this->company;

        if (!$company) {
            return false;
        }

        $currentEndDate = Carbon::parse($company->plan_end_date);
        $now = now();

        // Si le plan est expiré, on part d'aujourd'hui
        // Sinon, on ajoute 30 jours à la date de fin actuelle
        if ($currentEndDate->isPast()) {
            $newStartDate = $now;
            $newEndDate = $now->copy()->addMonth();
        } else {
            $newStartDate = $currentEndDate;
            $newEndDate = $currentEndDate->copy()->addMonth();
        }

        return $company->update([
            'plan_status' => $this->plan_type,
            'plan_start_date' => $newStartDate,
            'plan_end_date' => $newEndDate,
            'is_active' => true,
        ]);
    }

    /**
     * Rejeter le paiement
     */
    public function reject(int $adminId, string $reason): bool
    {
        return $this->update([
            'status' => 'rejected',
            'confirmed_by' => $adminId,
            'notes' => $reason,
        ]);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Formater pour l'affichage
     */
    public function formatForDisplay(): string
    {
        $statusEmoji = [
            'pending' => '⏳',
            'confirmed' => '✅',
            'rejected' => '❌',
            'cancelled' => '🚫',
        ];

        $methodEmoji = [
            'mobile_money' => '💳',
            'bank_transfer' => '🏦',
        ];

        $info = "{$statusEmoji[$this->status]} <b>Paiement {$this->payment_reference}</b>\n\n";
        $info .= "📦 Plan : <b>" . strtoupper($this->plan_type) . "</b>\n";
        $info .= "🔄 Type : " . match ($this->action_type) {
            'renew' => 'Renouvellement',
            'new' => 'Création',
            'upgrade' => 'Upgrade',
            default => ucfirst($this->action_type)
        } . "\n";
        $info .= "{$methodEmoji[$this->payment_method]} Méthode : " . ucfirst(str_replace('_', ' ', $this->payment_method)) . "\n";
        $info .= "💰 Montant : <b>" . number_format($this->amount, 0, ',', ' ') . " {$this->currency}</b>\n";
        $info .= "📅 Date : " . $this->created_at->format('d/m/Y H:i') . "\n";
        $info .= "🔖 Statut : <b>" . ucfirst($this->status) . "</b>\n";

        if ($this->confirmed_at) {
            $info .= "✅ Confirmé le : " . $this->confirmed_at->format('d/m/Y H:i') . "\n";
        }

        if ($this->notes) {
            $info .= "\n📝 Note : {$this->notes}";
        }

        return $info;
    }

    public function getActionTypeLabel(): string
    {
        return match ($this->action_type) {
            'renew' => 'Renouvellement',
            'new' => 'Création',
            'upgrade' => 'Upgrade',
            default => ucfirst($this->action_type)
        };
    }

    /**
     * Obtenir les prix des plans
     */
    public static function getPlanPrices(): array
    {
        return [
            'premium' => (config('subscription.plans.PREMIUM.price') * 1000) ?? 9900,
            'enterprise' => (config('subscription.plans.ENTERPRISE.price') * 1000) ?? 14900,
        ];
    }

    public static function getPlanPrice(string $plan): int
    {
        $planKey = strtoupper($plan);
        $price = config("subscription.plans.$planKey.price", 0);
        return (int) ($price * 1000);
    }

}
