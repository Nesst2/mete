<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTagihanIdToReturTable extends Migration
{
    public function up()
    {
        Schema::table('retur', function (Blueprint $table) {
            // Menambahkan kolom tagihan_id (unsignedBigInteger) setelah kolom id
            $table->unsignedBigInteger('tagihan_id')->after('id');

            // Jika ingin menambahkan constraint foreign key, uncomment baris berikut:
            // $table->foreign('tagihan_id')->references('id')->on('tagihan')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('retur', function (Blueprint $table) {
            // Jika menggunakan foreign key, drop terlebih dahulu
            // $table->dropForeign(['tagihan_id']);
            $table->dropColumn('tagihan_id');
        });
    }
}
