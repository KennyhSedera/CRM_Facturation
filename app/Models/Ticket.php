<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Ticket extends Model
{
    use HasFactory;

    /**
     * Table associée au modèle
     */
    protected $table = 'tickets';

    /**
     * Colonnes autorisées pour l'assignation de masse
     */
    protected $fillable = [
        'user_telegram_id',
        'username',
        'category',
        'subject',
        'description',
        'priority',
        'status',
        'attachment',
        'assigned_to',
        'closed_at',
    ];

    /**
     * Colonnes qui doivent être castées en types natifs
     */
    protected $casts = [
        'user_telegram_id' => 'integer',
        'assigned_to' => 'integer',
        'closed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Valeurs par défaut des attributs
     */
    protected $attributes = [
        'status' => 'open',
        'priority' => 'normal',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * Relation : Un ticket appartient à un utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_telegram_id', 'telegram_id');
    }

    /**
     * Relation : Un ticket peut être assigné à un agent
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to', 'telegram_id');
    }

    /**
     * Relation : Un ticket a plusieurs messages
     */
    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors (Getters)
    |--------------------------------------------------------------------------
    */

    /**
     * Obtenir l'emoji du statut
     */
    public function getStatusEmojiAttribute(): string
    {
        return match ($this->status) {
            'open' => '🆕',
            'in_progress' => '⚙️',
            'waiting' => '⏳',
            'closed' => '✅',
            'cancelled' => '❌',
            default => '❓',
        };
    }

    /**
     * Obtenir l'emoji de la priorité
     */
    public function getPriorityEmojiAttribute(): string
    {
        return match ($this->priority) {
            'low' => '🟢',
            'normal' => '🟡',
            'high' => '🔴',
            'urgent' => '🚨',
            default => '⚪',
        };
    }

    /**
     * Obtenir le label du statut en français
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open' => 'Ouvert',
            'in_progress' => 'En cours',
            'waiting' => 'En attente',
            'closed' => 'Fermé',
            'cancelled' => 'Annulé',
            default => 'Inconnu',
        };
    }

    /**
     * Obtenir le label de la priorité en français
     */
    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'Faible',
            'normal' => 'Normal',
            'high' => 'Élevée',
            'urgent' => 'Urgent',
            default => 'Non définie',
        };
    }

    /**
     * Obtenir la durée depuis la création
     */
    public function getAgeAttribute(): string
    {
        $diff = $this->created_at->diff(now());

        if ($diff->days > 0) {
            return $diff->days . ' jour' . ($diff->days > 1 ? 's' : '');
        } elseif ($diff->h > 0) {
            return $diff->h . ' heure' . ($diff->h > 1 ? 's' : '');
        } else {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        }
    }

    /**
     * Obtenir le temps de résolution (si fermé)
     */
    public function getResolutionTimeAttribute(): ?string
    {
        if (!$this->closed_at) {
            return null;
        }

        $diff = $this->created_at->diff($this->closed_at);

        if ($diff->days > 0) {
            return $diff->days . ' jour' . ($diff->days > 1 ? 's' : '');
        } elseif ($diff->h > 0) {
            return $diff->h . ' heure' . ($diff->h > 1 ? 's' : '');
        } else {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (Requêtes réutilisables)
    |--------------------------------------------------------------------------
    */

    /**
     * Scope : Tickets ouverts
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope : Tickets fermés
     */
    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', 'closed');
    }

    /**
     * Scope : Tickets en cours
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope : Tickets par priorité
     */
    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope : Tickets urgents
     */
    public function scopeUrgent(Builder $query): Builder
    {
        return $query->where('priority', 'urgent');
    }

    /**
     * Scope : Tickets d'un utilisateur
     */
    public function scopeForUser(Builder $query, int $telegramId): Builder
    {
        return $query->where('user_telegram_id', $telegramId);
    }

    /**
     * Scope : Tickets non assignés
     */
    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull('assigned_to');
    }

    /**
     * Scope : Tickets assignés à un agent
     */
    public function scopeAssignedTo(Builder $query, int $agentId): Builder
    {
        return $query->where('assigned_to', $agentId);
    }

    /**
     * Scope : Tickets par catégorie
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope : Tickets récents (dernières 24h)
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->subDay());
    }

    /**
     * Scope : Tickets anciens (plus de 7 jours sans réponse)
     */
    public function scopeOld(Builder $query): Builder
    {
        return $query->where('created_at', '<=', now()->subDays(7))
            ->whereIn('status', ['open', 'waiting']);
    }

    /*
    |--------------------------------------------------------------------------
    | Méthodes utiles
    |--------------------------------------------------------------------------
    */

    /**
     * Vérifier si le ticket est ouvert
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Vérifier si le ticket est fermé
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Vérifier si le ticket est en cours
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Vérifier si le ticket est assigné
     */
    public function isAssigned(): bool
    {
        return !is_null($this->assigned_to);
    }

    /**
     * Vérifier si le ticket est urgent
     */
    public function isUrgent(): bool
    {
        return $this->priority === 'urgent' || $this->priority === 'high';
    }

    /**
     * Clôturer le ticket
     */
    public function close(): bool
    {
        return $this->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }

    /**
     * Réouvrir le ticket
     */
    public function reopen(): bool
    {
        return $this->update([
            'status' => 'open',
            'closed_at' => null,
        ]);
    }

    /**
     * Assigner le ticket à un agent
     */
    public function assignTo(int $agentId): bool
    {
        return $this->update([
            'assigned_to' => $agentId,
            'status' => 'in_progress',
        ]);
    }

    /**
     * Désassigner le ticket
     */
    public function unassign(): bool
    {
        return $this->update([
            'assigned_to' => null,
            'status' => 'open',
        ]);
    }

    /**
     * Changer la priorité
     */
    public function setPriority(string $priority): bool
    {
        if (!in_array($priority, ['low', 'normal', 'high', 'urgent'])) {
            return false;
        }

        return $this->update(['priority' => $priority]);
    }

    /**
     * Changer le statut
     */
    public function setStatus(string $status): bool
    {
        $validStatuses = ['open', 'in_progress', 'waiting', 'closed', 'cancelled'];

        if (!in_array($status, $validStatuses)) {
            return false;
        }

        $data = ['status' => $status];

        // Si on ferme le ticket, on ajoute la date de fermeture
        if ($status === 'closed' || $status === 'cancelled') {
            $data['closed_at'] = now();
        }

        return $this->update($data);
    }

    /**
     * Ajouter un message au ticket
     */
    public function addMessage(string $message, bool $isFromUser = true, ?int $senderId = null): TicketMessage
    {
        return $this->messages()->create([
            'user_telegram_id' => $senderId ?? $this->user_telegram_id,
            'message' => $message,
            'is_from_user' => $isFromUser,
        ]);
    }

    /**
     * Obtenir le nombre de messages
     */
    public function getMessageCount(): int
    {
        return $this->messages()->count();
    }

    /**
     * Obtenir le dernier message
     */
    public function getLastMessage(): ?TicketMessage
    {
        return $this->messages()->latest()->first();
    }

    /**
     * Formater pour l'affichage dans Telegram
     */
    public function toTelegramMessage(): string
    {
        return "🎫 <b>Ticket #{$this->id}</b>\n\n"
            . "📝 <b>Sujet :</b> {$this->subject}\n"
            . "📂 <b>Catégorie :</b> {$this->category}\n"
            . "📊 <b>Statut :</b> {$this->status_emoji} {$this->status_label}\n"
            . "⚡ <b>Priorité :</b> {$this->priority_emoji} {$this->priority_label}\n"
            . "📅 <b>Créé le :</b> " . $this->created_at->format('d/m/Y à H:i') . "\n"
            . ($this->assigned_to ? "👤 <b>Assigné à :</b> Agent #{$this->assigned_to}\n" : "")
            . ($this->closed_at ? "✅ <b>Fermé le :</b> " . $this->closed_at->format('d/m/Y à H:i') . "\n" : "")
            . "\n💬 <b>Description :</b>\n{$this->description}";
    }

    /*
    |--------------------------------------------------------------------------
    | Events (Hooks Eloquent)
    |--------------------------------------------------------------------------
    */

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        // Avant la création
        static::creating(function ($ticket) {
            // Logger la création
            \Log::info("Creating ticket", [
                'user_id' => $ticket->user_telegram_id,
                'category' => $ticket->category,
            ]);
        });

        // Après la création
        static::created(function ($ticket) {
            // Envoyer une notification, créer un log, etc.
            \Log::info("Ticket created", ['ticket_id' => $ticket->id]);
        });

        // Avant la mise à jour
        static::updating(function ($ticket) {
            // Si le statut change vers "closed", ajouter la date
            if (
                $ticket->isDirty('status') &&
                ($ticket->status === 'closed' || $ticket->status === 'cancelled') &&
                !$ticket->closed_at
            ) {
                $ticket->closed_at = now();
            }
        });

        // Après la mise à jour
        static::updated(function ($ticket) {
            // Logger les changements importants
            if ($ticket->isDirty('status')) {
                \Log::info("Ticket status changed", [
                    'ticket_id' => $ticket->id,
                    'old_status' => $ticket->getOriginal('status'),
                    'new_status' => $ticket->status,
                ]);
            }
        });
    }
}
