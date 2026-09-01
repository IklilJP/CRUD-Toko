<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiDetail extends Model
{
    protected $table = 'transaksi_detail';

    protected $fillable = [
        'transaksi_id',
        'product_id',
        'nama_produk',
        'harga',
        'qty',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'transaksi_id' => 'integer',
            'product_id'   => 'integer',
            'harga'        => 'decimal:2',
            'qty'          => 'integer',
            'subtotal'     => 'decimal:2',
        ];
    }

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
