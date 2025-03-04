<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('retur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->decimal('nominal_debet', 15, 2);
            $table->unsignedInteger('jumlah_retur');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('retur');
    }
};

