<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['nama', 'alamat', 'no_telepon', 'email', 'password'])]
#[Hidden(['password'])]
class Pelanggan extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'pelanggan';

    protected $primaryKey = 'id_pelanggan';

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function pesanan(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'id_pelanggan', 'id_pelanggan');
    }
}
