<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('kode_vendor', 10)->unique();
            $table->string('nama', 255);
            $table->string('keterangan', 255);
            $table->string('jam_operasional', 50);
            $table->string('nomor_hp', 20)->unique();
            $table->text('location_link')->nullable();
            $table->string('gambar_vendor', 255)->nullable();
            //harus ada kolom daerah_id untuk menghubungkan daerah dengan vendor berdasarkan daerah /kota
            $table->enum('status', ['aktif', 'nonaktif', 'diblokir', 'menunggu_verifikasi'])->default('aktif');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('vendors');
    }
};

