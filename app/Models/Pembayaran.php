<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['id_pesanan', 'tanggal_bayar', 'metode_bayar', 'jumlah_bayar'])]
class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $primaryKey = 'id_pembayaran';

    protected function casts(): array
    {
        return ['tanggal_bayar' => 'datetime', 'jumlah_bayar' => 'decimal:2'];
    }
}
