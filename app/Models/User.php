<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $company_id
 * @property string $user_role
 * @property int|null $telegram_id
 * @property string|null $avatar
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTelegramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUserRole($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_role',
        'company_id',
        'telegram_id',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'telegram_id' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Méthodes de vérification des rôles (Web)
    |--------------------------------------------------------------------------
    */

    public function hasRole(string $role): bool
    {
        return $this->user_role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->user_role, $roles);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin_company']);
    }

    public function isCompanyAdmin(): bool
    {
        return $this->hasRole('admin_company');
    }

    public function isUser(): bool
    {
        return $this->hasRole('user');
    }

    /*
    |--------------------------------------------------------------------------
    | Méthodes Telegram
    |--------------------------------------------------------------------------
    */

    public static function findByTelegramId(int $telegramId): ?self
    {
        return self::with('company')->where('telegram_id', $telegramId)->first();
    }

    public function canAccess(bool $requireCompany = true): bool
    {
        if ($requireCompany) {
            if (!$this->company_id) {
                return false;
            }

            if (!$this->company || !$this->company->is_active) {
                return false;
            }
        }

        return true;
    }

    public function getAccessDeniedMessage(bool $requireCompany = true): ?string
    {
        if ($requireCompany) {
            if (!$this->company_id) {
                return "⚠️ <b>Entreprise requise</b>\n\n"
                    . "Vous devez d'abord créer une entreprise.\n"
                    . "Utilisez /createcompany pour commencer.";
            }

            if (!$this->company || !$this->company->is_active) {
                return "🚫 <b>Entreprise inactive</b>\n\n"
                    . "Votre entreprise est actuellement inactive.\n\n"
                    . "Raisons possibles :\n"
                    . "• Abonnement expiré\n"
                    . "• Suspension administrative\n"
                    . "• Paiement en attente\n\n"
                    . "Utilisez /subscription pour renouveler votre abonnement.";
            }
        }

        return null;
    }

    public function checkAccessOrFail(Nutgram $bot, bool $requireCompany = true): bool
    {
        $errorMessage = $this->getAccessDeniedMessage($requireCompany);

        if ($errorMessage) {
            $bot->sendMessage(
                $errorMessage,
                parse_mode: ParseMode::HTML
            );
            return false;
        }

        return true;
    }

    public static function checkTelegramAccess(Nutgram $bot, bool $requireCompany = true): ?self
    {
        $user = self::findByTelegramId($bot->userId());

        if (!$user) {
            $bot->sendMessage(
                "❌ <b>Compte non trouvé</b>\n\n"
                . "Vous devez d'abord créer un compte.\n"
                . "Utilisez /start pour commencer.",
                parse_mode: \SergiX44\Nutgram\Telegram\Properties\ParseMode::HTML
            );
            return null;
        }

        if (!$user->checkAccessOrFail($bot, $requireCompany)) {
            return null;
        }

        return $user;
    }

    public function isTelegramAdmin(): bool
    {
        if (!$this->telegram_id) {
            return false;
        }

        $adminIds = explode(',', env('TELEGRAM_ADMIN_IDS', ''));
        $adminIds = array_map('trim', $adminIds);

        return in_array($this->telegram_id, $adminIds);
    }

    public function checkTelegramAdminOrFail(Nutgram $bot): bool
    {
        if (!$this->isTelegramAdmin()) {
            $bot->sendMessage("❌ Commande réservée aux administrateurs.");
            return false;
        }

        return true;
    }

    public static function checkTelegramAdminAccess(Nutgram $bot): ?self
    {
        $user = self::checkTelegramAccess($bot, requireCompany: false);
        if (!$user)
            return null;

        if (!$user->checkTelegramAdminOrFail($bot)) {
            return null;
        }

        return $user;
    }

    public static function checkCompanyExistsForUser(Nutgram $bot): bool
    {
        $user = User::where('telegram_id', $bot->userId())->first();

        if (!$user) {
            return true;
        }

        if ($user->company_id) {
            $company = Company::find($user->company_id);

            $bot->sendMessage(
                "ℹ️ <b>Entreprise existante</b>\n\n"
                . "Vous êtes déjà membre de l'entreprise:\n"
                . "📌 <b>Nom:</b> " . e($company->company_name) . "\n"
                . "📧 <b>Email:</b> " . e($company->company_email) . "\n\n"
                . "Vous ne pouvez pas créer une nouvelle entreprise.",
                parse_mode: ParseMode::HTML
            );
            return false;
        }

        return true;
    }
}
