<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Article;

/**
 * @property int $mvt_id
 * @property int $article_id
 * @property int $user_id
 * @property string $mvtType
 * @property int $mvt_quantity
 * @property string $mvt_date
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Article|null $article
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MvtArticle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MvtArticle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MvtArticle query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MvtArticle whereArticleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MvtArticle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MvtArticle whereMvtDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MvtArticle whereMvtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MvtArticle whereMvtQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MvtArticle whereMvtType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MvtArticle whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MvtArticle whereUserId($value)
 * @mixin \Eloquent
 */
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
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // Relation avec l'article
    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id', 'article_id');
    }
}
