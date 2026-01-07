<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    // Spécifier la clé primaire personnalisée
    protected $primaryKey = 'company_id';

    // Nom de la table
    protected $table = 'companies';

    protected $fillable = [
        'company_id',
        'company_email',
        'company_name',
        'company_logo',
        'plan_status',
        'company_description',
        'company_phone',
        'company_website',
        'company_address',
        'company_city',
        'company_postal_code',
        'company_country',
        'company_registration_number',
        'company_tax_number',
        'plan_start_date',
        'plan_end_date',
        'is_active',
        'company_currency',
        'company_timezone',
        'client_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'plan_start_date' => 'date',
        'plan_end_date' => 'date',
    ];

    // Correction de la relation (doit retourner la relation)
    public function users()
    {
        return $this->hasMany(User::class, 'company_id', 'company_id');
    }
}
