<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeDaerahIdNullableInTagihanTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('tagihan', function (Blueprint $table) {
            // Ubah kolom daerah_id menjadi nullable
            $table->unsignedBigInteger('daerah_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('tagihan', function (Blueprint $table) {
            // Kembalikan kolom daerah_id menjadi tidak nullable
            $table->unsignedBigInteger('daerah_id')->nullable(false)->change();
        });
    }
}
