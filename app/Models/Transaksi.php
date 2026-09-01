<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaksi extends Model
{
    protected $table = 'transaksi';

    protected $fillable = [
        'user_id',
        'kode',
        'total',
        'status',
        'nama_penerima',
        'telepon',
        'alamat',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'total'   => 'decimal:2',
        ];
    }

    public const SEMUA_STATUS = ['menunggu', 'dibayar', 'dikirim', 'selesai', 'batal'];
    public const STATUS_ADMIN = ['menunggu', 'dibayar', 'dikirim', 'selesai'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransaksiDetail::class);
    }

    public function bisaDibatalkan(): bool
    {
        return $this->status === 'menunggu';
    }
}
