<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['id_pelanggan', 'id_pesanan', 'pesan', 'dibaca'])]
class Notifikasi extends Model
{
    protected $table = 'notifikasi';

    protected $primaryKey = 'id_notifikasi';

    protected function casts(): array
    {
        return ['dibaca' => 'boolean'];
    }
}
