<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['client_id', 'user_id', 'date', 'total', 'quote_id'];

    protected $casts = [
        'date' => 'date',
        'total' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class, 'quote_id', 'quote_id');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function company()
    {
        return $this->hasOneThrough(
            Company::class,
            User::class,
            'id',
            'company_id',
            'user_id',
            'company_id'
        );
    }
}
