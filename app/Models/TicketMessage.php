<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketMessage extends Model
{
    use HasFactory;

    /**
     * Table associée au modèle
     */
    protected $table = 'ticket_messages';

    /**
     * Colonnes autorisées pour l'assignation de masse
     */
    protected $fillable = [
        'ticket_id',
        'user_telegram_id',
        'message',
        'is_from_user',
        'attachment',
    ];

    /**
     * Colonnes qui doivent être castées en types natifs
     */
    protected $casts = [
        'ticket_id' => 'integer',
        'user_telegram_id' => 'integer',
        'is_from_user' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * Relation : Un message appartient à un ticket
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Relation : Un message appartient à un utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_telegram_id', 'telegram_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors (Getters)
    |--------------------------------------------------------------------------
    */

    /**
     * Obtenir l'emoji selon le type de message
     */
    public function getTypeEmojiAttribute(): string
    {
        return $this->is_from_user ? '👤' : '👨‍💼';
    }

    /**
     * Obtenir le label du type
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->is_from_user ? 'Utilisateur' : 'Agent';
    }

    /**
     * Obtenir le temps écoulé depuis le message
     */
    public function getTimeAgoAttribute(): string
    {
        $diff = $this->created_at->diff(now());

        if ($diff->days > 0) {
            return "Il y a {$diff->days} jour" . ($diff->days > 1 ? 's' : '');
        } elseif ($diff->h > 0) {
            return "Il y a {$diff->h} heure" . ($diff->h > 1 ? 's' : '');
        } elseif ($diff->i > 0) {
            return "Il y a {$diff->i} minute" . ($diff->i > 1 ? 's' : '');
        } else {
            return "À l'instant";
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Méthodes utiles
    |--------------------------------------------------------------------------
    */

    /**
     * Vérifier si le message est de l'utilisateur
     */
    public function isFromUser(): bool
    {
        return $this->is_from_user;
    }

    /**
     * Vérifier si le message est d'un agent
     */
    public function isFromAgent(): bool
    {
        return !$this->is_from_user;
    }

    /**
     * Vérifier si le message a une pièce jointe
     */
    public function hasAttachment(): bool
    {
        return !is_null($this->attachment);
    }

    /**
     * Formater pour l'affichage dans Telegram
     */
    public function toTelegramMessage(): string
    {
        $emoji = $this->type_emoji;
        $label = $this->type_label;
        $time = $this->created_at->format('d/m/Y H:i');

        $message = "{$emoji} <b>{$label}</b> - {$time}\n\n"
            . $this->message;

        if ($this->hasAttachment()) {
            $message .= "\n\n📎 Pièce jointe";
        }

        return $message;
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

        // Après la création d'un message
        static::created(function ($message) {
            // Logger
            \Log::info("Message added to ticket", [
                'ticket_id' => $message->ticket_id,
                'is_from_user' => $message->is_from_user,
            ]);

            // Mettre à jour la date de modification du ticket
            $message->ticket->touch();
        });
    }
}
