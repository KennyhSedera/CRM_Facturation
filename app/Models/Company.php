<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $company_id
 * @property string $company_email
 * @property string $company_name
 * @property string|null $company_logo
 * @property string $plan_status
 * @property string|null $company_description
 * @property string|null $company_phone
 * @property string|null $company_website
 * @property string|null $company_address
 * @property string|null $company_city
 * @property string|null $company_postal_code
 * @property string $company_country
 * @property string|null $company_registration_number
 * @property string|null $company_tax_number
 * @property \Illuminate\Support\Carbon|null $plan_start_date
 * @property \Illuminate\Support\Carbon|null $plan_end_date
 * @property bool $is_active
 * @property string $company_currency
 * @property string $company_timezone
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $client_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Article> $articles
 * @property-read int|null $articles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereClientCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyPostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyRegistrationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyTaxNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyWebsite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company wherePlanEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company wherePlanStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company wherePlanStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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

    public function articles()
    {
        return $this->hasMany(Article::class, 'company_id', 'company_id');
    }
}
