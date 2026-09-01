<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Keranjang extends Model
{

    protected $table = 'keranjang';
    protected $fillable = ['user_id', 'product_id', 'qty'];

    protected function casts(): array
    {
        return [
            'user_id'    => 'integer',
            'product_id' => 'integer',
            'qty'        => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
