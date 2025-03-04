<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        // Ubah ke tipe string sementara
        Schema::table('tagihan', function (Blueprint $table) {
            $table->string('status_kunjungan')->change();
        });

        // Update semua nilai agar sesuai dengan enum baru
        DB::table('tagihan')
            ->whereNotIn('status_kunjungan', ['ada orang', 'tidak ada orang', 'masih'])
            ->update(['status_kunjungan' => 'tidak ada orang']);

        // Ubah kembali ke enum dengan tambahan opsi 'masih'
        Schema::table('tagihan', function (Blueprint $table) {
            $table->enum('status_kunjungan', ['ada orang', 'tidak ada orang', 'masih'])->change();
        });
    }

    public function down()
    {
        // Rollback ke enum semula
        Schema::table('tagihan', function (Blueprint $table) {
            $table->string('status_kunjungan')->change();
        });

        DB::table('tagihan')
            ->where('status_kunjungan', 'masih')
            ->update(['status_kunjungan' => 'tidak ada orang']);

        Schema::table('tagihan', function (Blueprint $table) {
            $table->enum('status_kunjungan', ['ada orang', 'tidak ada orang'])->change();
        });
    }
};

