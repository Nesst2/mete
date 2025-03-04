<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('tagihan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->decimal('uang_masuk', 15, 2);
            $table->foreignId('daerah_id')->constrained('daerah')->onDelete('cascade');
            $table->date('tanggal_masuk')->nullable();
            $table->enum('status_kunjungan', ['ada orang', 'tidak ada orang']);
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('tagihan');
    }
};

