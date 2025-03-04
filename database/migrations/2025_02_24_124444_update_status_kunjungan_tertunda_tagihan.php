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
            ->whereNotIn('status_kunjungan', ['ada orang', 'tidak ada orang', 'masih', 'tertunda'])
            ->update(['status_kunjungan' => 'tidak ada orang']);

        // Ubah kembali ke enum dengan tambahan opsi 'masih' dan 'tertunda'
        Schema::table('tagihan', function (Blueprint $table) {
            $table->enum('status_kunjungan', ['ada orang', 'tidak ada orang', 'masih', 'tertunda'])->change();
        });
    }

    public function down()
    {
        // Rollback ke tipe string terlebih dahulu
        Schema::table('tagihan', function (Blueprint $table) {
            $table->string('status_kunjungan')->change();
        });

        // Update nilai 'masih' dan 'tertunda' agar sesuai dengan enum semula
        DB::table('tagihan')
            ->where('status_kunjungan', 'masih')
            ->update(['status_kunjungan' => 'tidak ada orang']);

        DB::table('tagihan')
            ->where('status_kunjungan', 'tertunda')
            ->update(['status_kunjungan' => 'ada orang']);

        // Ubah kembali ke enum semula tanpa opsi 'tertunda'
        Schema::table('tagihan', function (Blueprint $table) {
            $table->enum('status_kunjungan', ['ada orang', 'tidak ada orang', 'masih'])->change();
        });
    }
};
