<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Article;

class MvtArticle extends Model
{
    protected $table = 'mvt_articles';

    protected $primaryKey = 'mvt_id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'mvtType',
        'mvt_quantity',
        'mvt_date',
        'article_id',
        'user_id'
    ];

    // Relation avec l'utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relation avec l'article
    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
