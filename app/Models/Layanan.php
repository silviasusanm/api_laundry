<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'nama_layanan',
    'harga_per_unit',
    'satuan',
    'is_active'
])]
class Layanan extends Model
{
    protected $table = 'layanans';

    protected $primaryKey = 'id_layanan';

    protected function casts(): array
    {
        return [
            'harga_per_unit' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}