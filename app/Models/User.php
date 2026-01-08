<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     */
    public function hasRole(string $role): bool
    {
        return $this->user_role === $role;
    }

    /**
     * Vérifier si l'utilisateur a un des rôles dans un tableau
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->user_role, $roles);
    }

    /**
     * Vérifier si l'utilisateur est super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Vérifier si l'utilisateur est admin (super_admin OU admin_company)
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin_company']);
    }

    /**
     * Vérifier si l'utilisateur est admin de sa compagnie
     */
    public function isCompanyAdmin(): bool
    {
        return $this->hasRole('admin_company');
    }

    /**
     * Vérifier si l'utilisateur est un simple utilisateur
     */
    public function isUser(): bool
    {
        return $this->hasRole('user');
    }
}
