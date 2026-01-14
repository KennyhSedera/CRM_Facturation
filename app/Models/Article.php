<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

/**
 * @property int $article_id
 * @property int $user_id
 * @property string|null $article_source
 * @property string|null $article_unité
 * @property string $selling_price
 * @property string $article_name
 * @property string|null $article_reference
 * @property string $article_tva
 * @property int $quantity_stock
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereArticleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereArticleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereArticleReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereArticleSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereArticleTva($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereArticleUnité($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereQuantityStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereSellingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereUserId($value)
 * @mixin \Eloquent
 */
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
