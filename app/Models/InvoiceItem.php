<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read \App\Models\Invoice|null $invoice
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem query()
 * @property-read \App\Models\Article|null $article
 * @mixin \Eloquent
 */
class InvoiceItem extends Model
{
    protected $fillable = ['invoice_id', 'article_id', 'quantity', 'price'];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
