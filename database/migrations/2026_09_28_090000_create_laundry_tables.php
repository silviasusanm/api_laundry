<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id('id_pelanggan');
            $table->string('nama', 100);
            $table->string('alamat', 255);
            $table->string('no_telepon', 15);
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->timestamps();
        });

        Schema::create('user', function (Blueprint $table) {
            $table->id('id_user');
            $table->string('nama', 100);
            $table->string('username', 50)->unique();
            $table->string('password');
            $table->enum('role', ['kasir', 'pemilik'])->default('kasir');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('layanan', function (Blueprint $table) {
            $table->id('id_layanan');
            $table->string('nama_layanan', 100);
            $table->decimal('harga_per_unit', 10, 2);
            $table->string('satuan', 20);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pesanan', function (Blueprint $table) {
            $table->id('id_pesanan');
            $table->foreignId('id_pelanggan')->constrained('pelanggan', 'id_pelanggan')->restrictOnDelete();
            $table->foreignId('id_user')->nullable()->constrained('user', 'id_user')->nullOnDelete();
            $table->dateTime('tanggal_masuk');
            $table->dateTime('tanggal_selesai')->nullable();
            $table->string('alamat_jemput', 255);
            $table->dateTime('jadwal_jemput');
            $table->enum('status_pesanan', ['diterima', 'diproses', 'selesai', 'diambil', 'ditolak'])->default('diterima');
            $table->string('catatan_penolakan')->nullable();
            $table->decimal('total_bayar', 10, 2)->default(0);
            $table->enum('metode_bayar_pilihan', ['tunai', 'transfer', 'e-wallet'])->nullable();
            $table->boolean('berat_dikonfirmasi')->default(false);
            $table->timestamps();
            $table->index(['id_pelanggan', 'status_pesanan']);
        });

        Schema::create('detail_pesanan', function (Blueprint $table) {
            $table->id('id_detail');
            $table->foreignId('id_pesanan')->constrained('pesanan', 'id_pesanan')->cascadeOnDelete();
            $table->foreignId('id_layanan')->constrained('layanan', 'id_layanan')->restrictOnDelete();
            $table->decimal('berat_qty', 8, 2);
            $table->decimal('harga_per_unit', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });

        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id('id_pembayaran');
            $table->foreignId('id_pesanan')->unique()->constrained('pesanan', 'id_pesanan')->cascadeOnDelete();
            $table->dateTime('tanggal_bayar');
            $table->enum('metode_bayar', ['tunai', 'transfer', 'e-wallet']);
            $table->decimal('jumlah_bayar', 10, 2);
            $table->timestamps();
        });

        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id('id_notifikasi');
            $table->foreignId('id_pelanggan')->constrained('pelanggan', 'id_pelanggan')->cascadeOnDelete();
            $table->foreignId('id_pesanan')->constrained('pesanan', 'id_pesanan')->cascadeOnDelete();
            $table->string('pesan');
            $table->boolean('dibaca')->default(false);
            $table->timestamps();
            $table->index(['id_pelanggan', 'dibaca']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
        Schema::dropIfExists('pembayaran');
        Schema::dropIfExists('detail_pesanan');
        Schema::dropIfExists('pesanan');
        Schema::dropIfExists('layanan');
        Schema::dropIfExists('user');
        Schema::dropIfExists('pelanggan');
    }
};
