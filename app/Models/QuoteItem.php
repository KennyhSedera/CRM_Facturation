<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Quote;
use App\Models\Article;

class QuoteItem extends Model
{
    protected $table = 'quotes_items';

    protected $primaryKey = 'qitem_id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'unit_price',
        'quantity',
        'tva',
        'quote_id',
        'article_id',
    ];

    // Relation avec le devis
    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    // Relation avec l'article
    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
