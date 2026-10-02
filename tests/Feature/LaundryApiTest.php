<?php

namespace Tests\Feature;

use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LaundryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_and_receive_a_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'nama' => 'Dina',
            'alamat' => 'Jl. Melati 1',
            'no_telepon' => '081234567890',
            'email' => 'dina@example.com',
            'password' => 'rahasia123',
        ]);

        $response->assertCreated()->assertJsonPath('success', true)->assertJsonStructure(['data' => ['id_pelanggan'], 'token']);
        $this->assertDatabaseHas('pelanggan', ['email' => 'dina@example.com']);
    }

    public function test_order_total_is_calculated_from_server_service_prices(): void
    {
        $customer = Pelanggan::create([
            'nama' => 'Dina', 'alamat' => 'Jl. Melati 1', 'no_telepon' => '081234567890',
            'email' => 'dina@example.com', 'password' => Hash::make('rahasia123'),
        ]);
        $service = Layanan::create(['nama_layanan' => 'Cuci Kering', 'harga_per_unit' => 6000, 'satuan' => 'kg']);

        $response = $this->actingAs($customer)->postJson('/api/pesanan', [
            'alamat_jemput' => 'Jl. Melati 1',
            'jadwal_jemput' => now()->addDay()->toISOString(),
            'items' => [['layanan_id' => $service->getKey(), 'berat_qty' => 2.5]],
        ]);

        $response->assertCreated()->assertJsonPath('data.total_bayar', '15000.00');
        $this->assertDatabaseHas('detail_pesanan', ['id_layanan' => $service->getKey(), 'subtotal' => 15000]);
        $this->assertDatabaseHas('notifikasi', ['id_pelanggan' => $customer->getKey()]);
    }

    public function test_customer_cannot_read_another_customers_order(): void
    {
        $owner = Pelanggan::create([
            'nama' => 'Dina', 'alamat' => 'Jl. Melati 1', 'no_telepon' => '081234567890',
            'email' => 'dina@example.com', 'password' => Hash::make('rahasia123'),
        ]);
        $other = Pelanggan::create([
            'nama' => 'Rani', 'alamat' => 'Jl. Mawar 2', 'no_telepon' => '081234567891',
            'email' => 'rani@example.com', 'password' => Hash::make('rahasia123'),
        ]);
        $service = Layanan::create(['nama_layanan' => 'Cuci Kering', 'harga_per_unit' => 6000, 'satuan' => 'kg']);
        $order = $other->pesanan()->create([
            'tanggal_masuk' => now(), 'jadwal_jemput' => now()->addDay(),
            'alamat_jemput' => 'Jl. Mawar 2', 'status_pesanan' => 'diterima',
        ]);
        $order->items()->create(['id_layanan' => $service->getKey(), 'berat_qty' => 1, 'harga_per_unit' => 6000, 'subtotal' => 6000]);

        $this->actingAs($owner)->getJson('/api/pesanan/'.$order->getKey())->assertForbidden();
    }

    public function test_staff_login_uses_hashed_password_and_returns_role(): void
    {
        User::create(['nama' => 'Kasir', 'username' => 'kasir', 'password' => Hash::make('rahasia123'), 'role' => 'kasir']);

        $this->postJson('/api/auth/login', ['identifier' => 'kasir', 'password' => 'rahasia123'])
            ->assertOk()->assertJsonPath('data.role', 'kasir')->assertJsonStructure(['token']);
    }
}
