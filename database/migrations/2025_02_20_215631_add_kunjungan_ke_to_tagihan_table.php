<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('tagihan', function (Blueprint $table) {
        $table->integer('kunjungan_ke')->after('vendor_id'); // Menyimpan nomor kunjungan
    });
}

public function down()
{
    Schema::table('tagihan', function (Blueprint $table) {
        $table->dropColumn('kunjungan_ke');
    });
}

};
