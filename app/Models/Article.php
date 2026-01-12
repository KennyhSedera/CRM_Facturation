<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Article extends Model
{
    protected $table = 'articles';

    protected $primaryKey = 'article_id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'article_source',
        'article_unité',
        'selling_price',
        'article_name',
        'article_reference',
        'article_tva',
        'quantity_stock',
        'user_id',
        'company_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

}
