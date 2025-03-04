<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        // Ubah tabel daerah: hapus wilayah, kecamatan, kode_pos
        Schema::table('daerah', function (Blueprint $table) {
            if (Schema::hasColumn('daerah', 'wilayah')) {
                $table->dropColumn('wilayah');
            }
            if (Schema::hasColumn('daerah', 'kecamatan')) {
                $table->dropColumn('kecamatan');
            }
            if (Schema::hasColumn('daerah', 'kode_pos')) {
                $table->dropColumn('kode_pos');
            }
        });
        

        // Buat tabel wilayah yang berelasi dengan daerah melalui kota
        Schema::create('wilayah', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 255); // Nama wilayah (misal: Jabodetabek)
            $table->string('kota', 255); // Kota sebagai FK dari tabel daerah
            $table->foreign('kota')->references('kota')->on('daerah')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('wilayah');

        Schema::table('daerah', function (Blueprint $table) {
            $table->string('wilayah', 255);
            $table->string('kecamatan', 255);
            $table->string('kode_pos', 10)->index();
        });
    }
};
