<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id_pesanan', 'id_layanan', 'berat_qty', 'harga_per_unit', 'subtotal'])]
class DetailPesanan extends Model
{
    protected $table = 'detail_pesanan';

    protected $primaryKey = 'id_detail';

    protected function casts(): array
    {
        return ['berat_qty' => 'decimal:2', 'harga_per_unit' => 'decimal:2', 'subtotal' => 'decimal:2'];
    }

    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class, 'id_layanan', 'id_layanan');
    }
}
